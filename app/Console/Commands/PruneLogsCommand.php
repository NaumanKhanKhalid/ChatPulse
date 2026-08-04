<?php
namespace App\Console\Commands;

use App\Models\AdminLog;
use App\Models\LoginLog;
use Illuminate\Console\Command;

class PruneLogsCommand extends Command
{
    protected $signature = 'logs:prune {--days=90 : Keep this many days of audit and login history}';
    protected $description = 'Trim audit, login and Laravel log history so they do not grow forever';

    public function handle(): int
    {
        $cutoff = now()->subDays((int) $this->option('days'));

        $login = LoginLog::where('created_at', '<', $cutoff)->delete();
        $audit = AdminLog::where('created_at', '<', $cutoff)->delete();

        // Rotate laravel.log once it passes 20MB
        $path = storage_path('logs/laravel.log');
        $rotated = false;
        if (is_file($path) && filesize($path) > 20 * 1024 * 1024) {
            @rename($path, storage_path('logs/laravel-' . now()->format('Y-m-d-His') . '.log'));
            $rotated = true;
        }

        $this->info("Pruned {$login} login and {$audit} audit entries." . ($rotated ? ' Rotated laravel.log.' : ''));
        return self::SUCCESS;
    }
}
