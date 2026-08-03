<?php
namespace App\Http\Controllers;

use App\Events\MessagePinned;
use App\Events\MessageUnpinned;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\MessagePin;
use App\Services\GroupService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PinController extends Controller
{
    public function store(Request $request, Conversation $conversation, GroupService $groupService): JsonResponse
    {
        $request->validate(['message_id' => ['required','integer','exists:messages,id']]);

        if ($conversation->pins()->count() >= 10) {
            return response()->json(['error' => 'Maximum 10 pins per conversation.'], 422);
        }

        $message = Message::findOrFail($request->message_id);
        if ($message->conversation_id !== $conversation->id) {
            return response()->json(['error' => 'Message not in this conversation.'], 422);
        }

        $pin = MessagePin::firstOrCreate(
            ['conversation_id'=>$conversation->id,'message_id'=>$message->id],
            ['pinned_by'=>auth()->id(),'pinned_at'=>now()]
        );

        $groupService->sendSystemMessage($conversation, auth()->user()->name . ' pinned a message');
        try { broadcast(new MessagePinned($pin->load('message.user','pinner')))->toOthers(); } catch (\Throwable) { /* Reverb offline — realtime skipped */ }
        return response()->json(['pin' => $pin]);
    }

    public function destroy(Conversation $conversation, Message $message): JsonResponse
    {
        MessagePin::where('conversation_id',$conversation->id)->where('message_id',$message->id)->delete();
        try { broadcast(new MessageUnpinned($conversation->id, $message->id))->toOthers(); } catch (\Throwable) { /* Reverb offline — realtime skipped */ }
        return response()->json(['success' => true]);
    }
}
