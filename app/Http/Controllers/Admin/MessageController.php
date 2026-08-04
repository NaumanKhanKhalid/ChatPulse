<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\Message;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MessageController extends Controller
{
    public function index(Request $request): View
    {
        $q = Message::with('user', 'conversation');

        if ($s = $request->query('q'))      $q->where('body', 'like', "%$s%");
        if ($uid = $request->query('user')) $q->where('user_id', $uid);
        if ($cid = $request->query('conv')) $q->where('conversation_id', $cid);
        if ($from = $request->query('from')) $q->whereDate('created_at', '>=', $from);
        if ($to   = $request->query('to'))   $q->whereDate('created_at', '<=', $to);

        match ($request->query('type')) {
            'attachments' => $q->whereHas('attachments'),
            'edited'      => $q->where('is_edited', true),
            'voice'       => $q->where('type', 'voice'),
            'poll'        => $q->where('type', 'poll'),
            'system'      => $q->where('type', 'system'),
            default       => null,
        };

        $messages = $q->orderByDesc('created_at')->paginate(25)->withQueryString();

        // For the "from user" dropdown
        $authors = \App\Models\User::orderBy('name')->get(['id', 'name']);

        return view('admin.messages', compact('messages', 'authors'));
    }

    public function destroy(Message $message): RedirectResponse
    {
        $excerpt = \Illuminate\Support\Str::limit($message->body ?: '['.$message->type.']', 60);
        $message->delete();
        AdminLog::record('message.delete', 'message', $message->id, $excerpt, 'by '.($message->user?->name ?? 'unknown'));
        return back()->with('success', 'Message deleted.');
    }
}
