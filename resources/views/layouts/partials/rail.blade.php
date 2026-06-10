@php
$railUser = auth()->user();
$railGrad = $railUser->avatarGradient();
$railInitials = collect(explode(' ', $railUser->name))->map(fn($w) => strtoupper(substr($w,0,1)))->take(2)->join('');
$statusMap = ['available' => '#10b981', 'busy' => '#ef4444', 'away' => '#f59e0b'];
$railStatusColor = $statusMap[$railUser->status_type ?? 'available'] ?? '#10b981';
try { $railUnread = $railUser->conversations->sum(fn($c) => $c->getUnreadCountFor($railUser)); } catch(\Exception $e) { $railUnread = 0; }
@endphp
<nav id="rail">
    <div class="rail-logo">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><path d="M5 9.5C5 6.46 7.46 4 10.5 4h3C16.54 4 19 6.46 19 9.5S16.54 15 13.5 15H9l-3.2 2.9c-.5.46-1.3.1-1.3-.58V9.5Z" fill="#fff"/><circle cx="9.5" cy="9.5" r="1.2" fill="#10b981"/><circle cx="13.5" cy="9.5" r="1.2" fill="#10b981"/></svg>
    </div>

    <button class="rail-btn active" data-nav="chat" title="Messages">
        <svg width="21" height="21" viewBox="0 0 24 24" fill="none"><path d="M4 6.5C4 5.12 5.12 4 6.5 4h11C18.88 4 20 5.12 20 6.5v7c0 1.38-1.12 2.5-2.5 2.5H10l-3.6 3a1 1 0 0 1-1.65-.77V6.5Z" stroke="currentColor" stroke-width="1.8"/></svg>
        @if($railUnread > 0)<span class="rb-badge">{{ $railUnread > 9 ? '9+' : $railUnread }}</span>@endif
    </button>

    <div class="rail-div"></div>

    @if($railUser->isAdmin())
    <button class="rail-btn" data-nav="admin" title="Admin">
        <svg width="21" height="21" viewBox="0 0 24 24" fill="none"><path d="M12 3.5 5 6v5c0 4.5 3 8 7 9.5 4-1.5 7-5 7-9.5V6l-7-2.5Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/></svg>
    </button>
    @endif

    @if(!$railUser->is_guest)
    <button class="rail-btn" data-nav="settings" title="Settings">
        <svg width="21" height="21" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.8"/><path d="M12 3.5v2M12 18.5v2M5.5 5.5l1.4 1.4M17.1 17.1l1.4 1.4M3.5 12h2M18.5 12h2M5.5 18.5l1.4-1.4M17.1 6.9l1.4-1.4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
    </button>
    @endif

    <div class="rail-spacer"></div>

    <button class="rail-btn rail-ava" title="{{ $railUser->name }}">
        <div class="avatar" style="width:34px;height:34px;background:linear-gradient(135deg,{{ $railGrad[0] }},{{ $railGrad[1] }});font-size:13px">{{ $railInitials }}</div>
        <span class="pres" style="background:{{ $railStatusColor }}"></span>
    </button>

    <button class="rail-btn" id="darkToggle" title="Toggle theme">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><path d="M21 13A9 9 0 1 1 11 3a7 7 0 0 0 10 10Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/></svg>
    </button>
</nav>
