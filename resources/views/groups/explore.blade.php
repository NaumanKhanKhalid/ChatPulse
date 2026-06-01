@extends('layouts.app')
@section('title', 'Explore Groups')

@section('left-panel')
<div class="flex flex-col h-full p-4 gap-4">
    <div>
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-3">Groups</p>
        <div class="relative">
            <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z"/></svg>
            <input
                type="text"
                placeholder="Search groups…"
                class="w-full border border-slate-200 bg-slate-50 rounded-xl pl-9 pr-4 py-2.5 text-sm text-gray-700 placeholder-gray-400 outline-none"
                onfocus="this.style.borderColor='#10b981';this.style.boxShadow='0 0 0 3px rgba(16,185,129,0.12)'"
                onblur="this.style.borderColor='';this.style.boxShadow=''"
            >
        </div>
    </div>

    @if(!auth()->user()->is_guest)
    <a href="{{ route('groups.create') }}" class="flex items-center justify-center gap-2 text-white font-semibold rounded-xl px-4 py-2 text-sm transition-opacity hover:opacity-90" style="background:#10b981;">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Create Group
    </a>
    @endif
</div>
@endsection

@section('content')
<div class="flex-1 overflow-y-auto p-6">
    <div class="max-w-3xl mx-auto w-full">
        <h1 class="text-lg font-bold text-gray-900 mb-1">Explore Groups</h1>
        <p class="text-sm text-gray-400 mb-6">Discover and join public communities</p>

        @if($groups->isEmpty())
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm py-20 text-center">
            <div class="w-16 h-16 rounded-2xl bg-slate-50 flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0"/></svg>
            </div>
            <p class="font-semibold text-gray-700 mb-1">No public groups yet</p>
            <p class="text-sm text-gray-400">Be the first to create one!</p>
        </div>
        @else
        <div class="grid gap-4 sm:grid-cols-2">
            @foreach($groups as $group)
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 hover:shadow-md transition-shadow">
                <div class="flex items-start gap-3 mb-4">
                    <img src="{{ $group->getAvatarUrl(auth()->user()) }}" alt="{{ $group->name }}" class="w-12 h-12 rounded-xl object-cover flex-shrink-0">
                    <div class="flex-1 min-w-0">
                        <h3 class="font-semibold text-gray-900 truncate">{{ $group->name }}</h3>
                        <p class="text-xs text-gray-400 mt-0.5">
                            <span class="font-medium text-gray-500">{{ $group->participants_count }}</span> members
                        </p>
                        @if($group->description)
                        <p class="text-sm text-gray-500 mt-1.5 line-clamp-2">{{ $group->description }}</p>
                        @endif
                    </div>
                </div>

                @if($group->users()->where('users.id', auth()->id())->exists())
                <a href="{{ route('chat.conversation', $group) }}"
                    class="block w-full text-center text-sm font-semibold rounded-xl px-4 py-2 border-2 border-emerald-200 text-emerald-600 hover:bg-emerald-50 transition-colors">
                    Open Chat
                </a>
                @else
                <form method="POST" action="{{ route('groups.join', $group) }}">
                    @csrf
                    <button type="submit" class="w-full text-white font-semibold rounded-xl px-4 py-2 text-sm transition-opacity hover:opacity-90" style="background:#10b981;">
                        Join Group
                    </button>
                </form>
                @endif
            </div>
            @endforeach
        </div>

        <div class="mt-6">{{ $groups->links() }}</div>
        @endif
    </div>
</div>
@endsection
