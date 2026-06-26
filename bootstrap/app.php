<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'banned.ip' => \App\Http\Middleware\CheckBannedIP::class,
            'banned.user' => \App\Http\Middleware\CheckBannedUser::class,
            'not.guest' => \App\Http\Middleware\EnsureNotGuest::class,
            'admin' => \App\Http\Middleware\EnsureAdmin::class,
            'group.member' => \App\Http\Middleware\EnsureGroupMember::class,
            'conversation.participant' => \App\Http\Middleware\EnsureConversationParticipant::class,
            'horizon.access' => \App\Http\Middleware\EnsureHorizonAccess::class,
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\CheckBannedIP::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
