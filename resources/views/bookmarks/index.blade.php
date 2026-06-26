@extends('layouts.app')
@section('title', 'Bookmarks')

@section('left-panel')
<div class="p-4">
    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Library</p>
    <h2 class="font-semibold text-gray-900 text-sm">Bookmarks</h2>
    <p class="text-xs text-gray-400 mt-1">Your saved messages</p>
</div>
@endsection

@section('content')
<div class="flex-1 overflow-y-auto p-6">
    <div class="max-w-2xl mx-auto w-full">
        <h1 class="text-lg font-bold text-gray-900 mb-1">Bookmarks</h1>
        <p class="text-sm text-gray-400 mb-6">Messages you've saved for later</p>

        @forelse($bookmarks as $bookmark)
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 mb-3 hover:shadow-md transition-shadow">
            <div class="flex items-start gap-3">
                <img src="{{ $bookmark->message->user?->avatar_url }}" alt="{{ $bookmark->message->user?->name }}" class="w-9 h-9 rounded-full object-cover flex-shrink-0 mt-0.5">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center flex-wrap gap-2 mb-1.5">
                        <span class="text-sm font-semibold text-gray-800">{{ $bookmark->message->user?->name }}</span>
                        <span class="text-xs bg-emerald-50 text-emerald-600 font-medium px-2 py-0.5 rounded-full border border-emerald-100">
                            {{ $bookmark->message->conversation?->getDisplayName(auth()->user()) }}
                        </span>
                        <span class="text-xs text-gray-400">{{ $bookmark->message->created_at?->diffForHumans() }}</span>
                    </div>
                    <p class="text-sm text-gray-700 leading-relaxed">{{ $bookmark->message->body }}</p>

                    <div class="flex items-center gap-3 mt-3 pt-3 border-t border-slate-50">
                        <a href="{{ route('chat.conversation', $bookmark->message->conversation_id) }}" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700 transition-colors flex items-center gap-1">
                            Go to message
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                        </a>
                        <form method="POST" action="{{ route('bookmarks.toggle', $bookmark->message) }}">
                            @csrf
                            <button type="submit" class="text-xs text-red-400 hover:text-red-600 font-medium transition-colors">Remove</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm py-20 text-center">
            <div class="w-16 h-16 rounded-2xl bg-slate-50 flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/></svg>
            </div>
            <p class="font-semibold text-gray-700 mb-1">No bookmarks yet</p>
            <p class="text-sm text-gray-400">Save messages to find them here later.</p>
        </div>
        @endforelse

        <div class="mt-4">{{ $bookmarks->links() }}</div>
    </div>
</div>
@endsection
