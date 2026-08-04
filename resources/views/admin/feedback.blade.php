@extends('layouts.admin')
@section('page-title', 'User Feedback')
@section('content')

<div class="adm-tabs">
    @foreach([['open','Open',$counts['open']], ['reviewing','Reviewing',$counts['reviewing']], ['resolved','Resolved',null], ['all','All',null]] as [$key,$label,$n])
    <a href="{{ route('admin.feedback', ['status' => $key]) }}" class="adm-tab {{ $status === $key ? 'on' : '' }}">
        {{ $label }}@if($n)<em>{{ $n }}</em>@endif
    </a>
    @endforeach
</div>

<form method="GET" class="adm-filters">
    <input type="hidden" name="status" value="{{ $status }}">
    <div class="adm-search">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><circle cx="11" cy="11" r="6.5" stroke="currentColor" stroke-width="1.8"/><path d="m20 20-3.2-3.2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Search feedback text…">
    </div>
    <select name="type" onchange="this.form.submit()">
        <option value="">All types</option>
        @foreach(\App\Models\Feedback::TYPES as $k => $lbl)
        <option value="{{ $k }}" {{ request('type')===$k?'selected':'' }}>{{ $lbl }}</option>
        @endforeach
    </select>
    <button type="submit" class="adm-btn">Search</button>
</form>

<div class="adm-list">
    @forelse($items as $f)
    @php
        $tint = ['bug'=>'#ef4444','suggestion'=>'#8b5cf6','question'=>'#3b82f6','other'=>'#8a958f'][$f->type] ?? '#8a958f';
        $u = $f->user;
        $g = $u?->avatarGradient() ?? ['#94a3b8','#475569'];
        $ini = $u ? collect(explode(' ', $u->name))->map(fn($w)=>strtoupper(substr($w,0,1)))->take(2)->join('') : '?';
    @endphp
    <div class="adm-item {{ $f->status === 'open' ? '' : '' }}" style="{{ $f->status==='open' ? 'border-left:3px solid '.$tint : '' }}">
        <div class="adm-item-av">
            <div class="avatar" style="width:40px;height:40px;background:linear-gradient(135deg,{{ $g[0] }},{{ $g[1] }});font-size:14px">{{ $ini }}</div>
        </div>

        <div class="adm-item-main">
            <div class="adm-item-title">
                <b>{{ $u?->name ?? 'Anonymous' }}</b>
                <em class="adm-tag" style="background:{{ $tint }}1a;color:{{ $tint }}">{{ $f->typeLabel() }}</em>
                <span class="adm-badge {{ $f->status==='open' ? 'red' : ($f->status==='reviewing' ? 'dim' : 'green') }}">{{ ucfirst($f->status) }}</span>
            </div>
            <div class="adm-item-body">{{ $f->message }}</div>
            <div class="adm-item-sub">
                @if($f->contact_email)<span>{{ $f->contact_email }}</span><span class="adm-convo-dot">·</span>@endif
                @if($f->page)<span>{{ $f->page }}</span><span class="adm-convo-dot">·</span>@endif
                @if($f->screen)<span>{{ $f->screen }}</span><span class="adm-convo-dot">·</span>@endif
                <span>{{ $f->created_at->diffForHumans() }}</span>
                @if($f->handler)<span class="adm-convo-dot">·</span><span>handled by {{ $f->handler->name }}</span>@endif
            </div>
            @if($f->browser)<div class="adm-item-sub"><span style="opacity:.7">{{ \Illuminate\Support\Str::limit($f->browser, 90) }}</span></div>@endif
            @if($f->admin_note)<div class="adm-item-note" style="color:var(--text2)">Note: {{ $f->admin_note }}</div>@endif

            <form method="POST" action="{{ route('admin.feedback.update', $f) }}" class="adm-fb-form">
                @csrf @method('PATCH')
                <select name="status" class="adm-select-sm">
                    @foreach(\App\Models\Feedback::STATUSES as $k => $lbl)
                    <option value="{{ $k }}" {{ $f->status===$k?'selected':'' }}>{{ $lbl }}</option>
                    @endforeach
                </select>
                <input type="text" name="admin_note" class="adm-input" style="flex:1;min-width:180px;height:32px" placeholder="Internal note (optional)" value="{{ $f->admin_note }}">
                <button type="submit" class="adm-act dim">Save</button>
            </form>
        </div>

        <div class="adm-item-acts">
            <form method="POST" action="{{ route('admin.feedback.destroy', $f) }}" class="adm-inline" onsubmit="return confirm('Delete this feedback?')">
                @csrf @method('DELETE')
                <button type="submit" class="adm-act red">Delete</button>
            </form>
        </div>
    </div>
    @empty
    <div class="adm-card">
        <p class="adm-empty">
            @if($status === 'open') No open feedback — you are all caught up.
            @else Nothing here. @endif
        </p>
    </div>
    @endforelse
</div>

<div class="adm-pager">{{ $items->links() }}</div>
@endsection
