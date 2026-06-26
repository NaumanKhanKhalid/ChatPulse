<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureHorizonAccess
{
    public function handle(Request $request, Closure $next): mixed
    {
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            abort(403);
        }
        return $next($request);
    }
}
