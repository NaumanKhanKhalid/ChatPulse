<?php
namespace App\Http\Controllers;

use App\Events\ReactionToggled;
use App\Models\Message;
use App\Models\MessageReaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReactionController extends Controller
{
    public function toggle(Request $request, Message $message): JsonResponse
    {
        $request->validate(['emoji' => ['required','string','max:10']]);
        $user = auth()->user();

        $existing = MessageReaction::where('message_id', $message->id)
            ->where('user_id', $user->id)
            ->where('emoji', $request->emoji)
            ->first();

        if ($existing) {
            $existing->delete();
            $action = 'removed';
        } else {
            MessageReaction::create(['message_id'=>$message->id,'user_id'=>$user->id,'emoji'=>$request->emoji]);
            $action = 'added';
        }

        $message->load('reactions.user');
        broadcast(new ReactionToggled($message))->toOthers();
        return response()->json(['action'=>$action,'reactions'=>$message->getGroupedReactions()]);
    }
}
