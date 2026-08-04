<?php
namespace App\Http\Controllers;

use App\Models\Conversation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Per-user conversation preferences: pin, favourite, mute, archive,
 * mark unread, clear history and leave. These were all in-memory only.
 */
class ConversationPrefController extends Controller
{
    private function participant(Conversation $conversation)
    {
        $p = $conversation->participants()->where('user_id', auth()->id())->first();
        abort_unless($p, 403, 'You are not in this conversation.');
        return $p;
    }

    public function update(Request $request, Conversation $conversation): JsonResponse
    {
        $request->validate([
            'pinned'    => ['nullable', 'boolean'],
            'favourite' => ['nullable', 'boolean'],
            'muted'     => ['nullable', 'boolean'],
            'archived'  => ['nullable', 'boolean'],
        ]);

        $p = $this->participant($conversation);
        $data = [];
        foreach ([
            'pinned' => 'is_pinned', 'favourite' => 'is_favourite',
            'muted'  => 'is_muted',  'archived'  => 'is_archived',
        ] as $key => $col) {
            if ($request->has($key)) $data[$col] = $request->boolean($key);
        }
        if ($data) $p->update($data);

        return response()->json(['success' => true]);
    }

    /** Mark unread by rewinding the read pointer before the last message. */
    public function markUnread(Conversation $conversation): JsonResponse
    {
        $p = $this->participant($conversation);
        $last = $conversation->messages()->where('user_id', '!=', auth()->id())->latest()->first();
        $p->update(['last_read_at' => $last ? $last->created_at->subSecond() : null]);
        return response()->json(['success' => true]);
    }

    /** Hide history for this user only — everyone else keeps their copy. */
    public function clear(Conversation $conversation): JsonResponse
    {
        $this->participant($conversation)->update(['cleared_at' => now()]);
        return response()->json(['success' => true]);
    }

    /** Leave the conversation entirely. */
    public function destroy(Conversation $conversation): JsonResponse
    {
        $this->participant($conversation)->delete();
        return response()->json(['success' => true]);
    }
}
