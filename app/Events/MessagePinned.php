<?php
namespace App\Events;

use App\Models\MessagePin;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\InteractsWithSockets;

class MessagePinned implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public MessagePin $pin) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('conversation.' . $this->pin->conversation_id)];
    }

    public function broadcastWith(): array
    {
        $this->pin->load('message.user', 'pinner');
        return [
            'pin' => [
                'id' => $this->pin->id,
                'message_id' => $this->pin->message_id,
                'message_body' => $this->pin->message->body,
                'message_user' => $this->pin->message->user?->name,
                'pinned_by' => $this->pin->pinner?->name,
                'pinned_at' => $this->pin->pinned_at?->toISOString(),
            ]
        ];
    }
}
