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
        if ($s = $request->get('q')) $q->where('body', 'like', "%$s%");
        if ($uid = $request->get('user')) $q->where('user_id', $uid);
        $messages = $q->orderByDesc('created_at')->paginate(25)->withQueryString();
        return view('admin.messages', compact('messages'));
    }

    public function destroy(Message $message): RedirectResponse
    {
        $excerpt = \Illuminate\Support\Str::limit($message->body ?: '['.$message->type.']', 60);
        $message->delete();
        AdminLog::record('message.delete', 'message', $message->id, $excerpt, 'by '.($message->user?->name ?? 'unknown'));
        return back()->with('success', 'Message deleted.');
    }
}
