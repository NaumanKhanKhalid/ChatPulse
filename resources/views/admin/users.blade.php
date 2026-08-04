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
            @forelse($users as $u)
            @php
                $g    = $u->avatarGradient();
                $ini  = collect(explode(' ', $u->name))->map(fn($w)=>strtoupper(substr($w,0,1)))->take(2)->join('');
                $self = $u->id === auth()->id();
            @endphp
            <tr class="{{ $u->is_banned ? 'adm-tr-banned' : '' }}">
                <td>
                    <div class="adm-ucell">
                        <span class="adm-item-av">
                            <div class="avatar" style="width:34px;height:34px;background:linear-gradient(135deg,{{ $g[0] }},{{ $g[1] }});font-size:12.5px">{{ $ini }}</div>
                            @if($u->is_online && !$u->is_banned)<span class="adm-item-dot sm"></span>@endif
                        </span>
                        <span>
                            <span class="adm-ucell-name">
                                {{ $u->name }}
                                @if($u->is_guest)<em class="adm-tag amber">guest</em>@endif
                                @if($self)<em class="adm-tag dim">you</em>@endif
                            </span>
                            <span class="adm-ucell-sub">{{ $u->email ?? '@'.$u->username }}</span>
                        </span>
                    </div>
                </td>
                <td>
                    @if(!$self)
                    <form method="POST" action="{{ route('admin.users.role', $u) }}">
                        @csrf @method('PATCH')
                        <select name="role" onchange="this.form.submit()" class="adm-select-sm">
                            @foreach(['admin','user','guest'] as $r)
                            <option value="{{ $r }}" {{ $u->role === $r ? 'selected' : '' }}>{{ $r }}</option>
                            @endforeach
                        </select>
                    </form>
                    @else
                    <span class="adm-tag green">admin</span>
                    @endif
                </td>
                <td>
                    @if($u->is_banned)
                    <span class="adm-badge red" title="{{ $u->banned_reason }}">Banned</span>
                    @elseif($u->is_online)
                    <span class="adm-badge green">Online</span>
                    @else
                    <span class="adm-badge dim">{{ $u->last_seen_at ? $u->last_seen_at->diffForHumans(null, true) : 'Offline' }}</span>
                    @endif
                </td>
                <td class="adm-td-dim">{{ $u->created_at->format('M j, Y') }}</td>
                <td style="text-align:right">
                    @if(!$self && !$u->isAdmin())
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
                </td>
            </tr>
            @if(!$self && !$u->isAdmin())
            <tr class="adm-perms-row" id="perms-{{ $u->id }}">
                <td colspan="5">
                    <form method="POST" action="{{ route('admin.users.permissions', $u) }}" class="adm-perms">
                        @csrf @method('PATCH')
                        <span class="adm-perms-lbl">Permissions</span>
                        @foreach(\App\Models\User::PERMISSIONS as $key => [$label, $default])
                        <label class="adm-perm">
                            <input type="checkbox" name="{{ $key }}" value="1" {{ $u->hasPerm($key) ? 'checked' : '' }}>
                            <span>{{ $label }}</span>
                        </label>
                        @endforeach
                        <button type="submit" class="adm-btn" style="height:30px;padding:0 13px;font-size:12px">Save</button>
                    </form>
                </td>
            </tr>
            @endif
            @empty
            <tr><td colspan="5" class="adm-empty">No users found.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="adm-pager">{{ $users->links() }}</div>
</div>
@endsection
