<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin — {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>if(localStorage.getItem('cp-dark')!=='0')document.documentElement.classList.add('dark');</script>
</head>
<body class="adm-body">
<script>if(localStorage.getItem("cp-adm-collapsed")==="1")document.documentElement.classList.add("adm-collapsed");</script>
@php
    $admUser = auth()->user();
    $admGrad = $admUser->avatarGradient();
    $admInitials = collect(explode(' ', $admUser->name))->map(fn($w)=>strtoupper(substr($w,0,1)))->take(2)->join('');
@endphp
<div class="adm-app">
    {{-- Sidebar --}}
    <aside class="adm-side">
        <div class="adm-brand">
            <a href="{{ route('chat.index') }}" class="adm-logo" title="ChatPulse Admin">
                <span class="adm-logo-ic">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M5 9.5C5 6.46 7.46 4 10.5 4h3C16.54 4 19 6.46 19 9.5S16.54 15 13.5 15H9l-3.2 2.9c-.5.46-1.3.1-1.3-.58V9.5Z" fill="#fff"/><circle cx="9.5" cy="9.5" r="1.2" fill="#10b981"/><circle cx="13.5" cy="9.5" r="1.2" fill="#10b981"/></svg>
                </span>
                <span class="adm-logo-tx">ChatPulse <em>Admin</em></span>
            </a>
            <button class="adm-collapse" id="admCollapse" title="Toggle sidebar">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M4 6h16M4 12h16M4 18h16" stroke="currentColor" stroke-width="1.9" stroke-linecap="round"/></svg>
            </button>
        </div>

        <nav class="adm-nav">
            <span class="adm-nav-lbl">Overview</span>
            <a href="{{ route('admin.dashboard') }}" class="adm-nav-item {{ request()->routeIs('admin.dashboard') ? 'on' : '' }}">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none"><rect x="3.5" y="3.5" width="7" height="7" rx="1.6" stroke="currentColor" stroke-width="1.7"/><rect x="13.5" y="3.5" width="7" height="7" rx="1.6" stroke="currentColor" stroke-width="1.7"/><rect x="3.5" y="13.5" width="7" height="7" rx="1.6" stroke="currentColor" stroke-width="1.7"/><rect x="13.5" y="13.5" width="7" height="7" rx="1.6" stroke="currentColor" stroke-width="1.7"/></svg>
                <span>Dashboard</span>
            </a>

            <span class="adm-nav-lbl">Manage</span>
            <a href="{{ route('admin.users') }}" class="adm-nav-item {{ request()->routeIs('admin.users*') ? 'on' : '' }}">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none"><circle cx="9" cy="8" r="3.2" stroke="currentColor" stroke-width="1.7"/><path d="M3.5 19a5.5 5.5 0 0 1 11 0M15 5.5a3.2 3.2 0 0 1 0 5M17.5 19a5.5 5.5 0 0 0-3-4.9" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
                <span>Users</span>
            </a>
            <a href="{{ route('admin.conversations') }}" class="adm-nav-item {{ request()->routeIs('admin.conversations*') ? 'on' : '' }}">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none"><path d="M8 10h8M8 13.5h5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/><path d="M4 6.5C4 5.12 5.12 4 6.5 4h11C18.88 4 20 5.12 20 6.5v7c0 1.38-1.12 2.5-2.5 2.5H10l-3.6 3a1 1 0 0 1-1.65-.77V6.5Z" stroke="currentColor" stroke-width="1.7"/></svg>
                <span>Conversations</span>
            </a>
            <a href="{{ route('admin.messages') }}" class="adm-nav-item {{ request()->routeIs('admin.messages*') ? 'on' : '' }}">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none"><path d="M4 6.5C4 5.12 5.12 4 6.5 4h11C18.88 4 20 5.12 20 6.5v7c0 1.38-1.12 2.5-2.5 2.5H10l-3.6 3a1 1 0 0 1-1.65-.77V6.5Z" stroke="currentColor" stroke-width="1.7"/></svg>
                <span>Messages</span>
            </a>
            <a href="{{ route('admin.groups') }}" class="adm-nav-item {{ request()->routeIs('admin.groups*') ? 'on' : '' }}">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none"><circle cx="8" cy="9" r="2.8" stroke="currentColor" stroke-width="1.7"/><circle cx="16" cy="9" r="2.8" stroke="currentColor" stroke-width="1.7"/><path d="M2.5 18.5a5.5 5.5 0 0 1 11 0M10.5 18.5a5.5 5.5 0 0 1 11 0" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
                <span>Groups</span>
            </a>

            <span class="adm-nav-lbl">Safety</span>
            @php
                $openReports  = \App\Models\Report::where('status','open')->count();
                $openFeedback = \App\Models\Feedback::where('status','open')->count();
            @endphp
            <a href="{{ route('admin.feedback') }}" class="adm-nav-item {{ request()->routeIs('admin.feedback*') ? 'on' : '' }}">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none"><path d="M4 6.5C4 5.12 5.12 4 6.5 4h11C18.88 4 20 5.12 20 6.5v7c0 1.38-1.12 2.5-2.5 2.5H10l-3.6 3a1 1 0 0 1-1.65-.77V6.5Z" stroke="currentColor" stroke-width="1.7"/><path d="M12 7.5v3.5M12 13.2h.01" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
                <span>Feedback</span>
                @if($openFeedback > 0)<em class="adm-nav-badge">{{ $openFeedback }}</em>@endif
            </a>
            <a href="{{ route('admin.reports') }}" class="adm-nav-item {{ request()->routeIs('admin.reports*') ? 'on' : '' }}">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none"><path d="M12 3 3 19h18L12 3Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="M12 10v3.5M12 16.5h.01" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                <span>Reports</span>
                @if($openReports > 0)<em class="adm-nav-badge">{{ $openReports }}</em>@endif
            </a>
            <a href="{{ route('admin.security-log') }}" class="adm-nav-item {{ request()->routeIs('admin.security-log*') ? 'on' : '' }}">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none"><path d="M12 2a5 5 0 0 1 5 5v1h1a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-9a2 2 0 0 1 2-2h1V7a5 5 0 0 1 5-5Zm0 10a2 2 0 1 0 0 4 2 2 0 0 0 0-4Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></svg>
                <span>Security Log</span>
            </a>
            <a href="{{ route('admin.logs') }}" class="adm-nav-item {{ request()->routeIs('admin.logs*') ? 'on' : '' }}">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none"><path d="M12 3 3 19h18L12 3Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="M12 10v3.5M12 16.5h.01" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                <span>Error Logs</span>
            </a>
            <a href="{{ route('admin.jobs') }}" class="adm-nav-item {{ request()->routeIs('admin.jobs*') ? 'on' : '' }}">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none"><rect x="3.5" y="4.5" width="17" height="5" rx="1.5" stroke="currentColor" stroke-width="1.7"/><rect x="3.5" y="14.5" width="17" height="5" rx="1.5" stroke="currentColor" stroke-width="1.7"/></svg>
                <span>Queue Jobs</span>
            </a>
            <a href="{{ route('admin.activity') }}" class="adm-nav-item {{ request()->routeIs('admin.activity*') ? 'on' : '' }}">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none"><path d="M12 8v4.5l3 2" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/><circle cx="12" cy="12" r="8.3" stroke="currentColor" stroke-width="1.7"/></svg>
                <span>Activity Log</span>
            </a>
            <a href="{{ route('admin.security') }}" class="adm-nav-item {{ request()->routeIs('admin.security*') ? 'on' : '' }}">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none"><path d="M12 3.5 5 6v5c0 4.5 3 8 7 9.5 4-1.5 7-5 7-9.5V6l-7-2.5Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="m9.5 12 2 2 3.5-4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <span>Security</span>
            </a>
        </nav>

        <div class="adm-side-foot">
            <a href="{{ route('chat.index') }}" class="adm-back">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M10 19l-7-7m0 0 7-7m-7 7h18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <span>Back to Chat</span>
            </a>
        </div>
    </aside>

    {{-- Main --}}
    <div class="adm-main">
        <header class="adm-head">
            <h1>@yield('page-title', 'Admin')</h1>
            <div class="adm-head-right">
                <button class="adm-theme" onclick="document.documentElement.classList.toggle('dark');localStorage.setItem('cp-dark',document.documentElement.classList.contains('dark')?'1':'0')" title="Toggle theme">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none"><path d="M21 13A9 9 0 1 1 11 3a7 7 0 0 0 10 10Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/></svg>
                </button>
                <button class="adm-kbd-hint" onclick="document.dispatchEvent(new KeyboardEvent('keydown',{key:'k',ctrlKey:true}))" title="Command palette">
                    <kbd>Ctrl</kbd><kbd>K</kbd>
                </button>
                <div class="adm-me">
                    <div class="avatar" style="width:32px;height:32px;background:linear-gradient(135deg,{{ $admGrad[0] }},{{ $admGrad[1] }});font-size:12px">{{ $admInitials }}</div>
                    <span>{{ $admUser->name }}</span>
                </div>
            </div>
        </header>

        <main class="adm-content">
            @if(session('success'))
            <div class="adm-flash ok">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="m5 13 4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                {{ session('success') }}
            </div>
            @endif
            @if(session('error'))
            <div class="adm-flash err">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="8.5" stroke="currentColor" stroke-width="1.8"/><path d="M12 8v4.5M12 15.5h.01" stroke="currentColor" stroke-width="1.9" stroke-linecap="round"/></svg>
                {{ session('error') }}
            </div>
            @endif
            @yield('content')
        </main>
    </div>
</div>
{{-- Command palette (Cmd/Ctrl + K) --}}
<div class="adm-pal-ov" id="palOv">
    <div class="adm-pal">
        <div class="adm-pal-in">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none"><circle cx="11" cy="11" r="6.5" stroke="currentColor" stroke-width="1.8"/><path d="m20 20-3.2-3.2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
            <input type="text" id="palInput" placeholder="Jump to a page, search users…" autocomplete="off">
            <kbd>esc</kbd>
        </div>
        <div class="adm-pal-list" id="palList"></div>
    </div>
</div>

<script>
/* ---- Command palette ---- */
(function () {
    const PAGES = [
        ['Dashboard',     '{{ route('admin.dashboard') }}',    'overview stats health'],
        ['Users',         '{{ route('admin.users') }}',        'people accounts ban role'],
        ['Conversations', '{{ route('admin.conversations') }}','chats direct groups'],
        ['Messages',      '{{ route('admin.messages') }}',     'moderate content'],
        ['Groups',        '{{ route('admin.groups') }}',       'channels'],
        ['Reports',       '{{ route('admin.reports') }}',      'abuse spam flags'],
        ['Feedback',      '{{ route('admin.feedback') }}',     'bugs suggestions support users'],
        ['Security Log',  '{{ route('admin.security-log') }}', 'logins failed attempts brute force'],
        ['Error Logs',    '{{ route('admin.logs') }}',         'exceptions stack traces'],
        ['Queue Jobs',    '{{ route('admin.jobs') }}',         'failed retry'],
        ['Activity Log',  '{{ route('admin.activity') }}',     'audit trail admin actions'],
        ['IP Bans',       '{{ route('admin.security') }}',     'security block'],
        ['Back to Chat',  '{{ route('chat.index') }}',         'app messages'],
    ];
    const ov = document.getElementById('palOv');
    const input = document.getElementById('palInput');
    const list = document.getElementById('palList');
    let items = [], cursor = 0;

    function render(q) {
        const term = q.trim().toLowerCase();
        items = PAGES.filter(([name, , kw]) =>
            !term || name.toLowerCase().includes(term) || kw.includes(term));
        cursor = 0;
        if (!items.length) {
            list.innerHTML = `<div class="adm-pal-empty">Nothing matches “${q}”</div>`;
            return;
        }
        list.innerHTML = items.map(([name, url], i) =>
            `<a href="${url}" class="adm-pal-item ${i === 0 ? 'on' : ''}" data-i="${i}">
                <span>${name}</span><kbd>↵</kbd>
            </a>`).join('');
    }

    function move(step) {
        const els = [...list.querySelectorAll('.adm-pal-item')];
        if (!els.length) return;
        els[cursor]?.classList.remove('on');
        cursor = (cursor + step + els.length) % els.length;
        els[cursor].classList.add('on');
        els[cursor].scrollIntoView({ block: 'nearest' });
    }

    function open() {
        ov.classList.add('show');
        input.value = ''; render('');
        setTimeout(() => input.focus(), 30);
    }
    function close() { ov.classList.remove('show'); }

    document.addEventListener('keydown', e => {
        if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'k') { e.preventDefault(); ov.classList.contains('show') ? close() : open(); return; }
        if (!ov.classList.contains('show')) return;
        if (e.key === 'Escape') close();
        else if (e.key === 'ArrowDown') { e.preventDefault(); move(1); }
        else if (e.key === 'ArrowUp')   { e.preventDefault(); move(-1); }
        else if (e.key === 'Enter')     { e.preventDefault(); list.querySelectorAll('.adm-pal-item')[cursor]?.click(); }
    });
    input.addEventListener('input', () => render(input.value));
    ov.addEventListener('click', e => { if (e.target === ov) close(); });
})();

document.getElementById('admCollapse')?.addEventListener('click', () => {
    const on = document.documentElement.classList.toggle('adm-collapsed');
    localStorage.setItem('cp-adm-collapsed', on ? '1' : '0');
});
</script>
</body>
</html>
