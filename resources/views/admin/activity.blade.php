@extends('layouts.admin')
@section('page-title', 'Activity Log')
@section('content')

<form method="GET" class="adm-filters">
    <div class="adm-search">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><circle cx="11" cy="11" r="6.5" stroke="currentColor" stroke-width="1.8"/><path d="m20 20-3.2-3.2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Search target or details…">
    </div>
    <select name="action" onchange="this.form.submit()">
        <option value="">All actions</option>
        @foreach($actions as $a)
        <option value="{{ $a }}" {{ request('action')===$a?'selected':'' }}>{{ $a }}</option>
        @endforeach
    </select>
    <button type="submit" class="adm-btn">Search</button>
    @if(request()->hasAny(['q','action']))
    <a href="{{ route('admin.activity') }}" class="adm-link">Clear</a>
    @endif
</form>

<div class="adm-card adm-card-flush">
    <table class="adm-table">
        <thead><tr><th>Admin</th><th>Action</th><th>Target</th><th>Details</th><th>IP</th><th>When</th></tr></thead>
        <tbody>
            @forelse($logs as $log)
            @php
                $tint = str_contains($log->action, 'ban') && !str_contains($log->action, 'unban') ? 'red'
                    : (str_contains($log->action, 'delete') ? 'red'
                    : (str_contains($log->action, 'unban') ? 'green' : 'dim'));
            @endphp
            <tr>
                <td>
                    @if($log->admin)
                    @php $g = $log->admin->avatarGradient(); $ini = collect(explode(' ',$log->admin->name))->map(fn($w)=>strtoupper(substr($w,0,1)))->take(2)->join(''); @endphp
                    <div class="adm-ucell">
                        <div class="avatar" style="width:28px;height:28px;background:linear-gradient(135deg,{{ $g[0] }},{{ $g[1] }});font-size:10.5px">{{ $ini }}</div>
                        <span class="adm-ucell-name">{{ $log->admin->name }}</span>
                    </div>
                    @else — @endif
                </td>
                <td><span class="adm-badge {{ $tint }}">{{ $log->action }}</span></td>
                <td class="adm-td-msg">{{ $log->target_label ?? '—' }}</td>
                <td class="adm-td-msg adm-td-dim">{{ $log->details ?? '—' }}</td>
                <td class="adm-td-dim">{{ $log->ip_address }}</td>
                <td class="adm-td-dim" title="{{ $log->created_at }}">{{ $log->created_at->diffForHumans() }}</td>
            </tr>
            @empty
            <tr><td colspan="6" class="adm-empty">No admin activity recorded yet. Actions like bans, role changes and deletions will appear here.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="adm-pager">{{ $logs->links() }}</div>
</div>
@endsection
