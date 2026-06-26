<?php
namespace App\Http\Controllers;

use App\Events\PollUpdated;
use App\Http\Requests\CreatePollRequest;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Poll;
use App\Models\PollVote;
use App\Services\MessageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PollController extends Controller
{
    public function store(CreatePollRequest $request, Conversation $conversation, MessageService $messageService): JsonResponse
    {
        if (!$conversation->participants()->where('user_id', auth()->id())->exists()) {
            return response()->json(['error' => 'Access denied.'], 403);
        }

        $message = $messageService->send($conversation, auth()->user(), ['type' => 'poll', 'body' => null]);

        $poll = Poll::create([
            'message_id' => $message->id,
            'question' => $request->question,
            'is_multiple_choice' => $request->boolean('is_multiple_choice'),
            'is_anonymous' => $request->boolean('is_anonymous'),
            'ends_at' => $request->ends_at,
        ]);

        foreach ($request->options as $i => $text) {
            $poll->options()->create(['text' => $text, 'order' => $i]);
        }

        $poll->load('options');
        return response()->json(['poll' => $poll, 'message_id' => $message->id], 201);
    }

    public function vote(Request $request, Poll $poll): JsonResponse
    {
        $request->validate(['option_id' => ['required','integer','exists:poll_options,id']]);

        if ($poll->isClosed()) return response()->json(['error' => 'Poll is closed.'], 422);

        $user = auth()->user();
        $optionId = $request->option_id;

        if (!$poll->is_multiple_choice) {
            PollVote::where('poll_id', $poll->id)->where('user_id', $user->id)->delete();
        }

        $existing = PollVote::where('poll_id',$poll->id)->where('poll_option_id',$optionId)->where('user_id',$user->id)->first();
        if ($existing) {
            $existing->delete();
        } else {
            PollVote::create(['poll_id'=>$poll->id,'poll_option_id'=>$optionId,'user_id'=>$user->id]);
        }

        $poll->load('options.votes.user');
        broadcast(new PollUpdated($poll));
        return response()->json($this->formatPoll($poll, $user->id));
    }

    public function close(Poll $poll): JsonResponse
    {
        if ($poll->message->user_id !== auth()->id()) return response()->json(['error'=>'Unauthorized.'],403);
        $poll->update(['ends_at' => now()]);
        return response()->json(['success' => true]);
    }

    private function formatPoll(Poll $poll, int $userId): array
    {
        return [
            'id' => $poll->id,
            'question' => $poll->question,
            'is_multiple_choice' => $poll->is_multiple_choice,
            'is_anonymous' => $poll->is_anonymous,
            'is_closed' => $poll->isClosed(),
            'total_votes' => $poll->total_votes,
            'user_has_voted' => $poll->userHasVoted($userId),
            'options' => $poll->options->map(fn($opt) => [
                'id' => $opt->id,
                'text' => $opt->text,
                'votes_count' => $opt->votes_count,
                'voters' => $poll->is_anonymous ? [] : $opt->getVoters()->pluck('name'),
            ])->toArray(),
        ];
    }
}
