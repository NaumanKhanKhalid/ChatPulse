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

<div class="adm-list">
    @forelse($users as $u)
    @php
        $g   = $u->avatarGradient();
        $ini = collect(explode(' ', $u->name))->map(fn($w)=>strtoupper(substr($w,0,1)))->take(2)->join('');
        $self = $u->id === auth()->id();
    @endphp
    <div class="adm-item {{ $u->is_banned ? 'banned' : '' }}">
        <div class="adm-item-av">
            <div class="avatar" style="width:42px;height:42px;background:linear-gradient(135deg,{{ $g[0] }},{{ $g[1] }});font-size:15px">{{ $ini }}</div>
            @if($u->is_online && !$u->is_banned)<span class="adm-item-dot"></span>@endif
        </div>

        <div class="adm-item-main">
            <div class="adm-item-title">
                <b>{{ $u->name }}</b>
                @if($u->role === 'admin')<em class="adm-tag green">admin</em>@endif
                @if($u->is_guest)<em class="adm-tag amber">guest</em>@endif
                @if($u->is_banned)<em class="adm-tag red">banned</em>@endif
                @if($self)<em class="adm-tag dim">you</em>@endif
            </div>
            <div class="adm-item-sub">
                <span>{{ $u->email ?? '@'.$u->username }}</span>
                <span class="adm-convo-dot">·</span>
                <span>{{ $u->is_banned ? 'Banned '.$u->banned_at?->diffForHumans() : ($u->is_online ? 'Online now' : ($u->last_seen_at ? 'Last seen '.$u->last_seen_at->diffForHumans(null, true) : 'Offline')) }}</span>
                <span class="adm-convo-dot">·</span>
                <span>Joined {{ $u->created_at->format('M Y') }}</span>
            </div>
            @if($u->is_banned && $u->banned_reason)
            <div class="adm-item-note">Reason: {{ \Illuminate\Support\Str::limit($u->banned_reason, 80) }}</div>
            @endif
        </div>

        <div class="adm-item-acts">
            @if(!$self)
            <form method="POST" action="{{ route('admin.users.role', $u) }}" class="adm-inline">
                @csrf @method('PATCH')
                <select name="role" onchange="this.form.submit()" class="adm-select-sm">
                    @foreach(['admin','user','guest'] as $r)
                    <option value="{{ $r }}" {{ $u->role === $r ? 'selected' : '' }}>{{ $r }}</option>
                    @endforeach
                </select>
            </form>
                @if(!$u->isAdmin())
                <button type="button" class="adm-act dim" onclick="document.getElementById('perms-{{ $u->id }}').classList.toggle('open')">Perms</button>
                    @if($u->is_banned)
                    <form method="POST" action="{{ route('admin.users.unban', $u) }}" class="adm-inline">
                        @csrf<button type="submit" class="adm-act green">Unban</button>
                    </form>
                    @else
                    <form method="POST" action="{{ route('admin.users.ban', $u) }}" class="adm-inline"
                          onsubmit="const r = prompt('Ban reason (optional):'); if (r === null) return false; this.querySelector('[name=reason]').value = r; return true;">
                        @csrf<input type="hidden" name="reason" value="">
                        <button type="submit" class="adm-act red">Ban</button>
                    </form>
                    @endif
                @endif
            @endif
        </div>
    </div>

    @if(!$self && !$u->isAdmin())
    <div class="adm-perms-panel" id="perms-{{ $u->id }}">
        <form method="POST" action="{{ route('admin.users.permissions', $u) }}" class="adm-perms">
            @csrf @method('PATCH')
            <span class="adm-perms-lbl">Permissions</span>
            @foreach(\App\Models\User::PERMISSIONS as $key => [$label, $default])
            <label class="adm-perm">
                <input type="checkbox" name="{{ $key }}" value="1" {{ $u->hasPerm($key) ? 'checked' : '' }}>
                <span>{{ $label }}</span>
            </label>
            @endforeach
            <button type="submit" class="adm-btn" style="height:32px;padding:0 14px;font-size:12px">Save</button>
        </form>
    </div>
    @endif
    @empty
    <div class="adm-card"><p class="adm-empty">No users found.</p></div>
    @endforelse
</div>

<div class="adm-pager">{{ $users->links() }}</div>
@endsection
