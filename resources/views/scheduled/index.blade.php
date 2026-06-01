@extends('layouts.app')
@section('title', 'Scheduled Messages')

@section('left-panel')
<div class="p-4">
    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Queue</p>
    <h2 class="font-semibold text-gray-900 text-sm">Scheduled</h2>
    <p class="text-xs text-gray-400 mt-1">Messages sending later</p>
</div>
@endsection

@section('content')
<div class="flex-1 overflow-y-auto p-6">
    <div class="max-w-2xl mx-auto w-full">
        <h1 class="text-lg font-bold text-gray-900 mb-1">Scheduled Messages</h1>
        <p class="text-sm text-gray-400 mb-6">Messages queued to send at a later time</p>

        @forelse($scheduled as $msg)
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 mb-3 hover:shadow-md transition-shadow">
            <div class="flex items-start gap-4">
                {{-- Clock icon --}}
                <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center flex-shrink-0 mt-0.5">
                    <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>

                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="text-xs font-semibold text-amber-600">{{ $msg->scheduled_at?->format('M j, Y') }}</span>
                        <span class="text-xs text-gray-400">at {{ $msg->scheduled_at?->format('g:i A') }}</span>
                        <span class="ml-auto text-xs bg-slate-100 text-gray-500 font-medium px-2 py-0.5 rounded-full">
                            {{ $msg->conversation?->name ?? 'Direct Message' }}
                        </span>
                    </div>
                    <p class="text-sm text-gray-700 leading-relaxed">{{ $msg->body }}</p>

                    <div class="flex items-center gap-3 mt-3 pt-3 border-t border-slate-50">
                        <form method="POST" action="{{ route('scheduled.destroy', $msg) }}">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-xs font-semibold text-red-400 hover:text-red-600 transition-colors">Cancel</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm py-20 text-center">
            <div class="w-16 h-16 rounded-2xl bg-slate-50 flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <p class="font-semibold text-gray-700 mb-1">No scheduled messages</p>
            <p class="text-sm text-gray-400">Messages you schedule will appear here.</p>
        </div>
        @endforelse
    </div>
</div>
@endsection
