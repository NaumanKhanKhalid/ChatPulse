<?php
namespace App\Console\Commands;

use App\Events\UserStatusUpdated;
use App\Models\User;
use Illuminate\Console\Command;

class ClearExpiredStatusCommand extends Command
{
    protected $signature = 'presence:clear-expired-status';
    protected $description = 'Clear user statuses that have expired';

    public function handle(): void
    {
        $users = User::whereNotNull('status_clears_at')
            ->where('status_clears_at', '<=', now())
            ->where('status_type', '!=', 'available')
            ->get();

        foreach ($users as $user) {
            $user->update([
                'status_type' => 'available',
                'status_message' => null,
                'status_emoji' => null,
                'status_clears_at' => null,
            ]);
            broadcast(new UserStatusUpdated($user));
        }
    }
}
