<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} — @yield('title', 'Chat')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>if(localStorage.getItem('darkMode')!=='false')document.documentElement.classList.add('dark');</script>
</head>
<body x-data="teamflowApp()" data-user-id="{{ auth()->id() }}" data-user-name="{{ auth()->user()?->name }}">

<div id="netBanner"></div>

<div class="app">
    {{-- Rail --}}
    @include('layouts.partials.rail')

    {{-- Conversation list panel --}}
    <section id="list">
        @yield('list-panel')
    </section>

    {{-- Chat / main area --}}
    <section id="chat">
        @yield('content')
    </section>

    {{-- Right panel --}}
    @hasSection('right-panel')
    <aside id="rightPanel">
        <div class="panel-scroll">
            @yield('right-panel')
        </div>
    </aside>
    @endif
</div>

<div id="toasts"></div>

{{-- Toast system (Alpine) --}}
<template x-for="toast in toasts" :key="toast.id">
    <div x-show="toast.visible" x-transition
         :class="toast.type === 'error' ? 'err' : ''"
         class="toast"
         x-text="toast.message"
         x-init="setTimeout(() => removeToast(toast.id), 4000)">
    </div>
</template>

{{-- Incoming call overlay --}}
<div x-show="incomingCall" x-transition
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.6);backdrop-filter:blur(4px);z-index:200;display:grid;place-items:center;">
    <div style="background:var(--card);border:1px solid var(--line);border-radius:24px;padding:36px;text-align:center;max-width:340px;width:90%;box-shadow:0 24px 60px -12px rgba(0,0,0,.5);">
        <div style="width:72px;height:72px;border-radius:50%;background:var(--primary-light);display:grid;place-items:center;margin:0 auto 16px;">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none"><path d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11 11 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498A1 1 0 0121 15.72V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" stroke="var(--primary)" stroke-width="1.8"/></svg>
        </div>
        <h3 style="font-size:18px;font-weight:800;color:var(--text);margin:0 0 6px;" x-text="(incomingCall?.caller?.name ?? '') + ' is calling...'"></h3>
        <p style="font-size:13px;color:var(--text3);margin:0 0 28px;" x-text="incomingCall?.type === 'video' ? 'Incoming video call' : 'Incoming voice call'"></p>
        <div style="display:flex;gap:16px;justify-content:center;">
            <div style="display:flex;flex-direction:column;align-items:center;gap:8px;">
                <button @click="declineCall()" style="width:56px;height:56px;border-radius:50%;background:var(--busy);color:#fff;display:grid;place-items:center;">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M16 8l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2M5 3a2 2 0 00-2 2v1c0 8.284 6.716 15 15 15h1a2 2 0 002-2v-3.28a1 1 0 00-.684-.948l-4.493-1.498a1 1 0 00-1.21.502l-1.13 2.257a11.042 11.042 0 01-5.516-5.517l2.257-1.13a1 1 0 00.502-1.21L9.228 3.683A1 1 0 008.279 3H5z" stroke="currentColor" stroke-width="1.8"/></svg>
                </button>
                <span style="font-size:12px;color:var(--text3);">Decline</span>
            </div>
            <div style="display:flex;flex-direction:column;align-items:center;gap:8px;">
                <button @click="acceptCall()" style="width:56px;height:56px;border-radius:50%;background:var(--primary);color:#fff;display:grid;place-items:center;">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11 11 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498A1 1 0 0121 15.72V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" stroke="currentColor" stroke-width="1.8"/></svg>
                </button>
                <span style="font-size:12px;color:var(--text3);">Accept</span>
            </div>
        </div>
    </div>
</div>

</body>
</html>
