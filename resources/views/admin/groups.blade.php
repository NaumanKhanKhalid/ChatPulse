@extends('layouts.admin')
@section('page-title', 'Groups')
@section('content')

<form method="GET" class="adm-filters">
    <div class="adm-search">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><circle cx="11" cy="11" r="6.5" stroke="currentColor" stroke-width="1.8"/><path d="m20 20-3.2-3.2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Search group name…">
    </div>
    <select name="visibility" onchange="this.form.submit()">
        <option value="">All</option>
        <option value="public"  {{ request('visibility')==='public'?'selected':'' }}>Public</option>
        <option value="private" {{ request('visibility')==='private'?'selected':'' }}>Private</option>
    </select>
    <select name="sort" onchange="this.form.submit()">
        <option value="newest"   {{ request('sort','newest')==='newest'?'selected':'' }}>Newest</option>
        <option value="oldest"   {{ request('sort')==='oldest'?'selected':'' }}>Oldest</option>
        <option value="members"  {{ request('sort')==='members'?'selected':'' }}>Most members</option>
        <option value="messages" {{ request('sort')==='messages'?'selected':'' }}>Most messages</option>
        <option value="name"     {{ request('sort')==='name'?'selected':'' }}>Name A–Z</option>
    </select>
    <input type="date" name="from" value="{{ request('from') }}" class="adm-input" style="height:38px" title="Created from">
    <input type="date" name="to" value="{{ request('to') }}" class="adm-input" style="height:38px" title="Created to">
    <button type="submit" class="adm-btn">Apply</button>
    @if(request()->hasAny(['q','visibility','sort','from','to']))
    <a href="{{ route('admin.groups') }}" class="adm-link">Clear</a>
    @endif
</form>

<div class="adm-list">
    @forelse($groups as $group)
    @php $ini = collect(explode(' ', $group->name ?? 'G'))->map(fn($w)=>strtoupper(substr($w,0,1)))->take(2)->join(''); @endphp
    <div class="adm-item adm-row-click" onclick="if(!event.target.closest('form,a,button')) location.href='{{ route('admin.groups.show', $group) }}'">
        <div class="adm-item-av">
            <div class="avatar" style="width:42px;height:42px;background:linear-gradient(135deg,#818cf8,#7c3aed);font-size:15px">{{ $ini }}</div>
        </div>
        <div class="adm-item-main">
            <div class="adm-item-title">
                <b><a href="{{ route('admin.groups.show', $group) }}" class="adm-namelink">{{ $group->name ?? 'Unnamed group' }}</a></b>
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
            <a href="{{ route('admin.groups.show', $group) }}" class="adm-act dim">View</a>
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
