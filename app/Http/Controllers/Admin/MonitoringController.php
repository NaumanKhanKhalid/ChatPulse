<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\HealthSnapshot;
use App\Models\LoginLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MonitoringController extends Controller
{
    /** Security log: logins, failed attempts, logouts. */
    public function security(Request $request): View
    {
        $q = LoginLog::with('user');
        if ($event = $request->query('event')) $q->where('event', $event);
        if ($s = $request->query('q')) {
            $q->where(fn($w) => $w->where('email', 'like', "%$s%")
                ->orWhere('ip_address', 'like', "%$s%"));
        }
        $logs = $q->orderByDesc('created_at')->paginate(30)->withQueryString();

        $since = now()->subDay();
        $stats = [
            'success_24h' => LoginLog::where('event', 'success')->where('created_at', '>=', $since)->count(),
            'failed_24h'  => LoginLog::where('event', 'failed')->where('created_at', '>=', $since)->count(),
            'new_devices' => LoginLog::where('new_device', true)->where('created_at', '>=', $since)->count(),
        ];

        // IPs with repeated failures — likely brute force
        $suspects = LoginLog::select('ip_address', DB::raw('count(*) as attempts'))
            ->where('event', 'failed')->where('created_at', '>=', $since)
            ->groupBy('ip_address')->havingRaw('count(*) >= 3')
            ->orderByDesc('attempts')->limit(10)->get();

        return view('admin.security-log', compact('logs', 'stats', 'suspects'));
    }

    /** Recent exceptions parsed out of the Laravel log. */
    public function logs(Request $request): View
    {
        $path = storage_path('logs/laravel.log');
        $entries = [];
        $size = is_file($path) ? filesize($path) : 0;

        if ($size > 0) {
            // Read the tail only — these files get large
            $fh = fopen($path, 'r');
            fseek($fh, max(0, $size - 512 * 1024));
            $chunk = fread($fh, 512 * 1024);
            fclose($fh);

            // Split on the timestamp that begins every entry
            $parts = preg_split('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\]/m', $chunk, -1, PREG_SPLIT_DELIM_CAPTURE);
            for ($i = 1; $i < count($parts); $i += 2) {
                $when = $parts[$i];
                $body = trim($parts[$i + 1] ?? '');
                if ($body === '') continue;

                preg_match('/^(\w+)\.(\w+):/', $body, $m);
                $level = strtolower($m[2] ?? 'info');
                $head  = trim(preg_replace('/^\w+\.\w+:\s*/', '', strtok($body, "\n")));

                $entries[] = [
                    'time'  => $when,
                    'level' => $level,
                    'title' => mb_substr($head, 0, 300),
                    'trace' => mb_substr($body, 0, 4000),
                ];
            }
            $entries = array_reverse($entries);
        }

        if ($level = $request->query('level')) {
            $entries = array_values(array_filter($entries, fn($e) => $e['level'] === $level));
        }
        $entries = array_slice($entries, 0, 100);

        return view('admin.logs', [
            'entries' => $entries,
            'logSize' => $size,
            'level'   => $level,
        ]);
    }

    public function clearLogs(): RedirectResponse
    {
        $path = storage_path('logs/laravel.log');
        if (is_file($path)) file_put_contents($path, '');
        AdminLog::record('logs.clear', null, null, null, 'Cleared laravel.log');
        return back()->with('success', 'Log file cleared.');
    }

    /** Failed queue jobs with retry / delete. */
    public function jobs(): View
    {
        $failed = collect();
        $pending = 0;
        try {
            $failed = DB::table('failed_jobs')->orderByDesc('failed_at')->limit(50)->get()
                ->map(function ($j) {
                    $payload = json_decode($j->payload, true);
                    $j->job_name = $payload['displayName'] ?? 'Unknown job';
                    $j->short_exception = mb_substr(strtok($j->exception, "\n"), 0, 220);
                    return $j;
                });
            $pending = DB::table('jobs')->count();
        } catch (\Throwable) {}

        return view('admin.jobs', compact('failed', 'pending'));
    }

    public function retryJob(string $uuid): RedirectResponse
    {
        try {
            \Artisan::call('queue:retry', ['id' => [$uuid]]);
            AdminLog::record('job.retry', 'job', $uuid, 'Failed job retried');
            return back()->with('success', 'Job pushed back onto the queue.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Could not retry: ' . $e->getMessage());
        }
    }

    public function deleteJob(string $uuid): RedirectResponse
    {
        try { DB::table('failed_jobs')->where('uuid', $uuid)->delete(); } catch (\Throwable) {}
        AdminLog::record('job.delete', 'job', $uuid, 'Failed job deleted');
        return back()->with('success', 'Failed job deleted.');
    }

    public function retryAllJobs(): RedirectResponse
    {
        try {
            \Artisan::call('queue:retry', ['id' => ['all']]);
            AdminLog::record('job.retry', null, null, null, 'Retried all failed jobs');
            return back()->with('success', 'All failed jobs pushed back onto the queue.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Could not retry: ' . $e->getMessage());
        }
    }

    /** Health history for the trend chart. */
    public function history(Request $request): \Illuminate\Http\JsonResponse
    {
        $hours = min(168, max(1, (int) $request->query('hours', 24)));
        $rows = HealthSnapshot::where('created_at', '>=', now()->subHours($hours))
            ->orderBy('created_at')->get([
                'cpu_pct','mem_pct','disk_pct','online_users','failed_jobs','created_at',
            ]);

        return response()->json([
            'points' => $rows->map(fn($r) => [
                'at'      => $r->created_at->format('M j H:i'),
                'cpu'     => $r->cpu_pct,
                'mem'     => $r->mem_pct,
                'disk'    => $r->disk_pct,
                'online'  => $r->online_users,
                'failed'  => $r->failed_jobs,
            ]),
            'peak_cpu' => $rows->max('cpu_pct'),
            'peak_mem' => $rows->max('mem_pct'),
        ]);
    }
}
