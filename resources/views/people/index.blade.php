@extends('layouts.app')
@section('title', 'People')

@section('left-panel')
<div class="flex flex-col h-full p-4 gap-4">
    <div>
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-3">People</p>
        <div class="relative">
            <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z"/></svg>
            <input
                type="text"
                placeholder="Search people…"
                class="w-full border border-slate-200 bg-slate-50 rounded-xl pl-9 pr-4 py-2.5 text-sm text-gray-700 placeholder-gray-400 outline-none"
                onfocus="this.style.borderColor='#10b981';this.style.boxShadow='0 0 0 3px rgba(16,185,129,0.12)'"
                onblur="this.style.borderColor='';this.style.boxShadow=''"
            >
        </div>
    </div>

    <div class="flex gap-1">
        <button class="flex-1 text-xs font-semibold rounded-lg py-1.5 transition-colors" style="background:#10b981;color:#fff;">All</button>
        <button class="flex-1 text-xs font-medium rounded-lg py-1.5 bg-slate-100 hover:bg-slate-200 text-gray-600 transition-colors">Online</button>
    </div>
</div>
@endsection

@section('content')
<div class="flex-1 overflow-y-auto p-6">
    <div class="max-w-3xl mx-auto w-full">
        <h1 class="text-lg font-bold text-gray-900 mb-1">People</h1>
        <p class="text-sm text-gray-400 mb-6">Find teammates and start conversations</p>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($users as $user)
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 text-center hover:shadow-md transition-shadow">
                <div class="relative inline-block mb-3">
                    <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-16 h-16 rounded-full object-cover mx-auto">
                    <span class="absolute bottom-0.5 right-0.5 w-3.5 h-3.5 rounded-full border-2 border-white {{ $user->is_online ? 'bg-emerald-500' : 'bg-slate-300' }}"></span>
                </div>

                <div class="flex items-center justify-center gap-1.5 mb-0.5">
                    <p class="font-semibold text-sm text-gray-900 truncate">{{ $user->name }}</p>
                    @if($user->is_guest)
                    <span class="text-xs bg-amber-100 text-amber-600 px-1.5 py-0.5 rounded-md font-medium">Guest</span>
                    @endif
                </div>
                <p class="text-xs text-gray-400 mb-2">@{{ $user->username }}</p>

                @if($user->status_message)
                <p class="text-xs text-gray-500 mb-3 truncate">{{ $user->status_emoji }} {{ $user->status_message }}</p>
                @else
                <div class="mb-3"></div>
                @endif

                <div class="flex flex-col gap-2">
                    <form method="POST" action="{{ route('people.dm', $user) }}">
                        @csrf
                        <button type="submit" class="w-full text-white font-semibold rounded-xl px-4 py-2 text-sm transition-opacity hover:opacity-90" style="background:#10b981;">Message</button>
                    </form>
                    <a href="{{ route('people.profile', $user) }}" class="w-full bg-slate-100 hover:bg-slate-200 text-gray-700 rounded-xl px-4 py-2 text-sm font-medium text-center transition-colors block">View Profile</a>
                </div>
            </div>
            @endforeach
        </div>

        <div class="mt-6">{{ $users->links() }}</div>
    </div>
</div>
@endsection
