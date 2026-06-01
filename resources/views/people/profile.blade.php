@extends('layouts.app')
@section('title', $user->name)

@section('left-panel')
<div class="p-4">
    <a href="{{ route('people.index') }}" class="text-sm text-gray-600 hover:text-gray-900 flex items-center gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        People
    </a>
</div>
@endsection

@section('content')
<div class="p-6 max-w-lg mx-auto w-full">
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-6 text-center">
        <div class="relative inline-block mb-4">
            <img src="{{ $user->avatar_url }}" class="w-20 h-20 rounded-full object-cover mx-auto">
            <span class="absolute -bottom-1 -right-1 w-5 h-5 rounded-full border-2 border-white {{ $user->is_online ? 'bg-green-500' : 'bg-gray-400' }}"></span>
        </div>
        <h1 class="text-xl font-bold text-gray-900 dark:text-white">{{ $user->name }}</h1>
        <p class="text-gray-500 text-sm">@{{ $user->username }}</p>
        @if($user->status_message)
        <p class="text-sm text-gray-500 mt-2">{{ $user->status_emoji }} {{ $user->status_message }}</p>
        @endif
        @if($user->bio)
        <p class="text-sm text-gray-600 dark:text-gray-400 mt-3">{{ $user->bio }}</p>
        @endif
        @if($user->id !== auth()->id())
        <form method="POST" action="{{ route('people.dm', $user) }}" class="mt-4">
            @csrf
            <button type="submit" class="btn-primary px-8 py-2.5">Send Message</button>
        </form>
        @endif
    </div>
</div>
@endsection
