<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class HealthController extends Controller
{
    /** Live system health for the admin dashboard widget (fetched every ~10s). */
    public function index(): JsonResponse
    {
        // CPU load (1-min average, normalized by core count)
        $load = function_exists('sys_getloadavg') ? (sys_getloadavg()[0] ?? 0) : 0;
        $cores = (int) (@shell_exec('nproc 2>/dev/null') ?: 1) ?: 1;
        $cpuPct = min(100, round($load / $cores * 100));

        // Memory from /proc/meminfo (Linux) — fallback to PHP process usage
        $memTotal = $memUsedPct = null;
        if (is_readable('/proc/meminfo')) {
            $mi = file_get_contents('/proc/meminfo');
            preg_match('/MemTotal:\s+(\d+)/', $mi, $t);
            preg_match('/MemAvailable:\s+(\d+)/', $mi, $a);
            if (!empty($t[1]) && !empty($a[1])) {
                $memTotal = round($t[1] / 1024 / 1024, 1); // GB
                $memUsedPct = round((1 - $a[1] / $t[1]) * 100);
            }
        }

        // Disk
        $diskFree = @disk_free_space(base_path());
        $diskTotal = @disk_total_space(base_path());
        $diskPct = ($diskFree && $diskTotal) ? round((1 - $diskFree / $diskTotal) * 100) : null;

        // Queue — jobs / failed_jobs tables
        $pendingJobs = $failedJobs = 0;
        try { $pendingJobs = DB::table('jobs')->count(); } catch (\Throwable) {}
        try { $failedJobs = DB::table('failed_jobs')->count(); } catch (\Throwable) {}

        // Database ping
        $dbOk = true; $dbMs = null;
        try {
            $t0 = microtime(true);
            DB::select('select 1');
            $dbMs = round((microtime(true) - $t0) * 1000, 1);
        } catch (\Throwable) { $dbOk = false; }

        // Reverb WebSocket — TCP port check
        $reverbHost = config('reverb.servers.reverb.host', env('REVERB_HOST', '127.0.0.1'));
        $reverbPort = (int) (config('reverb.servers.reverb.port', env('REVERB_PORT', 8080)));
        $reverbOk = false;
        try {
            $sock = @fsockopen($reverbHost === '0.0.0.0' ? '127.0.0.1' : $reverbHost, $reverbPort, $e1, $e2, 0.4);
            if ($sock) { $reverbOk = true; fclose($sock); }
        } catch (\Throwable) {}

        return response()->json([
            'cpu_pct'       => $cpuPct,
            'load'          => round($load, 2),
            'mem_pct'       => $memUsedPct,
            'mem_total_gb'  => $memTotal,
            'disk_pct'      => $diskPct,
            'db_ok'         => $dbOk,
            'db_ms'         => $dbMs,
            'reverb_ok'     => $reverbOk,
            'pending_jobs'  => $pendingJobs,
            'failed_jobs'   => $failedJobs,
            'online_users'  => User::where('is_online', true)->count(),
            'messages_last_hour' => Message::where('created_at', '>=', now()->subHour())->count(),
            'ts'            => now()->format('g:i:s A'),
        ]);
    }
}
