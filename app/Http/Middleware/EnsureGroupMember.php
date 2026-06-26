<?php
namespace App\Http\Middleware;

use App\Models\Conversation;
use Closure;
use Illuminate\Http\Request;

class EnsureGroupMember
{
    public function handle(Request $request, Closure $next): mixed
    {
        $conversation = $request->route('conversation');
        if ($conversation && !$conversation->users()->where('users.id', auth()->id())->exists()) {
            abort(403, 'You are not a member of this group.');
        }
        return $next($request);
    }
}
