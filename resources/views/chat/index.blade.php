@extends('layouts.app')
@section('title', 'Messages')

@section('left-panel')
<div class="flex flex-col h-full bg-white" x-data="{ search: '' }" style="font-family: 'Inter', sans-serif;">
    {{-- Header --}}
    <div class="px-4 pt-5 pb-3 border-b border-slate-100">
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-base font-semibold text-gray-900">Messages</h2>
            <a href="{{ route('groups.create') }}"
               class="w-7 h-7 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl flex items-center justify-center transition-colors shadow-sm"
               title="New Group">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                </svg>
            </a>
        </div>
        {{-- Search --}}
        <div class="relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input type="text" x-model="search" placeholder="Search conversations..."
                   class="w-full pl-9 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-300 transition-colors">
        </div>
    </div>

    {{-- Conversation list --}}
    <div class="flex-1 overflow-y-auto py-1">
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
           class="flex items-center gap-3 px-4 py-3 hover:bg-slate-50 cursor-pointer transition-colors border-l-2 border-transparent">
            {{-- Avatar --}}
            <div class="relative flex-shrink-0">
                <img src="{{ $avatar }}" alt="{{ $name }}" class="w-10 h-10 rounded-full object-cover">
                @if($other)
                <span class="absolute -bottom-0.5 -right-0.5 w-3 h-3 rounded-full border-2 border-white {{ $other->is_online ? 'bg-emerald-500' : 'bg-gray-300' }}"></span>
                @endif
            </div>
            {{-- Info --}}
            <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between gap-1">
                    <span class="font-medium text-sm text-gray-900 truncate">{{ $name }}</span>
                    @if($lastMsg)
                    <span class="text-xs text-gray-400 flex-shrink-0">{{ $lastMsg->created_at->format('H:i') }}</span>
                    @endif
                </div>
                <div class="flex items-center justify-between mt-0.5 gap-1">
                    <p class="text-xs text-gray-500 truncate">
                        @if($conversation->isDirect() && $lastMsg?->user_id === auth()->id())
                        <span class="text-gray-400">You: </span>
                        @endif
                        {{ $lastMsg?->body ?? 'No messages yet' }}
                    </p>
                    @if($unread > 0)
                    <span class="ml-1 bg-emerald-500 text-white text-xs rounded-full px-1.5 py-0.5 flex-shrink-0 font-medium leading-none">{{ $unread }}</span>
                    @endif
                </div>
            </div>
        </a>
        @empty
        <div class="flex flex-col items-center justify-center p-8 text-center">
            <div class="w-14 h-14 bg-slate-100 rounded-2xl flex items-center justify-center mb-3">
                <svg class="w-7 h-7 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                </svg>
            </div>
            <p class="text-sm font-medium text-gray-600 mb-1">No conversations yet</p>
            <a href="{{ route('people.index') }}" class="text-xs text-emerald-600 hover:text-emerald-700 font-medium transition-colors">Find people to chat →</a>
        </div>
        @endforelse
    </div>
</div>
@endsection

@section('content')
<div class="flex-1 flex items-center justify-center text-center p-8 bg-slate-50 h-full" style="font-family: 'Inter', sans-serif;">
    <div class="max-w-xs">
        {{-- Icon --}}
        <div class="w-20 h-20 bg-white border border-slate-100 rounded-3xl flex items-center justify-center mx-auto mb-6 shadow-sm">
            <svg class="w-9 h-9 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
            </svg>
        </div>

        <h3 class="text-lg font-semibold text-gray-900 mb-2">Select a conversation</h3>
        <p class="text-sm text-gray-400 mb-6 leading-relaxed">Choose from your existing conversations on the left, or start a new one with someone.</p>

        <a href="{{ route('people.index') }}"
           class="inline-flex items-center gap-2 bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-medium px-5 py-2.5 rounded-xl transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Start a new chat
        </a>
    </div>
</div>
@endsection
