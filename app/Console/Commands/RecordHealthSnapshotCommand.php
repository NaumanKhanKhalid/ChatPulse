<?php
namespace App\Console\Commands;

use App\Http\Controllers\Admin\HealthController;
use App\Models\HealthSnapshot;
use Illuminate\Console\Command;

class RecordHealthSnapshotCommand extends Command
{
    protected $signature = 'health:snapshot {--prune-days=14 : Delete snapshots older than this}';
    protected $description = 'Store a system health snapshot so trends can be charted';

    public function handle(): int
    {
        $d = (new HealthController)->index()->getData(true);

        HealthSnapshot::create([
            'cpu_pct'            => $d['cpu_pct'] ?? null,
            'mem_pct'            => $d['mem_pct'] ?? null,
            'disk_pct'           => $d['disk_pct'] ?? null,
            'pending_jobs'       => $d['pending_jobs'] ?? 0,
            'failed_jobs'        => $d['failed_jobs'] ?? 0,
            'online_users'       => $d['online_users'] ?? 0,
            'messages_last_hour' => $d['messages_last_hour'] ?? 0,
            'db_ok'              => $d['db_ok'] ?? true,
            'reverb_ok'          => $d['reverb_ok'] ?? false,
            'created_at'         => now(),
        ]);

        $days = (int) $this->option('prune-days');
        if ($days > 0) {
            HealthSnapshot::where('created_at', '<', now()->subDays($days))->delete();
        }

        $this->info('Health snapshot recorded.');
        return self::SUCCESS;
    }
}
