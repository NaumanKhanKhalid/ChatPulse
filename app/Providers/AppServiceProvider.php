<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        RateLimiter::for('login', fn(Request $req) =>
            Limit::perMinute(5)->by($req->ip())
        );

        RateLimiter::for('guest-login', fn(Request $req) =>
            Limit::perHour(3)->by($req->ip())
        );

        RateLimiter::for('messages', fn(Request $req) =>
            Limit::perMinute(30)->by($req->user()?->id ?? $req->ip())
        );

        RateLimiter::for('uploads', fn(Request $req) =>
            Limit::perHour(10)->by($req->user()?->id ?? $req->ip())
        );

        RateLimiter::for('api', fn(Request $req) =>
            Limit::perMinute(60)->by($req->user()?->id ?? $req->ip())
        );
    }
}
