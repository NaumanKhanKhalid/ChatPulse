@extends('layouts.app')
@section('title', $conversation->getDisplayName(auth()->user()))

@php
$conversations = app(\App\Services\ConversationService::class)->getUserConversations(auth()->user());
$convName = $conversation->getDisplayName(auth()->user());
$other = $conversation->isDirect() ? $conversation->getOtherUser(auth()->user()) : null;
$isGroup = $conversation->isGroup();
$members = $conversation->members ?? collect();
$colors = [['#818cf8','#7c3aed'],['#7dd3fc','#2563eb'],['#c4b5fd','#7c3aed'],['#6ee7b7','#0d9488'],['#fcd34d','#ea580c'],['#f0abfc','#a21caf'],['#fda4af','#e11d48']];
$getInitials = fn($n) => collect(explode(' ', $n))->map(fn($w) => strtoupper(substr($w,0,1)))->take(2)->join('');
@endphp

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
        <input placeholder="Search messages" oninput="filterConvos(this.value)" />
    </div>
</div>
<div class="filters">
    <button class="filter on" onclick="setFilter('all',this)">All</button>
    <button class="filter" onclick="setFilter('unread',this)">Unread</button>
    <button class="filter" onclick="setFilter('groups',this)">Groups</button>
</div>
<div id="convoList">
    @foreach($conversations as $conv)
    @php
        $cName = $conv->getDisplayName(auth()->user());
        $cUnread = $conv->getUnreadCountFor(auth()->user());
        $cLastMsg = $conv->lastMessage;
        $cOther = $conv->isDirect() ? $conv->getOtherUser(auth()->user()) : null;
        $cIsActive = $conv->id === $conversation->id;
        $cIsGroup = $conv->isGroup();
        $cInitials = $getInitials($cName);
        $cColor = $colors[$conv->id % count($colors)];
    @endphp
    <a href="{{ route('chat.conversation', $conv) }}"
       class="convo {{ $cIsActive ? 'active' : '' }} {{ $cIsGroup ? 'is-group' : 'is-dm' }} {{ $cUnread > 0 ? 'has-unread' : '' }}"
       data-name="{{ strtolower($cName) }}">
        <div class="avwrap">
            @if($cOther && $cOther->avatar_url)
            <img src="{{ $cOther->avatar_url }}" alt="{{ $cName }}" style="width:44px;height:44px;border-radius:50%;object-fit:cover;flex-shrink:0;">
            @else
            <div class="avatar" style="width:44px;height:44px;background:linear-gradient(135deg,{{ $cColor[0] }},{{ $cColor[1] }});font-size:16px;">{{ $cInitials }}</div>
            @endif
            @if($cOther)
            <span class="pres" style="background:{{ $cOther->is_online ? 'var(--online)' : 'var(--line)' }};"></span>
            @endif
        </div>
        <div class="convo-main">
            <div class="convo-top">
                <span class="convo-name {{ $cUnread > 0 ? 'un' : '' }}">{{ $cName }}</span>
                @if($cLastMsg)
                <span class="convo-time {{ $cUnread > 0 ? 'un' : '' }}">{{ $cLastMsg->created_at->format('H:i') }}</span>
                @endif
            </div>
            <div class="convo-bot">
                <span class="convo-last {{ $cUnread > 0 ? 'un' : '' }}">
                    @if($cLastMsg?->user_id === auth()->id()) You: @endif{{ $cLastMsg?->body ?? 'No messages yet' }}
                </span>
                @if($cUnread > 0)
                <span class="badge">{{ $cUnread > 9 ? '9+' : $cUnread }}</span>
                @endif
            </div>
        </div>
    </a>
    @endforeach
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
<div x-data="chatConversation({{ $conversation->id }}, {{ auth()->id() }}, {{ json_encode($messages) }})"
     style="display:flex;flex-direction:column;height:100%;background:var(--bg);">

    {{-- Header --}}
    <header id="chatHeader">
        <div class="avwrap" style="position:relative;flex-shrink:0;">
            @if($other && $other->avatar_url)
            <img src="{{ $other->avatar_url }}" alt="{{ $convName }}" style="width:40px;height:40px;border-radius:50%;object-fit:cover;">
            @else
            @php $convColor = $colors[$conversation->id % count($colors)]; $convInitials = $getInitials($convName); @endphp
            <div class="avatar" style="width:40px;height:40px;background:linear-gradient(135deg,{{ $convColor[0] }},{{ $convColor[1] }});font-size:15px;">{{ $convInitials }}</div>
            @endif
            @if($other)
            <span class="pres" style="background:{{ $other->is_online ? 'var(--online)' : 'var(--line)' }};border-color:var(--bg);"></span>
            @endif
        </div>
        <div class="hdr-info">
            <p class="hdr-name">{{ $convName }}</p>
            <p class="hdr-sub">
                @if($other)
                    <span :class="''" style="color:{{ $other->is_online ? 'var(--online)' : 'var(--text3)' }}">
                        {{ $other->is_online ? 'Online' : 'Offline' }}
                    </span>
                @elseif($isGroup)
                    {{ $members->count() }} members
                @endif
                <span x-show="typing" style="color:var(--primary);font-weight:600;" x-transition>
                    &nbsp;· typing…
                </span>
            </p>
        </div>
        @if($isGroup)
        <div class="hdr-stack" style="margin-right:8px;">
            @foreach($members->take(4) as $member)
            @php $mi = $getInitials($member->name); $mc = $colors[$member->id % count($colors)]; @endphp
            <div class="mini-av" style="background:linear-gradient(135deg,{{ $mc[0] }},{{ $mc[1] }});">{{ $mi }}</div>
            @endforeach
            @if($members->count() > 4)
            <div class="mini-av more">+{{ $members->count() - 4 }}</div>
            @endif
        </div>
        @endif
        <div class="hdr-actions">
            <button class="hbtn" title="Voice call">
                <svg width="19" height="19" viewBox="0 0 24 24" fill="none"><path d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11 11 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498A1 1 0 0121 15.72V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" stroke="currentColor" stroke-width="1.8"/></svg>
            </button>
            <button class="hbtn" title="Video call">
                <svg width="19" height="19" viewBox="0 0 24 24" fill="none"><path d="M15 10l4.553-2.069A1 1 0 0121 8.87v6.26a1 1 0 01-1.447.894L15 14M3 8a2 2 0 012-2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V8z" stroke="currentColor" stroke-width="1.8"/></svg>
            </button>
            <button class="hbtn" title="Search in conversation">
                <svg width="19" height="19" viewBox="0 0 24 24" fill="none"><circle cx="11" cy="11" r="6.5" stroke="currentColor" stroke-width="1.8"/><path d="m20 20-3.2-3.2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
            </button>
        </div>
    </header>

    {{-- Message thread --}}
    <div id="thread" x-ref="messagesContainer" @scroll="handleScroll($event)">

        {{-- Loading skeleton --}}
        <div x-show="loading" style="display:flex;flex-direction:column;gap:16px;">
            <template x-for="i in 5" :key="i">
                <div style="display:flex;gap:11px;padding:0 10px;">
                    <div class="sk sk-circle" style="width:38px;height:38px;border-radius:50%;flex-shrink:0;"></div>
                    <div style="flex:1;display:flex;flex-direction:column;gap:7px;">
                        <div class="sk" style="height:14px;width:120px;border-radius:6px;"></div>
                        <div class="sk" style="height:46px;border-radius:5px 15px 15px 15px;max-width:420px;"></div>
                    </div>
                </div>
            </template>
        </div>

        {{-- Messages --}}
        <template x-for="(message, index) in messages" :key="message.id ?? message.temp_id">
            <div>
                {{-- Day divider --}}
                <template x-if="shouldShowDayDivider(index)">
                    <div class="day"><span x-text="getDayLabel(message)"></span></div>
                </template>

                {{-- Unread divider --}}
                <template x-if="message.id === firstUnreadId && firstUnreadId">
                    <div class="unread-div"><span>New Messages</span></div>
                </template>

                <div class="msg"
                     :class="{
                         'mine': message.user_id == currentUserId,
                         'grouped': isGrouped(index),
                     }"
                     :id="'msg-' + (message.id ?? message.temp_id)">

                    {{-- Avatar / gutter --}}
                    <template x-if="message.user_id != currentUserId">
                        <div class="b-av">
                            <template x-if="!isGrouped(index)">
                                <div class="avatar" style="width:38px;height:38px;font-size:13px;"
                                     :style="'background:linear-gradient(135deg,' + (message.sender_gradient ? message.sender_gradient[0] : '#10b981') + ',' + (message.sender_gradient ? message.sender_gradient[1] : '#059669') + ')'">
                                    <span x-text="message.sender_initials || message.sender_name?.split(' ').map(w=>w[0]).join('').toUpperCase().slice(0,2) || '?'"></span>
                                </div>
                            </template>
                            <template x-if="isGrouped(index)">
                                <span class="b-gutter"></span>
                            </template>
                        </div>
                    </template>
                    <template x-if="message.user_id == currentUserId">
                        <span class="b-gutter" style="order:1;"></span>
                    </template>

                    {{-- Body --}}
                    <div class="b-body" :style="message.user_id == currentUserId ? 'display:flex;flex-direction:column;align-items:flex-end;' : ''">

                        {{-- Sender name + time --}}
                        <template x-if="!isGrouped(index)">
                            <div class="b-head">
                                <span class="b-name" x-show="message.user_id != currentUserId" x-text="message.sender_name || 'Unknown'"></span>
                                <span class="b-time" x-text="formatTime(message.created_at)"></span>
                            </div>
                        </template>

                        {{-- Reply quote --}}
                        <template x-if="message.reply_to_id && message.reply_to">
                            <div class="reply-quote">
                                <div class="reply-bar"></div>
                                <div>
                                    <span class="reply-name" x-text="message.reply_to?.sender_name ?? 'Unknown'"></span>
                                    <span class="reply-text" x-text="(message.reply_to?.body ?? '').slice(0,80)"></span>
                                </div>
                            </div>
                        </template>

                        {{-- Message bubble --}}
                        <div class="b-text"
                             :class="{ 'mine': message.user_id == currentUserId }"
                             :style="message.user_id == currentUserId ? 'background:var(--bubble-mine);border-color:transparent;border-radius:15px 15px 5px 15px;' : 'background:var(--bubble-in);border-color:var(--bubble-in-border);border-radius:5px 15px 15px 15px;'"
                             x-text="message.body">
                        </div>

                        {{-- Reactions --}}
                        <template x-if="message.reactions && Object.keys(message.reactions).length > 0">
                            <div class="reax">
                                <template x-for="[emoji, users] in Object.entries(message.reactions ?? {})" :key="emoji">
                                    <button class="reax-pill" :class="users.includes(currentUserId) ? 'mine' : ''"
                                            @click="toggleReaction(message.id, emoji)">
                                        <span x-text="emoji"></span>
                                        <span x-text="users.length"></span>
                                    </button>
                                </template>
                                <button class="reax-add" @click="openReactionPicker(message.id, $event)" title="Add reaction">+</button>
                            </div>
                        </template>

                        {{-- Sent time + read indicator (own messages) --}}
                        <template x-if="message.user_id == currentUserId">
                            <div style="display:flex;align-items:center;gap:4px;margin-top:3px;">
                                <span style="font-size:10.5px;color:var(--text3);" x-text="formatTime(message.created_at)"></span>
                                <span x-show="message.sending" style="font-size:10px;color:var(--text3);opacity:.7;">sending…</span>
                                <svg x-show="!message.sending" width="16" height="16" viewBox="0 0 24 24" fill="none"
                                     :style="message.is_read ? 'color:#3b82f6;' : 'color:var(--text3);'">
                                    <path d="M4 12l5 5L20 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </div>
                        </template>
                    </div>

                    {{-- Hover actions --}}
                    <div class="msg-tools">
                        <button class="tbtn" @click="openReactionPicker(message.id, $event)" title="React">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="8.3" stroke="currentColor" stroke-width="1.7"/><circle cx="9.3" cy="10.3" r="1.1" fill="currentColor"/><circle cx="14.7" cy="10.3" r="1.1" fill="currentColor"/><path d="M8.8 14.2a4 4 0 0 0 6.4 0" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
                        </button>
                        <button class="tbtn" @click="setReply(message)" title="Reply">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M9 10L5 14l4 4M5 14h8a6 6 0 0 1 6 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </button>
                        <template x-if="message.user_id == currentUserId">
                            <button class="tbtn" @click="deleteMessage(message.id)" title="Delete" style="color:var(--busy);">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M5 7h14M10 7V5h4v2M6 7l1 13h10l1-13" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </button>
                        </template>
                    </div>
                </div>
            </div>
        </template>

        {{-- Typing indicator --}}
        <div class="typing-row" x-show="typing" x-transition style="display:none;">
            <div class="avatar" style="width:38px;height:38px;font-size:13px;background:linear-gradient(135deg,#7dd3fc,#2563eb);">
                <span x-text="typingName?.split(' ').map(w=>w[0]).join('').toUpperCase().slice(0,2) || '?'"></span>
            </div>
            <div class="typing-bubble">
                <span class="d"></span><span class="d"></span><span class="d"></span>
            </div>
        </div>
    </div>

    {{-- Reply bar --}}
    <div id="replyBar" x-show="replyTo" :class="replyTo ? 'show' : ''" style="display:none;">
        <div class="reply-bar" style="height:30px;"></div>
        <div class="rb-text" style="flex:1;min-width:0;">
            <span class="rb-name" x-text="replyTo?.sender_name ?? 'Message'"></span>
            <span class="rb-msg" x-text="(replyTo?.body ?? '').slice(0,60)"></span>
        </div>
        <button id="rbCancel" @click="replyTo = null" style="color:var(--text3);font-size:20px;line-height:1;padding:4px;">×</button>
    </div>

    {{-- Composer --}}
    <div class="composer-wrap">
        <div class="composer">
            <button class="ct" title="Attach">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="1.9" stroke-linecap="round"/></svg>
            </button>
            <textarea id="composer-input"
                      x-model="newMessage"
                      @keydown.enter.prevent="$event.shiftKey ? newMessage += '\n' : sendMessage()"
                      @input="onTyping()"
                      placeholder="Message…"
                      rows="1"
                      style="flex:1;min-height:38px;max-height:140px;overflow-y:auto;padding:9px 6px;font-size:14.5px;line-height:1.4;outline:none;background:none;border:none;color:var(--text);font-family:inherit;resize:none;"></textarea>
            <button class="ct" title="Emoji">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="8.3" stroke="currentColor" stroke-width="1.7"/><circle cx="9.3" cy="10.3" r="1.1" fill="currentColor"/><circle cx="14.7" cy="10.3" r="1.1" fill="currentColor"/><path d="M8.8 14.2a4 4 0 0 0 6.4 0" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
            </button>
            <button id="sendBtn" @click="sendMessage()" title="Send">
                <svg width="19" height="19" viewBox="0 0 24 24" fill="none"><path d="M5 12 19 5l-4 14-3.5-5.5L5 12Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/></svg>
            </button>
        </div>
    </div>
</div>
@endsection

@section('right-panel')
<div class="panel-hero">
    <div class="avwrap" style="display:flex;justify-content:center;margin-bottom:12px;">
        @php $convColor = $colors[$conversation->id % count($colors)]; $convInitials = $getInitials($convName); @endphp
        @if($other && $other->avatar_url)
        <img src="{{ $other->avatar_url }}" alt="{{ $convName }}" style="width:72px;height:72px;border-radius:50%;object-fit:cover;">
        @else
        <div class="avatar" style="width:72px;height:72px;background:linear-gradient(135deg,{{ $convColor[0] }},{{ $convColor[1] }});font-size:26px;">{{ $convInitials }}</div>
        @endif
    </div>
    <p class="panel-name">{{ $convName }}</p>
    <p class="panel-sub">
        @if($other) {{ $other->username ? '@'.$other->username : '' }} @else {{ $members->count() }} members @endif
    </p>
    <div class="panel-quick">
        <button class="qbtn" title="Voice call">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11 11 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498A1 1 0 0121 15.72V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" stroke="currentColor" stroke-width="1.8"/></svg>
        </button>
        <button class="qbtn" title="Video call">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M15 10l4.553-2.069A1 1 0 0121 8.87v6.26a1 1 0 01-1.447.894L15 14M3 8a2 2 0 012-2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V8z" stroke="currentColor" stroke-width="1.8"/></svg>
        </button>
        @if(!$other)
        <a href="{{ route('groups.explore') }}" class="qbtn" title="Group info">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="8" stroke="currentColor" stroke-width="1.8"/><path d="M12 8v4M12 16h.01" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
        </a>
        @endif
    </div>
</div>

@if($isGroup && $members->count())
<div class="psec">
    <div class="psec-h">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M17 20h5v-2a3 3 0 00-5.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" stroke="currentColor" stroke-width="1.8"/></svg>
        Members
    </div>
    @foreach($members->take(8) as $member)
    @php $mi = $getInitials($member->name); $mc = $colors[$member->id % count($colors)]; @endphp
    <div style="display:flex;align-items:center;gap:10px;padding:6px 0;">
        <div class="avatar" style="width:36px;height:36px;background:linear-gradient(135deg,{{ $mc[0] }},{{ $mc[1] }});font-size:13px;flex-shrink:0;">{{ $mi }}</div>
        <div>
            <div style="font-size:13.5px;font-weight:700;color:var(--text);">{{ $member->name }}</div>
            <div style="font-size:11.5px;color:var(--text3);">{{ $member->username ? '@'.$member->username : '' }}</div>
        </div>
    </div>
    @endforeach
</div>
@endif
@endsection
