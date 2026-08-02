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

<div class="adm-card adm-card-flush">
    <table class="adm-table">
        <thead><tr><th>From</th><th>Message</th><th>Conversation</th><th>Sent</th><th style="text-align:right">Actions</th></tr></thead>
        <tbody>
            @forelse($messages as $m)
            <tr>
                <td>
                    @if($m->user)
                    @php $g = $m->user->avatarGradient(); $ini = collect(explode(' ',$m->user->name))->map(fn($w)=>strtoupper(substr($w,0,1)))->take(2)->join(''); @endphp
                    <div class="adm-ucell">
                        <div class="avatar" style="width:30px;height:30px;background:linear-gradient(135deg,{{ $g[0] }},{{ $g[1] }});font-size:11px">{{ $ini }}</div>
                        <span class="adm-ucell-name">{{ $m->user->name }}</span>
                    </div>
                    @else — @endif
                </td>
                <td class="adm-td-msg">
                    {{ \Illuminate\Support\Str::limit($m->body ?: '['.$m->type.' message]', 80) }}
                    @if($m->is_edited)<em class="adm-tag dim">edited</em>@endif
                </td>
                <td>{{ $m->conversation?->name ?? 'Direct' }}</td>
                <td class="adm-td-dim">{{ $m->created_at->format('M j · g:i A') }}</td>
                <td style="text-align:right">
                    <form method="POST" action="{{ route('admin.messages.destroy', $m) }}" class="adm-inline" onsubmit="return confirm('Delete this message?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="adm-act red">Delete</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="5" class="adm-empty">No messages found.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="adm-pager">{{ $messages->links() }}</div>
</div>
@endsection
