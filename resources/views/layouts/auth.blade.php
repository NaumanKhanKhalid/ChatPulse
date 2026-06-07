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
    <style>
        body { overflow: auto !important; background: var(--bg); }
        .auth-shell { display: flex !important; min-height: 100vh; }
        .auth-brand { width: 420px; flex-shrink: 0; }
        .auth-form-side { flex: 1; display: flex; align-items: center; justify-content: center; padding: 40px 32px; overflow-y: auto; background: var(--bg); }
        .auth-card { width: 100%; max-width: 420px; }
        @media (max-width: 768px) { .auth-brand { display: none !important; } }
    </style>
</head>
<body>

<div class="auth-shell">
    {{-- Brand panel --}}
    <div class="auth-brand" style="background:linear-gradient(145deg,#064e3b 0%,#065f46 35%,#0d9488 70%,#0891b2 100%);display:flex;flex-direction:column;padding:52px 48px;position:relative;overflow:hidden;">
        <div style="position:absolute;inset:0;background:radial-gradient(ellipse at 30% 20%,rgba(16,185,129,.35) 0%,transparent 55%),radial-gradient(ellipse at 80% 80%,rgba(8,145,178,.4) 0%,transparent 55%);pointer-events:none;"></div>

        <div style="position:relative;z-index:1;display:flex;flex-direction:column;height:100%;">
            {{-- Logo --}}
            <div style="display:flex;align-items:center;gap:12px;">
                <div style="width:44px;height:44px;border-radius:14px;background:rgba(255,255,255,.2);display:grid;place-items:center;backdrop-filter:blur(8px);">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                        <path d="M5 9.5C5 6.46 7.46 4 10.5 4h3C16.54 4 19 6.46 19 9.5S16.54 15 13.5 15H9l-3.2 2.9c-.5.46-1.3.1-1.3-.58V9.5Z" fill="#fff"/>
                        <circle cx="9.5" cy="9.5" r="1.2" fill="#10b981"/>
                        <circle cx="13.5" cy="9.5" r="1.2" fill="#10b981"/>
                    </svg>
                </div>
                <span style="font-size:22px;font-weight:800;color:#fff;letter-spacing:-.02em;">ChatPulse</span>
            </div>

            {{-- Tagline (pushed to bottom) --}}
            <div style="margin-top:auto;">
                <h2 style="font-size:28px;font-weight:800;color:#fff;letter-spacing:-.02em;margin:0 0 10px;line-height:1.25;">
                    Connect your team<br>in real-time.
                </h2>
                <p style="font-size:15px;color:rgba(255,255,255,.75);margin:0;line-height:1.6;">
                    Instant messaging, group chats, file sharing and video calls — all in one place.
                </p>

                {{-- Feature list --}}
                <div style="margin-top:28px;display:flex;flex-direction:column;gap:12px;">
                    @foreach([['⚡','Real-time messaging'],['🔒','End-to-end secure'],['📁','File & media sharing'],['🎥','Voice & video calls']] as [$icon, $label])
                    <div style="display:flex;align-items:center;gap:12px;">
                        <div style="width:34px;height:34px;border-radius:10px;background:rgba(255,255,255,.15);display:grid;place-items:center;font-size:16px;flex-shrink:0;">{{ $icon }}</div>
                        <span style="font-size:14px;color:rgba(255,255,255,.85);font-weight:600;">{{ $label }}</span>
                    </div>
                    @endforeach
                </div>

                {{-- Dots --}}
                <div style="display:flex;gap:8px;margin-top:32px;">
                    <span style="width:24px;height:8px;border-radius:4px;background:#fff;"></span>
                    <span style="width:8px;height:8px;border-radius:50%;background:rgba(255,255,255,.35);"></span>
                    <span style="width:8px;height:8px;border-radius:50%;background:rgba(255,255,255,.35);"></span>
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
