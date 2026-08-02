@extends('layouts.admin')
@section('page-title', 'Dashboard')
@section('content')

{{-- Stat cards --}}
<div class="adm-stats">
    @php
    $cards = [
        ['label'=>'Total Users','val'=>$stats['users'],'tint'=>'#3b82f6','ic'=>'<circle cx="9" cy="8" r="3.2" stroke="currentColor" stroke-width="1.7"/><path d="M3.5 19a5.5 5.5 0 0 1 11 0M15 5.5a3.2 3.2 0 0 1 0 5M17.5 19a5.5 5.5 0 0 0-3-4.9" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>'],
        ['label'=>'Online Now','val'=>$stats['online_users'],'tint'=>'#10b981','ic'=>'<circle cx="12" cy="12" r="4" fill="currentColor"/><circle cx="12" cy="12" r="8" stroke="currentColor" stroke-width="1.7" opacity=".35"/>'],
        ['label'=>'Messages Today','val'=>$stats['messages_today'],'tint'=>'#8b5cf6','ic'=>'<path d="M4 6.5C4 5.12 5.12 4 6.5 4h11C18.88 4 20 5.12 20 6.5v7c0 1.38-1.12 2.5-2.5 2.5H10l-3.6 3a1 1 0 0 1-1.65-.77V6.5Z" stroke="currentColor" stroke-width="1.7"/>'],
        ['label'=>'Total Messages','val'=>number_format($stats['messages_total']),'tint'=>'#06b6d4','ic'=>'<path d="M7 20V10M12 20V4M17 20v-7" stroke="currentColor" stroke-width="1.9" stroke-linecap="round"/>'],
        ['label'=>'Groups','val'=>$stats['groups'],'tint'=>'#f59e0b','ic'=>'<circle cx="8" cy="9" r="2.8" stroke="currentColor" stroke-width="1.7"/><circle cx="16" cy="9" r="2.8" stroke="currentColor" stroke-width="1.7"/><path d="M2.5 18.5a5.5 5.5 0 0 1 11 0M10.5 18.5a5.5 5.5 0 0 1 11 0" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>'],
        ['label'=>'Conversations','val'=>$stats['conversations'],'tint'=>'#ec4899','ic'=>'<path d="M8 10h8M8 14h5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><path d="M4 6.5C4 5.12 5.12 4 6.5 4h11C18.88 4 20 5.12 20 6.5v7c0 1.38-1.12 2.5-2.5 2.5H10l-3.6 3a1 1 0 0 1-1.65-.77V6.5Z" stroke="currentColor" stroke-width="1.7"/>'],
        ['label'=>'Guests','val'=>$stats['guests'],'tint'=>'#eab308','ic'=>'<circle cx="12" cy="9" r="3.5" stroke="currentColor" stroke-width="1.7"/><path d="M5 20a7 7 0 0 1 14 0" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>'],
        ['label'=>'Banned','val'=>$stats['banned_users'],'tint'=>'#ef4444','ic'=>'<circle cx="12" cy="12" r="8.3" stroke="currentColor" stroke-width="1.7"/><path d="M6.5 6.5l11 11" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>'],
    ];
    @endphp
    @foreach($cards as $c)
    <div class="adm-stat">
        <span class="adm-stat-ic" style="color:{{ $c['tint'] }};background:{{ $c['tint'] }}1a">
            <svg width="19" height="19" viewBox="0 0 24 24" fill="none">{!! $c['ic'] !!}</svg>
        </span>
        <div class="adm-stat-tx">
            <span class="adm-stat-val">{{ $c['val'] }}</span>
            <span class="adm-stat-lbl">{{ $c['label'] }}</span>
        </div>
    </div>
    @endforeach
</div>

<div class="adm-grid2">
    {{-- 7-day activity chart --}}
    <div class="adm-card">
        <div class="adm-card-head">
            <h3>Message Activity</h3>
            <span class="adm-card-sub">Last 7 days</span>
        </div>
        @php $max = max(1, $days->max('count')); @endphp
        <div class="adm-chart">
            @foreach($days as $d)
            <div class="adm-bar-col" title="{{ $d['count'] }} messages">
                <span class="adm-bar-val">{{ $d['count'] }}</span>
                <div class="adm-bar" style="height:{{ max(4, round($d['count'] / $max * 130)) }}px"></div>
                <span class="adm-bar-lbl">{{ $d['label'] }}</span>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Recent users --}}
    <div class="adm-card">
        <div class="adm-card-head">
            <h3>Recent Users</h3>
            <a href="{{ route('admin.users') }}" class="adm-link">View all →</a>
        </div>
        <div class="adm-ulist">
            @foreach($recentUsers as $u)
            @php $g = $u->avatarGradient(); $ini = collect(explode(' ',$u->name))->map(fn($w)=>strtoupper(substr($w,0,1)))->take(2)->join(''); @endphp
            <div class="adm-urow">
                <div class="avatar" style="width:32px;height:32px;background:linear-gradient(135deg,{{ $g[0] }},{{ $g[1] }});font-size:12px">{{ $ini }}</div>
                <div class="adm-urow-tx">
                    <span class="adm-urow-name">{{ $u->name }}
                        @if($u->role==='admin')<em class="adm-tag green">admin</em>@endif
                        @if($u->is_guest)<em class="adm-tag amber">guest</em>@endif
                        @if($u->is_banned)<em class="adm-tag red">banned</em>@endif
                    </span>
                    <span class="adm-urow-sub">{{ $u->email ?? '@'.$u->username }}</span>
                </div>
                <span class="adm-urow-time">{{ $u->created_at->diffForHumans(null, true) }}</span>
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- Recent messages --}}
<div class="adm-card" style="margin-top:16px">
    <div class="adm-card-head">
        <h3>Latest Messages</h3>
        <a href="{{ route('admin.messages') }}" class="adm-link">Moderate →</a>
    </div>
    <table class="adm-table">
        <thead><tr><th>From</th><th>Message</th><th>Conversation</th><th>When</th></tr></thead>
        <tbody>
            @foreach($recentMessages as $m)
            <tr>
                <td>{{ $m->user?->name ?? '—' }}</td>
                <td class="adm-td-msg">{{ \Illuminate\Support\Str::limit($m->body ?: '['.$m->type.']', 60) }}</td>
                <td>{{ $m->conversation?->name ?? 'Direct' }}</td>
                <td class="adm-td-dim">{{ $m->created_at->diffForHumans(null, true) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
