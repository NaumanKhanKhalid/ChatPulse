@extends('layouts.app')
@section('title', 'Messages')

@section('left-panel')
<div class="flex flex-col h-full" x-data="{ search: '' }">
    {{-- Header --}}
    <div class="p-4 border-b border-sidebar-border dark:border-gray-700">
        <div class="flex items-center justify-between mb-3">
            <h2 class="font-semibold text-gray-900 dark:text-white text-sm">Messages</h2>
            <a href="{{ route('groups.create') }}" class="w-7 h-7 bg-primary hover:bg-primary-hover text-white rounded-full flex items-center justify-center transition-colors" title="New Group">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            </a>
        </div>
        <div class="relative">
            <svg class="absolute left-3 top-2.5 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="text" x-model="search" placeholder="Search conversations..." class="w-full pl-9 pr-3 py-2 bg-gray-100 dark:bg-gray-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 dark:text-white dark:placeholder-gray-400">
        </div>
    </div>

    {{-- Conversation list --}}
    <div class="flex-1 overflow-y-auto">
        @forelse($conversations as $conversation)
        @php
            $name = $conversation->getDisplayName(auth()->user());
            $avatar = $conversation->getAvatarUrl(auth()->user());
            $unread = $conversation->getUnreadCountFor(auth()->user());
            $lastMsg = $conversation->lastMessage;
            $other = $conversation->isDirect() ? $conversation->getOtherUser(auth()->user()) : null;
        @endphp
        <a href="{{ route('chat.conversation', $conversation) }}"
           x-show="!search || '{{ strtolower($name) }}'.includes(search.toLowerCase())"
           class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/50 cursor-pointer transition-colors border-l-2 {{ request()->routeIs('chat.conversation') && request()->route('conversation')->id === $conversation->id ? 'border-primary bg-emerald-50/50 dark:bg-emerald-900/20' : 'border-transparent' }}">
            {{-- Avatar --}}
            <div class="relative flex-shrink-0">
                <img src="{{ $avatar }}" alt="{{ $name }}" class="w-10 h-10 rounded-full object-cover">
                @if($other)
                <span class="absolute -bottom-0.5 -right-0.5 w-3 h-3 rounded-full border-2 border-white dark:border-gray-800 {{ $other->is_online ? 'bg-green-500' : 'bg-gray-400' }}"></span>
                @endif
            </div>
            {{-- Info --}}
            <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between">
                    <span class="font-medium text-sm text-gray-900 dark:text-white truncate">{{ $name }}</span>
                    @if($lastMsg)
                    <span class="text-xs text-gray-400 flex-shrink-0 ml-2">{{ $lastMsg->created_at->format('H:i') }}</span>
                    @endif
                </div>
                <div class="flex items-center justify-between mt-0.5">
                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                        @if($conversation->isDirect() && $lastMsg?->user_id === auth()->id())
                            <span class="text-gray-400">You: </span>
                        @endif
                        {{ $lastMsg?->body ?? 'No messages yet' }}
                    </p>
                    @if($unread > 0)
                    <span class="ml-2 bg-primary text-white text-xs rounded-full px-1.5 py-0.5 flex-shrink-0 font-medium">{{ $unread }}</span>
                    @endif
                </div>
            </div>
        </a>
        @empty
        <div class="p-6 text-center text-gray-500 dark:text-gray-400 text-sm">
            <svg class="w-12 h-12 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
            <p>No conversations yet</p>
            <a href="{{ route('people.index') }}" class="text-primary text-sm mt-2 inline-block">Find people to chat with →</a>
        </div>
        @endforelse
    </div>
</div>
@endsection

@section('content')
<div class="flex-1 flex items-center justify-center text-center p-8 bg-gray-50 dark:bg-gray-900">
    <div>
        <div class="w-20 h-20 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-10 h-10 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
        </div>
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Select a conversation</h3>
        <p class="text-gray-500 dark:text-gray-400 text-sm mb-4">Choose from your existing conversations or start a new one</p>
        <a href="{{ route('people.index') }}" class="btn-primary text-sm inline-block">Start a conversation</a>
    </div>
</div>
@endsection
