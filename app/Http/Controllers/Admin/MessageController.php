<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
        $message->delete();
        return back()->with('success', 'Message deleted.');
    }
}
