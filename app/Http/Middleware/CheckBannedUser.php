<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckBannedUser
{
    public function handle(Request $request, Closure $next): mixed
    {
        if (Auth::check() && Auth::user()->is_banned) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            abort(403, 'Your account has been suspended.');
        }
        return $next($request);
    }
}
