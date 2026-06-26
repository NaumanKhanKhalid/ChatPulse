<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureConversationParticipant
{
    public function handle(Request $request, Closure $next): mixed
    {
        $conversation = $request->route('conversation');
        if ($conversation && !$conversation->participants()->where('user_id', auth()->id())->exists()) {
            if ($request->expectsJson()) return response()->json(['message' => 'Access denied.'], 403);
            abort(403, 'You are not a participant in this conversation.');
        }
        return $next($request);
    }
}
