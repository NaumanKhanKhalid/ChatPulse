<nav id="rail" x-data>
    {{-- Logo --}}
    <div class="rail-logo">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
            <path d="M5 9.5C5 6.46 7.46 4 10.5 4h3C16.54 4 19 6.46 19 9.5S16.54 15 13.5 15H9l-3.2 2.9c-.5.46-1.3.1-1.3-.58V9.5Z" fill="#fff"/>
            <circle cx="9.5" cy="9.5" r="1.2" fill="#10b981"/>
            <circle cx="13.5" cy="9.5" r="1.2" fill="#10b981"/>
        </svg>
    </div>

    {{-- Messages --}}
    <a href="{{ route('chat.index') }}" class="rail-btn {{ request()->routeIs('chat.*') ? 'active' : '' }}" title="Messages">
        <svg width="21" height="21" viewBox="0 0 24 24" fill="none"><path d="M4 6.5C4 5.12 5.12 4 6.5 4h11C18.88 4 20 5.12 20 6.5v7c0 1.38-1.12 2.5-2.5 2.5H10l-3.6 3a1 1 0 0 1-1.65-.77V6.5Z" stroke="currentColor" stroke-width="1.8"/></svg>
        @php $unread = 0; try { $unread = auth()->user()->conversations->sum(fn($c) => $c->getUnreadCountFor(auth()->user())); } catch(\Exception $e) {} @endphp
        @if($unread > 0)
        <span class="rb-badge">{{ $unread > 9 ? '9+' : $unread }}</span>
        @endif
    </a>

    {{-- Calls --}}
    <a href="{{ route('calls.index') }}" class="rail-btn {{ request()->routeIs('calls.index') ? 'active' : '' }}" title="Calls">
        <svg width="21" height="21" viewBox="0 0 24 24" fill="none"><path d="M6.2 4.5 8 4l1.6 3.4-1.5 1.3a11 11 0 0 0 4.7 4.7l1.3-1.5L17.5 15l-.5 1.8c-.2.7-.9 1.1-1.6 1A14 14 0 0 1 4.2 6.6c-.1-.7.3-1.4 1-1.6Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/></svg>
        @php $missedCalls = 0; try { $missedCalls = auth()->user()->calls()->where('status','missed')->whereDoesntHave('participants',fn($q)=>$q->where('user_id',auth()->id()))->count(); } catch(\Exception $e) {} @endphp
        @if($missedCalls > 0)
        <span class="rb-badge">{{ $missedCalls > 9 ? '9+' : $missedCalls }}</span>
        @endif
    </a>

    <div class="rail-div"></div>

    @if(auth()->user()->isAdmin())
    <a href="{{ route('admin.dashboard') }}" class="rail-btn {{ request()->routeIs('admin.*') ? 'active' : '' }}" title="Admin">
        <svg width="21" height="21" viewBox="0 0 24 24" fill="none"><path d="M12 3.5 5 6v5c0 4.5 3 8 7 9.5 4-1.5 7-5 7-9.5V6l-7-2.5Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/></svg>
    </a>
    @endif

    @if(!auth()->user()->is_guest)
    <a href="{{ route('settings.index') }}" class="rail-btn {{ request()->routeIs('settings.*') ? 'active' : '' }}" title="Settings">
        <svg width="21" height="21" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.8"/><path d="M12 3.5v2M12 18.5v2M5.5 5.5l1.4 1.4M17.1 17.1l1.4 1.4M3.5 12h2M18.5 12h2M5.5 18.5l1.4-1.4M17.1 6.9l1.4-1.4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
    </a>
    @endif

    <div class="rail-spacer"></div>

    {{-- Dark toggle --}}
    <button @click="toggleDark()" class="rail-btn" title="Toggle theme">
        <svg x-show="!darkMode" width="20" height="20" viewBox="0 0 24 24" fill="none"><path d="M21 13A9 9 0 1 1 11 3a7 7 0 0 0 10 10Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/></svg>
        <svg x-show="darkMode" width="20" height="20" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="5" stroke="currentColor" stroke-width="1.8"/><path d="M12 2v2M12 20v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M2 12h2M20 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
    </button>

    {{-- Avatar + logout popup --}}
    <div class="rail-ava" style="margin-bottom:4px;position:relative;" x-data="{ open: false }" @click.away="open = false">
        @php $u = auth()->user(); $initials = collect(explode(' ', $u->name))->map(fn($w) => strtoupper(substr($w,0,1)))->take(2)->join(''); @endphp
        <button class="rail-btn" @click="open = !open" title="{{ $u->name }}">
            @if($u->avatar_url)
            <img src="{{ $u->avatar_url }}" alt="{{ $u->name }}" style="width:34px;height:34px;border-radius:50%;object-fit:cover;">
            @else
            <div class="avatar" style="width:34px;height:34px;background:linear-gradient(135deg,#10b981,#059669);font-size:13px;">{{ $initials }}</div>
            @endif
        </button>
        <span class="pres" style="background:var(--online);"></span>

        {{-- Popup menu --}}
        <div x-show="open" x-transition style="display:none;position:absolute;bottom:0;left:52px;min-width:200px;background:var(--card);border:1px solid var(--line);border-radius:14px;padding:6px;box-shadow:0 16px 40px -12px rgba(0,0,0,.35);z-index:200;">
            <div style="padding:10px 12px 8px;border-bottom:1px solid var(--line2);margin-bottom:4px;">
                <p style="font-size:13.5px;font-weight:800;color:var(--text);margin:0;">{{ $u->name }}</p>
                <p style="font-size:12px;color:var(--text3);margin:2px 0 0;">{{ $u->email }}</p>
            </div>
            @if(!$u->is_guest)
            <a href="{{ route('settings.index') }}" style="display:flex;align-items:center;gap:10px;padding:9px 11px;border-radius:9px;font-size:13.5px;font-weight:600;color:var(--text2);text-decoration:none;" onmouseover="this.style.background='var(--hover)'" onmouseout="this.style.background=''">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.8"/><path d="M12 3.5v2M12 18.5v2M5.5 5.5l1.4 1.4M17.1 17.1l1.4 1.4M3.5 12h2M18.5 12h2M5.5 18.5l1.4-1.4M17.1 6.9l1.4-1.4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                Settings
            </a>
            @endif
            <div style="height:1px;background:var(--line);margin:4px 0;"></div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" style="display:flex;align-items:center;gap:10px;width:100%;padding:9px 11px;border-radius:9px;font-size:13.5px;font-weight:600;color:#ef4444;background:none;border:none;cursor:pointer;font-family:inherit;text-align:left;" onmouseover="this.style.background='rgba(239,68,68,.08)'" onmouseout="this.style.background=''">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    Sign out
                </button>
            </form>
        </div>
    </div>
</nav>
