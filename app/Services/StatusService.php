<?php
namespace App\Services;

use App\Models\User;
use App\Events\UserStatusUpdated;

class StatusService
{
    public function update(User $user, array $data): User
    {
        $clearsAt = null;
        if (!empty($data['clear_after'])) {
            $clearsAt = match($data['clear_after']) {
                '1hour' => now()->addHour(),
                '4hours' => now()->addHours(4),
                'today' => now()->endOfDay(),
                'week' => now()->endOfWeek(),
                default => null,
            };
        }

        $user->update([
            'status_type' => $data['status_type'] ?? 'available',
            'status_message' => isset($data['status_message']) ? substr($data['status_message'], 0, 60) : null,
            'status_emoji' => $data['status_emoji'] ?? null,
            'status_clears_at' => $clearsAt,
        ]);

        broadcast(new UserStatusUpdated($user));

        return $user->fresh();
    }
}
