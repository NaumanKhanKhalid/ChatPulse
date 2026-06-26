<?php
namespace App\Events;

use App\Models\Call;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\InteractsWithSockets;

class CallEnded implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Call $call) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('call.' . $this->call->id)];
    }

    public function broadcastWith(): array
    {
        return ['call_id' => $this->call->id, 'status' => 'ended', 'duration' => $this->call->duration_seconds];
    }
}
