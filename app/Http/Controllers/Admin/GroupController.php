<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class GroupController extends Controller
{
    public function index(\Illuminate\Http\Request $request): View
    {
        $q = Conversation::where('type','group')->withCount(['participants','messages']);

        if ($s = $request->query('q')) $q->where('name', 'like', "%$s%");
        if ($v = $request->query('visibility')) $q->where('is_private', $v === 'private');
        if ($from = $request->query('from')) $q->whereDate('created_at', '>=', $from);
        if ($to   = $request->query('to'))   $q->whereDate('created_at', '<=', $to);

        match ($request->query('sort', 'newest')) {
            'oldest'   => $q->orderBy('created_at'),
            'members'  => $q->orderByDesc('participants_count'),
            'messages' => $q->orderByDesc('messages_count'),
            'name'     => $q->orderBy('name'),
            default    => $q->orderByDesc('created_at'),
        };

        $groups = $q->paginate(20)->withQueryString();
        return view('admin.groups', compact('groups'));
    }

    /** Group detail: members with roles, activity and moderation history. */
    public function show(Conversation $conversation): View
    {
        abort_unless($conversation->isGroup(), 404);

        $members = $conversation->participants()->with('user')
            ->orderByRaw("role = 'admin' desc")->orderBy('joined_at')->get();

        $stats = [
            'members'   => $members->count(),
            'messages'  => $conversation->messages()->count(),
            'today'     => $conversation->messages()->whereDate('created_at', today())->count(),
            'files'     => Message::where('conversation_id', $conversation->id)
                              ->whereHas('attachments')->count(),
        ];

        $recentMessages = $conversation->messages()->with('user')->latest()->limit(10)->get();

        $topPosters = Message::where('conversation_id', $conversation->id)
            ->selectRaw('user_id, count(*) as total')
            ->groupBy('user_id')->orderByDesc('total')->limit(5)->get()
            ->map(fn($r) => ['user' => User::find($r->user_id), 'total' => $r->total])
            ->filter(fn($r) => $r['user']);

        $adminActions = \App\Models\AdminLog::with('admin')
            ->where('target_type', 'group')->where('target_id', (string) $conversation->id)
            ->latest()->limit(15)->get();

        return view('admin.group-detail', compact('conversation', 'members', 'stats', 'recentMessages', 'topPosters', 'adminActions'));
    }

    public function destroy(Conversation $conversation): RedirectResponse
    {
        AdminLog::record('group.delete', 'group', $conversation->id, $conversation->name ?? 'Unnamed');
        $conversation->delete();
        return back()->with('success', 'Group deleted.');
    }
}
