@extends('layouts.app')
@section('title', 'People')

@section('left-panel')
<div class="p-4">
    <h2 class="font-semibold text-gray-900 dark:text-white text-sm mb-4">People</h2>
    <p class="text-xs text-gray-500">Find teammates and start conversations</p>
</div>
@endsection

@section('content')
<div class="p-6 max-w-3xl mx-auto w-full">
    <h1 class="text-xl font-bold text-gray-900 dark:text-white mb-6">People</h1>
    <div class="grid gap-3 md:grid-cols-2 lg:grid-cols-3">
        @foreach($users as $user)
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-4 hover:shadow-md transition-shadow">
            <div class="flex items-center gap-3 mb-3">
                <div class="relative">
                    <img src="{{ $user->avatar_url }}" class="w-11 h-11 rounded-full object-cover">
                    <span class="absolute -bottom-0.5 -right-0.5 w-3 h-3 rounded-full border-2 border-white {{ $user->is_online ? 'bg-green-500' : 'bg-gray-400' }}"></span>
                </div>
                <div class="min-w-0">
                    <div class="flex items-center gap-1">
                        <p class="font-medium text-sm text-gray-900 dark:text-white truncate">{{ $user->name }}</p>
                        @if($user->is_guest)<span class="text-xs bg-yellow-100 text-yellow-700 px-1 rounded">Guest</span>@endif
                    </div>
                    <p class="text-xs text-gray-500">@{{ $user->username }}</p>
                </div>
            </div>
            @if($user->status_message)
            <p class="text-xs text-gray-500 mb-3 truncate">{{ $user->status_emoji }} {{ $user->status_message }}</p>
            @endif
            <form method="POST" action="{{ route('people.dm', $user) }}">
                @csrf
                <button type="submit" class="w-full text-sm btn-primary py-1.5">Message</button>
            </form>
        </div>
        @endforeach
    </div>
    <div class="mt-6">{{ $users->links() }}</div>
</div>
@endsection
