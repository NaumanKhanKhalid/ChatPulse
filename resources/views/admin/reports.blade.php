@extends('layouts.admin')
@section('page-title', 'Reports')
@section('content')

<div class="adm-tabs">
    @foreach([['open','Open',$counts['open']], ['closed','Closed',$counts['closed']], ['all','All',null]] as [$key,$label,$n])
    <a href="{{ route('admin.reports', ['status' => $key]) }}" class="adm-tab {{ $status === $key ? 'on' : '' }}">
        {{ $label }}@if($n !== null && $n > 0)<em>{{ $n }}</em>@endif
    </a>
    @endforeach
</div>

<div class="adm-reports">
    @forelse($reports as $r)
    @php $open = $r->status === 'open'; @endphp
    <div class="adm-report {{ $open ? 'open' : '' }}">
        <span class="adm-report-ic {{ $open ? 'red' : 'green' }}">
            @if($open)
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none"><path d="M12 3 3 19h18L12 3Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="M12 10v3.5M12 16.5h.01" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
            @else
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none"><path d="m5 13 4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            @endif
        </span>

        <div class="adm-report-main">
            <div class="adm-report-top">
                <h4>{{ $r->reasonLabel() }}</h4>
                @if($open)
                <span class="adm-badge red">OPEN</span>
                @else
                <span class="adm-badge dim">CLOSED</span>
                @if($r->resolution === 'user_banned')<span class="adm-tag red">user banned</span>@endif
                @if($r->resolution === 'dismissed')<span class="adm-tag dim">dismissed</span>@endif
                @endif
            </div>

            @if($r->excerpt)
            <p class="adm-report-quote">“{{ \Illuminate\Support\Str::limit($r->excerpt, 120) }}”</p>
            @endif
            @if($r->details)
            <p class="adm-report-details">{{ \Illuminate\Support\Str::limit($r->details, 160) }}</p>
            @endif

            <p class="adm-report-meta">
                Reported by <b>{{ $r->reporter?->name ?? 'Unknown' }}</b>
                @if($r->reportedUser) against <b>{{ $r->reportedUser->name }}</b>@endif
                @if($r->conversation) · {{ $r->conversation->name ?? 'Direct message' }} @endif
                · {{ $r->created_at->diffForHumans(null, true) }}
                @if(!$open && $r->resolver) · closed by {{ $r->resolver->name }} @endif
            </p>
        </div>

        <div class="adm-report-acts">
            @if($open)
            <form method="POST" action="{{ route('admin.reports.dismiss', $r) }}">
                @csrf
                <button type="submit" class="adm-btn-ghost">Dismiss</button>
            </form>
            @if($r->reportedUser && !$r->reportedUser->isAdmin())
            <form method="POST" action="{{ route('admin.reports.ban', $r) }}" onsubmit="return confirm('Ban {{ $r->reportedUser->name }}?')">
                @csrf
                <button type="submit" class="adm-btn-ghost danger">Ban user</button>
            </form>
            @endif
            @else
            <span class="adm-report-closed">Closed</span>
            @endif
        </div>
    </div>
    @empty
    <div class="adm-card">
        <p class="adm-empty">
            @if($status === 'open') No open reports — everything is clear.
            @else No reports found. @endif
        </p>
    </div>
    @endforelse
</div>

<div class="adm-pager">{{ $reports->links() }}</div>
@endsection
