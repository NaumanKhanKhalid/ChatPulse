<?php
namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Message $message) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('conversation.' . $this->message->conversation_id)];
    }

    public function broadcastWith(): array
    {
        $this->message->load(['user', 'attachments', 'reactions.user', 'parent.user', 'poll.options.votes']);
        return ['message' => $this->formatMessage()];
    }

    private function formatMessage(): array
    {
        $m = $this->message;
        return [
            'id' => $m->id,
            'conversation_id' => $m->conversation_id,
            'user_id' => $m->user_id,
            'body' => $m->body,
            'type' => $m->type,
            'parent_id' => $m->parent_id,
            'forwarded_from_id' => $m->forwarded_from_id,
            'is_edited' => $m->is_edited,
            'sent_at' => $m->sent_at?->toISOString(),
            'created_at' => $m->created_at->toISOString(),
            'user' => $m->user ? ['id'=>$m->user->id,'name'=>$m->user->name,'avatar_url'=>$m->user->avatar_url,'is_guest'=>$m->user->is_guest] : null,
            'attachments' => $m->attachments->map(fn($a) => ['id'=>$a->id,'original_name'=>$a->original_name,'url'=>$a->url,'file_type'=>$a->file_type,'file_size'=>$a->file_size,'formatted_size'=>$a->formatted_size,'thumbnail_path'=>$a->thumbnail_path])->toArray(),
            'reactions' => $m->getGroupedReactions(),
            'parent' => $m->parent ? ['id'=>$m->parent->id,'body'=>$m->parent->body,'user'=>['name'=>$m->parent->user?->name]] : null,
        ];
    }
}
