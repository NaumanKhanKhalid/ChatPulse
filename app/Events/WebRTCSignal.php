<?php
namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\InteractsWithSockets;

class WebRTCSignal implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public int $callId, public array $signal, public int $fromUserId) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('call.' . $this->callId)];
    }

    public function broadcastWith(): array
    {
        return ['signal' => $this->signal, 'from_user_id' => $this->fromUserId];
    }
}
