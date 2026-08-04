<?php
namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Report;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'reason'      => ['required', 'string', 'max:40'],
            'note'        => ['nullable', 'string', 'max:500'],
            'message_id'  => ['nullable', 'integer', 'exists:messages,id'],
            'user_id'     => ['nullable', 'integer', 'exists:users,id'],
        ]);

        if (!$request->message_id && !$request->user_id) {
            return response()->json(['error' => 'Nothing to report.'], 422);
        }

        $reporter = auth()->user();
        $message  = $request->message_id ? Message::find($request->message_id) : null;
        $reported = $message?->user_id ?? $request->user_id;

        if ($reported === $reporter->id) {
            return response()->json(['error' => 'You cannot report yourself.'], 422);
        }

        // One open report per reporter per target — stops duplicate spam
        $existing = Report::where('reporter_id', $reporter->id)
            ->where('status', 'open')
            ->when($message, fn($q) => $q->where('message_id', $message->id))
            ->when(!$message, fn($q) => $q->where('reported_user_id', $reported)->whereNull('message_id'))
            ->first();

        if ($existing) {
            return response()->json(['success' => true, 'duplicate' => true]);
        }

        Report::create([
            'reporter_id'      => $reporter->id,
            'reported_user_id' => $reported,
            'message_id'       => $message?->id,
            'conversation_id'  => $message?->conversation_id,
            'reason'           => $request->reason,
            'details'          => $request->note,
            'excerpt'          => $message
                ? \Illuminate\Support\Str::limit($message->body ?: '[' . $message->type . ' message]', 200)
                : null,
            'status'           => 'open',
        ]);

        try {
            \App\Events\AdminActivity::fire('report', $reporter, 'reported',
                $message ? 'a message' : (User::find($reported)?->name ?? 'a user'));
        } catch (\Throwable) {}

        return response()->json(['success' => true]);
    }
}
