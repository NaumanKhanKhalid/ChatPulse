<?php
namespace App\Http\Controllers;

use App\Http\Requests\CreateGroupRequest;
use App\Http\Requests\UpdateGroupRequest;
use App\Models\Conversation;
use App\Models\User;
use App\Services\ConversationService;
use App\Services\FileUploadService;
use App\Services\GroupService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GroupController extends Controller
{
    public function __construct(
        private ConversationService $conversationService,
        private GroupService $groupService,
        private FileUploadService $fileUploadService
    ) {}

    public function explore(): View
    {
        $groups = Conversation::where('type', 'group')
            ->where('is_private', false)
            ->withCount('participants')
            ->with('creator')
            ->orderByDesc('last_activity_at')
            ->paginate(20);
        return view('groups.explore', compact('groups'));
    }

    public function create(): View
    {
        $users = User::where('id', '!=', auth()->id())->where('is_guest', false)->orderBy('name')->get();
        return view('groups.create', compact('users'));
    }

    public function store(CreateGroupRequest $request): RedirectResponse
    {
        if (auth()->user()->is_guest) {
            return back()->with('error', 'Guests cannot create groups.');
        }
        $conversation = $this->conversationService->createGroup(auth()->user(), $request->validated());
        return redirect()->route('chat.conversation', $conversation)->with('success', 'Group created!');
    }

    public function update(UpdateGroupRequest $request, Conversation $conversation): JsonResponse
    {
        if (!$this->conversationService->isAdmin($conversation, auth()->user())) {
            return response()->json(['error' => 'Admin access required.'], 403);
        }
        $updated = $this->groupService->updateGroup($conversation, $request->validated());
        return response()->json(['conversation' => $updated]);
    }

    public function join(Conversation $conversation): RedirectResponse
    {
        $this->groupService->joinPublicGroup($conversation, auth()->user());
        return redirect()->route('chat.conversation', $conversation)->with('success', 'Joined group!');
    }

    public function leave(Conversation $conversation): RedirectResponse
    {
        if (!$conversation->participants()->where('user_id', auth()->id())->exists()) {
            return back()->with('error', 'You are not a member.');
        }
        $this->conversationService->removeMember($conversation, auth()->user());
        $this->groupService->sendSystemMessage($conversation, auth()->user()->name . ' left the group');
        return redirect()->route('chat.index')->with('success', 'You left the group.');
    }

    public function generateInvite(Conversation $conversation): JsonResponse
    {
        if (!$this->conversationService->isAdmin($conversation, auth()->user())) {
            return response()->json(['error' => 'Admin access required.'], 403);
        }
        $invite = $this->groupService->generateInviteLink($conversation, auth()->user());
        return response()->json(['link' => route('groups.join-invite', $invite->token)]);
    }

    public function joinViaInvite(string $token): RedirectResponse
    {
        $conversation = $this->groupService->joinViaInvite($token, auth()->user());
        return redirect()->route('chat.conversation', $conversation)->with('success', 'Joined group!');
    }
}
