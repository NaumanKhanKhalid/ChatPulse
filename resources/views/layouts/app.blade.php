<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>if(localStorage.getItem('cp-dark')!=='0')document.documentElement.classList.add('dark');</script>
    @yield('head')
</head>
<script>
console.log({
    host: "{{ env('VITE_REVERB_HOST') }}",
    port: "{{ env('VITE_REVERB_PORT') }}",
    scheme: "{{ env('VITE_REVERB_SCHEME') }}",
    key: "{{ env('VITE_REVERB_APP_KEY') }}"
});
</script>
<body data-user-id="{{ auth()->id() }}" data-user-name="{{ auth()->user()?->name }}">

<div id="netBanner"></div>

<div class="app">
    @include('layouts.partials.rail')

    <section id="list">
        @yield('list-panel')
    </section>

    <section id="chat">
        @yield('content')
    </section>

    <aside id="rightPanel">
        <div class="panel-scroll">
            @yield('right-panel')
        </div>
    </aside>
</div>

<nav id="mobileTabs">
    <button class="mtab active" data-mnav="chat">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M4 6.5C4 5.12 5.12 4 6.5 4h11C18.88 4 20 5.12 20 6.5v7c0 1.38-1.12 2.5-2.5 2.5H10l-3.6 3a1 1 0 0 1-1.65-.77V6.5Z" stroke="currentColor" stroke-width="1.8"/></svg>
        <span>Chats</span>
    </button>
    <button class="mtab" data-mnav="settings">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.8"/><path d="M12 3.5v2M12 18.5v2M5.5 5.5l1.4 1.4M17.1 17.1l1.4 1.4M3.5 12h2M18.5 12h2M5.5 18.5l1.4-1.4M17.1 6.9l1.4-1.4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
        <span>You</span>
    </button>
</nav>

<div id="toasts"></div>

@yield('scripts')

</body>
</html>
