<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureAdmin
{
    public function handle(Request $request, Closure $next): mixed
    {
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            if ($request->expectsJson()) return response()->json(['message' => 'Admin access required.'], 403);
            abort(403, 'Admin access required.');
        }
        return $next($request);
    }
}
