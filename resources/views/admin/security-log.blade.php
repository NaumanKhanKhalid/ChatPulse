@extends('layouts.admin')
@section('page-title', 'Security Log')
@section('content')

<div class="adm-stats">
    @foreach([
        ['Successful logins', $stats['success_24h'], '#10b981'],
        ['Failed attempts', $stats['failed_24h'], '#ef4444'],
        ['New devices', $stats['new_devices'], '#f59e0b'],
    ] as [$label, $val, $tint])
    <div class="adm-stat">
        <span class="adm-stat-ic" style="color:{{ $tint }};background:{{ $tint }}1a">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M12 3.5 5 6v5c0 4.5 3 8 7 9.5 4-1.5 7-5 7-9.5V6l-7-2.5Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/></svg>
        </span>
        <div class="adm-stat-tx">
            <span class="adm-stat-val">{{ $val }}</span>
            <span class="adm-stat-lbl">{{ $label }} · 24h</span>
        </div>
    </div>
    @endforeach
</div>

@if($suspects->count())
<div class="adm-card" style="margin-top:16px;border-color:rgba(239,68,68,.3)">
    <div class="adm-card-head">
        <h3 style="color:#ef4444">Repeated failures — possible brute force</h3>
        <span class="adm-card-sub">last 24h</span>
    </div>
    @foreach($suspects as $sus)
    <div class="adm-mini">
        <div class="adm-mini-body"><b>{{ $sus->ip_address ?? 'unknown IP' }}</b> — {{ $sus->attempts }} failed attempts</div>
        <div class="adm-mini-meta">Consider adding this IP to the ban list under Security</div>
    </div>
    @endforeach
</div>
@endif

<form method="GET" class="adm-filters" style="margin-top:16px">
    <div class="adm-search">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><circle cx="11" cy="11" r="6.5" stroke="currentColor" stroke-width="1.8"/><path d="m20 20-3.2-3.2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Search email or IP…">
    </div>
    <select name="event" onchange="this.form.submit()">
        <option value="">All events</option>
        <option value="success" {{ request('event')==='success'?'selected':'' }}>Successful</option>
        <option value="failed"  {{ request('event')==='failed'?'selected':'' }}>Failed</option>
        <option value="logout"  {{ request('event')==='logout'?'selected':'' }}>Logout</option>
    </select>
    <button type="submit" class="adm-btn">Search</button>
    @if(request()->hasAny(['q','event']))<a href="{{ route('admin.security-log') }}" class="adm-link">Clear</a>@endif
</form>

<div class="adm-card adm-card-flush">
    <table class="adm-table">
        <thead><tr><th>Event</th><th>Account</th><th>IP</th><th>Device</th><th>When</th></tr></thead>
        <tbody>
            @forelse($logs as $log)
            <tr class="{{ $log->event === 'failed' ? 'adm-tr-banned' : '' }}">
                <td>
                    @if($log->event === 'success')<span class="adm-badge green">Signed in</span>
                    @elseif($log->event === 'failed')<span class="adm-badge red">Failed</span>
                    @else<span class="adm-badge dim">Signed out</span>@endif
                    @if($log->new_device)<em class="adm-tag amber">new device</em>@endif
                </td>
                <td>
                    @if($log->user)
                    <a href="{{ route('admin.users.show', $log->user) }}" class="adm-namelink">{{ $log->user->name }}</a>
                    @else
                    <span class="adm-td-dim">{{ $log->email ?? 'unknown' }}</span>
                    @endif
                </td>
                <td class="adm-td-dim">{{ $log->ip_address ?? '—' }}</td>
                <td class="adm-td-dim">{{ $log->deviceLabel() }}</td>
                <td class="adm-td-dim" title="{{ $log->created_at }}">{{ $log->created_at->diffForHumans() }}</td>
            </tr>
            @empty
            <tr><td colspan="5" class="adm-empty">No authentication events recorded yet.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="adm-pager">{{ $logs->links() }}</div>
</div>
@endsection
