<div class="w-16 flex-shrink-0 flex flex-col items-center py-4 gap-1 h-full" style="background-color:#111827;" x-data>
    {{-- Logo --}}
    <div class="w-10 h-10 rounded-xl flex items-center justify-center mb-4" style="background-color:#10b981;">
        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
            <path d="M13 10V3L4 14h7v7l9-11h-7z"/>
        </svg>
    </div>

    {{-- Chat --}}
    <a href="{{ route('chat.index') }}" title="Messages"
       class="relative w-10 h-10 rounded-xl flex items-center justify-center transition-colors {{ request()->routeIs('chat.*') ? 'text-white' : 'text-gray-500 hover:text-white hover:bg-white/10' }}"
       @if(request()->routeIs('chat.*')) style="background-color:#10b981;" @endif>
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
        </svg>
        @php $unread = auth()->user()->conversations->sum(fn($c) => $c->getUnreadCountFor(auth()->user())) @endphp
        @if($unread > 0)
        <span class="absolute -top-1 -right-1 w-4 h-4 bg-red-500 text-white text-[10px] rounded-full flex items-center justify-center font-bold leading-none">{{ $unread > 9 ? '9+' : $unread }}</span>
        @endif
    </a>

    {{-- Groups --}}
    <a href="{{ route('groups.explore') }}" title="Groups"
       class="w-10 h-10 rounded-xl flex items-center justify-center transition-colors {{ request()->routeIs('groups.*') ? 'text-white' : 'text-gray-500 hover:text-white hover:bg-white/10' }}"
       @if(request()->routeIs('groups.*')) style="background-color:#10b981;" @endif>
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
        </svg>
    </a>

    {{-- People --}}
    <a href="{{ route('people.index') }}" title="People"
       class="w-10 h-10 rounded-xl flex items-center justify-center transition-colors {{ request()->routeIs('people.*') ? 'text-white' : 'text-gray-500 hover:text-white hover:bg-white/10' }}"
       @if(request()->routeIs('people.*')) style="background-color:#10b981;" @endif>
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
        </svg>
    </a>

    {{-- Notifications --}}
    <a href="{{ route('notifications.index') }}" title="Notifications"
       class="relative w-10 h-10 rounded-xl flex items-center justify-center transition-colors {{ request()->routeIs('notifications.*') ? 'text-white' : 'text-gray-500 hover:text-white hover:bg-white/10' }}"
       @if(request()->routeIs('notifications.*')) style="background-color:#10b981;" @endif>
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
        </svg>
        @php $notifCount = auth()->user()->unreadNotificationsCount() @endphp
        @if($notifCount > 0)
        <span class="absolute -top-1 -right-1 w-4 h-4 bg-red-500 text-white text-[10px] rounded-full flex items-center justify-center font-bold leading-none">{{ $notifCount > 9 ? '9+' : $notifCount }}</span>
        @endif
    </a>

    {{-- Bookmarks --}}
    <a href="{{ route('bookmarks.index') }}" title="Bookmarks"
       class="w-10 h-10 rounded-xl flex items-center justify-center transition-colors {{ request()->routeIs('bookmarks.*') ? 'text-white' : 'text-gray-500 hover:text-white hover:bg-white/10' }}"
       @if(request()->routeIs('bookmarks.*')) style="background-color:#10b981;" @endif>
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
        </svg>
    </a>

    <div class="w-8 my-2 border-t border-white/10"></div>

    {{-- Admin --}}
    @if(auth()->user()->isAdmin())
    <a href="{{ route('admin.dashboard') }}" title="Admin"
       class="w-10 h-10 rounded-xl flex items-center justify-center transition-colors {{ request()->routeIs('admin.*') ? 'text-white' : 'text-gray-500 hover:text-white hover:bg-white/10' }}"
       @if(request()->routeIs('admin.*')) style="background-color:#10b981;" @endif>
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
        </svg>
    </a>
    @endif

    {{-- Settings --}}
    @if(!auth()->user()->is_guest)
    <a href="{{ route('settings.index') }}" title="Settings"
       class="w-10 h-10 rounded-xl flex items-center justify-center transition-colors {{ request()->routeIs('settings.*') ? 'text-white' : 'text-gray-500 hover:text-white hover:bg-white/10' }}"
       @if(request()->routeIs('settings.*')) style="background-color:#10b981;" @endif>
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
        </svg>
    </a>
    @endif

    {{-- Spacer --}}
    <div class="flex-1"></div>

    {{-- Dark mode toggle --}}
    <button @click="toggleDark()" title="Toggle Dark Mode"
            class="w-10 h-10 rounded-xl flex items-center justify-center text-gray-500 hover:text-white hover:bg-white/10 transition-colors mb-2">
        <svg x-show="!darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
        </svg>
        <svg x-show="darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
        </svg>
    </button>

    {{-- Own avatar + status --}}
    <div class="relative mb-2" x-data="statusPicker({{ json_encode(['type' => auth()->user()->status_type, 'message' => auth()->user()->status_message, 'emoji' => auth()->user()->status_emoji, 'clears_at' => null]) }})">
        <button @click="open = !open" class="relative block">
            <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}" class="w-9 h-9 rounded-full object-cover ring-2 ring-transparent hover:ring-emerald-500 transition-all">
            <span class="absolute bottom-0 right-0 w-2.5 h-2.5 rounded-full border-2 {{ auth()->user()->status_color }}" style="border-color:#111827;"></span>
        </button>

        {{-- Status picker modal --}}
        <div x-show="open" @click.away="open = false" x-transition
             class="absolute bottom-14 left-14 w-72 bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border border-slate-100 dark:border-gray-700 p-4 z-50" style="display:none">
            <h4 class="font-semibold text-sm text-gray-900 dark:text-white mb-3">Set Status</h4>

            <div class="grid grid-cols-3 gap-2 mb-3">
                <button @click="statusType = 'available'" :class="statusType === 'available' ? 'border-emerald-400 bg-emerald-50 text-emerald-700' : 'border-slate-200 text-gray-600 hover:border-slate-300'" class="border rounded-xl p-2 text-xs font-medium text-center transition-colors">
                    <span class="block w-2 h-2 rounded-full bg-emerald-500 mx-auto mb-1"></span>Available
                </button>
                <button @click="statusType = 'busy'" :class="statusType === 'busy' ? 'border-red-400 bg-red-50 text-red-700' : 'border-slate-200 text-gray-600 hover:border-slate-300'" class="border rounded-xl p-2 text-xs font-medium text-center transition-colors">
                    <span class="block w-2 h-2 rounded-full bg-red-500 mx-auto mb-1"></span>Busy
                </button>
                <button @click="statusType = 'away'" :class="statusType === 'away' ? 'border-amber-400 bg-amber-50 text-amber-700' : 'border-slate-200 text-gray-600 hover:border-slate-300'" class="border rounded-xl p-2 text-xs font-medium text-center transition-colors">
                    <span class="block w-2 h-2 rounded-full bg-amber-500 mx-auto mb-1"></span>Away
                </button>
            </div>

            <div class="flex gap-2 mb-3">
                <input type="text" x-model="statusText" placeholder="What's on your mind?" maxlength="60"
                       class="flex-1 border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400 transition-colors">
                <div class="relative">
                    <button class="w-9 h-9 border border-slate-200 rounded-xl flex items-center justify-center text-lg hover:border-slate-300 transition-colors" x-text="statusEmoji || '😊'"></button>
                </div>
            </div>

            <div class="grid grid-cols-5 gap-1 mb-3">
                <template x-for="emoji in emojis.slice(0,10)" :key="emoji">
                    <button @click="statusEmoji = emoji" class="text-xl p-1.5 hover:bg-slate-100 rounded-lg transition-colors" x-text="emoji"></button>
                </template>
            </div>

            <select x-model="clearAfter" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm mb-3 focus:outline-none focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400 transition-colors bg-white dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                <option value="">Don't clear</option>
                <option value="1hour">Clear after 1 hour</option>
                <option value="4hours">Clear after 4 hours</option>
                <option value="today">Clear today</option>
                <option value="week">Clear this week</option>
            </select>

            <button @click="save()" class="w-full btn-primary text-sm py-2 rounded-xl font-medium">Save Status</button>
        </div>
    </div>
</div>
