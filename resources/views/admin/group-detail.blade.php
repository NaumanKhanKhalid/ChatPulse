@extends('layouts.admin')
@section('page-title', $conversation->name ?? 'Group')
@section('content')

@php $ini = collect(explode(' ', $conversation->name ?? 'G'))->map(fn($w)=>strtoupper(substr($w,0,1)))->take(2)->join(''); @endphp

<a href="{{ route('admin.groups') }}" class="adm-backlink">
    <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M10 19l-7-7m0 0 7-7m-7 7h18" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/></svg>
    All groups
</a>

<div class="adm-card adm-profile">
    <div class="avatar" style="width:60px;height:60px;background:linear-gradient(135deg,#818cf8,#7c3aed);font-size:21px;flex-shrink:0">{{ $ini }}</div>
    <div class="adm-profile-main">
        <div class="adm-item-title" style="font-size:19px">
            <b>{{ $conversation->name ?? 'Unnamed group' }}</b>
            <em class="adm-tag {{ $conversation->is_private ? 'dim' : 'green' }}">{{ $conversation->is_private ? 'private' : 'public' }}</em>
        </div>
        @if($conversation->description)<div class="adm-item-body">{{ $conversation->description }}</div>@endif
        <div class="adm-item-sub">
            <span>Created {{ $conversation->created_at->format('M j, Y') }}</span>
            <span class="adm-convo-dot">·</span>
            <span>Last active {{ $conversation->last_activity_at?->diffForHumans() ?? 'never' }}</span>
        </div>
    </div>
    <div class="adm-profile-acts">
        <form method="POST" action="{{ route('admin.groups.destroy', $conversation) }}" onsubmit="return confirm('Delete this group and all its messages?')">
            @csrf @method('DELETE')
            <button type="submit" class="adm-btn-ghost danger">Delete group</button>
        </form>
    </div>
</div>

<div class="adm-stats" style="margin-top:16px">
    @foreach([
        ['Members', $stats['members'], '#3b82f6'],
        ['Messages', $stats['messages'], '#8b5cf6'],
        ['Today', $stats['today'], '#10b981'],
        ['With files', $stats['files'], '#f59e0b'],
    ] as [$label, $val, $tint])
    <div class="adm-stat">
        <span class="adm-stat-ic" style="color:{{ $tint }};background:{{ $tint }}1a">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M7 20V10M12 20V4M17 20v-7" stroke="currentColor" stroke-width="1.9" stroke-linecap="round"/></svg>
        </span>
        <div class="adm-stat-tx"><span class="adm-stat-val">{{ $val }}</span><span class="adm-stat-lbl">{{ $label }}</span></div>
    </div>
    @endforeach
</div>

<div class="adm-grid2" style="margin-top:16px">
    {{-- Members --}}
    <div class="adm-card">
        <div class="adm-card-head"><h3>Members</h3><span class="adm-card-sub">{{ $members->count() }}</span></div>
        @foreach($members as $m)
        @php
            $u = $m->user; if (!$u) continue;
            $g = $u->avatarGradient();
            $mi = collect(explode(' ', $u->name))->map(fn($w)=>strtoupper(substr($w,0,1)))->take(2)->join('');
        @endphp
        <div class="adm-urow">
            <div class="avatar" style="width:32px;height:32px;background:linear-gradient(135deg,{{ $g[0] }},{{ $g[1] }});font-size:12px">{{ $mi }}</div>
            <div class="adm-urow-tx">
                <span class="adm-urow-name">
                    <a href="{{ route('admin.users.show', $u) }}" class="adm-namelink">{{ $u->name }}</a>
                    @if($m->role === 'admin')<em class="adm-tag green">admin</em>@endif
                    @if($u->is_banned)<em class="adm-tag red">banned</em>@endif
                </span>
                <span class="adm-urow-sub">Joined {{ $m->joined_at?->diffForHumans(null, true) ?? '—' }} ago</span>
            </div>
            @if($conversation->created_by !== $u->id)
            <form method="POST" action="{{ route('groups.members.remove', [$conversation, $u]) }}" class="adm-inline"
                  onsubmit="return confirm('Remove {{ $u->name }} from this group?')">
                @csrf @method('DELETE')
                <button type="submit" class="adm-act red">Remove</button>
            </form>
            @else
            <span class="adm-tag dim">owner</span>
            @endif
        </div>
        @endforeach
    </div>

    {{-- Top posters --}}
    <div class="adm-card">
        <div class="adm-card-head"><h3>Most active</h3><span class="adm-card-sub">by messages</span></div>
        @php $tmax = max(1, $topPosters->max('total') ?? 1); @endphp
        @forelse($topPosters as $tp)
        @php
            $u = $tp['user']; $g = $u->avatarGradient();
            $ti = collect(explode(' ', $u->name))->map(fn($w)=>strtoupper(substr($w,0,1)))->take(2)->join('');
        @endphp
        <div class="adm-topg">
            <div class="avatar" style="width:30px;height:30px;background:linear-gradient(135deg,{{ $g[0] }},{{ $g[1] }});font-size:11px">{{ $ti }}</div>
            <span class="adm-topg-name"><a href="{{ route('admin.users.show', $u) }}" class="adm-namelink">{{ $u->name }}</a></span>
            <div class="adm-topg-bar"><span style="width:{{ round($tp['total'] / $tmax * 100) }}%"></span></div>
            <span class="adm-topg-n">{{ $tp['total'] }}</span>
        </div>
        @empty
        <p class="adm-empty">No messages yet.</p>
        @endforelse
    </div>
</div>

<div class="adm-grid2" style="margin-top:16px">
    {{-- Recent messages --}}
    <div class="adm-card">
        <div class="adm-card-head">
            <h3>Recent messages</h3>
            <a href="{{ route('admin.messages', ['conv' => $conversation->id]) }}" class="adm-link">View all →</a>
        </div>
        @forelse($recentMessages as $m)
        <div class="adm-mini">
            <div class="adm-mini-body">{{ \Illuminate\Support\Str::limit($m->body ?: '['.$m->type.']', 90) }}</div>
            <div class="adm-mini-meta">{{ $m->user?->name ?? 'Unknown' }} · {{ $m->created_at->diffForHumans(null, true) }} ago</div>
        </div>
        @empty
        <p class="adm-empty">No messages yet.</p>
        @endforelse
    </div>

    {{-- Moderation history --}}
    <div class="adm-card">
        <div class="adm-card-head"><h3>Moderation history</h3></div>
        @forelse($adminActions as $log)
        <div class="adm-mini">
            <div class="adm-mini-body"><span class="adm-badge dim">{{ $log->action }}</span> {{ \Illuminate\Support\Str::limit($log->details ?? '', 60) }}</div>
            <div class="adm-mini-meta">by {{ $log->admin?->name ?? '—' }} · {{ $log->created_at->diffForHumans() }}</div>
        </div>
        @empty
        <p class="adm-empty">No admin actions on this group.</p>
        @endforelse
    </div>
</div>
@endsection
