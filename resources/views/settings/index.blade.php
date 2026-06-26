@extends('layouts.app')
@section('title', 'Settings')

@section('list-panel')
@php
    $u = auth()->user();
    $grad = $u->avatarGradient();
    $initials = collect(explode(' ', $u->name))->map(fn($w)=>strtoupper(substr($w,0,1)))->take(2)->join('');
    $username = $u->username ?? strtolower(str_replace(' ','_',$u->name));
@endphp
<div class="sset-sidebar">
    {{-- Profile mini card --}}
    <div class="sset-who">
        <div class="avatar" style="width:42px;height:42px;background:linear-gradient(135deg,{{ $grad[0] }},{{ $grad[1] }});font-size:16px;flex-shrink:0">{{ $initials }}</div>
        <div class="sset-who-tx">
            <span class="sset-who-name">{{ $u->name }}</span>
            <span class="sset-who-un">@{{ $username }}</span>
        </div>
    </div>

    <div class="sset-divider"></div>

    {{-- Nav --}}
    <nav class="sset-nav">
        @php
        $items = [
            ['id'=>'profile',       'label'=>'Profile',       'ic'=>'M12 12a5 5 0 1 0 0-10 5 5 0 0 0 0 10Zm-7 9v-1c0-2.2 3.13-4 7-4s7 1.8 7 4v1'],
            ['id'=>'appearance',    'label'=>'Appearance',    'ic'=>'M12 3a9 9 0 1 0 0 18A9 9 0 0 0 12 3Zm0 3.5a5.5 5.5 0 0 1 0 11V6.5Z'],
            ['id'=>'notifications', 'label'=>'Notifications', 'ic'=>'M15 17h5l-1.405-1.405A2.032 2.032 0 0 1 18 14.158V11a6.002 6.002 0 0 0-4-5.659V5a2 2 0 1 0-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 1 1-6 0v-1m6 0H9'],
            ['id'=>'privacy',       'label'=>'Privacy',       'ic'=>'M12 1 3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4Z'],
            ['id'=>'account',       'label'=>'Account',       'ic'=>'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0 1 12 2.944a11.955 11.955 0 0 1-8.618 3.04A12.02 12.02 0 0 0 3 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016Z'],
        ];
        @endphp
        @foreach($items as $item)
        <button class="sset-nav-btn" id="nav-{{ $item['id'] }}" onclick="showSec('{{ $item['id'] }}')">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="{{ $item['ic'] }}" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
            {{ $item['label'] }}
        </button>
        @endforeach
    </nav>

    <div class="sset-divider"></div>

    <form method="POST" action="{{ route('logout') }}">@csrf
        <button class="sset-nav-btn sset-logout">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M17 16l4-4m0 0-4-4m4 4H7m6 4v1a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v1" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
            Sign out
        </button>
    </form>
</div>
@endsection

@section('content')
<div class="sset-main">

    {{-- PROFILE --}}
    <section class="sset-sec" id="sec-profile">
        <div class="sset-sec-header">
            <h2>Profile</h2>
            <p>How others see you on ChatPulse</p>
        </div>

        <div class="sset-card sset-profile-hero">
            <div class="avatar" style="width:54px;height:54px;background:linear-gradient(135deg,{{ $grad[0] }},{{ $grad[1] }});font-size:20px;flex-shrink:0">{{ $initials }}</div>
            <div>
                <div class="sset-hero-name">{{ $u->name }}</div>
                <div class="sset-hero-un">@{{ $username }}</div>
                @if($u->bio)<div class="sset-hero-bio">{{ $u->bio }}</div>@endif
            </div>
        </div>

        <form id="profileForm" class="sset-form">
            <div class="sset-field">
                <label>Display Name</label>
                <input type="text" name="name" value="{{ $u->name }}" maxlength="60">
            </div>
            <div class="sset-field">
                <label>Username</label>
                <div class="sset-prefix-input">
                    <span>@</span><input type="text" name="username" value="{{ $username }}" maxlength="30">
                </div>
            </div>
            <div class="sset-field">
                <label>Bio <span id="bioCount" class="sset-count">{{ strlen($u->bio ?? '') }}/200</span></label>
                <textarea name="bio" rows="3" maxlength="200" placeholder="Tell people a little about yourself…">{{ $u->bio }}</textarea>
            </div>
            <div class="sset-row2">
                <div class="sset-field">
                    <label>Status</label>
                    <select name="status_type">
                        <option value="available" {{ ($u->status_type??'available')==='available'?'selected':'' }}>🟢 Available</option>
                        <option value="busy"      {{ ($u->status_type??'')==='busy'?'selected':'' }}>🔴 Busy</option>
                        <option value="away"      {{ ($u->status_type??'')==='away'?'selected':'' }}>🟡 Away</option>
                    </select>
                </div>
                <div class="sset-field">
                    <label>Status Message</label>
                    <input type="text" name="status_message" value="{{ $u->status_message }}" placeholder="e.g. In a meeting…" maxlength="100">
                </div>
            </div>
            <div class="sset-actions">
                <button type="submit" class="sset-btn">Save Profile</button>
                <span class="sset-ok" id="profileOk">✓ Saved</span>
            </div>
        </form>
    </section>

    {{-- APPEARANCE --}}
    <section class="sset-sec" id="sec-appearance" style="display:none">
        <div class="sset-sec-header"><h2>Appearance</h2><p>Customize how ChatPulse looks</p></div>
        <div class="sset-card">
            <div class="sset-toggle-row">
                <div><p class="sset-tl">Dark Mode</p><p class="sset-ts">Switch between light and dark theme</p></div>
                <label class="sset-switch"><input type="checkbox" id="darkTogglePage" {{ $u->dark_mode?'checked':'' }}><span class="sset-knob"></span></label>
            </div>
        </div>
        <div class="sset-card" style="margin-top:10px">
            <p class="sset-card-lbl">Chat Bubble Style</p>
            <div class="sset-choices">
                <label class="sset-choice on"><input type="radio" name="bubble" value="modern" checked><span>Modern</span></label>
                <label class="sset-choice"><input type="radio" name="bubble" value="classic"><span>Classic</span></label>
                <label class="sset-choice"><input type="radio" name="bubble" value="minimal"><span>Minimal</span></label>
            </div>
        </div>
        <div class="sset-card" style="margin-top:10px">
            <p class="sset-card-lbl">Font Size</p>
            <div class="sset-choices">
                <label class="sset-choice"><input type="radio" name="font_sz" value="sm"><span>Small</span></label>
                <label class="sset-choice on"><input type="radio" name="font_sz" value="md" checked><span>Medium</span></label>
                <label class="sset-choice"><input type="radio" name="font_sz" value="lg"><span>Large</span></label>
            </div>
        </div>
    </section>

    {{-- NOTIFICATIONS --}}
    <section class="sset-sec" id="sec-notifications" style="display:none">
        <div class="sset-sec-header"><h2>Notifications</h2><p>Control what alerts you receive</p></div>
        <form id="notifForm">
            <div class="sset-card">
                <div class="sset-toggle-row">
                    <div><p class="sset-tl">Email Notifications</p><p class="sset-ts">Receive message updates in your inbox</p></div>
                    <label class="sset-switch"><input type="checkbox" name="email_notifications" {{ $u->email_notifications?'checked':'' }}><span class="sset-knob"></span></label>
                </div>
                <div class="sset-sep"></div>
                <div class="sset-toggle-row">
                    <div><p class="sset-tl">Message Previews</p><p class="sset-ts">Show message text in notifications</p></div>
                    <label class="sset-switch"><input type="checkbox" checked><span class="sset-knob"></span></label>
                </div>
                <div class="sset-sep"></div>
                <div class="sset-toggle-row">
                    <div><p class="sset-tl">Sound Alerts</p><p class="sset-ts">Play a sound on new messages</p></div>
                    <label class="sset-switch"><input type="checkbox" checked><span class="sset-knob"></span></label>
                </div>
            </div>
            <div class="sset-card" style="margin-top:10px">
                <div class="sset-field">
                    <label>Email Digest Frequency</label>
                    <select name="email_digest">
                        <option value="never"  {{ ($u->email_digest??'never')==='never'?'selected':'' }}>Never</option>
                        <option value="daily"  {{ ($u->email_digest??'')==='daily'?'selected':'' }}>Daily summary</option>
                        <option value="weekly" {{ ($u->email_digest??'')==='weekly'?'selected':'' }}>Weekly summary</option>
                    </select>
                </div>
            </div>
            <div class="sset-actions">
                <button type="submit" class="sset-btn">Save Preferences</button>
                <span class="sset-ok" id="notifOk">✓ Saved</span>
            </div>
        </form>
    </section>

    {{-- PRIVACY --}}
    <section class="sset-sec" id="sec-privacy" style="display:none">
        <div class="sset-sec-header"><h2>Privacy</h2><p>Manage your visibility and data</p></div>
        <div class="sset-card">
            <div class="sset-toggle-row">
                <div><p class="sset-tl">Read Receipts</p><p class="sset-ts">Let others know when you've read their messages</p></div>
                <label class="sset-switch"><input type="checkbox" checked><span class="sset-knob"></span></label>
            </div>
            <div class="sset-sep"></div>
            <div class="sset-toggle-row">
                <div><p class="sset-tl">Online Status</p><p class="sset-ts">Show when you're active to others</p></div>
                <label class="sset-switch"><input type="checkbox" checked><span class="sset-knob"></span></label>
            </div>
            <div class="sset-sep"></div>
            <div class="sset-toggle-row">
                <div><p class="sset-tl">Typing Indicator</p><p class="sset-ts">Show "typing…" to others while you type</p></div>
                <label class="sset-switch"><input type="checkbox" checked><span class="sset-knob"></span></label>
            </div>
        </div>
        <div class="sset-card" style="margin-top:10px">
            <p class="sset-card-lbl">Who can message you</p>
            <div class="sset-choices" style="margin-top:10px">
                <label class="sset-choice on"><input type="radio" name="who_msg" value="everyone" checked><span>Everyone</span></label>
                <label class="sset-choice"><input type="radio" name="who_msg" value="contacts"><span>Contacts only</span></label>
            </div>
        </div>
    </section>

    {{-- ACCOUNT --}}
    <section class="sset-sec" id="sec-account" style="display:none">
        <div class="sset-sec-header"><h2>Account</h2><p>Manage credentials and security</p></div>

        <div class="sset-card">
            <p class="sset-card-lbl">Email Address</p>
            <div class="sset-email-row">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><rect x="3" y="5" width="18" height="14" rx="2" stroke="currentColor" stroke-width="1.8"/><path d="m3 7 9 6 9-6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                <span>{{ $u->email }}</span>
                @if($u->email_verified_at)
                <span class="sset-badge green">Verified</span>
                @else
                <span class="sset-badge red">Unverified</span>
                @endif
            </div>
        </div>

        <div class="sset-card" style="margin-top:10px">
            <p class="sset-card-lbl">Change Password</p>
            <form id="pwForm" class="sset-form" style="margin-top:14px">
                <div class="sset-field">
                    <label>Current Password</label>
                    <input type="password" name="current_password" placeholder="Enter current password">
                </div>
                <div class="sset-row2">
                    <div class="sset-field">
                        <label>New Password</label>
                        <input type="password" name="password" placeholder="Min 8 characters">
                    </div>
                    <div class="sset-field">
                        <label>Confirm Password</label>
                        <input type="password" name="password_confirmation" placeholder="Repeat new password">
                    </div>
                </div>
                <div class="sset-actions">
                    <button type="submit" class="sset-btn">Update Password</button>
                    <span class="sset-ok" id="pwOk">✓ Updated</span>
                    <span class="sset-err" id="pwErr"></span>
                </div>
            </form>
        </div>

        <div class="sset-card sset-danger" style="margin-top:10px">
            <p class="sset-card-lbl" style="color:#ef4444">Danger Zone</p>
            <p class="sset-danger-txt">Permanently delete your account and all associated data. This cannot be undone.</p>
            <button class="sset-btn-danger" onclick="if(confirm('Delete your account permanently? This cannot be undone.')) alert('Please contact an admin to delete your account.')">
                Delete My Account
            </button>
        </div>
    </section>

</div>

<script>
function showSec(id) {
    document.querySelectorAll('.sset-sec').forEach(s => s.style.display = 'none');
    document.getElementById('sec-' + id).style.display = '';
    document.querySelectorAll('.sset-nav-btn[id^=nav-]').forEach(b => b.classList.toggle('active', b.id === 'nav-' + id));
}
showSec('profile');

// Dark mode
document.getElementById('darkTogglePage')?.addEventListener('change', function() {
    fetch('{{ route('settings.dark-mode') }}', { method:'PATCH', headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}'} });
    document.documentElement.classList.toggle('dark', this.checked);
    localStorage.setItem('cp-dark', this.checked ? '1' : '0');
});

// Bubble style + font size choices
document.querySelectorAll('.sset-choices').forEach(grp => {
    grp.querySelectorAll('.sset-choice').forEach(c => {
        c.addEventListener('click', () => { grp.querySelectorAll('.sset-choice').forEach(x=>x.classList.remove('on')); c.classList.add('on'); });
    });
});

// Profile save
document.getElementById('profileForm')?.addEventListener('submit', async e => {
    e.preventDefault();
    const btn = e.target.querySelector('button[type=submit]');
    btn.textContent = 'Saving…'; btn.disabled = true;
    const fd = new FormData(e.target); fd.append('_method','PATCH');
    await fetch('{{ route('profile.update') }}', { method:'POST', body:fd, headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}'} }).catch(()=>{});
    btn.textContent = 'Save Profile'; btn.disabled = false;
    flash('profileOk');
});

// Notif save
document.getElementById('notifForm')?.addEventListener('submit', async e => {
    e.preventDefault();
    const fd = new FormData(e.target);
    await fetch('{{ route('settings.notifications') }}', { method:'PATCH', body:fd, headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}'} }).catch(()=>{});
    flash('notifOk');
});

// Password
document.getElementById('pwForm')?.addEventListener('submit', async e => {
    e.preventDefault();
    const fd = new FormData(e.target); fd.append('_method','PATCH');
    const res = await fetch('{{ route('settings.password') }}', { method:'POST', body:fd, headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}'} }).catch(()=>{});
    if (res?.ok) { flash('pwOk'); e.target.reset(); }
    else { const err = document.getElementById('pwErr'); err.textContent = 'Incorrect password'; flash('pwErr', 3000); }
});

// Bio counter
document.querySelector('textarea[name=bio]')?.addEventListener('input', function() {
    document.getElementById('bioCount').textContent = this.value.length + '/200';
});

function flash(id, dur=2500) {
    const el = document.getElementById(id); if (!el) return;
    el.style.opacity = 1; setTimeout(() => el.style.opacity = 0, dur);
}
</script>
@endsection
