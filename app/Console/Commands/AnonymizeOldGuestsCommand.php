<?php
namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AnonymizeOldGuestsCommand extends Command
{
    protected $signature = 'guests:anonymize-old';
    protected $description = 'Anonymize guest accounts older than 30 days';

    public function handle(): void
    {
        $cutoff = now()->subDays(30);

        User::where('is_guest', true)
            ->where('created_at', '<', $cutoff)
            ->each(function (User $user) {
                $user->update([
                    'name' => 'Deleted Guest',
                    'username' => 'deleted_' . $user->id,
                    'bio' => null,
                    'avatar' => null,
                    'status_message' => null,
                ]);
            });

        $this->info('Old guest accounts anonymized.');
    }
}
