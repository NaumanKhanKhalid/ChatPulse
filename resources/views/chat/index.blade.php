@extends('layouts.app')
@section('title', 'Messages')

@section('list-panel')
<div class="list-head">
    <div class="list-title-row">
        <h1 class="list-title">Messages</h1>
        <a href="{{ route('groups.create') }}" class="list-new" title="New Group">
            <svg width="19" height="19" viewBox="0 0 24 24" fill="none"><path d="M16.5 4.5 19.5 7.5 9 18l-3.6.6.6-3.6L16.5 4.5Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/></svg>
        </a>
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
                <span class="convo-name {{ $unread > 0 ? 'un' : '' }}">{{ $name }}</span>
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
        <p>Find people to start chatting with.</p>
        <a href="{{ route('people.index') }}" style="margin-top:16px;display:inline-flex;align-items:center;gap:6px;padding:10px 18px;border-radius:12px;background:var(--primary);color:#fff;font-size:13.5px;font-weight:700;text-decoration:none;">Find people</a>
    </div>
    @endforelse
</div>
<script>
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
    <a href="{{ route('people.index') }}" class="estate-cta">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none"><path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
        New Chat
    </a>
</div>
@endsection
