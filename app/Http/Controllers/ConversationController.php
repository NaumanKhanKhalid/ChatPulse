<?php
namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
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
        return $this->buildChatView(auth()->user());
    }

    public function show(Conversation $conversation): View
    {
        $this->authorizeParticipant($conversation);
        return $this->buildChatView(auth()->user(), $conversation->id);
    }

    private function buildChatView(User $user, ?int $activeConvId = null): View
    {
        $allUsers = User::all();
        $conversations = $this->service->getUserConversations($user);

        $usersMap = $allUsers->mapWithKeys(fn($u) => [$u->id => $this->userToCP($u)])->all();

        $convosCP = $conversations->map(fn($c) => $this->convToCP($c, $user))->values()->all();

        $me = $this->userToCP($user);
        $me['role'] = $user->role ?? 'user';

        $scheduled = Message::where('user_id', $user->id)
            ->where('is_scheduled', true)
            ->whereNull('sent_at')
            ->with('conversation')
            ->orderBy('scheduled_at')
            ->get()
            ->map(fn($m) => [
                'dbId'    => $m->id,
                'convoId' => 'c' . $m->conversation_id,
                'uid'     => $m->user_id,
                'when'    => $m->scheduled_at?->format('D · g:i A') ?? 'Scheduled',
                'text'    => $m->body ?? '',
            ])->values()->all();

        $cpData = json_encode([
            'me'           => $me,
            'users'        => $usersMap,
            'conversations'=> $convosCP,
            'reactionsPool'=> ['👍','🔥','🎉','❤️','😂','👀','✅','🙏'],
            'scheduled'    => $scheduled,
            'activeId'     => $activeConvId ? 'c'.$activeConvId : null,
        ], JSON_UNESCAPED_UNICODE);

        $cpRoutes = json_encode([
            'admin'        => route('admin.dashboard'),
            'settings'     => route('settings.index'),
            'sendMessage'  => url('/conversations/{conv}/messages'),
            'markRead'     => url('/conversations/{conv}/read'),
            'react'        => url('/messages/{msg}/reactions'),
            'pinAdd'       => url('/conversations/{conv}/pins'),
            'pinRemove'    => url('/conversations/{conv}/pins/{msg}'),
            'bookmark'     => url('/messages/{msg}/bookmark'),
            'editMessage'  => url('/messages/{msg}'),
            'deleteMessage'=> url('/messages/{msg}'),
            'forward'      => url('/messages/{msg}/forward'),
            'startDirect'  => route('conversations.direct'),
            'createGroup'  => route('groups.store'),
            'chat'         => route('chat.index'),
            'heartbeat'    => route('presence.heartbeat'),
            'pollVote'     => url('/polls/{poll}/vote'),
            'pollStore'    => url('/conversations/{conv}/polls'),
            'scheduleMsg'  => url('/conversations/{conv}/messages'),
            'scheduleDel'  => url('/scheduled/{msg}'),
            'callInitiate' => url('/conversations/{conv}/call'),
            'callAnswer'   => url('/calls/{call}/answer'),
            'callDecline'  => url('/calls/{call}/decline'),
            'callEnd'      => url('/calls/{call}/end'),
            'callSignal'   => url('/calls/{call}/signal'),
            'notifFetch'   => route('notifications.fetch'),
            'notifRead'    => url('/notifications/{notif}/read'),
            'notifReadAll' => route('notifications.read-all'),
            'csrf'         => csrf_token(),
        ]);

        $activeConvIdStr = $activeConvId ? 'c'.$activeConvId : null;

        return view('chat.index', compact('cpData', 'cpRoutes', 'activeConvIdStr'));
    }

    private function userToCP(User $u): array
    {
        $grad = $u->avatarGradient();
        $initials = collect(explode(' ', $u->name))
            ->map(fn($w) => strtoupper(substr($w, 0, 1)))
            ->take(2)->join('');
        return [
            'id'       => $u->id,
            'name'     => $u->name,
            'username' => $u->username ?? strtolower(str_replace(' ', '_', $u->name)),
            'initials' => $initials,
            'grad'     => $grad,
            'status'   => $u->status_type ?? 'available',
            'online'   => (bool)$u->is_online,
            'last'     => ($u->last_seen_at && !$u->is_online) ? $u->last_seen_at->diffForHumans() : null,
            'guest'    => (bool)$u->is_guest,
            'role'     => $u->role ?? 'user',
        ];
    }

    private function convToCP(Conversation $c, User $user): array
    {
        $lastMsg  = $c->lastMessage;
        $unread   = $c->getUnreadCountFor($user);
        $initials = $c->isGroup()
            ? collect(explode(' ', $c->name ?? 'Group'))->map(fn($w) => strtoupper(substr($w,0,1)))->take(2)->join('')
            : '';

        $gradPool = [
            ['#818cf8','#7c3aed'],['#7dd3fc','#2563eb'],['#2dd4bf','#0891b2'],
            ['#6ee7b7','#0d9488'],['#fcd34d','#ea580c'],['#f0abfc','#a21caf'],
            ['#34d399','#059669'],
        ];
        $grad = $gradPool[$c->id % count($gradPool)];

        $messages = $c->messages()
            ->with(['user','reactions','parent.user','attachments'])
            ->orderBy('created_at','asc')
            ->take(60)
            ->get()
            ->map(fn($m) => $this->msgToCP($m, $user))
            ->values()->all();

        $participant   = $c->participants()->where('user_id', $user->id)->first();
        $firstUnreadId = null;
        if ($participant && $participant->last_read_at) {
            $fu = $c->messages()
                ->where('user_id','!=',$user->id)
                ->where('created_at','>',$participant->last_read_at)
                ->orderBy('created_at')
                ->first();
            if ($fu) $firstUnreadId = 'db'.$fu->id;
        }

        $pinnedIds = $c->pins()->pluck('message_id')->map(fn($id) => 'db'.$id)->values()->all();

        $lastText = '';
        if ($lastMsg) {
            $isMine  = $lastMsg->user_id === $user->id;
            $prefix  = $isMine ? 'You' : (($lastMsg->user?->name) ? explode(' ',$lastMsg->user->name)[0] : '');
            $body    = $lastMsg->body ?: ($lastMsg->type === 'voice' ? '🎤 Voice message' : '📎 File');
            $lastText = $prefix ? "$prefix: $body" : $body;
        }

        $base = [
            'id'           => 'c'.$c->id,
            'type'         => $c->type,
            'unread'       => $unread,
            'time'         => $lastMsg ? $lastMsg->created_at->format('g:i A') : '',
            'last'         => $lastText,
            'muted'        => (bool)($participant?->is_muted ?? false),
            'pinnedIds'    => $pinnedIds,
            'firstUnreadId'=> $firstUnreadId,
            'messages'     => $messages,
        ];

        if ($c->isDirect()) {
            $other       = $c->getOtherUser($user);
            $base['with'] = $other?->id;
        } else {
            $base['name']    = $c->name ?? 'Group';
            $base['initials']= $initials;
            $base['grad']    = $grad;
            $base['desc']    = $c->description ?? '';
            $base['members'] = $c->participants()->pluck('user_id')->values()->all();
            $base['public']  = !(bool)$c->is_private;
        }

        return $base;
    }

    private function msgToCP(Message $m, User $user): array
    {
        $reactions = $m->reactions->groupBy('emoji')
            ->map(fn($group) => $group->pluck('user_id')->values()->all())
            ->all();

        $msg = [
            'id'     => 'db'.$m->id,
            'user'   => $m->user_id,
            't'      => $m->created_at->format('g:i A'),
            'text'   => $m->body ?? '',
            'status' => $m->user_id === $user->id ? 'sent' : null,
        ];

        if (!empty($reactions))     $msg['reactions'] = $reactions;
        if ($m->is_edited)          $msg['edited']    = true;
        if ($m->forwarded_from_id)  $msg['forwarded'] = true;
        if ($m->parent_id)          $msg['reply']     = 'db'.$m->parent_id;

        if ($m->type === 'voice') {
            $msg['voice'] = '0:30';
            unset($msg['text']);
        }

        if ($m->attachments->count()) {
            $att = $m->attachments->first();
            if ($att && str_starts_with($att->file_type ?? '', 'image/')) {
                $msg['image'] = ['src' => $att->url, 'name' => $att->original_name];
            } elseif ($att) {
                $msg['file']  = ['name' => $att->original_name, 'size' => $att->formatted_size ?? '?'];
            }
        }

        return $msg;
    }

    public function markRead(Conversation $conversation, \App\Services\MessageService $messageService): JsonResponse
    {
        $this->authorizeParticipant($conversation);
        $user = auth()->user();
        $messageService->markConversationAsRead($conversation, $user);
        try {
            broadcast(new \App\Events\ConversationRead($conversation->id, $user->id));
        } catch (\Throwable) {}
        return response()->json(['success' => true]);
    }

    public function startDirect(\Illuminate\Http\Request $request): JsonResponse
    {
        $request->validate(['user_id' => ['required', 'integer', 'exists:users,id']]);
        $other = User::findOrFail($request->user_id);
        $conversation = $this->service->getOrCreateDirect(auth()->user(), $other);
        return response()->json([
            'id'   => 'c' . $conversation->id,
            'dbId' => $conversation->id,
        ]);
    }

    private function authorizeParticipant(Conversation $conversation): void
    {
        if (!$conversation->participants()->where('user_id', auth()->id())->exists()) {
            abort(403, 'Access denied.');
        }
    }
}
