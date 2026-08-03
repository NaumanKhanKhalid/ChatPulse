<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\Conversation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ConversationController extends Controller
{
    public function index(Request $request): View
    {
        $q = Conversation::withCount(['participants', 'messages'])->with('users');

        if ($s = $request->query('q')) {
            $q->where(fn($w) => $w->where('name', 'like', "%$s%")
                ->orWhereHas('users', fn($u) => $u->where('name', 'like', "%$s%")));
        }
        if ($type = $request->query('type')) $q->where('type', $type);

        $conversations = $q->orderByDesc('last_activity_at')->orderByDesc('created_at')
            ->paginate(20)->withQueryString();

        return view('admin.conversations', compact('conversations'));
    }

    /** Read-only message thread for the admin popup. */
    public function show(Conversation $conversation): JsonResponse
    {
        $messages = $conversation->messages()->with('user')
            ->orderBy('created_at')->take(200)->get()
            ->map(fn($m) => [
                'id'      => $m->id,
                'body'    => $m->body,
                'type'    => $m->type,
                'deleted' => (bool) $m->deleted_at,
                'time'    => $m->created_at->format('g:i A'),
                'date'    => $m->created_at->format('M j'),
                'user'    => $m->user ? [
                    'id'       => $m->user->id,
                    'name'     => $m->user->name,
                    'grad'     => $m->user->avatarGradient(),
                    'initials' => collect(explode(' ', $m->user->name))->map(fn($w) => strtoupper(substr($w, 0, 1)))->take(2)->join(''),
                ] : null,
            ]);

        $title = $conversation->isGroup()
            ? ($conversation->name ?? 'Group')
            : $conversation->users->pluck('name')->join(' & ');

        return response()->json([
            'title'    => $title,
            'subtitle' => ($conversation->isGroup() ? 'Group · ' . $conversation->participants()->count() . ' members' : 'Direct message')
                          . ' · ' . $conversation->messages()->count() . ' messages',
            'messages' => $messages,
        ]);
    }

    public function destroy(Conversation $conversation): \Illuminate\Http\RedirectResponse
    {
        $label = $conversation->name ?? ('Conversation #' . $conversation->id);
        AdminLog::record('conversation.delete', 'conversation', $conversation->id, $label);
        $conversation->delete();
        return back()->with('success', 'Conversation deleted.');
    }
}
