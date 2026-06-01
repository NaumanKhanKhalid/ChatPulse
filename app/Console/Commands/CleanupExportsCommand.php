<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanupExportsCommand extends Command
{
    protected $signature = 'storage:cleanup-exports';
    protected $description = 'Delete export files older than 24 hours';

    public function handle(): void
    {
        $files = Storage::files('exports');
        $deleted = 0;

        foreach ($files as $file) {
            $lastModified = Storage::lastModified($file);
            if (now()->timestamp - $lastModified > 86400) {
                Storage::delete($file);
                $deleted++;
            }
        }

        $this->info("Deleted {$deleted} export file(s).");
    }
}
