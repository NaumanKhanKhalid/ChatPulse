@extends('layouts.app')
@section('title', 'Messages')

@section('list-panel')

{{-- New Message Modal --}}
<div id="newChatOverlay" onclick="if(event.target===this)closeNewChat()"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);backdrop-filter:blur(6px);z-index:500;align-items:center;justify-content:center;">
    <div style="background:var(--card,#1a2420);border:1px solid var(--line);border-radius:20px;width:100%;max-width:460px;max-height:80vh;display:flex;flex-direction:column;box-shadow:0 32px 80px -12px rgba(0,0,0,.6);margin:0 16px;">

        {{-- Header --}}
        <div style="display:flex;align-items:center;justify-content:space-between;padding:22px 22px 16px;">
            <h2 style="font-size:18px;font-weight:800;color:var(--text);margin:0;">New message</h2>
            <button onclick="closeNewChat()" style="width:32px;height:32px;border-radius:50%;display:grid;place-items:center;background:var(--hover);border:none;cursor:pointer;color:var(--text2);">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M18 6 6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
            </button>
        </div>

        {{-- Search --}}
        <div style="padding:0 16px 14px;">
            <div style="display:flex;align-items:center;gap:10px;background:var(--hover);border:1.5px solid var(--line);border-radius:12px;padding:0 14px;height:46px;transition:.15s;" onfocusin="this.style.borderColor='var(--primary)'" onfocusout="this.style.borderColor='var(--line)'">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" style="color:var(--text3);flex-shrink:0;"><circle cx="11" cy="11" r="6.5" stroke="currentColor" stroke-width="1.8"/><path d="m20 20-3.2-3.2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                <input id="peopleSearch" placeholder="Search people by name or @username…" oninput="filterPeople(this.value)"
                       style="flex:1;background:none;border:none;outline:none;font-size:14px;color:var(--text);font-family:inherit;" />
            </div>
        </div>

        {{-- New Group row --}}
        <div style="padding:0 16px 6px;">
            <a href="{{ route('groups.create') }}" style="display:flex;align-items:center;gap:14px;padding:12px 14px;border-radius:14px;text-decoration:none;transition:.12s;" onmouseover="this.style.background='var(--hover)'" onmouseout="this.style.background=''">
                <div style="width:44px;height:44px;border-radius:50%;background:rgba(16,185,129,.15);display:grid;place-items:center;flex-shrink:0;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><circle cx="9" cy="7" r="3" stroke="#10b981" stroke-width="1.8"/><circle cx="17" cy="9" r="2.5" stroke="#10b981" stroke-width="1.8"/><path d="M2 19c0-3.3 3.1-6 7-6s7 2.7 7 6" stroke="#10b981" stroke-width="1.8" stroke-linecap="round"/><path d="M19 14c1.7.8 3 2.3 3 4" stroke="#10b981" stroke-width="1.8" stroke-linecap="round"/></svg>
                </div>
                <div style="flex:1;">
                    <div style="font-size:14.5px;font-weight:700;color:var(--text);">New group</div>
                    <div style="font-size:12.5px;color:var(--text3);margin-top:1px;">Start a conversation with multiple people</div>
                </div>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" style="color:var(--text3);"><path d="m9 18 6-6-6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </a>
        </div>

        {{-- People list --}}
        <div style="padding:8px 16px 4px;">
            <p style="font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:var(--text3);margin:0 0 6px 4px;">People</p>
        </div>
        <div id="peopleList" style="flex:1;overflow-y:auto;padding:0 10px 14px;">
            @foreach($allUsers as $person)
            @php
                $pInitials = collect(explode(' ', $person->name))->map(fn($w) => strtoupper(substr($w,0,1)))->take(2)->join('');
                $colors = [['#818cf8','#7c3aed'],['#7dd3fc','#2563eb'],['#c4b5fd','#7c3aed'],['#6ee7b7','#0d9488'],['#fcd34d','#ea580c'],['#f0abfc','#a21caf'],['#fda4af','#e11d48']];
                $cp = $colors[$person->id % count($colors)];
                $statusText = $person->is_online ? 'Active now' : ($person->last_seen_at ? 'Last seen ' . $person->last_seen_at->diffForHumans() : 'Offline');
                $statusColor = $person->is_online ? 'var(--online)' : 'var(--text3)';
            @endphp
            <form method="POST" action="{{ route('people.dm', $person) }}" style="margin:0;" data-pname="{{ strtolower($person->name . ' ' . $person->username) }}">
                @csrf
                <button type="submit" style="display:flex;align-items:center;gap:14px;width:100%;padding:10px 12px;border-radius:14px;background:none;border:none;cursor:pointer;text-align:left;font-family:inherit;transition:.12s;" onmouseover="this.style.background='var(--hover)'" onmouseout="this.style.background=''">
                    <div style="position:relative;flex-shrink:0;">
                        @if($person->avatar_url)
                        <img src="{{ $person->avatar_url }}" alt="{{ $person->name }}" style="width:44px;height:44px;border-radius:50%;object-fit:cover;">
                        @else
                        <div style="width:44px;height:44px;border-radius:50%;background:linear-gradient(135deg,{{ $cp[0] }},{{ $cp[1] }});display:grid;place-items:center;font-size:15px;font-weight:700;color:#fff;">{{ $pInitials }}</div>
                        @endif
                        <span style="position:absolute;bottom:1px;right:1px;width:11px;height:11px;border-radius:50%;background:{{ $person->is_online ? 'var(--online)' : 'var(--line)' }};border:2px solid var(--card,#1a2420);"></span>
                    </div>
                    <div style="flex:1;min-width:0;">
                        <div style="font-size:14.5px;font-weight:700;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $person->name }}</div>
                        <div style="font-size:12.5px;color:var(--text3);margin-top:1px;">@{{ $person->username ?? strtolower(str_replace(' ','_',$person->name)) }} · <span style="color:{{ $statusColor }};">{{ $statusText }}</span></div>
                    </div>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" style="color:var(--text3);flex-shrink:0;"><path d="m9 18 6-6-6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
            </form>
            @endforeach
        </div>
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
    document.body.style.overflow = 'hidden';
    setTimeout(() => document.getElementById('peopleSearch')?.focus(), 80);
}
function closeNewChat() {
    document.getElementById('newChatOverlay').style.display = 'none';
    document.body.style.overflow = '';
}
document.addEventListener('keydown', e => { if(e.key==='Escape') closeNewChat(); });
function filterPeople(q) {
    q = q.toLowerCase();
    document.querySelectorAll('#peopleList form[data-pname]').forEach(el => {
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
