@extends('layouts.app')
@section('title', 'Scheduled Messages')

@section('left-panel')
<div class="p-4">
    <h2 class="font-semibold text-gray-900 dark:text-white text-sm">Scheduled</h2>
</div>
@endsection

@section('content')
<div class="p-6 max-w-2xl mx-auto w-full">
    <h1 class="text-xl font-bold text-gray-900 dark:text-white mb-6">Scheduled Messages</h1>
    @forelse($scheduled as $msg)
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-4 mb-3">
        <div class="flex items-start justify-between gap-3">
            <div class="flex-1">
                <p class="text-sm font-medium text-yellow-600">⏰ {{ $msg->scheduled_at?->format('M j, Y g:i A') }}</p>
                <p class="text-sm text-gray-700 dark:text-gray-300 mt-1">{{ $msg->body }}</p>
                <p class="text-xs text-gray-400 mt-1">in: {{ $msg->conversation?->name ?? 'Direct Message' }}</p>
            </div>
            <form method="POST" action="{{ route('scheduled.destroy', $msg) }}">
                @csrf @method('DELETE')
                <button type="submit" class="text-xs text-red-500 hover:text-red-700">Cancel</button>
            </form>
        </div>
    </div>
    @empty
    <div class="text-center py-16 text-gray-500">
        <p>No scheduled messages</p>
    </div>
    @endforelse
</div>
@endsection
