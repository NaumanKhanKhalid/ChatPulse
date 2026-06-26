<?php
namespace App\Services;

use App\Models\User;
use App\Events\UserPresenceUpdated;
use Illuminate\Support\Facades\Cache;

class PresenceService
{
    public function markOnline(User $user): void
    {
        $wasOffline = !$user->is_online;
        $user->update(['is_online' => true, 'last_seen_at' => now()]);
        Cache::put("user_online:{$user->id}", true, now()->addMinutes(2));

        if ($wasOffline) {
            try {
                broadcast(new UserPresenceUpdated($user->id, true, now()));
            } catch (\Throwable $e) {
                // Reverb may not be running; presence update is non-critical
            }
        }
    }

    public function markOffline(User $user): void
    {
        $user->update(['is_online' => false, 'last_seen_at' => now()]);
        Cache::forget("user_online:{$user->id}");
        try {
            broadcast(new UserPresenceUpdated($user->id, false, now()));
        } catch (\Throwable $e) {
            //
        }
    }

    public function heartbeat(User $user): void
    {
        $wasOffline = !Cache::has("user_online:{$user->id}");
        Cache::put("user_online:{$user->id}", true, now()->addMinutes(2));

        if ($wasOffline) {
            $user->update(['is_online' => true, 'last_seen_at' => now()]);
            try {
                broadcast(new UserPresenceUpdated($user->id, true, now()));
            } catch (\Throwable $e) {
                //
            }
        } else {
            $user->update(['last_seen_at' => now()]);
        }
    }
}
