@php
    $currentUser = auth()->user();
    $otherUser = $conversation->isDirect() ? $conversation->getOtherUser($currentUser) : null;
    $participant = $conversation->participants()->where('user_id', $currentUser->id)->first();
    $isAdmin = $conversation->isGroup() && $participant?->role === 'admin';
@endphp

<div class="flex flex-col h-full bg-white" style="font-family: 'Inter', sans-serif;">

    {{-- Header: avatar + name + status --}}
    <div class="px-4 pt-6 pb-4 border-b border-slate-100 text-center flex-shrink-0">
        <div class="relative inline-block mb-3">
            <img src="{{ $conversation->getAvatarUrl($currentUser) }}" alt=""
                 class="w-16 h-16 rounded-full object-cover mx-auto ring-2 ring-slate-100">
            @if($otherUser)
            <span class="absolute bottom-0.5 right-0.5 w-3.5 h-3.5 rounded-full border-2 border-white {{ $otherUser->is_online ? 'bg-emerald-500' : 'bg-gray-300' }}"></span>
            @endif
        </div>
        <h3 class="font-semibold text-gray-900 text-sm leading-tight">{{ $conversation->getDisplayName($currentUser) }}</h3>
        @if($otherUser)
        <p class="text-xs text-gray-500 mt-1">{{ $otherUser->status_emoji }} {{ $otherUser->status_message }}</p>
        <span class="inline-flex items-center gap-1.5 text-xs mt-2 px-2.5 py-1 rounded-full {{ $otherUser->is_online ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-100 text-gray-400' }}">
            <span class="w-1.5 h-1.5 rounded-full {{ $otherUser->is_online ? 'bg-emerald-500' : 'bg-gray-400' }}"></span>
            {{ $otherUser->is_online ? 'Online' : 'Last seen ' . $otherUser->last_seen_at?->diffForHumans() }}
        </span>
        @elseif($conversation->isGroup())
        <p class="text-xs text-gray-400 mt-1.5">{{ $conversation->participants()->count() }} members</p>
        @endif

        {{-- Action buttons --}}
        <div class="flex gap-2 justify-center mt-4">
            @if($otherUser)
            <button class="w-10 h-10 rounded-xl bg-slate-100 hover:bg-slate-200 text-gray-600 flex items-center justify-center transition-colors" title="Voice Call">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                </svg>
            </button>
            <button class="w-10 h-10 rounded-xl bg-slate-100 hover:bg-slate-200 text-gray-600 flex items-center justify-center transition-colors" title="Video Call">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.069A1 1 0 0121 8.87v6.26a1 1 0 01-1.447.894L15 14M3 8a2 2 0 012-2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V8z"/>
                </svg>
            </button>
            @endif
            <button class="w-10 h-10 rounded-xl bg-slate-100 hover:bg-slate-200 text-gray-600 flex items-center justify-center transition-colors" title="Search in conversation">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </button>
            <button class="w-10 h-10 rounded-xl bg-slate-100 hover:bg-slate-200 text-gray-600 flex items-center justify-center transition-colors" title="Mute notifications">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
            </button>
        </div>
    </div>

    {{-- Scrollable content --}}
    <div class="flex-1 overflow-y-auto px-4 py-4 space-y-5">

        {{-- Pinned messages --}}
        @if($pinnedMessages->count() > 0)
        <div x-data="{ open: true }">
            <button @click="open = !open"
                    class="flex items-center justify-between w-full mb-2 group">
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Pinned ({{ $pinnedMessages->count() }})</span>
                <svg class="w-3.5 h-3.5 text-gray-400 transition-transform group-hover:text-gray-600" :class="open ? 'rotate-180' : ''"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="open" x-transition class="space-y-2">
                @foreach($pinnedMessages as $pin)
                <div class="rounded-xl bg-slate-50 border border-slate-100 p-3">
                    <p class="text-xs font-semibold text-gray-700 mb-1">{{ $pin->message->user?->name }}</p>
                    <p class="text-sm text-gray-600 line-clamp-2">{{ $pin->message->body }}</p>
                    @if($isAdmin)
                    <form method="POST" action="{{ route('pins.destroy', [$conversation, $pin->message]) }}" class="mt-2">
                        @csrf @method('DELETE')
                        <button type="submit"
                                class="text-xs text-red-400 hover:text-red-600 transition-colors font-medium">Unpin</button>
                    </form>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Members (group only) --}}
        @if($conversation->isGroup())
        <div x-data="{ open: true }">
            <button @click="open = !open"
                    class="flex items-center justify-between w-full mb-2 group">
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Members</span>
                <svg class="w-3.5 h-3.5 text-gray-400 transition-transform group-hover:text-gray-600" :class="open ? 'rotate-180' : ''"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="open" x-transition class="space-y-2">
                @foreach($participants as $p)
                <div class="flex items-center gap-2.5">
                    <div class="relative flex-shrink-0">
                        <img src="{{ $p->user->avatar_url }}" class="w-8 h-8 rounded-full object-cover">
                        <span class="absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 rounded-full border-2 border-white {{ $p->user->is_online ? 'bg-emerald-500' : 'bg-gray-300' }}"></span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-800 truncate">{{ $p->user->name }}</p>
                        @if($p->user->status_message)
                        <p class="text-xs text-gray-400 truncate">{{ $p->user->status_emoji }} {{ $p->user->status_message }}</p>
                        @endif
                    </div>
                    <div class="flex items-center gap-1 flex-shrink-0">
                        @if($p->role === 'admin')
                        <span class="text-xs bg-emerald-50 text-emerald-600 border border-emerald-100 px-1.5 py-0.5 rounded-md font-medium">Admin</span>
                        @endif
                        @if($p->user->is_guest)
                        <span class="text-xs bg-yellow-50 text-yellow-600 border border-yellow-100 px-1.5 py-0.5 rounded-md">Guest</span>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Admin actions --}}
        @if($isAdmin)
        <div>
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2">Admin Actions</p>
            <div class="space-y-2">
                <button onclick="document.getElementById('invite-modal').classList.toggle('hidden')"
                        class="w-full text-sm text-gray-700 border border-slate-200 hover:bg-slate-50 rounded-xl px-3 py-2.5 transition-colors text-left flex items-center gap-2 font-medium">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                    </svg>
                    Generate Invite Link
                </button>
                <div id="invite-modal" class="hidden">
                    <button onclick="generateInvite({{ $conversation->id }})"
                            class="w-full text-sm bg-emerald-500 hover:bg-emerald-600 text-white font-medium py-2.5 rounded-xl transition-colors mt-1">Generate Link</button>
                    <p id="invite-link" class="text-xs text-gray-500 mt-2 break-all bg-slate-50 border border-slate-100 rounded-xl px-3 py-2 hidden"></p>
                </div>
            </div>
        </div>
        @endif
        @endif

    </div>
</div>

<script>
async function generateInvite(conversationId) {
    const csrf = document.querySelector('meta[name=csrf-token]').content;
    const res = await fetch(`/groups/${conversationId}/invite`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
    });
    const data = await res.json();
    if (data.link) {
        const el = document.getElementById('invite-link');
        el.textContent = data.link;
        el.classList.remove('hidden');
        navigator.clipboard?.writeText(data.link);
    }
}
</script>
