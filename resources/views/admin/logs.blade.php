@extends('layouts.admin')
@section('page-title', 'Error Logs')
@section('content')

<div class="adm-filters">
    <div class="adm-tabs" style="margin:0;border:none">
        @foreach([['', 'All'], ['error','Errors'], ['warning','Warnings'], ['critical','Critical']] as [$k, $lbl])
        <a href="{{ route('admin.logs', $k ? ['level' => $k] : []) }}" class="adm-tab {{ $level === ($k ?: null) ? 'on' : '' }}">{{ $lbl }}</a>
        @endforeach
    </div>
    <span class="adm-card-sub" style="margin-left:auto">
        laravel.log · {{ $logSize > 1048576 ? round($logSize/1048576, 1).' MB' : round($logSize/1024).' KB' }}
    </span>
    <form method="POST" action="{{ route('admin.logs.clear') }}" onsubmit="return confirm('Clear the whole log file?')">
        @csrf<button type="submit" class="adm-btn-ghost danger">Clear log</button>
    </form>
</div>

<div class="adm-list">
    @forelse($entries as $i => $e)
    @php
        $tint = in_array($e['level'], ['error','critical','emergency','alert']) ? 'red'
              : ($e['level'] === 'warning' ? 'amber' : 'dim');
    @endphp
    <div class="adm-item">
        <div class="adm-item-main">
            <div class="adm-item-title">
                <span class="adm-badge {{ $tint === 'amber' ? 'dim' : $tint }}" style="{{ $tint === 'amber' ? 'background:rgba(245,158,11,.14);color:#b45309' : '' }}">{{ $e['level'] }}</span>
                <b style="font-weight:600;font-size:13.5px">{{ $e['time'] }}</b>
            </div>
            <div class="adm-item-body">{{ $e['title'] }}</div>
            <button type="button" class="adm-link" style="font-size:12px;margin-top:6px" onclick="document.getElementById('tr-{{ $i }}').classList.toggle('open')">Stack trace</button>
            <pre class="adm-trace" id="tr-{{ $i }}">{{ $e['trace'] }}</pre>
        </div>
    </div>
    @empty
    <div class="adm-card"><p class="adm-empty">No log entries{{ $level ? ' at this level' : '' }}. That is good news.</p></div>
    @endforelse
</div>
@endsection
