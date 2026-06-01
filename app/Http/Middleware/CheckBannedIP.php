<?php
namespace App\Http\Middleware;

use App\Models\IpBan;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CheckBannedIP
{
    public function handle(Request $request, Closure $next): mixed
    {
        $banned = Cache::remember("ip_ban:{$request->ip()}", 300, fn() =>
            IpBan::where('ip_address', $request->ip())
                 ->where(fn($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
                 ->exists()
        );

        if ($banned) abort(403, 'Access denied.');
        return $next($request);
    }
}
