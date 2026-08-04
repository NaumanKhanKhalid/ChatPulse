@extends('layouts.admin')
@section('page-title', 'Groups')
@section('content')

<form method="GET" class="adm-filters">
    <div class="adm-search">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><circle cx="11" cy="11" r="6.5" stroke="currentColor" stroke-width="1.8"/><path d="m20 20-3.2-3.2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Search group name…">
    </div>
    <button type="submit" class="adm-btn">Search</button>
    @if(request('q'))
    <a href="{{ route('admin.groups') }}" class="adm-link">Clear</a>
    @endif
</form>

<div class="adm-list">
    @forelse($groups as $group)
    @php $ini = collect(explode(' ', $group->name ?? 'G'))->map(fn($w)=>strtoupper(substr($w,0,1)))->take(2)->join(''); @endphp
    <div class="adm-item">
        <div class="adm-item-av">
            <div class="avatar" style="width:42px;height:42px;background:linear-gradient(135deg,#818cf8,#7c3aed);font-size:15px">{{ $ini }}</div>
        </div>
        <div class="adm-item-main">
            <div class="adm-item-title">
                <b>{{ $group->name ?? 'Unnamed group' }}</b>
                <em class="adm-tag {{ $group->is_private ? 'dim' : 'green' }}">{{ $group->is_private ? 'private' : 'public' }}</em>
            </div>
            @if($group->description)
            <div class="adm-item-body">{{ \Illuminate\Support\Str::limit($group->description, 100) }}</div>
            @endif
            <div class="adm-item-sub">
                <span>{{ $group->participants_count }} members</span>
                <span class="adm-convo-dot">·</span>
                <span>{{ $group->messages_count }} messages</span>
                <span class="adm-convo-dot">·</span>
                <span>Created {{ $group->created_at->format('M j, Y') }}</span>
            </div>
        </div>
        <div class="adm-item-acts">
            <form method="POST" action="{{ route('admin.groups.destroy', $group) }}" class="adm-inline" onsubmit="return confirm('Delete group “{{ $group->name }}” and all its messages?')">
                @csrf @method('DELETE')
                <button type="submit" class="adm-act red">Delete</button>
            </form>
        </div>
    </div>
    @empty
    <div class="adm-card"><p class="adm-empty">No groups found.</p></div>
    @endforelse
</div>

<div class="adm-pager">{{ $groups->links() }}</div>
@endsection
