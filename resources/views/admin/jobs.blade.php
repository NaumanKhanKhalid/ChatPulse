@extends('layouts.admin')
@section('page-title', 'Queue Jobs')
@section('content')

<div class="adm-stats">
    <div class="adm-stat">
        <span class="adm-stat-ic" style="color:#3b82f6;background:#3b82f61a">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M12 8v4.5l3 2" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/><circle cx="12" cy="12" r="8.3" stroke="currentColor" stroke-width="1.7"/></svg>
        </span>
        <div class="adm-stat-tx"><span class="adm-stat-val">{{ $pending }}</span><span class="adm-stat-lbl">Pending jobs</span></div>
    </div>
    <div class="adm-stat">
        <span class="adm-stat-ic" style="color:#ef4444;background:#ef44441a">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="8.3" stroke="currentColor" stroke-width="1.7"/><path d="M12 8v4.5M12 15.5h.01" stroke="currentColor" stroke-width="1.9" stroke-linecap="round"/></svg>
        </span>
        <div class="adm-stat-tx"><span class="adm-stat-val">{{ $failed->count() }}</span><span class="adm-stat-lbl">Failed jobs</span></div>
    </div>
</div>

@if($failed->count())
<div class="adm-filters" style="margin-top:16px">
    <form method="POST" action="{{ route('admin.jobs.retry-all') }}" onsubmit="return confirm('Retry every failed job?')">
        @csrf<button type="submit" class="adm-btn">Retry all</button>
    </form>
</div>
@endif

<div class="adm-list">
    @forelse($failed as $j)
    <div class="adm-item">
        <div class="adm-item-main">
            <div class="adm-item-title">
                <b>{{ $j->job_name }}</b>
                <em class="adm-tag dim">{{ $j->queue }}</em>
            </div>
            <div class="adm-item-body">{{ $j->short_exception }}</div>
            <div class="adm-item-sub"><span>Failed {{ \Carbon\Carbon::parse($j->failed_at)->diffForHumans() }}</span></div>
        </div>
        <div class="adm-item-acts">
            <form method="POST" action="{{ route('admin.jobs.retry', $j->uuid) }}" class="adm-inline">
                @csrf<button type="submit" class="adm-act green">Retry</button>
            </form>
            <form method="POST" action="{{ route('admin.jobs.delete', $j->uuid) }}" class="adm-inline" onsubmit="return confirm('Delete this failed job?')">
                @csrf @method('DELETE')<button type="submit" class="adm-act red">Delete</button>
            </form>
        </div>
    </div>
    @empty
    <div class="adm-card"><p class="adm-empty">No failed jobs. The queue is healthy.</p></div>
    @endforelse
</div>
@endsection
