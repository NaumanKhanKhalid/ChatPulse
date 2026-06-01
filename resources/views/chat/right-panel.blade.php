@php
    $currentUser = auth()->user();
    $otherUser = $conversation->isDirect() ? $conversation->getOtherUser($currentUser) : null;
    $participant = $conversation->participants()->where('user_id', $currentUser->id)->first();
    $isAdmin = $conversation->isGroup() && $participant?->role === 'admin';
@endphp

<div class="flex flex-col h-full">
    {{-- Header info --}}
    <div class="p-4 border-b border-gray-100 dark:border-gray-700 text-center">
        <img src="{{ $conversation->getAvatarUrl($currentUser) }}" alt="" class="w-16 h-16 rounded-full object-cover mx-auto mb-3">
        <h3 class="font-semibold text-gray-900 dark:text-white text-sm">{{ $conversation->getDisplayName($currentUser) }}</h3>
        @if($otherUser)
        <p class="text-xs text-gray-500 mt-1">{{ $otherUser->status_emoji }} {{ $otherUser->status_message }}</p>
        <span class="inline-flex items-center gap-1 text-xs mt-1 {{ $otherUser->is_online ? 'text-green-600' : 'text-gray-400' }}">
            <span class="w-1.5 h-1.5 rounded-full {{ $otherUser->is_online ? 'bg-green-500' : 'bg-gray-400' }}"></span>
            {{ $otherUser->is_online ? 'Online' : 'Last seen ' . $otherUser->last_seen_at?->diffForHumans() }}
        </span>
        @elseif($conversation->isGroup())
        <p class="text-xs text-gray-500 mt-1">{{ $conversation->participants()->count() }} members</p>
        @endif
    </div>

    <div class="flex-1 overflow-y-auto p-4 space-y-4">
        {{-- Pinned messages --}}
        @if($pinnedMessages->count() > 0)
        <div x-data="{ open: true }">
            <button @click="open = !open" class="flex items-center justify-between w-full text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                <span>📌 Pinned ({{ $pinnedMessages->count() }})</span>
                <svg class="w-4 h-4 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div x-show="open" x-transition class="space-y-2">
                @foreach($pinnedMessages as $pin)
                <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-2.5">
                    <p class="text-xs font-medium text-yellow-800 dark:text-yellow-300">{{ $pin->message->user?->name }}</p>
                    <p class="text-xs text-gray-700 dark:text-gray-300 mt-0.5 line-clamp-2">{{ $pin->message->body }}</p>
                    @if($isAdmin)
                    <form method="POST" action="{{ route('pins.destroy', [$conversation, $pin->message]) }}" class="mt-1">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-xs text-red-500 hover:text-red-700">Unpin</button>
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
            <button @click="open = !open" class="flex items-center justify-between w-full text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                <span>Members</span>
                <svg class="w-4 h-4 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div x-show="open" x-transition class="space-y-2">
                @foreach($participants as $p)
                <div class="flex items-center gap-2">
                    <div class="relative">
                        <img src="{{ $p->user->avatar_url }}" class="w-7 h-7 rounded-full object-cover">
                        <span class="absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 rounded-full border-2 border-white {{ $p->user->is_online ? 'bg-green-500' : 'bg-gray-400' }}"></span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-medium text-gray-800 dark:text-gray-200 truncate">{{ $p->user->name }}</p>
                        @if($p->user->status_message)
                        <p class="text-xs text-gray-400 truncate">{{ $p->user->status_emoji }} {{ $p->user->status_message }}</p>
                        @endif
                    </div>
                    @if($p->role === 'admin')
                    <span class="text-xs bg-primary/10 text-primary px-1.5 rounded font-medium">Admin</span>
                    @endif
                    @if($p->user->is_guest)
                    <span class="text-xs bg-yellow-100 text-yellow-700 px-1.5 rounded">Guest</span>
                    @endif
                </div>
                @endforeach
            </div>
        </div>

        {{-- Group admin actions --}}
        @if($isAdmin)
        <div class="border-t border-gray-100 dark:border-gray-700 pt-4 space-y-2">
            <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Admin Actions</h4>
            <button onclick="document.getElementById('invite-modal').classList.toggle('hidden')"
                    class="w-full text-sm text-primary border border-primary/30 hover:bg-primary/5 rounded-lg px-3 py-2 transition-colors text-left">
                🔗 Generate Invite Link
            </button>
            <div id="invite-modal" class="hidden">
                <button onclick="generateInvite({{ $conversation->id }})" class="w-full text-sm btn-primary py-2 mt-1">Generate Link</button>
                <p id="invite-link" class="text-xs text-gray-500 mt-2 break-all hidden"></p>
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
