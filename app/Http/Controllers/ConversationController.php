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
        return view('chat.index', compact('conversations', 'user'));
    }

    public function show(Conversation $conversation): View
    {
        $this->authorizeParticipant($conversation);
        $user = auth()->user();
        $messages = $conversation->messages()
            ->with(['user', 'attachments', 'reactions.user', 'parent.user', 'poll.options.votes.user'])
            ->orderBy('created_at')
            ->latest()
            ->paginate(50);
        $messages = $messages->reverse()->values();
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
