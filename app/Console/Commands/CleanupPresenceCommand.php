<?php
namespace App\Console\Commands;

use App\Models\User;
use App\Events\UserPresenceUpdated;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class CleanupPresenceCommand extends Command
{
    protected $signature = 'presence:cleanup';
    protected $description = 'Mark users offline if their heartbeat has expired';

    public function handle(): void
    {
        // Users marked online but no heartbeat cache key in last 2 min
        User::where('is_online', true)->each(function (User $user) {
            if (!Cache::has("user_online:{$user->id}")) {
                $user->update(['is_online' => false, 'last_seen_at' => now()]);
                broadcast(new UserPresenceUpdated($user->id, false, now()));
            }
        });
    }
}
