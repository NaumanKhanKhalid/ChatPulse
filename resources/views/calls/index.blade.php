@extends('layouts.app')
@section('title', 'Calls')

@section('list-panel')
<div class="list-head">
    <div class="list-title-row">
        <h1 class="list-title">Calls</h1>
    </div>
</div>
<div style="padding:4px 8px 14px;flex:1;overflow-y:auto;">
    @forelse($calls as $call)
    @php
        $isMine = $call->initiated_by === auth()->id();
        $other = $isMine
            ? $call->participants->first(fn($p) => $p->id !== auth()->id())
            : $call->participants->first(fn($p) => $p->id === $call->initiated_by);
        if (!$other) $other = $call->participants->first();
        $isGroup = $call->conversation?->isGroup();
        $name = $isGroup ? $call->conversation->getDisplayName(auth()->user()) : ($other?->name ?? 'Unknown');
        $initials = collect(explode(' ', $name))->map(fn($w) => strtoupper(substr($w,0,1)))->take(2)->join('');
        $cp = $other instanceof \App\Models\User ? $other->avatarGradient() : ['#94a3b8','#334155'];
        $isMissed = $call->status === 'missed';
        $dir = $isMine ? 'outgoing' : ($isMissed ? 'missed' : 'incoming');
        $dur = $call->duration_seconds ? gmdate($call->duration_seconds >= 3600 ? 'H:i:s' : 'i:s', $call->duration_seconds) : null;
    @endphp
    <div class="call-row">
        <div class="avwrap" style="flex-shrink:0;">
            @if($other?->avatar_url)
            <img src="{{ $other->avatar_url }}" style="width:44px;height:44px;border-radius:50%;object-fit:cover;">
            @else
            <div class="avatar" style="width:44px;height:44px;background:linear-gradient(135deg,{{ $cp[0] }},{{ $cp[1] }});font-size:16px;">{{ $initials }}</div>
            @endif
        </div>
        <div class="call-info">
            <div class="cl-name {{ $isMissed ? 'missed' : '' }}">
                {{ $name }}
                @if($isGroup)<span class="call-grp">Group</span>@endif
                @if($isMissed)<span style="font-size:9.5px;font-weight:800;color:var(--busy);background:#fee2e2;padding:1px 6px;border-radius:99px;text-transform:uppercase;letter-spacing:.03em;">Missed</span>@endif
            </div>
            <div class="call-meta">
                {{-- direction arrow --}}
                @if($dir === 'incoming')
                <span class="call-dir in">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><path d="M7 17L17 7M7 7h10v10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </span>
                @elseif($dir === 'outgoing')
                <span class="call-dir out">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><path d="M17 7L7 17M17 17V7H7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </span>
                @else
                <span class="call-dir missed">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><path d="M7 17L17 7M7 7h10v10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </span>
                @endif
                {{ $call->type === 'video' ? 'Video' : 'Voice' }}
                · {{ $call->started_at->diffForHumans() }}
                @if($dur) · {{ $dur }} @endif
            </div>
        </div>
        <div class="call-actions">
            @if($call->conversation)
            <a href="{{ route('chat.conversation', $call->conversation) }}" class="call-btn" title="Open chat">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none"><path d="M4 6.5C4 5.12 5.12 4 6.5 4h11C18.88 4 20 5.12 20 6.5v7c0 1.38-1.12 2.5-2.5 2.5H10l-3.6 3a1 1 0 0 1-1.65-.77V6.5Z" stroke="currentColor" stroke-width="1.8"/></svg>
            </a>
            @endif
        </div>
    </div>
    @empty
    <div class="estate" style="height:auto;padding:60px 24px;">
        <div class="estate-ic">
            <svg width="36" height="36" viewBox="0 0 24 24" fill="none"><path d="M6.2 4.5 8 4l1.6 3.4-1.5 1.3a11 11 0 0 0 4.7 4.7l1.3-1.5L17.5 15l-.5 1.8c-.2.7-.9 1.1-1.6 1A14 14 0 0 1 4.2 6.6c-.1-.7.3-1.4 1-1.6Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/></svg>
        </div>
        <h3>No calls yet</h3>
        <p>Start a call from any conversation.</p>
    </div>
    @endforelse
</div>
@endsection

@section('content')
<div class="estate welcome" style="background:var(--bg);">
    <div class="estate-ic">
        <svg width="42" height="42" viewBox="0 0 24 24" fill="none"><path d="M6.2 4.5 8 4l1.6 3.4-1.5 1.3a11 11 0 0 0 4.7 4.7l1.3-1.5L17.5 15l-.5 1.8c-.2.7-.9 1.1-1.6 1A14 14 0 0 1 4.2 6.6c-.1-.7.3-1.4 1-1.6Z" fill="currentColor" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"/></svg>
    </div>
    <h3>Call log</h3>
    <p>Select a call to open the conversation, or start a new call from a chat.</p>
</div>
@endsection
