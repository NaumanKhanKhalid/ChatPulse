<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} — @yield('title', 'Welcome')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>if(localStorage.getItem('darkMode')==='true')document.documentElement.classList.add('dark');</script>
    <style>body{overflow:auto;}</style>
</head>
<body>

<div class="auth-shell">
    {{-- Brand panel --}}
    <div class="auth-brand">
        <div class="auth-brand-inner">
            <div class="auth-logo">
                <div class="auth-logo-ic">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                        <path d="M5 9.5C5 6.46 7.46 4 10.5 4h3C16.54 4 19 6.46 19 9.5S16.54 15 13.5 15H9l-3.2 2.9c-.5.46-1.3.1-1.3-.58V9.5Z" fill="#fff"/>
                        <circle cx="9.5" cy="9.5" r="1.2" fill="#10b981"/>
                        <circle cx="13.5" cy="9.5" r="1.2" fill="#10b981"/>
                    </svg>
                </div>
                <span>ChatPulse</span>
            </div>

            <div class="auth-tagline">
                <h2>Connect your team<br>in real-time.</h2>
                <p>Instant messaging, group chats, file sharing and video calls — all in one place.</p>
                <div class="auth-dots">
                    <span class="on"></span><span></span><span></span>
                </div>
            </div>
        </div>
    </div>

    {{-- Form panel --}}
    <div class="auth-form-side">
        <div class="auth-card">

            @if(session('error'))
            <div style="margin-bottom:20px;padding:12px 14px;border-radius:12px;border:1px solid #fecaca;background:#fef2f2;color:#b91c1c;font-size:13.5px;font-weight:600;">
                {{ session('error') }}
            </div>
            @endif

            @if(session('success'))
            <div style="margin-bottom:20px;padding:12px 14px;border-radius:12px;border:1px solid #bbf7d0;background:#f0fdf4;color:#15803d;font-size:13.5px;font-weight:600;">
                {{ session('success') }}
            </div>
            @endif

            @yield('content')
        </div>
    </div>
</div>

</body>
</html>
