@extends('layouts.admin')
@section('page-title', 'Users')
@section('content')

{{-- Filters --}}
<form method="GET" class="adm-filters">
    <div class="adm-search">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><circle cx="11" cy="11" r="6.5" stroke="currentColor" stroke-width="1.8"/><path d="m20 20-3.2-3.2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Search name, email, username…">
    </div>
    <select name="role" onchange="this.form.submit()">
        <option value="">All roles</option>
        @foreach(['admin','user','guest'] as $r)
        <option value="{{ $r }}" {{ request('role')===$r?'selected':'' }}>{{ ucfirst($r) }}</option>
        @endforeach
    </select>
    <select name="status" onchange="this.form.submit()">
        <option value="">All status</option>
        <option value="online" {{ request('status')==='online'?'selected':'' }}>Online</option>
        <option value="banned" {{ request('status')==='banned'?'selected':'' }}>Banned</option>
    </select>
    <button type="submit" class="adm-btn">Search</button>
    @if(request()->hasAny(['q','role','status']))
    <a href="{{ route('admin.users') }}" class="adm-link">Clear</a>
    @endif
</form>

<div class="adm-card adm-card-flush">
    <table class="adm-table">
        <thead><tr><th>User</th><th>Role</th><th>Status</th><th>Joined</th><th style="text-align:right">Actions</th></tr></thead>
        <tbody>
            @foreach($users as $u)
            @php $g = $u->avatarGradient(); $ini = collect(explode(' ',$u->name))->map(fn($w)=>strtoupper(substr($w,0,1)))->take(2)->join(''); @endphp
            <tr>
                <td>
                    <div class="adm-ucell">
                        <div class="avatar" style="width:34px;height:34px;background:linear-gradient(135deg,{{ $g[0] }},{{ $g[1] }});font-size:12.5px">{{ $ini }}</div>
                        <div>
                            <span class="adm-ucell-name">{{ $u->name }}@if($u->is_guest)<em class="adm-tag amber">guest</em>@endif</span>
                            <span class="adm-ucell-sub">{{ $u->email ?? '@'.$u->username }}</span>
                        </div>
                    </div>
                </td>
                <td>
                    @if($u->id !== auth()->id())
                    <form method="POST" action="{{ route('admin.users.role', $u) }}">
                        @csrf @method('PATCH')
                        <select name="role" onchange="this.form.submit()" class="adm-select-sm">
                            @foreach(['admin','user','guest'] as $r)
                            <option value="{{ $r }}" {{ $u->role === $r ? 'selected' : '' }}>{{ $r }}</option>
                            @endforeach
                        </select>
                    </form>
                    @else
                    <span class="adm-tag green">you · admin</span>
                    @endif
                </td>
                <td>
                    @if($u->is_banned)
                    <span class="adm-badge red" title="{{ $u->banned_reason }}">Banned</span>
                    @elseif($u->is_online)
                    <span class="adm-badge green">Online</span>
                    @else
                    <span class="adm-badge dim">Offline</span>
                    @endif
                </td>
                <td class="adm-td-dim">{{ $u->created_at->format('M j, Y') }}</td>
                <td style="text-align:right">
                    @if($u->id !== auth()->id() && !$u->isAdmin())
                        @if($u->is_banned)
                        <form method="POST" action="{{ route('admin.users.unban', $u) }}" class="adm-inline">
                            @csrf
                            <button type="submit" class="adm-act green">Unban</button>
                        </form>
                        @else
                        <form method="POST" action="{{ route('admin.users.ban', $u) }}" class="adm-inline"
                              onsubmit="const r = prompt('Ban reason (optional):'); if (r === null) return false; this.querySelector('[name=reason]').value = r; return true;">
                            @csrf
                            <input type="hidden" name="reason" value="">
                            <button type="submit" class="adm-act red">Ban</button>
                        </form>
                        @endif
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="adm-pager">{{ $users->links() }}</div>
</div>
@endsection
