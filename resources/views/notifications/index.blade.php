@extends('layouts.app')
@section('title', 'Notifications')

@section('left-panel')
<div class="p-4">
    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-3">Inbox</p>
    <h2 class="font-semibold text-gray-900 text-sm mb-4">Notifications</h2>
    <form method="POST" action="{{ route('notifications.read-all') }}">
        @csrf
        <button type="submit" class="w-full bg-slate-100 hover:bg-slate-200 text-gray-700 rounded-xl px-4 py-2 text-sm font-medium transition-colors">
            Mark all read
        </button>
    </form>
</div>
@endsection

@section('content')
<div class="flex-1 overflow-y-auto p-6">
    <div class="max-w-2xl mx-auto w-full">
        <h1 class="text-lg font-bold text-gray-900 mb-1">Notifications</h1>
        <p class="text-sm text-gray-400 mb-6">Stay up to date with your conversations</p>

        @forelse($notifications as $notif)
        <div class="rounded-2xl mb-2 overflow-hidden transition-all
            {{ $notif->read_at
                ? 'bg-white border border-slate-100 shadow-sm opacity-70'
                : 'bg-emerald-50/50 border border-slate-100 shadow-sm border-l-2 border-l-emerald-500' }}">
            <div class="flex items-start gap-3 p-4">
                <div class="w-9 h-9 rounded-full flex items-center justify-center flex-shrink-0 text-base
                    {{ $notif->type === 'new_message' ? 'bg-blue-100' : ($notif->type === 'call' ? 'bg-emerald-100' : 'bg-slate-100') }}">
                    @if($notif->type === 'new_message') 💬
                    @elseif($notif->type === 'call') 📞
                    @else 🔔
                    @endif
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-gray-800">{{ $notif->title }}</p>
                    <p class="text-sm text-gray-500 mt-0.5">{{ $notif->body }}</p>
                    <p class="text-xs text-gray-400 mt-1.5">{{ $notif->created_at->diffForHumans() }}</p>
                </div>
                @if(!$notif->read_at)
                <span class="w-2 h-2 rounded-full mt-1.5 flex-shrink-0" style="background:#10b981;"></span>
                @endif
            </div>
        </div>
        @empty
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm py-20 text-center">
            <div class="w-16 h-16 rounded-2xl bg-slate-50 flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
            </div>
            <p class="font-semibold text-gray-700 mb-1">All caught up</p>
            <p class="text-sm text-gray-400">You have no notifications right now.</p>
        </div>
        @endforelse

        <div class="mt-4">{{ $notifications->links() }}</div>
    </div>
</div>
@endsection
