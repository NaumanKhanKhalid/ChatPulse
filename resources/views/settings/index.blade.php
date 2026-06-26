@extends('layouts.app')
@section('title', 'Settings')

@section('left-panel')
<div class="set-sidebar">
    <div class="set-sb-head">Settings</div>
    <nav class="set-nav">
        @php
        $sections = [
            ['id'=>'profile',       'label'=>'Profile',       'ic'=>'M12 12a5 5 0 1 0 0-10 5 5 0 0 0 0 10Zm0 2c-5.33 0-8 2.67-8 4v1h16v-1c0-1.33-2.67-4-8-4Z'],
            ['id'=>'appearance',    'label'=>'Appearance',    'ic'=>'M12 3a9 9 0 1 0 0 18A9 9 0 0 0 12 3Zm0 2a7 7 0 0 1 6.93 6H5.07A7 7 0 0 1 12 5Z'],
            ['id'=>'notifications', 'label'=>'Notifications', 'ic'=>'M18 16H6l-2 2V8a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v6a2 2 0 0 1-2 2Z'],
            ['id'=>'privacy',       'label'=>'Privacy',       'ic'=>'M12 2a5 5 0 0 1 5 5v1h1a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-9a2 2 0 0 1 2-2h1V7a5 5 0 0 1 5-5Zm0 10a2 2 0 1 0 0 4 2 2 0 0 0 0-4Zm0-8a3 3 0 0 0-3 3v1h6V7a3 3 0 0 0-3-3Z'],
            ['id'=>'account',       'label'=>'Account',       'ic'=>'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0 1 12 2.944a11.955 11.955 0 0 1-8.618 3.04A12.02 12.02 0 0 0 3 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016Z'],
        ];
        @endphp
        @foreach($sections as $s)
        <button class="set-nav-item" data-sec="{{ $s['id'] }}" onclick="showSec('{{ $s['id'] }}')">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="{{ $s['ic'] }}" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
            {{ $s['label'] }}
        </button>
        @endforeach
    </nav>
    <div class="set-sb-foot">
        <form method="POST" action="{{ route('logout') }}">@csrf
            <button class="set-logout">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M17 16l4-4m0 0-4-4m4 4H7m6 4v1a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v1" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                Sign out
            </button>
        </form>
    </div>
</div>
@endsection

@section('content')
<div class="set-main">

    {{-- ===== PROFILE ===== --}}
    <div class="set-sec" id="sec-profile">
        <div class="set-sec-head">
            <h2>Profile</h2>
            <p>How others see you on ChatPulse</p>
        </div>

        {{-- Avatar preview --}}
        <div class="set-avatar-row">
            @php
                $grad = $user->avatarGradient();
                $initials = collect(explode(' ', $user->name))->map(fn($w)=>strtoupper(substr($w,0,1)))->take(2)->join('');
            @endphp
            <div class="set-av" style="background:linear-gradient(135deg,{{ $grad[0] }},{{ $grad[1] }})">{{ $initials }}</div>
            <div>
                <p class="set-av-name">{{ $user->name }}</p>
                <p class="set-av-sub">@{{ $user->username ?? strtolower(str_replace(' ','_',$user->name)) }}</p>
            </div>
        </div>

        <form id="profileForm" class="set-form">
            @csrf
            <div class="set-row">
                <label>Display Name</label>
                <input type="text" name="name" value="{{ $user->name }}" placeholder="Your name" maxlength="60">
            </div>
            <div class="set-row">
                <label>Username</label>
                <div class="set-input-pre">
                    <span>@</span>
                    <input type="text" name="username" value="{{ $user->username }}" placeholder="username" maxlength="30">
                </div>
            </div>
            <div class="set-row">
                <label>Bio</label>
                <textarea name="bio" rows="3" placeholder="Tell people a little about yourself…" maxlength="200">{{ $user->bio }}</textarea>
                <span class="set-hint">{{ strlen($user->bio ?? '') }}/200</span>
            </div>
            <div class="set-row">
                <label>Status</label>
                <select name="status_type">
                    <option value="available" {{ $user->status_type === 'available' ? 'selected' : '' }}>🟢 Available</option>
                    <option value="busy"      {{ $user->status_type === 'busy'      ? 'selected' : '' }}>🔴 Busy</option>
                    <option value="away"      {{ $user->status_type === 'away'      ? 'selected' : '' }}>🟡 Away</option>
                </select>
            </div>
            <div class="set-row">
                <label>Status Message</label>
                <input type="text" name="status_message" value="{{ $user->status_message }}" placeholder="e.g. In a meeting…" maxlength="100">
            </div>
            <div class="set-actions">
                <button type="submit" class="set-btn-primary" id="profileSave">Save Profile</button>
                <span class="set-saved" id="profileSaved">Saved ✓</span>
            </div>
        </form>
    </div>

    {{-- ===== APPEARANCE ===== --}}
    <div class="set-sec" id="sec-appearance" style="display:none">
        <div class="set-sec-head">
            <h2>Appearance</h2>
            <p>Customize how ChatPulse looks</p>
        </div>

        <div class="set-card">
            <div class="set-toggle-row">
                <div>
                    <p class="set-toggle-label">Dark Mode</p>
                    <p class="set-toggle-sub">Switch to dark theme</p>
                </div>
                <label class="set-switch">
                    <input type="checkbox" id="darkToggle" {{ $user->dark_mode ? 'checked' : '' }}>
                    <span class="set-track"></span>
                </label>
            </div>
        </div>

        <div class="set-card" style="margin-top:12px">
            <p class="set-card-label">Font Size</p>
            <div class="set-radio-group">
                <label class="set-radio"><input type="radio" name="font_size" value="small"> <span>Small</span></label>
                <label class="set-radio"><input type="radio" name="font_size" value="normal" checked> <span>Normal</span></label>
                <label class="set-radio"><input type="radio" name="font_size" value="large"> <span>Large</span></label>
            </div>
        </div>

        <div class="set-card" style="margin-top:12px">
            <p class="set-card-label">Chat Wallpaper</p>
            <div class="set-wallpapers">
                @foreach(['none','dots','grid','waves','hexagons'] as $w)
                <button class="set-wp {{ $w === 'none' ? 'on' : '' }}" data-wp="{{ $w }}" title="{{ ucfirst($w) }}">
                    @if($w==='none') <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M5 5l14 14M19 5 5 19" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    @else {{ ucfirst($w) }} @endif
                </button>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ===== NOTIFICATIONS ===== --}}
    <div class="set-sec" id="sec-notifications" style="display:none">
        <div class="set-sec-head">
            <h2>Notifications</h2>
            <p>Control what alerts you receive</p>
        </div>

        <form id="notifForm">
            @csrf
            <div class="set-card">
                <div class="set-toggle-row">
                    <div>
                        <p class="set-toggle-label">Email Notifications</p>
                        <p class="set-toggle-sub">Receive updates in your inbox</p>
                    </div>
                    <label class="set-switch">
                        <input type="checkbox" name="email_notifications" {{ $user->email_notifications ? 'checked' : '' }}>
                        <span class="set-track"></span>
                    </label>
                </div>
                <div class="set-sep"></div>
                <div class="set-toggle-row">
                    <div>
                        <p class="set-toggle-label">Message Previews</p>
                        <p class="set-toggle-sub">Show message text in notifications</p>
                    </div>
                    <label class="set-switch">
                        <input type="checkbox" checked>
                        <span class="set-track"></span>
                    </label>
                </div>
                <div class="set-sep"></div>
                <div class="set-toggle-row">
                    <div>
                        <p class="set-toggle-label">Sound Alerts</p>
                        <p class="set-toggle-sub">Play a sound on new messages</p>
                    </div>
                    <label class="set-switch">
                        <input type="checkbox" checked>
                        <span class="set-track"></span>
                    </label>
                </div>
            </div>

            <div class="set-card" style="margin-top:12px">
                <p class="set-card-label">Email Digest</p>
                <select name="email_digest" class="set-select">
                    <option value="never"  {{ $user->email_digest === 'never'  ? 'selected':'' }}>Never</option>
                    <option value="daily"  {{ $user->email_digest === 'daily'  ? 'selected':'' }}>Daily summary</option>
                    <option value="weekly" {{ $user->email_digest === 'weekly' ? 'selected':'' }}>Weekly summary</option>
                </select>
            </div>

            <div class="set-actions">
                <button type="submit" class="set-btn-primary">Save Preferences</button>
                <span class="set-saved" id="notifSaved">Saved ✓</span>
            </div>
        </form>
    </div>

    {{-- ===== PRIVACY ===== --}}
    <div class="set-sec" id="sec-privacy" style="display:none">
        <div class="set-sec-head">
            <h2>Privacy</h2>
            <p>Manage your visibility and data</p>
        </div>

        <div class="set-card">
            <div class="set-toggle-row">
                <div>
                    <p class="set-toggle-label">Read Receipts</p>
                    <p class="set-toggle-sub">Let others know when you've read messages</p>
                </div>
                <label class="set-switch">
                    <input type="checkbox" checked>
                    <span class="set-track"></span>
                </label>
            </div>
            <div class="set-sep"></div>
            <div class="set-toggle-row">
                <div>
                    <p class="set-toggle-label">Online Status</p>
                    <p class="set-toggle-sub">Show when you're active</p>
                </div>
                <label class="set-switch">
                    <input type="checkbox" checked>
                    <span class="set-track"></span>
                </label>
            </div>
            <div class="set-sep"></div>
            <div class="set-toggle-row">
                <div>
                    <p class="set-toggle-label">Typing Indicator</p>
                    <p class="set-toggle-sub">Show when you're typing</p>
                </div>
                <label class="set-switch">
                    <input type="checkbox" checked>
                    <span class="set-track"></span>
                </label>
            </div>
        </div>

        <div class="set-card" style="margin-top:12px">
            <p class="set-card-label">Who can message you</p>
            <div class="set-radio-group">
                <label class="set-radio"><input type="radio" name="who_msg" value="everyone" checked> <span>Everyone</span></label>
                <label class="set-radio"><input type="radio" name="who_msg" value="contacts"> <span>Contacts only</span></label>
            </div>
        </div>
    </div>

    {{-- ===== ACCOUNT ===== --}}
    <div class="set-sec" id="sec-account" style="display:none">
        <div class="set-sec-head">
            <h2>Account</h2>
            <p>Manage your credentials and data</p>
        </div>

        {{-- Change password --}}
        <div class="set-card">
            <p class="set-card-label">Change Password</p>
            <form id="pwForm" class="set-form" style="margin-top:14px">
                @csrf
                <div class="set-row">
                    <label>Current Password</label>
                    <input type="password" name="current_password" placeholder="••••••••">
                </div>
                <div class="set-row">
                    <label>New Password</label>
                    <input type="password" name="password" placeholder="••••••••">
                </div>
                <div class="set-row">
                    <label>Confirm New Password</label>
                    <input type="password" name="password_confirmation" placeholder="••••••••">
                </div>
                <div class="set-actions">
                    <button type="submit" class="set-btn-primary">Update Password</button>
                    <span class="set-saved" id="pwSaved">Updated ✓</span>
                </div>
            </form>
        </div>

        {{-- Email --}}
        <div class="set-card" style="margin-top:12px">
            <p class="set-card-label">Email Address</p>
            <div class="set-email-row">
                <span class="set-email-val">{{ $user->email }}</span>
                @if($user->email_verified_at)
                <span class="set-badge-green">Verified</span>
                @else
                <span class="set-badge-red">Not verified</span>
                @endif
            </div>
        </div>

        {{-- Danger --}}
        <div class="set-card set-danger-card" style="margin-top:12px">
            <p class="set-card-label" style="color:#ef4444">Danger Zone</p>
            <p class="set-danger-sub">Once deleted, your account and all messages are permanently removed.</p>
            <button class="set-btn-danger" onclick="confirm('Delete your account permanently?') && alert('Contact admin to delete account.')">Delete Account</button>
        </div>
    </div>

</div>

<style>
/* ===== Settings Layout ===== */
.set-sidebar { display:flex; flex-direction:column; height:100%; padding:20px 12px 16px; background:var(--side); border-right:1px solid var(--line); }
.set-sb-head { font-size:11px; font-weight:800; text-transform:uppercase; letter-spacing:.07em; color:var(--text3); padding:0 8px; margin-bottom:14px; }
.set-nav { display:flex; flex-direction:column; gap:2px; flex:1; }
.set-nav-item { display:flex; align-items:center; gap:10px; width:100%; padding:9px 10px; border-radius:11px; font-size:13.5px; font-weight:600; color:var(--text2); text-align:left; transition:background .1s, color .1s; }
.set-nav-item:hover { background:var(--hover); color:var(--text); }
.set-nav-item.active { background:var(--primary-light); color:var(--primary-dark); }
html.dark .set-nav-item.active { background:rgba(16,185,129,.14); color:#6ee7b7; }
.set-sb-foot { padding-top:12px; border-top:1px solid var(--line); }
.set-logout { display:flex; align-items:center; gap:9px; width:100%; padding:9px 10px; border-radius:11px; font-size:13.5px; font-weight:600; color:#ef4444; transition:background .1s; }
.set-logout:hover { background:rgba(239,68,68,.08); }

/* ===== Main content ===== */
.set-main { flex:1; overflow-y:auto; padding:32px 36px; background:var(--bg); }
.set-sec-head { margin-bottom:24px; }
.set-sec-head h2 { font-size:20px; font-weight:800; color:var(--text); margin-bottom:4px; }
.set-sec-head p { font-size:13.5px; color:var(--text3); }

/* Avatar row */
.set-avatar-row { display:flex; align-items:center; gap:16px; margin-bottom:28px; padding:18px; background:var(--card); border:1px solid var(--line); border-radius:16px; }
.set-av { width:56px; height:56px; border-radius:50%; display:grid; place-items:center; color:#fff; font-size:20px; font-weight:800; flex-shrink:0; }
.set-av-name { font-size:15px; font-weight:800; color:var(--text); }
.set-av-sub { font-size:13px; color:var(--text3); margin-top:2px; }

/* Card */
.set-card { background:var(--card); border:1px solid var(--line); border-radius:14px; padding:18px 20px; }
.set-card-label { font-size:11.5px; font-weight:800; text-transform:uppercase; letter-spacing:.06em; color:var(--text3); margin-bottom:12px; }

/* Form rows */
.set-form { display:flex; flex-direction:column; gap:16px; }
.set-row { display:flex; flex-direction:column; gap:6px; }
.set-row label { font-size:12.5px; font-weight:700; color:var(--text2); }
.set-row input, .set-row textarea, .set-row select { background:var(--input); border:1px solid var(--line); border-radius:10px; padding:9px 13px; font-size:14px; color:var(--text); outline:none; font-family:inherit; transition:border-color .15s; width:100%; }
.set-row input:focus, .set-row textarea:focus, .set-row select:focus { border-color:var(--primary); box-shadow:0 0 0 3px rgba(16,185,129,.1); }
.set-row textarea { resize:vertical; min-height:80px; line-height:1.5; }
.set-input-pre { display:flex; align-items:center; background:var(--input); border:1px solid var(--line); border-radius:10px; overflow:hidden; transition:border-color .15s; }
.set-input-pre:focus-within { border-color:var(--primary); box-shadow:0 0 0 3px rgba(16,185,129,.1); }
.set-input-pre span { padding:0 10px 0 13px; font-size:14px; font-weight:700; color:var(--text3); }
.set-input-pre input { border:none; background:none; box-shadow:none; padding-left:0; }
.set-input-pre input:focus { box-shadow:none; }
.set-hint { font-size:11.5px; color:var(--text3); text-align:right; margin-top:-4px; }
.set-select { background:var(--input); border:1px solid var(--line); border-radius:10px; padding:9px 13px; font-size:14px; color:var(--text); outline:none; width:100%; transition:border-color .15s; }
.set-select:focus { border-color:var(--primary); }

/* Toggle switch */
.set-toggle-row { display:flex; align-items:center; justify-content:space-between; gap:16px; padding:4px 0; }
.set-toggle-label { font-size:14px; font-weight:600; color:var(--text); }
.set-toggle-sub { font-size:12.5px; color:var(--text3); margin-top:2px; }
.set-switch { position:relative; width:44px; height:24px; flex-shrink:0; }
.set-switch input { opacity:0; width:0; height:0; }
.set-track { position:absolute; inset:0; border-radius:99px; background:var(--line); cursor:pointer; transition:background .2s; }
.set-track::after { content:''; position:absolute; top:3px; left:3px; width:18px; height:18px; border-radius:50%; background:#fff; transition:transform .2s; }
.set-switch input:checked ~ .set-track { background:var(--primary); }
.set-switch input:checked ~ .set-track::after { transform:translateX(20px); }
.set-sep { height:1px; background:var(--line); margin:14px 0; }

/* Radio group */
.set-radio-group { display:flex; gap:10px; flex-wrap:wrap; }
.set-radio { display:flex; align-items:center; gap:8px; padding:8px 14px; border:1px solid var(--line); border-radius:10px; cursor:pointer; font-size:13.5px; font-weight:600; color:var(--text2); transition:border-color .15s, color .15s; }
.set-radio:has(input:checked) { border-color:var(--primary); color:var(--primary-dark); background:var(--primary-light); }
html.dark .set-radio:has(input:checked) { color:#6ee7b7; background:rgba(16,185,129,.12); }
.set-radio input { display:none; }

/* Wallpaper picker */
.set-wallpapers { display:flex; gap:8px; flex-wrap:wrap; margin-top:4px; }
.set-wp { padding:7px 14px; border:1px solid var(--line); border-radius:10px; font-size:12.5px; font-weight:600; color:var(--text2); transition:border-color .15s; }
.set-wp.on, .set-wp:hover { border-color:var(--primary); color:var(--primary-dark); background:var(--primary-light); }

/* Actions */
.set-actions { display:flex; align-items:center; gap:12px; padding-top:4px; }
.set-btn-primary { background:var(--primary); color:#fff; font-size:13.5px; font-weight:700; padding:9px 22px; border-radius:11px; transition:background .15s, transform .1s; box-shadow:0 4px 14px -4px rgba(16,185,129,.5); }
.set-btn-primary:hover { background:var(--primary-hover); }
.set-btn-primary:active { transform:scale(.97); }
.set-saved { font-size:13px; font-weight:600; color:var(--primary); opacity:0; transition:opacity .3s; }
.set-saved.show { opacity:1; }

/* Email row */
.set-email-row { display:flex; align-items:center; gap:10px; margin-top:8px; }
.set-email-val { font-size:14px; color:var(--text); }
.set-badge-green { font-size:11.5px; font-weight:700; background:#d1fae5; color:#065f46; padding:2px 9px; border-radius:99px; }
.set-badge-red { font-size:11.5px; font-weight:700; background:#fee2e2; color:#b91c1c; padding:2px 9px; border-radius:99px; }

/* Danger */
.set-danger-card { border-color:rgba(239,68,68,.2); }
.set-danger-sub { font-size:13px; color:var(--text3); margin-bottom:14px; }
.set-btn-danger { font-size:13.5px; font-weight:700; color:#ef4444; background:rgba(239,68,68,.08); border:1px solid rgba(239,68,68,.2); padding:8px 18px; border-radius:11px; transition:background .15s; }
.set-btn-danger:hover { background:rgba(239,68,68,.14); }
</style>

<script>
function showSec(id) {
    document.querySelectorAll('.set-sec').forEach(s => s.style.display = 'none');
    document.getElementById('sec-' + id).style.display = '';
    document.querySelectorAll('.set-nav-item').forEach(b => b.classList.toggle('active', b.dataset.sec === id));
}
// Init active
showSec('profile');

// Dark mode toggle
document.getElementById('darkToggle')?.addEventListener('change', function() {
    fetch('{{ route('settings.dark-mode') }}', {
        method:'PATCH', headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}','Content-Type':'application/json'}
    }).catch(()=>{});
    document.documentElement.classList.toggle('dark', this.checked);
});

// Profile save
document.getElementById('profileForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('profileSave');
    btn.textContent = 'Saving…'; btn.disabled = true;
    const fd = new FormData(this);
    fd.append('_method','PATCH');
    await fetch('{{ route('profile.update') }}', { method:'POST', body:fd, headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}'} }).catch(()=>{});
    btn.textContent = 'Save Profile'; btn.disabled = false;
    const s = document.getElementById('profileSaved');
    s.classList.add('show'); setTimeout(() => s.classList.remove('show'), 2500);
});

// Notif save
document.getElementById('notifForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const fd = new FormData(this);
    await fetch('{{ route('settings.notifications') }}', {
        method:'PATCH', body:fd, headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}'}
    }).catch(()=>{});
    const s = document.getElementById('notifSaved');
    s.classList.add('show'); setTimeout(() => s.classList.remove('show'), 2500);
});

// Bio char counter
document.querySelector('textarea[name=bio]')?.addEventListener('input', function() {
    const hint = this.closest('.set-row').querySelector('.set-hint');
    if (hint) hint.textContent = this.value.length + '/200';
});

// Password form
document.getElementById('pwForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const fd = new FormData(this); fd.append('_method','PATCH');
    const res = await fetch('/settings/password', { method:'POST', body:fd, headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}'} }).catch(()=>{});
    const s = document.getElementById('pwSaved');
    s.classList.add('show'); setTimeout(() => s.classList.remove('show'), 2500);
    this.reset();
});

// Wallpaper picker
document.querySelectorAll('.set-wp').forEach(b => {
    b.addEventListener('click', () => {
        document.querySelectorAll('.set-wp').forEach(x => x.classList.remove('on'));
        b.classList.add('on');
    });
});
</script>
@endsection
