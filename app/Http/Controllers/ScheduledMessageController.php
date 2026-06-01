<?php
namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScheduledMessageController extends Controller
{
    public function index(): View
    {
        $scheduled = Message::where('user_id', auth()->id())
            ->where('is_scheduled', true)
            ->whereNull('sent_at')
            ->with('conversation')
            ->orderBy('scheduled_at')
            ->get();
        return view('scheduled.index', compact('scheduled'));
    }

    public function update(Request $request, Message $message): JsonResponse
    {
        if ($message->user_id !== auth()->id()) return response()->json(['error'=>'Unauthorized.'],403);
        if ($message->sent_at) return response()->json(['error'=>'Already sent.'],422);

        $request->validate([
            'body' => ['sometimes','string','max:10000'],
            'scheduled_at' => ['sometimes','date','after:now'],
        ]);

        $message->update(array_filter($request->only('body','scheduled_at')));
        return response()->json(['message' => $message]);
    }

    public function destroy(Message $message): JsonResponse
    {
        if ($message->user_id !== auth()->id()) return response()->json(['error'=>'Unauthorized.'],403);
        $message->delete();
        return response()->json(['success' => true]);
    }
}
