<?php
namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\User;
use App\Services\ConversationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ConversationController extends Controller
{
    public function __construct(private ConversationService $service) {}

    public function index(): View
    {
        $user = auth()->user();
        $conversations = $this->service->getUserConversations($user);
        $allUsers = User::where('id', '!=', $user->id)
            ->where('is_guest', false)
            ->orderBy('name')
            ->get();
        return view('chat.index', compact('conversations', 'user', 'allUsers'));
    }

    public function show(Conversation $conversation): View
    {
        $this->authorizeParticipant($conversation);
        $user = auth()->user();
        $messages = $conversation->messages()
            ->with(['user', 'attachments', 'reactions.user', 'parent.user', 'poll.options.votes.user'])
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(fn($m) => [
                'id'                => $m->id,
                'conversation_id'   => $m->conversation_id,
                'user_id'           => $m->user_id,
                'body'              => $m->body,
                'type'              => $m->type,
                'parent_id'         => $m->parent_id,
                'forwarded_from_id' => $m->forwarded_from_id,
                'is_edited'         => $m->is_edited,
                'sent_at'           => $m->sent_at?->toISOString(),
                'created_at'        => $m->created_at->toISOString(),
                'link_previews'     => [],
                'user'              => $m->user ? [
                    'id'         => $m->user->id,
                    'name'       => $m->user->name,
                    'avatar_url' => $m->user->avatar_url,
                    'is_guest'   => $m->user->is_guest,
                ] : null,
                'attachments' => $m->attachments->map(fn($a) => [
                    'id'             => $a->id,
                    'original_name'  => $a->original_name,
                    'url'            => $a->url,
                    'file_type'      => $a->file_type,
                    'formatted_size' => $a->formatted_size,
                ])->values()->toArray(),
                'reactions' => $m->getGroupedReactions(),
                'parent'    => $m->parent ? [
                    'id'   => $m->parent->id,
                    'body' => $m->parent->body,
                    'user' => ['name' => $m->parent->user?->name],
                ] : null,
            ])
            ->values();
        $participants = $conversation->participants()->with('user')->get();
        $pinnedMessages = $conversation->pins()->with('message.user')->get();
        return view('chat.conversation', compact('conversation', 'messages', 'participants', 'pinnedMessages', 'user'));
    }

    public function markRead(Conversation $conversation, \App\Services\MessageService $messageService): JsonResponse
    {
        $this->authorizeParticipant($conversation);
        $messageService->markConversationAsRead($conversation, auth()->user());
        return response()->json(['success' => true]);
    }

    private function authorizeParticipant(Conversation $conversation): void
    {
        if (!$conversation->participants()->where('user_id', auth()->id())->exists()) {
            abort(403, 'Access denied.');
        }
    }
}
