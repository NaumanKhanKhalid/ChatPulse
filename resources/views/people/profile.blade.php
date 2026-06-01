@extends('layouts.app')
@section('title', $user->name)

@section('left-panel')
<div class="p-4">
    <a href="{{ route('people.index') }}" class="flex items-center gap-2 text-sm font-medium text-gray-500 hover:text-gray-900 transition-colors group">
        <span class="w-7 h-7 rounded-lg bg-slate-100 group-hover:bg-slate-200 flex items-center justify-center transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        </span>
        People
    </a>
</div>
@endsection

@section('content')
<div class="flex-1 overflow-y-auto p-6">
    <div class="max-w-lg mx-auto w-full">

        {{-- Main profile card --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-8 text-center mb-4">
            <div class="relative inline-block mb-5">
                <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-24 h-24 rounded-full object-cover mx-auto ring-4 ring-slate-50">
                <span class="absolute bottom-1 right-1 w-4 h-4 rounded-full border-2 border-white {{ $user->is_online ? 'bg-emerald-500' : 'bg-slate-300' }}"></span>
            </div>

            <h1 class="text-2xl font-bold text-gray-900 mb-1">{{ $user->name }}</h1>
            <p class="text-gray-400 text-sm mb-2">@{{ $user->username }}</p>

            @if($user->status_message)
            <p class="text-sm text-gray-500 mb-3">{{ $user->status_emoji }} {{ $user->status_message }}</p>
            @endif

            @if($user->bio)
            <p class="text-sm text-gray-600 leading-relaxed max-w-sm mx-auto mb-5">{{ $user->bio }}</p>
            @else
            <div class="mb-5"></div>
            @endif

            @if($user->id !== auth()->id())
            <form method="POST" action="{{ route('people.dm', $user) }}">
                @csrf
                <button type="submit" class="text-white font-semibold rounded-xl px-8 py-2.5 text-sm transition-opacity hover:opacity-90" style="background:#10b981;">Send Message</button>
            </form>
            @endif
        </div>

        {{-- Info rows --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-4">Details</p>
            <div class="space-y-3">
                @if(auth()->user()->is_admin ?? false)
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-slate-50 flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 font-medium">Email</p>
                        <p class="text-sm text-gray-700">{{ $user->email }}</p>
                    </div>
                </div>
                @endif
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-slate-50 flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 font-medium">Joined</p>
                        <p class="text-sm text-gray-700">{{ $user->created_at->format('F Y') }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-slate-50 flex items-center justify-center flex-shrink-0">
                        <span class="w-2.5 h-2.5 rounded-full {{ $user->is_online ? 'bg-emerald-500' : 'bg-slate-300' }}"></span>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 font-medium">Status</p>
                        <p class="text-sm text-gray-700">{{ $user->is_online ? 'Online' : 'Offline' }}</p>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
