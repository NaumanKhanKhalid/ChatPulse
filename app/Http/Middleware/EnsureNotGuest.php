<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureNotGuest
{
    public function handle(Request $request, Closure $next): mixed
    {
        if (auth()->check() && auth()->user()->is_guest) {
            if ($request->expectsJson()) return response()->json(['message' => 'Guests cannot access this feature.'], 403);
            return redirect()->route('register')->with('error', 'Please create an account to access this feature.');
        }
        return $next($request);
    }
}
