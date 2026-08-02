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

<div class="adm-card adm-card-flush">
    <table class="adm-table">
        <thead><tr><th>Group</th><th>Type</th><th>Members</th><th>Messages</th><th>Created</th><th style="text-align:right">Actions</th></tr></thead>
        <tbody>
            @forelse($groups as $group)
            <tr>
                <td>
                    <div class="adm-ucell">
                        @php $ini = collect(explode(' ',$group->name ?? 'G'))->map(fn($w)=>strtoupper(substr($w,0,1)))->take(2)->join(''); @endphp
                        <div class="avatar" style="width:32px;height:32px;background:linear-gradient(135deg,#818cf8,#7c3aed);font-size:12px">{{ $ini }}</div>
                        <div>
                            <span class="adm-ucell-name">{{ $group->name ?? 'Unnamed' }}</span>
                            @if($group->description)<span class="adm-ucell-sub">{{ \Illuminate\Support\Str::limit($group->description, 40) }}</span>@endif
                        </div>
                    </div>
                </td>
                <td><span class="adm-badge {{ $group->is_private ? 'dim' : 'green' }}">{{ $group->is_private ? 'Private' : 'Public' }}</span></td>
                <td>{{ $group->participants_count }}</td>
                <td>{{ $group->messages_count }}</td>
                <td class="adm-td-dim">{{ $group->created_at->format('M j, Y') }}</td>
                <td style="text-align:right">
                    <form method="POST" action="{{ route('admin.groups.destroy', $group) }}" class="adm-inline" onsubmit="return confirm('Delete group “{{ $group->name }}” and all its messages?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="adm-act red">Delete</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="adm-empty">No groups found.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="adm-pager">{{ $groups->links() }}</div>
</div>
@endsection
