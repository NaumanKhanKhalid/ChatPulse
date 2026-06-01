<?php
namespace App\Events;

use App\Models\Poll;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\InteractsWithSockets;

class PollUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Poll $poll) {}

    public function broadcastOn(): array
    {
        $conversationId = $this->poll->message->conversation_id;
        return [new PrivateChannel('conversation.' . $conversationId)];
    }

    public function broadcastWith(): array
    {
        $this->poll->load('options.votes.user');
        return [
            'poll_id' => $this->poll->id,
            'message_id' => $this->poll->message_id,
            'total_votes' => $this->poll->total_votes,
            'options' => $this->poll->options->map(fn($opt) => [
                'id' => $opt->id,
                'text' => $opt->text,
                'votes_count' => $opt->votes_count,
                'voters' => $this->poll->is_anonymous ? [] : $opt->getVoters()->pluck('name'),
            ])->toArray(),
        ];
    }
}
