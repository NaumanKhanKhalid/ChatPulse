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
        <button class="adm-collapse" id="admCollapse" title="Toggle sidebar">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none"><path d="M4 6h16M4 12h16M4 18h16" stroke="currentColor" stroke-width="1.9" stroke-linecap="round"/></svg>
        </button>
        <a href="{{ route('chat.index') }}" class="adm-logo">
            <span class="adm-logo-ic">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M5 9.5C5 6.46 7.46 4 10.5 4h3C16.54 4 19 6.46 19 9.5S16.54 15 13.5 15H9l-3.2 2.9c-.5.46-1.3.1-1.3-.58V9.5Z" fill="#fff"/><circle cx="9.5" cy="9.5" r="1.2" fill="#10b981"/><circle cx="13.5" cy="9.5" r="1.2" fill="#10b981"/></svg>
            </span>
            <span class="adm-logo-tx">ChatPulse <em>Admin</em></span>
        </a>

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
            @php $openReports = \App\Models\Report::where('status','open')->count(); @endphp
            <a href="{{ route('admin.reports') }}" class="adm-nav-item {{ request()->routeIs('admin.reports*') ? 'on' : '' }}">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none"><path d="M12 3 3 19h18L12 3Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="M12 10v3.5M12 16.5h.01" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                <span>Reports</span>
                @if($openReports > 0)<em class="adm-nav-badge">{{ $openReports }}</em>@endif
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
<script>
document.getElementById('admCollapse')?.addEventListener('click', () => {
    const on = document.documentElement.classList.toggle('adm-collapsed');
    localStorage.setItem('cp-adm-collapsed', on ? '1' : '0');
});
</script>
</body>
</html>
