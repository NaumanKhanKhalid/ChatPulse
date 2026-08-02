@extends('layouts.admin')
@section('page-title', 'Security')
@section('content')

{{-- Ban IP form --}}
<div class="adm-card">
    <div class="adm-card-head"><h3>Ban IP Address</h3></div>
    <form method="POST" action="{{ route('admin.security.ban-ip') }}" class="adm-iprow">
        @csrf
        <input type="text" name="ip_address" placeholder="e.g. 192.168.1.10" required class="adm-input" style="width:180px">
        <input type="text" name="reason" placeholder="Reason (optional)" class="adm-input" style="flex:1">
        <input type="datetime-local" name="expires_at" class="adm-input" style="width:200px" title="Expires (optional — empty = permanent)">
        <button type="submit" class="adm-btn danger">Ban IP</button>
    </form>
</div>

<div class="adm-grid2" style="margin-top:16px">
    {{-- IP Bans --}}
    <div class="adm-card">
        <div class="adm-card-head">
            <h3>Active IP Bans</h3>
            <span class="adm-card-sub">{{ $ipBans->count() }}</span>
        </div>
        @forelse($ipBans as $ban)
        <div class="adm-secrow">
            <span class="adm-secrow-ic red">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="8.3" stroke="currentColor" stroke-width="1.7"/><path d="M6.5 6.5l11 11" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
            </span>
            <div class="adm-secrow-tx">
                <span class="adm-secrow-t">{{ $ban->ip_address }}
                    @if($ban->expires_at)<em class="adm-tag amber">expires {{ $ban->expires_at->diffForHumans() }}</em>
                    @else<em class="adm-tag red">permanent</em>@endif
                </span>
                <span class="adm-secrow-s">{{ $ban->reason ?? 'No reason' }} · by {{ $ban->banner?->name ?? '—' }} · {{ $ban->created_at->diffForHumans() }}</span>
            </div>
            <form method="POST" action="{{ route('admin.security.unban-ip', $ban) }}" class="adm-inline">
                @csrf @method('DELETE')
                <button type="submit" class="adm-act green">Unban</button>
            </form>
        </div>
        @empty
        <p class="adm-empty">No IP bans active.</p>
        @endforelse
    </div>

    {{-- Banned users --}}
    <div class="adm-card">
        <div class="adm-card-head">
            <h3>Banned Users</h3>
            <span class="adm-card-sub">{{ $bannedUsers->count() }}</span>
        </div>
        @forelse($bannedUsers as $u)
        @php $g = $u->avatarGradient(); $ini = collect(explode(' ',$u->name))->map(fn($w)=>strtoupper(substr($w,0,1)))->take(2)->join(''); @endphp
        <div class="adm-secrow">
            <div class="avatar" style="width:32px;height:32px;background:linear-gradient(135deg,{{ $g[0] }},{{ $g[1] }});font-size:12px;opacity:.6">{{ $ini }}</div>
            <div class="adm-secrow-tx">
                <span class="adm-secrow-t">{{ $u->name }}</span>
                <span class="adm-secrow-s">{{ $u->banned_reason ?? 'No reason given' }} · {{ $u->banned_at?->diffForHumans() }}</span>
            </div>
            <form method="POST" action="{{ route('admin.users.unban', $u) }}" class="adm-inline">
                @csrf
                <button type="submit" class="adm-act green">Unban</button>
            </form>
        </div>
        @empty
        <p class="adm-empty">No banned users.</p>
        @endforelse
    </div>
</div>
@endsection
