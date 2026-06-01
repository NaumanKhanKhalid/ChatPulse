<?php
namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\InteractsWithSockets;

class UserStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public User $user) {}

    public function broadcastOn(): array
    {
        return [new PresenceChannel('app')];
    }

    public function broadcastWith(): array
    {
        return [
            'user_id' => $this->user->id,
            'status_type' => $this->user->status_type,
            'status_message' => $this->user->status_message,
            'status_emoji' => $this->user->status_emoji,
        ];
    }
}
