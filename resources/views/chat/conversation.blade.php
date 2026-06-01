@extends('layouts.app')
@section('title', $conversation->getDisplayName(auth()->user()))

@section('left-panel')
@php $conversations = app(\App\Services\ConversationService::class)->getUserConversations(auth()->user()) @endphp
<div class="flex flex-col h-full bg-white dark:bg-gray-800" x-data="{ search: '' }">
    {{-- Header --}}
    <div class="px-4 pt-5 pb-3 border-b border-slate-100">
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-base font-semibold text-gray-900" style="font-family: 'Inter', sans-serif;">Messages</h2>
            <a href="{{ route('groups.create') }}"
               class="w-7 h-7 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl flex items-center justify-center transition-colors shadow-sm"
               title="New Group">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                </svg>
            </a>
        </div>
        {{-- Search --}}
        <div class="relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input type="text" x-model="search" placeholder="Search..."
                   class="w-full pl-9 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-300 transition-colors"
                   style="font-family: 'Inter', sans-serif;">
        </div>
    </div>

    {{-- Conversation list --}}
    <div class="flex-1 overflow-y-auto py-1">
        @foreach($conversations as $conv)
        @php
            $cName = $conv->getDisplayName(auth()->user());
            $cAvatar = $conv->getAvatarUrl(auth()->user());
            $cUnread = $conv->getUnreadCountFor(auth()->user());
            $cOther = $conv->isDirect() ? $conv->getOtherUser(auth()->user()) : null;
            $isActive = $conv->id === $conversation->id;
        @endphp
        <a href="{{ route('chat.conversation', $conv) }}"
           x-show="!search || '{{ strtolower($cName) }}'.includes(search.toLowerCase())"
           class="flex items-center gap-3 px-4 py-3 cursor-pointer transition-all border-l-2 {{ $isActive ? 'border-emerald-500 bg-emerald-50/40 dark:bg-emerald-900/20' : 'border-transparent hover:bg-slate-50 dark:hover:bg-gray-700/50' }}"
           style="font-family: 'Inter', sans-serif;">
            {{-- Avatar with online dot --}}
            <div class="relative flex-shrink-0">
                <img src="{{ $cAvatar }}" alt="{{ $cName }}" class="w-10 h-10 rounded-full object-cover">
                @if($cOther)
                <span class="absolute -bottom-0.5 -right-0.5 w-3 h-3 rounded-full border-2 border-white {{ $cOther->is_online ? 'bg-emerald-500' : 'bg-gray-300' }}"></span>
                @endif
            </div>
            {{-- Info --}}
            <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between gap-1">
                    <span class="font-medium text-sm text-gray-900 truncate">{{ $cName }}</span>
                    @if($conv->lastMessage)
                    <span class="text-xs text-gray-400 flex-shrink-0">{{ $conv->lastMessage->created_at->format('H:i') }}</span>
                    @endif
                </div>
                <div class="flex items-center justify-between mt-0.5 gap-1">
                    <p class="text-xs text-gray-500 truncate">{{ $conv->lastMessage?->body ?? 'No messages yet' }}</p>
                    @if($cUnread > 0)
                    <span class="ml-1 bg-emerald-500 text-white text-xs rounded-full px-1.5 py-0.5 flex-shrink-0 font-medium leading-none">{{ $cUnread }}</span>
                    @endif
                </div>
            </div>
        </a>
        @endforeach
    </div>
</div>
@endsection

@section('content')
@php
    $currentUser = auth()->user();
    $convName = $conversation->getDisplayName($currentUser);
    $convAvatar = $conversation->getAvatarUrl($currentUser);
    $otherUser = $conversation->isDirect() ? $conversation->getOtherUser($currentUser) : null;
    $participant = $conversation->participants()->where('user_id', $currentUser->id)->first();
    $isAdmin = $conversation->isGroup() && $participant?->role === 'admin';
@endphp

<script>
    window.__chatInit = {
        conversationId: {{ $conversation->id }},
        currentUserId: {{ auth()->id() }},
        messages: {!! json_encode($messages, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}
    };
</script>
<div class="flex flex-col h-full bg-white dark:bg-gray-900" x-data="chatConversation(window.__chatInit.conversationId, window.__chatInit.currentUserId, window.__chatInit.messages)" style="font-family: 'Inter', sans-serif;">

    {{-- Chat header --}}
    <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100 dark:border-gray-700 bg-white dark:bg-gray-800 flex-shrink-0">
        <div class="flex items-center gap-3">
            <div class="relative">
                <img src="{{ $convAvatar }}" alt="{{ $convName }}" class="w-9 h-9 rounded-full object-cover">
                @if($otherUser)
                <span x-bind:class="{{ $otherUser->is_online ? 'true' : 'false' }} ? 'bg-emerald-500' : 'bg-gray-300'"
                      class="absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 rounded-full border-2 border-white"></span>
                @endif
            </div>
            <div>
                <h3 class="font-semibold text-sm text-gray-900 leading-tight">{{ $convName }}</h3>
                @if($otherUser && $otherUser->status_message)
                <p class="text-xs text-gray-400 leading-tight mt-0.5">{{ $otherUser->status_emoji }} {{ $otherUser->status_message }}</p>
                @elseif($conversation->isGroup())
                <p class="text-xs text-gray-400 leading-tight mt-0.5">{{ $conversation->participants()->count() }} members</p>
                @else
                <p class="text-xs text-gray-400 leading-tight mt-0.5"
                   x-text="typingUsers.length ? typingUsers.join(', ') + ' is typing...' : ({{ $otherUser?->is_online ? 'true' : 'false' }} ? 'Online' : 'Offline')"></p>
                @endif
            </div>
        </div>

        <div class="flex items-center gap-1">
            {{-- Call buttons (DM only) --}}
            @if($conversation->isDirect() && $otherUser)
            <button @click="startCall({{ $otherUser->id }}, 'audio')" title="Voice Call"
                    class="w-8 h-8 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-slate-100 flex items-center justify-center transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                </svg>
            </button>
            <button @click="startCall({{ $otherUser->id }}, 'video')" title="Video Call"
                    class="w-8 h-8 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-slate-100 flex items-center justify-center transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.069A1 1 0 0121 8.87v6.26a1 1 0 01-1.447.894L15 14M3 8a2 2 0 012-2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V8z"/>
                </svg>
            </button>
            @endif

            {{-- Right panel toggle --}}
            <button onclick="document.getElementById('right-panel').classList.toggle('hidden')"
                    class="w-8 h-8 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-slate-100 flex items-center justify-center transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </button>

            {{-- Options menu --}}
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open"
                        class="w-8 h-8 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-slate-100 flex items-center justify-center transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/>
                    </svg>
                </button>
                <div x-show="open" @click.away="open = false" x-transition
                     class="absolute right-0 top-10 w-48 bg-white rounded-xl shadow-xl border border-slate-100 py-1 z-20" style="display:none">
                    <button @click="showExportModal = true; open = false"
                            class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-slate-50 transition-colors">Export Chat</button>
                    @if($conversation->isGroup() && $isAdmin)
                    <button @click="showGroupSettings = true; open = false"
                            class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-slate-50 transition-colors">Group Settings</button>
                    @endif
                    @if($conversation->isGroup())
                    <form method="POST" action="{{ route('groups.leave', $conversation) }}" onsubmit="return confirm('Leave this group?')">
                        @csrf
                        <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-500 hover:bg-slate-50 transition-colors">Leave Group</button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Messages area --}}
    <div class="flex-1 overflow-y-auto px-4 py-4 bg-slate-50 dark:bg-gray-900" x-ref="messagesContainer" id="messages-container">

        <div class="space-y-1">
            <template x-for="(message, index) in messages" :key="message.id">
                <div class="group relative py-0.5">

                    {{-- System message --}}
                    <div x-show="message.type === 'system'" class="flex justify-center py-1">
                        <span class="text-xs text-gray-400 italic bg-slate-100 dark:bg-gray-800 rounded-full px-3 py-1" x-text="message.body"></span>
                    </div>

                    {{-- Normal message --}}
                    <div x-show="message.type !== 'system'"
                         class="flex items-end gap-2"
                         :class="message.user_id == currentUserId ? 'flex-row-reverse' : 'flex-row'">

                        {{-- Avatar (received only) --}}
                        <div class="flex-shrink-0 w-8 h-8 self-end"
                             :class="message.user_id == currentUserId ? 'hidden' : ''">
                            <img x-show="!isSameUserAsPrev(index)"
                                 :src="message.user?.avatar_url"
                                 :alt="message.user?.name"
                                 class="w-8 h-8 rounded-full object-cover">
                        </div>

                        {{-- Bubble column --}}
                        <div class="flex flex-col max-w-[70%]"
                             :class="message.user_id == currentUserId ? 'items-end' : 'items-start'">

                            {{-- Name row (received, first in group) --}}
                            <div x-show="!isSameUserAsPrev(index) && message.user_id != currentUserId"
                                 class="flex items-center gap-1.5 mb-1 px-1">
                                <span class="text-xs font-semibold text-gray-700 dark:text-gray-300" x-text="message.user?.name"></span>
                                <span x-show="message.user?.is_guest" class="text-xs bg-yellow-100 text-yellow-700 px-1.5 py-0.5 rounded font-medium">Guest</span>
                            </div>

                            {{-- Reply reference --}}
                            <div x-show="message.parent"
                                 class="flex items-center gap-1.5 mb-1.5 px-3 py-1.5 rounded-xl border-l-2 border-emerald-400 text-xs"
                                 :class="message.user_id == currentUserId ? 'bg-emerald-700/30 text-emerald-100' : 'bg-white dark:bg-gray-700 text-gray-500 dark:text-gray-400'">
                                <span class="font-semibold" x-text="message.parent?.user?.name"></span>:
                                <span class="truncate" x-text="(message.parent?.body || '').slice(0, 60)"></span>
                            </div>

                            {{-- Forwarded label --}}
                            <div x-show="message.type === 'forwarded'"
                                 class="flex items-center gap-1 text-xs text-gray-400 mb-1 px-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                                Forwarded
                            </div>

                            {{-- Bubble --}}
                            <div class="px-3.5 py-2.5 max-w-full rounded-2xl"
                                 :class="message.user_id == currentUserId ? 'rounded-br-sm' : 'rounded-bl-sm'"
                                 :style="message.user_id == currentUserId
                                     ? 'background:#10b981; color:#fff;'
                                     : 'background:#ffffff; color:#1f2937; border:1px solid #e2e8f0;'">

                                {{-- Message text --}}
                                <p x-show="!editingMessageId || editingMessageId !== message.id"
                                   class="text-sm leading-relaxed whitespace-pre-wrap break-words"
                                   x-text="message.body"></p>

                                {{-- Edit input --}}
                                <div x-show="editingMessageId === message.id" class="flex gap-2 min-w-[200px]">
                                    <input type="text" x-model="editBody"
                                           class="flex-1 border border-white/30 rounded-lg px-3 py-1.5 text-sm focus:outline-none bg-white/20 text-white placeholder-white/60"
                                           @keydown.enter="saveEdit(message)"
                                           @keydown.escape="editingMessageId = null">
                                    <button @click="saveEdit(message)" class="text-xs bg-white/20 hover:bg-white/30 text-white px-3 py-1.5 rounded-lg transition-colors">Save</button>
                                    <button @click="editingMessageId = null" class="text-xs text-white/60 hover:text-white px-2 transition-colors">✕</button>
                                </div>

                                {{-- Attachments --}}
                                <template x-for="att in (message.attachments || [])" :key="att.id">
                                    <div class="mt-2">
                                        <a x-show="att.file_type && att.file_type.startsWith('image/')"
                                           :href="att.url" target="_blank">
                                            <img :src="att.url" class="max-w-xs max-h-48 rounded-xl object-cover cursor-pointer hover:opacity-90 transition-opacity mt-1">
                                        </a>
                                        <a x-show="!att.file_type || !att.file_type.startsWith('image/')"
                                           :href="att.url" target="_blank"
                                           class="flex items-center gap-2 bg-black/10 rounded-xl px-3 py-2 max-w-xs hover:bg-black/20 transition-colors mt-1">
                                            <svg class="w-5 h-5 flex-shrink-0 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                            <div class="min-w-0">
                                                <p class="text-sm font-medium truncate" x-text="att.original_name"></p>
                                                <p class="text-xs opacity-70" x-text="att.formatted_size"></p>
                                            </div>
                                        </a>
                                    </div>
                                </template>
                            </div>

                            {{-- Timestamp + edited --}}
                            <div class="flex items-center gap-1 mt-0.5 px-1">
                                <span class="text-xs text-gray-400" x-text="formatTime(message.created_at)"></span>
                                <span x-show="message.is_edited" class="text-xs text-gray-400 italic">(edited)</span>
                            </div>

                            {{-- Reactions --}}
                            <div x-show="(message.reactions || []).length > 0" class="flex flex-wrap gap-1 mt-1 px-1">
                                <template x-for="reaction in (message.reactions || [])" :key="reaction.emoji">
                                    <button @click="toggleReaction(message, reaction.emoji)"
                                            class="flex items-center gap-1 bg-white dark:bg-gray-700 border border-slate-100 dark:border-gray-600 hover:border-emerald-300 rounded-full px-2 py-0.5 text-xs shadow-sm transition-colors"
                                            :title="(reaction.users || []).join(', ')">
                                        <span x-text="reaction.emoji"></span>
                                        <span class="text-gray-600 dark:text-gray-300" x-text="reaction.count"></span>
                                    </button>
                                </template>
                            </div>
                        </div>

                        {{-- Hover actions --}}
                        <div class="hidden group-hover:flex items-center gap-1 self-center flex-shrink-0 opacity-0 group-hover:opacity-100 transition-opacity">
                            <div class="relative" x-data="{ emojiOpen: false }">
                                <button @click="emojiOpen = !emojiOpen" title="React"
                                        class="w-7 h-7 rounded-lg bg-white dark:bg-gray-700 border border-slate-200 dark:border-gray-600 shadow-sm flex items-center justify-center text-gray-500 hover:text-emerald-600 transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </button>
                                <div x-show="emojiOpen" @click.away="emojiOpen = false" x-transition
                                     class="absolute bottom-9 bg-white dark:bg-gray-800 border border-slate-100 dark:border-gray-700 rounded-xl p-2 shadow-xl z-30 flex flex-wrap gap-1 w-44"
                                     :class="message.user_id == currentUserId ? 'right-0' : 'left-0'"
                                     style="display:none">
                                    <template x-for="emoji in ['👍','❤️','😂','😮','😢','🔥','✅','👏','🎉','💯']">
                                        <button @click="toggleReaction(message, emoji); emojiOpen = false"
                                                class="text-lg p-1 hover:bg-slate-50 dark:hover:bg-gray-700 rounded-lg" x-text="emoji"></button>
                                    </template>
                                </div>
                            </div>
                            <button @click="replyTo = message" title="Reply"
                                    class="w-7 h-7 rounded-lg bg-white dark:bg-gray-700 border border-slate-200 dark:border-gray-600 shadow-sm flex items-center justify-center text-gray-500 hover:text-emerald-600 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                            </button>
                            <button x-show="message.user_id == currentUserId" @click="startEdit(message)" title="Edit"
                                    class="w-7 h-7 rounded-lg bg-white dark:bg-gray-700 border border-slate-200 dark:border-gray-600 shadow-sm flex items-center justify-center text-gray-500 hover:text-emerald-600 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </button>
                            <button @click="toggleBookmark(message)" title="Bookmark"
                                    class="w-7 h-7 rounded-lg bg-white dark:bg-gray-700 border border-slate-200 dark:border-gray-600 shadow-sm flex items-center justify-center text-gray-500 hover:text-emerald-600 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/></svg>
                            </button>
                            <button x-show="message.user_id == currentUserId || {{ auth()->user()->isAdmin() ? 'true' : 'false' }}"
                                    @click="deleteMessage(message)" title="Delete"
                                    class="w-7 h-7 rounded-lg bg-white dark:bg-gray-700 border border-slate-200 dark:border-gray-600 shadow-sm flex items-center justify-center text-gray-500 hover:text-red-500 hover:border-red-200 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </div>

                </div>
            </template>
        </div>

        {{-- Typing indicator --}}
        <div x-show="typingUsers.length > 0" class="flex items-center gap-2 px-2 pb-2 pt-3">
            <div class="flex gap-1 items-center">
                <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay:0ms"></span>
                <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay:150ms"></span>
                <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay:300ms"></span>
            </div>
            <span class="text-xs text-gray-400" x-text="typingUsers.join(', ') + ' is typing...'"></span>
        </div>
    </div>

    {{-- Reply preview --}}
    <div x-show="replyTo" class="px-4 py-2.5 bg-emerald-50/60 border-t border-emerald-100 flex items-center justify-between flex-shrink-0">
        <div class="flex items-center gap-2.5 text-sm">
            <div class="w-0.5 h-8 bg-emerald-500 rounded-full flex-shrink-0"></div>
            <div>
                <p class="text-emerald-600 font-semibold text-xs" x-text="'Replying to ' + replyTo?.user?.name"></p>
                <p class="text-gray-500 text-xs truncate max-w-xs mt-0.5" x-text="(replyTo?.body || '').slice(0, 80)"></p>
            </div>
        </div>
        <button @click="replyTo = null" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg hover:bg-white transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    {{-- Message input --}}
    <div class="px-4 py-3 border-t border-slate-100 dark:border-gray-700 bg-white dark:bg-gray-800 flex-shrink-0">
        {{-- Scheduled indicator --}}
        <div x-show="scheduledAt"
             class="text-xs text-yellow-700 bg-yellow-50 border border-yellow-200 rounded-xl px-3 py-1.5 mb-2 flex items-center justify-between">
            <span>Scheduled: <span class="font-medium" x-text="scheduledAt"></span></span>
            <button @click="scheduledAt = null" class="text-yellow-400 hover:text-yellow-600 ml-2">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <div class="flex items-end gap-2">
            {{-- Attachment button --}}
            <label class="w-9 h-9 flex-shrink-0 rounded-xl text-gray-400 hover:text-gray-600 hover:bg-slate-100 flex items-center justify-center cursor-pointer transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                </svg>
                <input type="file" multiple class="hidden" @change="handleFileSelect($event)" accept="image/*,video/*,.pdf,.doc,.docx,.xls,.xlsx,.zip">
            </label>

            {{-- Input area --}}
            <div class="flex-1 bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 focus-within:border-emerald-300 focus-within:ring-2 focus-within:ring-emerald-500/20 transition-all">
                {{-- File previews --}}
                <div x-show="selectedFiles.length > 0" class="flex flex-wrap gap-2 mb-2">
                    <template x-for="(file, i) in selectedFiles" :key="i">
                        <div class="flex items-center gap-1 bg-white border border-slate-200 rounded-lg px-2 py-1 text-xs text-gray-700">
                            <span x-text="file.name.slice(0, 20)"></span>
                            <button @click="removeFile(i)" class="text-gray-400 hover:text-gray-600 ml-0.5 transition-colors">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </template>
                </div>
                <textarea x-model="newMessage"
                          @keydown.enter.exact.prevent="sendMessage()"
                          @input="handleTyping()"
                          placeholder="Type your message..."
                          rows="1"
                          class="w-full bg-transparent text-sm text-gray-900 placeholder-gray-400 resize-none focus:outline-none leading-relaxed"
                          style="max-height: 120px; overflow-y: auto; font-family: 'Inter', sans-serif;"></textarea>
            </div>

            {{-- Schedule button --}}
            <div class="relative" x-data="scheduledPicker()">
                <button @click="open = !open"
                        class="w-9 h-9 flex-shrink-0 rounded-xl text-gray-400 hover:text-gray-600 hover:bg-slate-100 flex items-center justify-center transition-colors"
                        title="Schedule">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </button>
                <div x-show="open" @click.away="open = false" x-transition
                     class="absolute bottom-12 right-0 bg-white border border-slate-100 rounded-2xl shadow-xl p-4 w-64 z-20" style="display:none">
                    <p class="text-sm font-semibold text-gray-800 mb-3">Schedule Message</p>
                    <input type="datetime-local" x-model="scheduledAt" :min="minDate"
                           class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-300 mb-3 transition-colors">
                    <button @click="applySchedule()"
                            class="w-full bg-emerald-500 hover:bg-emerald-600 text-white text-sm py-2 rounded-xl transition-colors font-medium">Set Schedule</button>
                </div>
            </div>

            {{-- Send button --}}
            <button @click="sendMessage()" :disabled="!newMessage.trim() && selectedFiles.length === 0"
                    class="w-10 h-10 flex-shrink-0 rounded-xl bg-emerald-500 hover:bg-emerald-600 disabled:opacity-40 disabled:cursor-not-allowed text-white flex items-center justify-center transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                </svg>
            </button>
        </div>
    </div>

    {{-- Forward modal --}}
    <div x-show="forwardModalOpen" x-transition class="fixed inset-0 bg-black/40 backdrop-blur-sm z-50 flex items-center justify-center p-4" style="display:none">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
            <div class="p-5 border-b border-slate-100">
                <h3 class="font-semibold text-gray-900">Forward Message</h3>
            </div>
            <div class="p-4 max-h-64 overflow-y-auto space-y-1">
                @foreach($conversations ?? [] as $fc)
                <label class="flex items-center gap-3 p-2.5 hover:bg-slate-50 rounded-xl cursor-pointer transition-colors">
                    <input type="checkbox" :value="{{ $fc->id }}" x-model="forwardTargets"
                           class="rounded text-emerald-500 border-slate-300 focus:ring-emerald-500/30">
                    <img src="{{ $fc->getAvatarUrl(auth()->user()) }}" class="w-8 h-8 rounded-full object-cover">
                    <span class="text-sm text-gray-800 font-medium">{{ $fc->getDisplayName(auth()->user()) }}</span>
                </label>
                @endforeach
            </div>
            <div class="p-4 border-t border-slate-100 flex gap-2 justify-end">
                <button @click="forwardModalOpen = false"
                        class="px-4 py-2 text-sm text-gray-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors font-medium">Cancel</button>
                <button @click="forwardMessage()" :disabled="forwardTargets.length === 0"
                        class="px-4 py-2 text-sm bg-emerald-500 hover:bg-emerald-600 disabled:opacity-40 text-white rounded-xl transition-colors font-medium">Forward</button>
            </div>
        </div>
    </div>

    {{-- Export modal --}}
    <div x-show="showExportModal" x-transition class="fixed inset-0 bg-black/40 backdrop-blur-sm z-50 flex items-center justify-center p-4" style="display:none">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6">
            <h3 class="font-semibold text-gray-900 mb-5">Export Chat</h3>
            <div class="space-y-4 mb-5">
                <div>
                    <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5 block">Format</label>
                    <select x-model="exportFormat"
                            class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-300 text-gray-800 transition-colors">
                        <option value="csv">CSV</option>
                        <option value="pdf">PDF</option>
                    </select>
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5 block">From (optional)</label>
                    <input type="date" x-model="exportFrom"
                           class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-300 text-gray-800 transition-colors">
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5 block">To (optional)</label>
                    <input type="date" x-model="exportTo"
                           class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-300 text-gray-800 transition-colors">
                </div>
            </div>
            <div class="flex gap-2 justify-end">
                <button @click="showExportModal = false"
                        class="px-4 py-2 text-sm text-gray-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors font-medium">Cancel</button>
                <button @click="exportChat()"
                        class="px-4 py-2 text-sm bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl transition-colors font-medium">Export</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('right-panel')
@include('chat.right-panel')
@endsection
