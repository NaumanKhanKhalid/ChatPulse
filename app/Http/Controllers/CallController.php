<?php
namespace App\Http\Controllers;

use App\Events\CallAnswered;
use App\Events\CallEnded;
use App\Events\CallInitiated;
use App\Events\WebRTCSignal;
use App\Models\Call;
use App\Models\CallParticipant;
use App\Models\Conversation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CallController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $calls = Call::whereHas('participants', fn($q) => $q->where('user_id', $user->id))
            ->orWhere('initiated_by', $user->id)
            ->with(['participants', 'conversation'])
            ->orderByDesc('started_at')
            ->limit(50)
            ->get();
        return view('calls.index', compact('calls'));
    }

    public function initiate(Request $request, Conversation $conversation): JsonResponse
    {
        $request->validate(['type' => ['required','in:audio,video']]);

        if (!$conversation->participants()->where('user_id', auth()->id())->exists()) {
            return response()->json(['error' => 'Access denied.'], 403);
        }

        $call = Call::create([
            'conversation_id' => $conversation->id,
            'initiated_by' => auth()->id(),
            'type' => $request->type,
            'status' => 'missed', // default, updated on answer
            'started_at' => now(),
        ]);

        CallParticipant::create(['call_id'=>$call->id,'user_id'=>auth()->id(),'joined_at'=>now()]);

        // Notify all other participants
        $otherParticipants = $conversation->participants()
            ->where('user_id', '!=', auth()->id())
            ->pluck('user_id');

        foreach ($otherParticipants as $userId) {
            broadcast(new CallInitiated($call, $userId));
        }

        return response()->json(['call_id' => $call->id]);
    }

    public function answer(Call $call): JsonResponse
    {
        $call->update(['status' => 'answered']);
        CallParticipant::firstOrCreate(
            ['call_id'=>$call->id,'user_id'=>auth()->id()],
            ['joined_at'=>now()]
        );
        broadcast(new CallAnswered($call));
        return response()->json(['success' => true]);
    }

    public function decline(Call $call): JsonResponse
    {
        $call->update(['status' => 'declined']);
        broadcast(new CallEnded($call));
        return response()->json(['success' => true]);
    }

    public function end(Call $call): JsonResponse
    {
        $started = $call->started_at;
        $duration = $started ? (int) $started->diffInSeconds(now()) : 0;
        $call->update(['ended_at' => now(), 'duration_seconds' => $duration]);

        CallParticipant::where('call_id', $call->id)
            ->whereNull('left_at')
            ->update(['left_at' => now()]);

        broadcast(new CallEnded($call));
        return response()->json(['success' => true]);
    }

    public function signal(Request $request, Call $call): JsonResponse
    {
        $request->validate(['signal' => ['required','array']]);
        broadcast(new WebRTCSignal($call->id, $request->signal, auth()->id()))->toOthers();
        return response()->json(['success' => true]);
    }
}
