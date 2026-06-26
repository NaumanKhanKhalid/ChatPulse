<?php
namespace App\Events;

use App\Models\Call;
use App\Models\User;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\InteractsWithSockets;

class CallInitiated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Call $call, public int $recipientId) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('user.' . $this->recipientId)];
    }

    public function broadcastWith(): array
    {
        $this->call->load('initiator', 'conversation');
        return [
            'call_id' => $this->call->id,
            'type' => $this->call->type,
            'caller' => ['id'=>$this->call->initiator->id,'name'=>$this->call->initiator->name,'avatar_url'=>$this->call->initiator->avatar_url],
            'conversation_id' => $this->call->conversation_id,
        ];
    }
}
