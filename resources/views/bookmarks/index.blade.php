@extends('layouts.app')
@section('title', 'Bookmarks')

@section('left-panel')
<div class="p-4">
    <h2 class="font-semibold text-gray-900 dark:text-white text-sm">Bookmarks</h2>
    <p class="text-xs text-gray-500 mt-1">Saved messages</p>
</div>
@endsection

@section('content')
<div class="p-6 max-w-2xl mx-auto w-full">
    <h1 class="text-xl font-bold text-gray-900 dark:text-white mb-6">Bookmarks</h1>
    @forelse($bookmarks as $bookmark)
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-4 mb-3">
        <div class="flex items-start justify-between gap-3">
            <div class="flex items-start gap-3 flex-1 min-w-0">
                <img src="{{ $bookmark->message->user?->avatar_url }}" class="w-8 h-8 rounded-full object-cover flex-shrink-0 mt-0.5">
                <div class="min-w-0">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ $bookmark->message->user?->name }}</span>
                        <span class="text-xs text-gray-400">in {{ $bookmark->message->conversation?->getDisplayName(auth()->user()) }}</span>
                        <span class="text-xs text-gray-400">{{ $bookmark->message->created_at?->diffForHumans() }}</span>
                    </div>
                    <p class="text-sm text-gray-700 dark:text-gray-300">{{ $bookmark->message->body }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2 flex-shrink-0">
                <a href="{{ route('chat.conversation', $bookmark->message->conversation_id) }}" class="text-xs text-primary hover:text-primary-hover">Open →</a>
                <form method="POST" action="{{ route('bookmarks.toggle', $bookmark->message) }}">
                    @csrf
                    <button type="submit" class="text-xs text-red-500 hover:text-red-700">Remove</button>
                </form>
            </div>
        </div>
    </div>
    @empty
    <div class="text-center py-16 text-gray-500">
        <svg class="w-16 h-16 mx-auto mb-4 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/></svg>
        <p>No bookmarks yet</p>
    </div>
    @endforelse
    <div class="mt-4">{{ $bookmarks->links() }}</div>
</div>
@endsection
