@extends('layouts.app')
@section('title', 'Notifications')

@section('left-panel')
<div class="p-4">
    <h2 class="font-semibold text-gray-900 dark:text-white text-sm">Notifications</h2>
</div>
@endsection

@section('content')
<div class="p-6 max-w-2xl mx-auto w-full">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-bold text-gray-900 dark:text-white">Notifications</h1>
        <form method="POST" action="{{ route('notifications.read-all') }}">
            @csrf
            <button type="submit" class="text-sm text-primary hover:text-primary-hover">Mark all read</button>
        </form>
    </div>
    @forelse($notifications as $notif)
    <div class="flex items-start gap-3 p-3 rounded-xl mb-2 {{ $notif->read_at ? 'bg-white dark:bg-gray-800' : 'bg-primary/5 border border-primary/20' }}">
        <div class="w-9 h-9 rounded-full bg-primary/10 flex items-center justify-center flex-shrink-0 text-primary text-sm">
            @if($notif->type === 'new_message') 💬
            @elseif($notif->type === 'call') 📞
            @else 🔔
            @endif
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ $notif->title }}</p>
            <p class="text-sm text-gray-500 mt-0.5">{{ $notif->body }}</p>
            <p class="text-xs text-gray-400 mt-1">{{ $notif->created_at->diffForHumans() }}</p>
        </div>
        @if(!$notif->read_at)
        <span class="w-2 h-2 rounded-full bg-primary flex-shrink-0 mt-2"></span>
        @endif
    </div>
    @empty
    <div class="text-center py-16 text-gray-500">
        <p>No notifications</p>
    </div>
    @endforelse
    <div class="mt-4">{{ $notifications->links() }}</div>
</div>
@endsection
