@extends('layouts.admin')
@section('page-title', 'Messages')
@section('content')

<form method="GET" class="adm-filters">
    <div class="adm-search">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><circle cx="11" cy="11" r="6.5" stroke="currentColor" stroke-width="1.8"/><path d="m20 20-3.2-3.2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Search message text…">
    </div>
    <button type="submit" class="adm-btn">Search</button>
    @if(request('q'))
    <a href="{{ route('admin.messages') }}" class="adm-link">Clear</a>
    @endif
</form>

<div class="adm-list">
    @forelse($messages as $m)
    @php
        $u = $m->user;
        $g = $u?->avatarGradient() ?? ['#8a958f','#5d6b65'];
        $ini = $u ? collect(explode(' ', $u->name))->map(fn($w)=>strtoupper(substr($w,0,1)))->take(2)->join('') : '?';
    @endphp
    <div class="adm-item">
        <div class="adm-item-av">
            <div class="avatar" style="width:40px;height:40px;background:linear-gradient(135deg,{{ $g[0] }},{{ $g[1] }});font-size:14px">{{ $ini }}</div>
        </div>
        <div class="adm-item-main">
            <div class="adm-item-title">
                <b>{{ $u?->name ?? 'Unknown user' }}</b>
                <em class="adm-tag dim">{{ $m->conversation?->name ?? 'Direct' }}</em>
                @if($m->is_edited)<em class="adm-tag dim">edited</em>@endif
                @if($m->type !== 'text')<em class="adm-tag amber">{{ $m->type }}</em>@endif
            </div>
            <div class="adm-item-body">{{ \Illuminate\Support\Str::limit($m->body ?: '['.$m->type.' message]', 140) }}</div>
            <div class="adm-item-sub"><span>{{ $m->created_at->format('M j, Y · g:i A') }}</span></div>
        </div>
        <div class="adm-item-acts">
            <form method="POST" action="{{ route('admin.messages.destroy', $m) }}" class="adm-inline" onsubmit="return confirm('Delete this message?')">
                @csrf @method('DELETE')
                <button type="submit" class="adm-act red">Delete</button>
            </form>
        </div>
    </div>
    @empty
    <div class="adm-card"><p class="adm-empty">No messages found.</p></div>
    @endforelse
</div>

<div class="adm-pager">{{ $messages->links() }}</div>
@endsection
