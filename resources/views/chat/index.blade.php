@extends('layouts.app')
@section('title', 'Messages')

@section('list-panel')

{{-- New Chat Overlay (slides over the list) --}}
<div id="newChatOverlay" style="display:none;position:absolute;inset:0;background:var(--side);z-index:10;display:flex;flex-direction:column;">
    <div class="list-head" style="border-bottom:1px solid var(--line2);">
        <div class="list-title-row">
            <button onclick="closeNewChat()" style="width:32px;height:32px;border-radius:9px;display:grid;place-items:center;color:var(--text2);" onmouseover="this.style.background='var(--hover)'" onmouseout="this.style.background=''">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M19 12H6m0 0 5-5m-5 5 5 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </button>
            <h2 style="font-size:16px;font-weight:800;flex:1;margin:0 8px;">New message</h2>
        </div>
        <div class="search" style="margin-top:12px;">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none"><circle cx="11" cy="11" r="6.5" stroke="currentColor" stroke-width="1.8"/><path d="m20 20-3.2-3.2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
            <input id="peopleSearch" placeholder="Search people…" oninput="filterPeople(this.value)" autofocus />
        </div>
    </div>
    <div id="peopleList" style="flex:1;overflow-y:auto;padding:6px 8px 14px;">
        @foreach($allUsers as $person)
        @php
            $pInitials = collect(explode(' ', $person->name))->map(fn($w) => strtoupper(substr($w,0,1)))->take(2)->join('');
            $colors = [['#818cf8','#7c3aed'],['#7dd3fc','#2563eb'],['#c4b5fd','#7c3aed'],['#6ee7b7','#0d9488'],['#fcd34d','#ea580c'],['#f0abfc','#a21caf'],['#fda4af','#e11d48']];
            $cp = $colors[$person->id % count($colors)];
        @endphp
        <form method="POST" action="{{ route('people.dm', $person) }}" style="margin:0;">
            @csrf
            <button type="submit" class="convo" data-pname="{{ strtolower($person->name) }}" style="width:100%;text-align:left;">
                <div class="avwrap">
                    @if($person->avatar_url)
                    <img src="{{ $person->avatar_url }}" alt="{{ $person->name }}" style="width:44px;height:44px;border-radius:50%;object-fit:cover;">
                    @else
                    <div class="avatar" style="width:44px;height:44px;background:linear-gradient(135deg,{{ $cp[0] }},{{ $cp[1] }});font-size:16px;">{{ $pInitials }}</div>
                    @endif
                    <span class="pres" style="background:{{ $person->is_online ? 'var(--online)' : 'var(--line)' }};"></span>
                </div>
                <div class="convo-main">
                    <div class="convo-top">
                        <span class="convo-name">{{ $person->name }}</span>
                        <span style="font-size:11px;color:{{ $person->is_online ? 'var(--online)' : 'var(--text3)' }};font-weight:600;">{{ $person->is_online ? 'Online' : 'Offline' }}</span>
                    </div>
                    <div class="convo-bot">
                        <span class="convo-last">{{ '@' . ($person->username ?? strtolower(str_replace(' ','_',$person->name))) }}</span>
                    </div>
                </div>
            </button>
        </form>
        @endforeach
    </div>
</div>

<div class="list-head">
    <div class="list-title-row">
        <h1 class="list-title">Messages</h1>
        <button onclick="openNewChat()" class="list-new" title="New message">
            <svg width="19" height="19" viewBox="0 0 24 24" fill="none"><path d="M16.5 4.5 19.5 7.5 9 18l-3.6.6.6-3.6L16.5 4.5Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/></svg>
        </button>
    </div>
    <div class="search">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none"><circle cx="11" cy="11" r="6.5" stroke="currentColor" stroke-width="1.8"/><path d="m20 20-3.2-3.2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
        <input id="convSearch" placeholder="Search messages" oninput="filterConvos(this.value)" />
    </div>
</div>
<div class="filters">
    <button class="filter on" onclick="setFilter('all',this)">All</button>
    <button class="filter" onclick="setFilter('unread',this)">Unread</button>
    <button class="filter" onclick="setFilter('groups',this)">Groups</button>
    <button class="filter" onclick="setFilter('scheduled',this)">Scheduled</button>
</div>
<div id="convoList">
    @forelse($conversations as $conversation)
    @php
        $name = $conversation->getDisplayName(auth()->user());
        $unread = $conversation->getUnreadCountFor(auth()->user());
        $lastMsg = $conversation->lastMessage;
        $other = $conversation->isDirect() ? $conversation->getOtherUser(auth()->user()) : null;
        $isGroup = $conversation->isGroup();
        $initials = collect(explode(' ', $name))->map(fn($w) => strtoupper(substr($w,0,1)))->take(2)->join('');
        $colors = [['#818cf8','#7c3aed'],['#7dd3fc','#2563eb'],['#c4b5fd','#7c3aed'],['#6ee7b7','#0d9488'],['#fcd34d','#ea580c'],['#f0abfc','#a21caf'],['#fda4af','#e11d48']];
        $colorPair = $colors[$conversation->id % count($colors)];
        $isActive = request()->route('conversation') && request()->route('conversation')->id === $conversation->id;
    @endphp
    <a href="{{ route('chat.conversation', $conversation) }}"
       class="convo {{ $isActive ? 'active' : '' }} {{ $isGroup ? 'is-group' : 'is-dm' }} {{ $unread > 0 ? 'has-unread' : '' }}"
       data-name="{{ strtolower($name) }}">
        <div class="avwrap">
            @if($other && $other->avatar_url)
            <img src="{{ $other->avatar_url }}" alt="{{ $name }}" style="width:44px;height:44px;border-radius:50%;object-fit:cover;flex-shrink:0;">
            @else
            <div class="avatar" style="width:44px;height:44px;background:linear-gradient(135deg,{{ $colorPair[0] }},{{ $colorPair[1] }});font-size:16px;">{{ $initials }}</div>
            @endif
            @if($other)
            <span class="pres" style="background:{{ $other->is_online ? 'var(--online)' : 'var(--line)' }};"></span>
            @endif
        </div>
        <div class="convo-main">
            <div class="convo-top">
                <span class="convo-name {{ $unread > 0 ? 'un' : '' }}">
                    {{ $name }}
                    @if($isGroup)
                    <span style="font-size:9.5px;font-weight:800;text-transform:uppercase;letter-spacing:.04em;color:#b45309;background:#fef3c7;padding:1px 6px;border-radius:99px;">Group</span>
                    @endif
                </span>
                @if($lastMsg)
                <span class="convo-time {{ $unread > 0 ? 'un' : '' }}">{{ $lastMsg->created_at->format('H:i') }}</span>
                @endif
            </div>
            <div class="convo-bot">
                <span class="convo-last {{ $unread > 0 ? 'un' : '' }}">
                    @if($lastMsg?->user_id === auth()->id()) You: @endif{{ $lastMsg?->body ?? 'No messages yet' }}
                </span>
                @if($unread > 0)
                <span class="badge">{{ $unread > 9 ? '9+' : $unread }}</span>
                @endif
            </div>
        </div>
    </a>
    @empty
    <div class="estate" style="height:auto;padding:60px 24px;">
        <div class="estate-ic">
            <svg width="36" height="36" viewBox="0 0 24 24" fill="none"><path d="M4 6.5C4 5.12 5.12 4 6.5 4h11C18.88 4 20 5.12 20 6.5v7c0 1.38-1.12 2.5-2.5 2.5H10l-3.6 3a1 1 0 0 1-1.65-.77V6.5Z" stroke="currentColor" stroke-width="1.5"/></svg>
        </div>
        <h3>No conversations yet</h3>
        <p>Click the pencil icon above to start chatting.</p>
        <button onclick="openNewChat()" style="margin-top:16px;display:inline-flex;align-items:center;gap:6px;padding:10px 18px;border-radius:12px;background:var(--primary);color:#fff;font-size:13.5px;font-weight:700;border:none;cursor:pointer;">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
            New Chat
        </button>
    </div>
    @endforelse
</div>

<script>
function openNewChat() {
    const ov = document.getElementById('newChatOverlay');
    ov.style.display = 'flex';
    setTimeout(() => document.getElementById('peopleSearch')?.focus(), 50);
}
function closeNewChat() {
    document.getElementById('newChatOverlay').style.display = 'none';
}
function filterPeople(q) {
    q = q.toLowerCase();
    document.querySelectorAll('#peopleList [data-pname]').forEach(el => {
        el.style.display = !q || el.dataset.pname.includes(q) ? '' : 'none';
    });
}
function filterConvos(q) {
    q = q.toLowerCase();
    document.querySelectorAll('#convoList .convo').forEach(el => {
        el.style.display = !q || el.dataset.name.includes(q) ? '' : 'none';
    });
}
function setFilter(type, btn) {
    document.querySelectorAll('.filter').forEach(b => b.classList.remove('on'));
    btn.classList.add('on');
    document.querySelectorAll('#convoList .convo').forEach(el => {
        let show = true;
        if (type === 'unread') show = el.classList.contains('has-unread');
        if (type === 'groups') show = el.classList.contains('is-group');
        if (type === 'scheduled') show = el.classList.contains('has-scheduled');
        el.style.display = show ? '' : 'none';
    });
}
</script>
@endsection

@section('content')
<div class="estate welcome" style="background:var(--bg);">
    <div class="estate-ic">
        <svg width="42" height="42" viewBox="0 0 24 24" fill="none"><path d="M5 9.5C5 6.46 7.46 4 10.5 4h3C16.54 4 19 6.46 19 9.5S16.54 15 13.5 15H9l-3.2 2.9c-.5.46-1.3.1-1.3-.58V9.5Z" fill="currentColor"/></svg>
    </div>
    <h3>Welcome to ChatPulse</h3>
    <p>Select a conversation on the left to get started, or start a new chat.</p>
    <button onclick="openNewChat()" class="estate-cta" style="border:none;cursor:pointer;">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none"><path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
        New Chat
    </button>
</div>
@endsection
