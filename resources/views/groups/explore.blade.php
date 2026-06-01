@extends('layouts.app')
@section('title', 'Explore Groups')

@section('left-panel')
<div class="p-4">
    <h2 class="font-semibold text-gray-900 dark:text-white text-sm mb-4">Browse Groups</h2>
    @if(!auth()->user()->is_guest)
    <a href="{{ route('groups.create') }}" class="w-full btn-primary text-sm py-2 flex items-center justify-center gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Create Group
    </a>
    @endif
</div>
@endsection

@section('content')
<div class="p-6 max-w-3xl mx-auto w-full">
    <h1 class="text-xl font-bold text-gray-900 dark:text-white mb-6">Explore Public Groups</h1>

    @if($groups->isEmpty())
    <div class="text-center py-16 text-gray-500">
        <svg class="w-16 h-16 mx-auto mb-4 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0"/></svg>
        <p>No public groups yet</p>
    </div>
    @else
    <div class="grid gap-4 md:grid-cols-2">
        @foreach($groups as $group)
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-4 hover:shadow-md transition-shadow">
            <div class="flex items-start gap-3">
                <img src="{{ $group->getAvatarUrl(auth()->user()) }}" class="w-12 h-12 rounded-xl object-cover flex-shrink-0">
                <div class="flex-1 min-w-0">
                    <h3 class="font-semibold text-gray-900 dark:text-white">{{ $group->name }}</h3>
                    @if($group->description)
                    <p class="text-sm text-gray-500 mt-0.5 line-clamp-2">{{ $group->description }}</p>
                    @endif
                    <p class="text-xs text-gray-400 mt-1">{{ $group->participants_count }} members</p>
                </div>
            </div>
            <div class="mt-3 flex gap-2">
                @if($group->users()->where('users.id', auth()->id())->exists())
                <a href="{{ route('chat.conversation', $group) }}" class="flex-1 text-center text-sm text-primary border border-primary/30 hover:bg-primary/5 rounded-lg py-1.5 transition-colors">Open Chat</a>
                @else
                <form method="POST" action="{{ route('groups.join', $group) }}" class="flex-1">
                    @csrf
                    <button type="submit" class="w-full btn-primary text-sm py-1.5">Join Group</button>
                </form>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    <div class="mt-6">{{ $groups->links() }}</div>
    @endif
</div>
@endsection
