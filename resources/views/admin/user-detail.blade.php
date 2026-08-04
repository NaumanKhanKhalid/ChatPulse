@extends('layouts.admin')
@section('page-title', $user->name)
@section('content')

@php
    $g   = $user->avatarGradient();
    $ini = collect(explode(' ', $user->name))->map(fn($w)=>strtoupper(substr($w,0,1)))->take(2)->join('');
    $self = $user->id === auth()->id();
@endphp

<a href="{{ route('admin.users') }}" class="adm-backlink">
    <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M10 19l-7-7m0 0 7-7m-7 7h18" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/></svg>
    All users
</a>

{{-- Profile header --}}
<div class="adm-card adm-profile">
    <div class="adm-item-av">
        <div class="avatar" style="width:64px;height:64px;background:linear-gradient(135deg,{{ $g[0] }},{{ $g[1] }});font-size:23px">{{ $ini }}</div>
        @if($user->is_online && !$user->is_banned)<span class="adm-item-dot"></span>@endif
    </div>

    <div class="adm-profile-main">
        <div class="adm-item-title" style="font-size:19px">
            <b>{{ $user->name }}</b>
            @if($user->role === 'admin')<em class="adm-tag green">admin</em>@endif
            @if($user->is_guest)<em class="adm-tag amber">guest</em>@endif
            @if($user->is_banned)<em class="adm-tag red">banned</em>@endif
            @if($self)<em class="adm-tag dim">you</em>@endif
        </div>
        <div class="adm-item-sub">
            <span>&#64;{{ $user->username ?? strtolower(str_replace(' ','_',$user->name)) }}</span>
            <span class="adm-convo-dot">·</span>
            <span>{{ $user->email ?? 'No email' }}</span>
            <span class="adm-convo-dot">·</span>
            <span>Joined {{ $user->created_at->format('M j, Y') }}</span>
        </div>
        @if($user->bio)<div class="adm-item-body">{{ $user->bio }}</div>@endif
        @if($user->is_banned)
        <div class="adm-item-note">
            Banned {{ $user->banned_at?->diffForHumans() }}@if($user->banned_reason) — {{ $user->banned_reason }}@endif
        </div>
        @endif
    </div>

    @if(!$self)
    <div class="adm-profile-acts">
        <form method="POST" action="{{ route('admin.users.logout', $user) }}" onsubmit="return confirm('Sign {{ $user->name }} out of all devices?')">
            @csrf<button type="submit" class="adm-btn-ghost">Force sign-out</button>
        </form>
        @if(!$user->isAdmin())
            @if($user->is_banned)
            <form method="POST" action="{{ route('admin.users.unban', $user) }}">
                @csrf<button type="submit" class="adm-btn-ghost" style="color:var(--primary)">Unban</button>
            </form>
            @else
            <form method="POST" action="{{ route('admin.users.ban', $user) }}"
                  onsubmit="const r = prompt('Ban reason (optional):'); if (r === null) return false; this.querySelector('[name=reason]').value = r; return true;">
                @csrf<input type="hidden" name="reason" value="">
                <button type="submit" class="adm-btn-ghost danger">Ban user</button>
            </form>
            @endif
        @endif
    </div>
    @endif
</div>

{{-- Stats --}}
<div class="adm-stats" style="margin-top:16px">
    @foreach([
        ['Messages sent', $stats['messages'], '#8b5cf6'],
        ['Last 7 days', $stats['messages_7d'], '#10b981'],
        ['Conversations', $stats['conversations'], '#3b82f6'],
        ['Reactions given', $stats['reactions'], '#f59e0b'],
    ] as [$label, $val, $tint])
    <div class="adm-stat">
        <span class="adm-stat-ic" style="color:{{ $tint }};background:{{ $tint }}1a">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M7 20V10M12 20V4M17 20v-7" stroke="currentColor" stroke-width="1.9" stroke-linecap="round"/></svg>
        </span>
        <div class="adm-stat-tx">
            <span class="adm-stat-val">{{ $val }}</span>
            <span class="adm-stat-lbl">{{ $label }}</span>
        </div>
    </div>
    @endforeach
</div>

<div class="adm-grid2" style="margin-top:16px">
    {{-- Recent messages --}}
    <div class="adm-card">
        <div class="adm-card-head"><h3>Recent messages</h3><span class="adm-card-sub">last 10</span></div>
        @forelse($recentMessages as $m)
        <div class="adm-mini">
            <div class="adm-mini-body">{{ \Illuminate\Support\Str::limit($m->body ?: '['.$m->type.']', 90) }}</div>
            <div class="adm-mini-meta">
                {{ $m->conversation?->name ?? 'Direct' }} · {{ $m->created_at->diffForHumans(null, true) }} ago
            </div>
        </div>
        @empty
        <p class="adm-empty">No messages yet.</p>
        @endforelse
    </div>

    {{-- Conversations --}}
    <div class="adm-card">
        <div class="adm-card-head"><h3>Conversations</h3><span class="adm-card-sub">{{ $stats['conversations'] }} total</span></div>
        @forelse($conversations as $c)
        <div class="adm-mini">
            <div class="adm-mini-body">
                <b>{{ $c->type === 'group' ? ($c->name ?? 'Unnamed group') : 'Direct message' }}</b>
            </div>
            <div class="adm-mini-meta">
                {{ $c->messages_count }} messages · active {{ $c->last_activity_at?->diffForHumans(null, true) ?? '—' }}
            </div>
        </div>
        @empty
        <p class="adm-empty">Not in any conversation.</p>
        @endforelse
    </div>
</div>

<div class="adm-grid2" style="margin-top:16px">
    {{-- Active sessions --}}
    <div class="adm-card">
        <div class="adm-card-head"><h3>Active sessions</h3><span class="adm-card-sub">{{ $sessions->count() }}</span></div>
        @forelse($sessions as $s)
        <div class="adm-mini">
            <div class="adm-mini-body">{{ \Illuminate\Support\Str::limit($s->user_agent ?? 'Unknown device', 70) }}</div>
            <div class="adm-mini-meta">
                IP {{ $s->ip_address ?? '—' }} · active {{ \Carbon\Carbon::createFromTimestamp($s->last_activity)->diffForHumans() }}
            </div>
        </div>
        @empty
        <p class="adm-empty">No active sessions.</p>
        @endforelse
    </div>

    {{-- Moderation history --}}
    <div class="adm-card">
        <div class="adm-card-head"><h3>Moderation history</h3></div>
        @forelse($adminActions as $log)
        @php $tint = str_contains($log->action,'unban') ? 'green' : (str_contains($log->action,'ban') || str_contains($log->action,'delete') ? 'red' : 'dim'); @endphp
        <div class="adm-mini">
            <div class="adm-mini-body">
                <span class="adm-badge {{ $tint }}">{{ $log->action }}</span>
                @if($log->details)<span style="margin-left:6px">{{ \Illuminate\Support\Str::limit($log->details, 60) }}</span>@endif
            </div>
            <div class="adm-mini-meta">by {{ $log->admin?->name ?? '—' }} · {{ $log->created_at->diffForHumans() }}</div>
        </div>
        @empty
        <p class="adm-empty">No admin actions on this account.</p>
        @endforelse
    </div>
</div>
@endsection
