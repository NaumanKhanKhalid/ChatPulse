/* ChatPulse — account, privacy prefs, profile editing & first-run onboarding.
   Shared by App + Screens. Loads AFTER data/modals/overlays, BEFORE app/inline. */
(function () {
  const CP = window.CP;
  const me = CP.me, users = CP.users;
  const esc = s => (s || '').replace(/[&<>"]/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]));
  const av = (u, s) => `<div class="avatar" style="width:${s}px;height:${s}px;background:linear-gradient(135deg,${u.grad[0]},${u.grad[1]});font-size:${s * .38}px">${u.initials}</div>`;
  const toast = m => (window.CPModals ? CPModals.toast(m) : null);

  /* ============ PREFERENCES ============ */
  const PREF_DEFAULTS = {
    desktopNotifs: true, sounds: true, mentionsOnly: false, emailDigest: false,
    readReceipts: true, lastSeen: true, typingIndicator: true,
    enterToSend: true, autoDownload: true, linkPreviews: true,
    twofa: false, textSize: 'default', wallpaper: 'default', quietHours: { on: false, from: '22:00', to: '07:00' },
  };
  // id used by the Settings toggles -> pref key
  const TOGGLE_MAP = {
    t1: 'desktopNotifs', t2: 'sounds', t3: 'mentionsOnly', t4: 'emailDigest',
    p1: 'readReceipts', p2: 'lastSeen', p3: 'typingIndicator',
    c1: 'enterToSend', c2: 'autoDownload', c3: 'linkPreviews',
  };
  function loadPrefs() { try { return Object.assign({}, PREF_DEFAULTS, JSON.parse(localStorage.getItem('cp-prefs') || '{}')); } catch (e) { return Object.assign({}, PREF_DEFAULTS); } }
  CP.prefs = loadPrefs();
  CP.TOGGLE_MAP = TOGGLE_MAP;
  function savePrefs() { localStorage.setItem('cp-prefs', JSON.stringify(CP.prefs)); }
  CP.setPref = function (key, val) {
    CP.prefs[key] = val; savePrefs(); applyPrefsToDoc();
    document.dispatchEvent(new CustomEvent('cp-prefs-change', { detail: { key, value: val } }));
  };
  function applyPrefsToDoc() {
    const d = document.documentElement;
    d.dataset.textSize = CP.prefs.textSize;
    d.dataset.lastSeen = CP.prefs.lastSeen ? '1' : '0';
    d.dataset.readReceipts = CP.prefs.readReceipts ? '1' : '0';
    d.dataset.typingPref = CP.prefs.typingIndicator ? '1' : '0';
    d.dataset.wallpaper = CP.prefs.wallpaper || 'default';
  }

  /* ============ IDENTITY / PROFILE ============ */
  const PALETTES = [
    ['#f9a8d4', '#db2777'], ['#7dd3fc', '#2563eb'], ['#6ee7b7', '#0d9488'], ['#fcd34d', '#ea580c'],
    ['#c4b5fd', '#7c3aed'], ['#f0abfc', '#a21caf'], ['#fda4af', '#e11d48'], ['#34d399', '#059669'],
  ];
  const initialsOf = n => { const p = (n || '').trim().split(/\s+/); return (((p[0] || '')[0] || '') + ((p[1] || '')[0] || '')).toUpperCase() || (n ? n[0].toUpperCase() : '?'); };
  const sameGrad = (a, b) => a[0] === b[0] && a[1] === b[1];
  function loadProfile() { try { return JSON.parse(localStorage.getItem('cp-profile') || 'null'); } catch (e) { return null; } }
  function applyProfile(p) {
    if (!p) return;
    if (p.name) { me.name = p.name; me.initials = initialsOf(p.name); }
    if (p.username) me.username = p.username;
    if (p.grad) me.grad = p.grad;
    if (p.status) me.status = p.status;
    if (p.bio != null) CP.bio = p.bio;
  }
  CP.bio = 'Designing calm, fast interfaces.';
  CP.profile = loadProfile();
  applyProfile(CP.profile);
  function saveProfile(p) {
    CP.profile = Object.assign({}, CP.profile || {}, p);
    localStorage.setItem('cp-profile', JSON.stringify(CP.profile));
    applyProfile(CP.profile);
    reflectIdentity();
    document.dispatchEvent(new CustomEvent('cp-profile-change'));
  }
  function reflectIdentity() {
    document.querySelectorAll('.rail-ava .avatar').forEach(el => {
      el.textContent = me.initials;
      el.style.background = `linear-gradient(135deg,${me.grad[0]},${me.grad[1]})`;
    });
  }

  /* ============ BLOCKED USERS ============ */
  function loadBlocked() { try { const v = JSON.parse(localStorage.getItem('cp-blocked') || 'null'); return Array.isArray(v) ? v : [6, 50]; } catch (e) { return [6, 50]; } }
  let blocked = loadBlocked();
  function saveBlocked() { localStorage.setItem('cp-blocked', JSON.stringify(blocked)); }
  const isBlocked = id => blocked.includes(id);
  function block(id) { if (!blocked.includes(id)) { blocked.push(id); saveBlocked(); document.dispatchEvent(new CustomEvent('cp-blocked-change')); } }
  function unblock(id) { blocked = blocked.filter(x => x !== id); saveBlocked(); document.dispatchEvent(new CustomEvent('cp-blocked-change')); }

  /* ============ SESSIONS ============ */
  const SESS_ICONS = {
    mac: '<rect x="3" y="4" width="18" height="12" rx="2" stroke="currentColor" stroke-width="1.7"/><path d="M8 20h8M10 16v4M14 16v4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>',
    phone: '<rect x="7" y="3" width="10" height="18" rx="2.5" stroke="currentColor" stroke-width="1.7"/><path d="M11 18h2" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>',
    win: '<rect x="3" y="5" width="18" height="12" rx="1.5" stroke="currentColor" stroke-width="1.7"/><path d="M3 11h18" stroke="currentColor" stroke-width="1.5"/>',
  };
  let sessions = [
    { id: 's1', device: 'Chrome · macOS', loc: 'Karachi, PK', current: true, last: 'Active now', ic: 'mac' },
    { id: 's2', device: 'ChatPulse for iOS · iPhone 15', loc: 'Karachi, PK', current: false, last: '2 hours ago', ic: 'phone' },
    { id: 's3', device: 'Firefox · Windows', loc: 'Lahore, PK', current: false, last: 'Yesterday, 9:41 PM', ic: 'win' },
  ];

  /* ============ shared modal shell (reuses CPModals styles) ============ */
  function open(title, body, foot, opts) {
    opts = opts || {};
    document.querySelectorAll('.cp-overlay').forEach(o => o.remove());
    const ov = document.createElement('div'); ov.className = 'cp-overlay';
    ov.innerHTML = `<div class="cp-modal" style="${opts.wide ? 'max-width:520px' : ''}"><div class="cp-head"><span class="cp-title">${esc(title)}</span><button class="cp-x" data-close><svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M6 6l12 12M18 6 6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg></button></div><div class="cp-body">${body}</div>${foot ? `<div class="cp-foot">${foot}</div>` : ''}</div>`;
    document.body.appendChild(ov);
    ov.addEventListener('click', e => { if (e.target === ov || e.target.closest('[data-close]')) ov.remove(); });
    document.addEventListener('keydown', function k(e) { if (e.key === 'Escape') { ov.remove(); document.removeEventListener('keydown', k); } });
    return ov;
  }
  const closeAll = () => document.querySelectorAll('.cp-overlay,.cp-ob').forEach(o => o.remove());

  /* password strength (shared) */
  function scorePw(p) { let s = 0; if (p.length >= 8) s++; if (/[A-Z]/.test(p) && /[a-z]/.test(p)) s++; if (/\d/.test(p)) s++; if (/[^A-Za-z0-9]/.test(p)) s++; return p.length === 0 ? 0 : Math.max(1, s); }

  /* ============ EDIT PROFILE ============ */
  function openEditProfile(onSaved) {
    let grad = me.grad.slice(), status = me.status || 'available';
    const body = `
      <div class="ep-top">
        <div id="epAv">${av(me, 76)}</div>
        <div class="ep-sw-wrap" id="epSw">${PALETTES.map((p, i) => `<button class="ep-sw ${sameGrad(p, grad) ? 'on' : ''}" data-g="${i}" style="background:linear-gradient(135deg,${p[0]},${p[1]})"></button>`).join('')}</div>
        <span class="ep-hint">Pick an avatar color</span>
      </div>
      <div class="cp-row"><span class="cp-label">Display name</span><input class="cp-input" id="epName" maxlength="32" value="${esc(me.name)}" /></div>
      <div class="cp-row"><span class="cp-label">Username</span><input class="cp-input" id="epUser" maxlength="24" value="${esc(me.username)}" /></div>
      <div class="cp-row"><span class="cp-label">Bio</span><textarea class="cp-textarea" id="epBio" maxlength="160" placeholder="Add a short bio…">${esc(CP.bio || '')}</textarea></div>
      <div class="cp-row"><span class="cp-label">Status</span><div class="st-types" id="epStatus">${[['available', 'Available', '#10b981'], ['busy', 'Busy', '#ef4444'], ['away', 'Away', '#f59e0b']].map(t => `<button class="st-type ${t[0] === status ? 'on' : ''}" data-s="${t[0]}" style="color:${t[2]}"><span class="d" style="background:${t[2]}"></span>${t[1]}</button>`).join('')}</div></div>`;
    const foot = `<button class="cp-btn ghost" data-close>Cancel</button><button class="cp-btn primary" id="epSave">Save changes</button>`;
    const ov = open('Edit profile', body, foot);
    const repaint = () => { const nm = ov.querySelector('#epName').value; ov.querySelector('#epAv').innerHTML = av({ grad, initials: initialsOf(nm) || me.initials }, 76); };
    ov.querySelectorAll('[data-g]').forEach(b => b.addEventListener('click', () => { grad = PALETTES[+b.dataset.g]; ov.querySelectorAll('.ep-sw').forEach(x => x.classList.toggle('on', x === b)); repaint(); }));
    ov.querySelector('#epName').addEventListener('input', repaint);
    ov.querySelectorAll('#epStatus [data-s]').forEach(b => b.addEventListener('click', () => { status = b.dataset.s; ov.querySelectorAll('#epStatus .st-type').forEach(x => x.classList.toggle('on', x === b)); }));
    ov.querySelector('#epSave').addEventListener('click', () => {
      const name = ov.querySelector('#epName').value.trim() || me.name;
      const username = ov.querySelector('#epUser').value.trim().replace(/^@/, '') || me.username;
      const bio = ov.querySelector('#epBio').value.trim();
      saveProfile({ name, username, grad, status, bio });
      closeAll(); toast('Profile updated'); onSaved && onSaved();
    });
  }

  /* ============ BLOCKED USERS MANAGER ============ */
  function openBlocked() {
    const ov = open('Blocked users', `<p class="ac-note">Blocked people can’t message you or see your activity.</p><div id="blkList"></div>`, '');
    const paint = () => {
      const list = blocked.map(id => users[id]).filter(Boolean);
      const el = ov.querySelector('#blkList');
      if (!list.length) { el.innerHTML = `<div class="ac-empty">No blocked users.</div>`; return; }
      el.innerHTML = list.map(u => `<div class="ac-row"><span class="ac-avwrap">${av(u, 40)}</span><div class="ac-rtx"><b>${esc(u.name)}</b><span>@${esc(u.username)}</span></div><button class="cp-btn ghost ac-sm" data-unblock="${u.id}">Unblock</button></div>`).join('');
      el.querySelectorAll('[data-unblock]').forEach(b => b.addEventListener('click', () => { unblock(+b.dataset.unblock); paint(); toast(users[+b.dataset.unblock].name + ' unblocked'); }));
    };
    paint();
  }

  /* ============ ACTIVE SESSIONS ============ */
  function openSessions() {
    const ov = open('Active sessions', `<p class="ac-note">You’re signed in on these devices. Sign out anywhere you don’t recognize.</p><div id="sessList"></div>`, `<button class="cp-btn ghost" data-close>Close</button><button class="cp-btn primary" id="sessAll" style="background:var(--busy);box-shadow:none">Sign out all others</button>`);
    const paint = () => {
      ov.querySelector('#sessList').innerHTML = sessions.map(s => `
        <div class="ac-row"><span class="sess-ic"><svg width="20" height="20" viewBox="0 0 24 24" fill="none">${SESS_ICONS[s.ic]}</svg></span>
        <div class="ac-rtx"><b>${esc(s.device)}${s.current ? '<span class="sess-now">This device</span>' : ''}</b><span>${esc(s.loc)} · ${esc(s.last)}</span></div>
        ${s.current ? '' : `<button class="cp-btn ghost ac-sm" data-signout="${s.id}">Sign out</button>`}</div>`).join('');
      ov.querySelectorAll('[data-signout]').forEach(b => b.addEventListener('click', () => { sessions = sessions.filter(x => x.id !== b.dataset.signout); paint(); toast('Signed out of that session'); }));
    };
    paint();
    ov.querySelector('#sessAll').addEventListener('click', () => { sessions = sessions.filter(s => s.current); paint(); toast('Signed out of all other sessions'); });
  }

  /* ============ TWO-FACTOR ENROLLMENT ============ */
  function open2FA() {
    if (CP.prefs.twofa) {
      const ov = open('Two-factor authentication', `<div class="ac-2fa-on"><span class="ac-2fa-ic"><svg width="26" height="26" viewBox="0 0 24 24" fill="none"><path d="M12 3 5 6v5c0 4.5 3 8 7 9.5 4-1.5 7-5 7-9.5V6l-7-3Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="m9 12 2 2 4-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></span><div><b>Two-factor is on</b><span>Your account is protected with an authenticator app.</span></div></div>`, `<button class="cp-btn ghost" data-close>Close</button><button class="cp-btn primary" id="tfDisable" style="background:var(--busy);box-shadow:none">Turn off</button>`);
      ov.querySelector('#tfDisable').addEventListener('click', () => { CP.setPref('twofa', false); closeAll(); toast('Two-factor disabled'); document.dispatchEvent(new CustomEvent('cp-twofa-change')); });
      return;
    }
    const secret = 'JBSW Y3DP EHPK 3PXP';
    const body = `
      <p class="ac-note">Scan the QR code with Google Authenticator, 1Password or Authy — then enter the 6-digit code to finish.</p>
      <div class="tf-enroll">
        <div class="tf-qr">${qrSvg()}</div>
        <div class="tf-key"><span class="cp-label" style="margin-bottom:4px">Or enter this key</span><code>${secret}</code></div>
      </div>
      <div class="cp-row" style="margin-top:6px"><span class="cp-label">6-digit code</span>
        <div class="tf-otp" id="tfOtp">${Array.from({ length: 6 }).map(() => '<input inputmode="numeric" maxlength="1" class="tf-box" />').join('')}</div></div>`;
    const foot = `<button class="cp-btn ghost" data-close>Cancel</button><button class="cp-btn primary" id="tfVerify" disabled>Enable 2FA</button>`;
    const ov = open('Set up two-factor', body, foot);
    const boxes = [...ov.querySelectorAll('.tf-box')], btn = ov.querySelector('#tfVerify');
    const sync = () => btn.disabled = !boxes.every(b => b.value);
    boxes.forEach((box, i) => {
      box.addEventListener('input', () => { box.value = box.value.replace(/\D/g, '').slice(0, 1); box.classList.toggle('filled', !!box.value); if (box.value && i < 5) boxes[i + 1].focus(); sync(); });
      box.addEventListener('keydown', e => { if (e.key === 'Backspace' && !box.value && i > 0) boxes[i - 1].focus(); });
    });
    boxes[0].focus();
    btn.addEventListener('click', () => { CP.setPref('twofa', true); closeAll(); toast('Two-factor enabled 🔐'); document.dispatchEvent(new CustomEvent('cp-twofa-change')); });
  }
  function qrSvg() {
    // deterministic decorative QR-like grid (not a real code)
    let cells = ''; const n = 11; let seed = 7;
    for (let y = 0; y < n; y++) for (let x = 0; x < n; x++) { seed = (seed * 1103515245 + 12345) & 0x7fffffff; const on = (seed >> 16) & 1; const finder = (x < 3 && y < 3) || (x > n - 4 && y < 3) || (x < 3 && y > n - 4); if (on || finder) cells += `<rect x="${x * 8 + 2}" y="${y * 8 + 2}" width="8" height="8" fill="#0c1411"/>`; }
    return `<svg width="104" height="104" viewBox="0 0 ${n * 8 + 4} ${n * 8 + 4}">${cells}</svg>`;
  }

  /* ============ CHANGE PASSWORD ============ */
  function openChangePassword() {
    const body = `
      <div class="cp-row"><span class="cp-label">Current password</span><div class="ac-pw"><input type="password" class="cp-input" id="pwCur" placeholder="••••••••" /></div></div>
      <div class="cp-row"><span class="cp-label">New password</span><input type="password" class="cp-input" id="pwNew" placeholder="••••••••" />
        <div class="ac-strength" id="pwStr"><span></span><span></span><span></span><span></span></div></div>
      <div class="cp-row"><span class="cp-label">Confirm new password</span><input type="password" class="cp-input" id="pwConf" placeholder="••••••••" /></div>`;
    const foot = `<button class="cp-btn ghost" data-close>Cancel</button><button class="cp-btn primary" id="pwSave">Update password</button>`;
    const ov = open('Change password', body, foot);
    const colors = ['var(--busy)', 'var(--away)', 'var(--away)', 'var(--primary)'];
    ov.querySelector('#pwNew').addEventListener('input', e => { const sc = scorePw(e.target.value); ov.querySelectorAll('#pwStr span').forEach((b, i) => b.style.background = i < sc ? colors[Math.max(0, sc - 1)] : 'var(--line)'); });
    ov.querySelector('#pwSave').addEventListener('click', () => {
      const cur = ov.querySelector('#pwCur').value, nw = ov.querySelector('#pwNew').value, cf = ov.querySelector('#pwConf').value;
      if (!cur) return toast('Enter your current password');
      if (scorePw(nw) < 2) return toast('Choose a stronger password');
      if (nw !== cf) return toast('Passwords don’t match');
      closeAll(); toast('Password updated');
    });
  }

  /* ============ EXPORT DATA ============ */
  function openExport() {
    const body = `<p class="ac-note">We’ll bundle your messages, contacts and settings into a JSON file you can download.</p>
      <div class="ac-export" id="exBox">
        <div class="ac-export-row"><span>Messages &amp; conversations</span><b>${CP.conversations.length} chats</b></div>
        <div class="ac-export-row"><span>Contacts</span><b>${Object.keys(users).length} people</b></div>
        <div class="ac-export-row"><span>Settings &amp; preferences</span><b>included</b></div>
        <div class="ac-progress" id="exProg" style="display:none"><span id="exFill"></span></div>
      </div>`;
    const foot = `<button class="cp-btn ghost" data-close>Cancel</button><button class="cp-btn primary" id="exGo">Prepare export</button>`;
    const ov = open('Export my data', body, foot);
    ov.querySelector('#exGo').addEventListener('click', function () {
      const btn = this; btn.disabled = true; btn.textContent = 'Preparing…';
      ov.querySelector('#exProg').style.display = 'block';
      let p = 0; const fill = ov.querySelector('#exFill');
      const iv = setInterval(() => {
        p = Math.min(100, p + 12 + Math.random() * 10); fill.style.width = p + '%';
        if (p >= 100) {
          clearInterval(iv);
          const data = { exportedAt: new Date().toISOString(), profile: { name: me.name, username: me.username, bio: CP.bio }, prefs: CP.prefs, conversations: CP.conversations.map(c => ({ id: c.id, type: c.type, name: c.name || (users[c.with] && users[c.with].name), messages: (c.messages || []).map(m => ({ from: (users[m.user] || {}).name, t: m.t, text: m.text || (m.voice ? '[voice]' : m.poll ? '[poll]' : '[attachment]') })) })) };
          const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
          const a = document.createElement('a'); a.href = URL.createObjectURL(blob); a.download = 'chatpulse-export.json'; document.body.appendChild(a); a.click(); a.remove();
          btn.textContent = 'Downloaded ✓'; toast('Export ready — download started');
          setTimeout(closeAll, 700);
        }
      }, 260);
    });
  }

  /* ============ DELETE ACCOUNT ============ */
  function openDeleteAccount() {
    const body = `<div class="ac-danger-head"><span class="ac-danger-ic"><svg width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M12 3 3 19h18L12 3Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><path d="M12 10v4M12 17h.01" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg></span><div><b>This is permanent</b><span>Your profile, messages and group memberships will be deleted. This can’t be undone.</span></div></div>
      <div class="cp-row" style="margin-top:4px"><span class="cp-label">Type DELETE to confirm</span><input class="cp-input" id="delConf" placeholder="DELETE" autocomplete="off" /></div>`;
    const foot = `<button class="cp-btn ghost" data-close>Cancel</button><button class="cp-btn primary" id="delGo" disabled style="background:var(--busy);box-shadow:none">Delete account</button>`;
    const ov = open('Delete account', body, foot);
    const inp = ov.querySelector('#delConf'), go = ov.querySelector('#delGo');
    inp.addEventListener('input', () => go.disabled = inp.value.trim().toUpperCase() !== 'DELETE');
    go.addEventListener('click', () => { closeAll(); toast('Account scheduled for deletion'); setTimeout(() => { try { location.href = 'ChatPulse Auth.html'; } catch (e) {} }, 900); });
  }

  /* ============ CHAT WALLPAPER ============ */
  const WALLPAPERS = [{ id: 'default', label: 'Default' }, { id: 'mist', label: 'Mist' }, { id: 'dots', label: 'Dots' }, { id: 'grid', label: 'Grid' }, { id: 'emerald', label: 'Emerald' }, { id: 'dusk', label: 'Dusk' }];
  function openWallpaper() {
    let sel = CP.prefs.wallpaper || 'default';
    const body = `<p class="ac-note">Personalize your conversation background — it applies to every chat.</p>
      <div class="wp-list">${WALLPAPERS.map(w => `<button class="wp-opt ${w.id === sel ? 'on' : ''}" data-wp="${w.id}"><span class="wp-prev wp-${w.id}"></span><span class="wp-lbl">${w.label}</span></button>`).join('')}</div>`;
    const ov = open('Chat wallpaper', body, `<button class="cp-btn primary" data-close>Done</button>`);
    ov.querySelectorAll('[data-wp]').forEach(b => b.addEventListener('click', () => { sel = b.dataset.wp; ov.querySelectorAll('.wp-opt').forEach(x => x.classList.toggle('on', x === b)); CP.setPref('wallpaper', sel); }));
  }

  /* ============ STORAGE MANAGER ============ */
  function openStorage() {
    const items = [{ label: 'Photos', val: 142, color: '#10b981' }, { label: 'Files & documents', val: 64, color: '#2563eb' }, { label: 'Voice messages', val: 28, color: '#f59e0b' }, { label: 'Other', val: 14, color: '#94a3b8' }];
    const total = items.reduce((s, i) => s + i.val, 0);
    const body = `<p class="ac-note" id="stgTotal">${total} MB used across this workspace.</p>
      <div class="stg-bar">${items.map(i => `<span style="width:${(i.val / total * 100).toFixed(1)}%;background:${i.color}"></span>`).join('')}</div>
      <div>${items.map(i => `<div class="stg-leg"><span class="stg-dot" style="background:${i.color}"></span><span class="stg-leg-l">${i.label}</span><b>${i.val} MB</b></div>`).join('')}</div>`;
    const ov = open('Storage', body, `<button class="cp-btn ghost" data-close>Close</button><button class="cp-btn primary" id="stgClear">Clear cache · 42 MB</button>`);
    ov.querySelector('#stgClear').addEventListener('click', function () { this.disabled = true; this.textContent = 'Cleared ✓'; ov.querySelector('#stgTotal').textContent = (total - 42) + ' MB used across this workspace.'; toast('Cleared 42 MB of cached media'); });
  }

  /* ============ QUIET HOURS ============ */
  function openQuietHours() {
    const q = CP.prefs.quietHours || { on: false, from: '22:00', to: '07:00' };
    let on = q.on;
    const body = `<p class="ac-note">Pause alerts and sounds during set hours. Messages still arrive — just quietly.</p>
      <div class="cp-toggle-row" style="border-bottom:1px solid var(--line2)"><div><div class="tt">Enable quiet hours</div><div class="ts">Mute alerts overnight</div></div><button class="cp-switch ${on ? 'on' : ''}" id="qhOn"></button></div>
      <div class="qh-times" id="qhTimes" style="${on ? '' : 'opacity:.4;pointer-events:none'}">
        <div class="qh-field"><span class="cp-label">From</span><input type="time" class="cp-input" id="qhFrom" value="${q.from}" /></div>
        <div class="qh-field"><span class="cp-label">To</span><input type="time" class="cp-input" id="qhTo" value="${q.to}" /></div>
      </div>`;
    const ov = open('Quiet hours', body, `<button class="cp-btn ghost" data-close>Cancel</button><button class="cp-btn primary" id="qhSave">Save</button>`);
    ov.querySelector('#qhOn').addEventListener('click', e => { on = !on; e.currentTarget.classList.toggle('on', on); const t = ov.querySelector('#qhTimes'); t.style.opacity = on ? '' : '.4'; t.style.pointerEvents = on ? '' : 'none'; });
    ov.querySelector('#qhSave').addEventListener('click', () => { const from = ov.querySelector('#qhFrom').value, to = ov.querySelector('#qhTo').value; CP.setPref('quietHours', { on, from, to }); document.dispatchEvent(new CustomEvent('cp-quiet-change')); closeAll(); toast(on ? `Quiet hours on · ${from}–${to}` : 'Quiet hours off'); });
  }

  /* ============ ONBOARDING (first run) ============ */
  function startOnboarding(force) {
    if (!force && localStorage.getItem('cp-onboarded') === '1') return;
    document.querySelectorAll('.cp-ob').forEach(o => o.remove());
    let step = 0, grad = me.grad.slice(), name = me.name === 'Sara Karim' ? '' : me.name, bio = '', status = 'available';
    const ov = document.createElement('div'); ov.className = 'cp-ob';
    document.body.appendChild(ov);
    const steps = ['welcome', 'profile', 'status', 'notifs', 'done'];
    function finish(skip) {
      if (!skip) saveProfile({ name: name.trim() || me.name, grad, status, bio: bio.trim() });
      localStorage.setItem('cp-onboarded', '1');
      ov.remove();
      if (!skip) toast('Welcome to ChatPulse 👋');
    }
    function dots() { return `<div class="ob-dots">${steps.map((s, i) => `<span class="${i === step ? 'on' : ''} ${i < step ? 'done' : ''}"></span>`).join('')}</div>`; }
    function paint() {
      const k = steps[step];
      let inner = '';
      if (k === 'welcome') inner = `
        <div class="ob-logo"><svg width="34" height="34" viewBox="0 0 24 24" fill="none"><path d="M5 9.5C5 6.46 7.46 4 10.5 4h3C16.54 4 19 6.46 19 9.5S16.54 15 13.5 15H9l-3.2 2.9c-.5.46-1.3.1-1.3-.58V9.5Z" fill="#fff"/><circle cx="9.5" cy="9.5" r="1.3" fill="#10b981"/><circle cx="13.5" cy="9.5" r="1.3" fill="#10b981"/></svg></div>
        <h2>Welcome to ChatPulse</h2><p>Let’s set up your profile — it takes about 30 seconds.</p>
        <button class="ob-btn" data-next>Get started</button><button class="ob-skip" data-skip>Skip for now</button>`;
      else if (k === 'profile') inner = `
        <h2>Make it yours</h2><p>Choose an avatar color and tell people who you are.</p>
        <div id="obAv" class="ob-av">${av({ grad, initials: initialsOf(name) || me.initials }, 84)}</div>
        <div class="ep-sw-wrap" id="obSw" style="justify-content:center">${PALETTES.map((p, i) => `<button class="ep-sw ${sameGrad(p, grad) ? 'on' : ''}" data-g="${i}" style="background:linear-gradient(135deg,${p[0]},${p[1]})"></button>`).join('')}</div>
        <input class="ob-input" id="obName" maxlength="32" placeholder="Your name" value="${esc(name)}" />
        <textarea class="ob-input ob-ta" id="obBio" maxlength="160" placeholder="Short bio (optional)">${esc(bio)}</textarea>
        <button class="ob-btn" data-next ${name.trim() ? '' : 'disabled'}>Continue</button><button class="ob-skip" data-back>Back</button>`;
      else if (k === 'status') inner = `
        <h2>Set your vibe</h2><p>How are you showing up today? You can change this anytime.</p>
        <div class="st-types ob-status" id="obStatus">${[['available', 'Available', '#10b981'], ['busy', 'Busy', '#ef4444'], ['away', 'Away', '#f59e0b']].map(t => `<button class="st-type ${t[0] === status ? 'on' : ''}" data-s="${t[0]}" style="color:${t[2]}"><span class="d" style="background:${t[2]}"></span>${t[1]}</button>`).join('')}</div>
        <button class="ob-btn" data-next>Continue</button><button class="ob-skip" data-back>Back</button>`;
      else if (k === 'notifs') inner = `
        <div class="ob-logo ob-bell"><svg width="30" height="30" viewBox="0 0 24 24" fill="none"><path d="M6 9a6 6 0 0 1 12 0c0 5 2 6 2 6H4s2-1 2-6Z" stroke="#fff" stroke-width="1.8" stroke-linejoin="round"/><path d="M10 19a2 2 0 0 0 4 0" stroke="#fff" stroke-width="1.8"/></svg></div>
        <h2>Stay in the loop</h2><p>Turn on notifications so you don’t miss messages and mentions.</p>
        <div class="ob-toggle"><div><b>Desktop notifications</b><span>Alerts for new messages</span></div><button class="cp-switch ${CP.prefs.desktopNotifs ? 'on' : ''}" id="obNotif"></button></div>
        <button class="ob-btn" data-next>Continue</button><button class="ob-skip" data-back>Back</button>`;
      else inner = `
        <div class="ob-logo ob-check"><svg width="34" height="34" viewBox="0 0 24 24" fill="none"><path d="m5 12 4 4 10-10" stroke="#fff" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg></div>
        <h2>You’re all set${name.trim() ? ', ' + esc(name.trim().split(/\s+/)[0]) : ''}!</h2><p>Your workspace is ready. Jump in and start chatting.</p>
        <button class="ob-btn" data-finish>Enter ChatPulse</button>`;
      ov.innerHTML = `<div class="ob-card">${dots()}${inner}</div>`;
      // bind
      ov.querySelector('[data-next]')?.addEventListener('click', () => { collect(); step++; paint(); });
      ov.querySelector('[data-back]')?.addEventListener('click', () => { collect(); step--; paint(); });
      ov.querySelector('[data-skip]')?.addEventListener('click', () => finish(true));
      ov.querySelector('[data-finish]')?.addEventListener('click', () => finish(false));
      ov.querySelectorAll('#obSw [data-g]').forEach(b => b.addEventListener('click', () => { grad = PALETTES[+b.dataset.g]; ov.querySelectorAll('#obSw .ep-sw').forEach(x => x.classList.toggle('on', x === b)); ov.querySelector('#obAv').innerHTML = av({ grad, initials: initialsOf(ov.querySelector('#obName').value) || me.initials }, 84); }));
      ov.querySelector('#obName')?.addEventListener('input', e => { name = e.target.value; ov.querySelector('[data-next]').disabled = !name.trim(); ov.querySelector('#obAv').innerHTML = av({ grad, initials: initialsOf(name) || me.initials }, 84); });
      ov.querySelectorAll('#obStatus [data-s]').forEach(b => b.addEventListener('click', () => { status = b.dataset.s; ov.querySelectorAll('#obStatus .st-type').forEach(x => x.classList.toggle('on', x === b)); }));
      ov.querySelector('#obNotif')?.addEventListener('click', e => { const on = !e.currentTarget.classList.contains('on'); e.currentTarget.classList.toggle('on', on); CP.setPref('desktopNotifs', on); });
    }
    function collect() { const n = ov.querySelector('#obName'); if (n) name = n.value; const b = ov.querySelector('#obBio'); if (b) bio = b.value; }
    paint();
  }

  /* ============ STYLES ============ */
  const st = document.createElement('style'); st.id = 'cp-account-style';
  st.textContent = `
    /* feature gating from prefs */
    html[data-last-seen="0"] .pres,html[data-last-seen="0"] .rail-ava .pres{display:none!important;}
    html[data-read-receipts="0"] .ticks.read{display:none!important;}
    html[data-read-receipts="0"] .b-status-ic .tick.read{color:var(--text3)!important;}
    html[data-text-size="small"] .b-text{font-size:13px;}
    html[data-text-size="large"] .b-text{font-size:16.5px;line-height:1.55;}
    /* chat wallpapers (App #thread) */
    html[data-wallpaper="mist"] #thread{background:linear-gradient(180deg,var(--side),var(--bg));}
    html[data-wallpaper="dots"] #thread{background-color:var(--bg);background-image:radial-gradient(var(--line) 1px,transparent 1px);background-size:18px 18px;}
    html[data-wallpaper="grid"] #thread{background-color:var(--bg);background-image:linear-gradient(var(--line2) 1px,transparent 1px),linear-gradient(90deg,var(--line2) 1px,transparent 1px);background-size:22px 22px;}
    html[data-wallpaper="emerald"] #thread{background:radial-gradient(circle at 50% 0%,rgba(16,185,129,.08),var(--bg) 60%);}
    html[data-wallpaper="dusk"] #thread{background:linear-gradient(160deg,rgba(124,58,237,.07),var(--bg) 55%);}
    .wp-list{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;}
    .wp-opt{border:1px solid var(--line);border-radius:13px;padding:7px;display:flex;flex-direction:column;gap:7px;align-items:center;}
    .wp-opt.on{border-color:var(--primary);box-shadow:0 0 0 3px rgba(16,185,129,.14);}
    .wp-prev{width:100%;height:52px;border-radius:8px;border:1px solid var(--line2);background:var(--bg);}
    .wp-mist{background:linear-gradient(180deg,var(--side),var(--bg));}
    .wp-dots{background-color:var(--bg);background-image:radial-gradient(var(--line) 1px,transparent 1px);background-size:11px 11px;}
    .wp-grid{background-color:var(--bg);background-image:linear-gradient(var(--line2) 1px,transparent 1px),linear-gradient(90deg,var(--line2) 1px,transparent 1px);background-size:13px 13px;}
    .wp-emerald{background:radial-gradient(circle at 50% 0%,rgba(16,185,129,.2),var(--bg) 72%);}
    .wp-dusk{background:linear-gradient(160deg,rgba(124,58,237,.2),var(--bg) 66%);}
    .wp-lbl{font-size:11.5px;font-weight:700;color:var(--text2);}
    /* storage */
    .stg-bar{display:flex;height:12px;border-radius:99px;overflow:hidden;margin:6px 0 16px;background:var(--input);}
    .stg-bar span{display:block;height:100%;}
    .stg-leg{display:flex;align-items:center;gap:9px;padding:9px 0;border-bottom:1px solid var(--line2);font-size:13.5px;}
    .stg-leg:last-child{border-bottom:none;}
    .stg-leg b{margin-left:auto;color:var(--text2);font-weight:700;white-space:nowrap;}
    .stg-dot{width:10px;height:10px;border-radius:50%;flex-shrink:0;}
    .stg-leg-l{color:var(--text);}
    /* quiet hours */
    .qh-times{display:flex;gap:12px;margin-top:14px;transition:opacity .15s;}
    .qh-field{flex:1;}
    /* shared account modal bits */
    .ac-note{font-size:13px;color:var(--text3);line-height:1.5;margin:0 0 14px;}
    .ac-row{display:flex;align-items:center;gap:12px;padding:10px 0;border-bottom:1px solid var(--line2);}
    .ac-row:last-child{border-bottom:none;}
    .ac-rtx{flex:1;min-width:0;display:flex;flex-direction:column;}
    .ac-rtx b{font-size:14px;font-weight:700;display:flex;align-items:center;gap:8px;}
    .ac-rtx span{font-size:12.5px;color:var(--text3);margin-top:1px;}
    .ac-sm{height:34px!important;padding:0 14px!important;font-size:12.5px!important;flex-shrink:0;}
    .ac-empty{text-align:center;color:var(--text3);font-size:13.5px;padding:22px 0;}
    .sess-ic{width:40px;height:40px;border-radius:11px;background:var(--input);color:var(--text2);display:grid;place-items:center;flex-shrink:0;}
    .sess-now{font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.03em;color:var(--primary-dark);background:var(--primary-light);padding:1px 7px;border-radius:99px;white-space:nowrap;flex-shrink:0;}
    html.dark .sess-now{background:rgba(16,185,129,.16);color:#6ee7b7;}
    /* edit profile */
    .ep-top{display:flex;flex-direction:column;align-items:center;gap:11px;margin-bottom:18px;}
    .ep-sw-wrap{display:flex;flex-wrap:wrap;gap:8px;justify-content:center;max-width:320px;}
    .ep-sw{width:30px;height:30px;border-radius:50%;border:2px solid transparent;box-shadow:0 0 0 2px var(--card);}
    .ep-sw.on{border-color:var(--card);box-shadow:0 0 0 2px var(--primary);}
    .ep-hint{font-size:12px;color:var(--text3);}
    /* 2fa */
    .tf-enroll{display:flex;gap:16px;align-items:center;background:var(--bg);border:1px solid var(--line);border-radius:14px;padding:14px;}
    .tf-qr{width:104px;height:104px;border-radius:10px;background:#fff;display:grid;place-items:center;flex-shrink:0;padding:6px;}
    .tf-key code{font-family:ui-monospace,monospace;font-size:15px;font-weight:700;letter-spacing:.08em;color:var(--text);}
    .tf-otp{display:flex;gap:8px;}
    .tf-box{width:100%;height:50px;border:1px solid var(--line);border-radius:11px;background:var(--bg);text-align:center;font-size:20px;font-weight:800;color:var(--text);outline:none;}
    .tf-box:focus{border-color:var(--primary);box-shadow:0 0 0 4px rgba(16,185,129,.12);}
    .tf-box.filled{border-color:var(--primary);}
    .ac-2fa-on{display:flex;gap:13px;align-items:center;}
    .ac-2fa-ic{width:46px;height:46px;border-radius:13px;background:var(--primary-light);color:var(--primary-dark);display:grid;place-items:center;flex-shrink:0;}
    html.dark .ac-2fa-ic{background:rgba(16,185,129,.16);color:#6ee7b7;}
    .ac-2fa-on b{font-size:15px;font-weight:800;display:block;}
    .ac-2fa-on span{font-size:13px;color:var(--text3);}
    /* strength */
    .ac-strength{display:flex;gap:6px;margin-top:9px;}
    .ac-strength span{height:5px;flex:1;border-radius:99px;background:var(--line);transition:background .2s;}
    /* export */
    .ac-export{border:1px solid var(--line);border-radius:14px;padding:6px 14px;}
    .ac-export-row{display:flex;align-items:center;justify-content:space-between;padding:11px 0;border-bottom:1px solid var(--line2);font-size:13.5px;}
    .ac-export-row:last-of-type{border-bottom:none;}
    .ac-export-row b{font-weight:700;color:var(--text2);}
    .ac-progress{height:7px;border-radius:99px;background:var(--input);overflow:hidden;margin:6px 0 12px;}
    .ac-progress span{display:block;height:100%;width:0;background:var(--primary);border-radius:99px;transition:width .25s;}
    /* delete */
    .ac-danger-head{display:flex;gap:13px;align-items:flex-start;margin-bottom:6px;}
    .ac-danger-ic{width:46px;height:46px;border-radius:13px;background:#fee2e2;color:var(--busy);display:grid;place-items:center;flex-shrink:0;}
    html.dark .ac-danger-ic{background:rgba(239,68,68,.16);}
    .ac-danger-head b{font-size:15px;font-weight:800;display:block;margin-bottom:3px;}
    .ac-danger-head span{font-size:13px;color:var(--text3);line-height:1.5;}
    /* onboarding */
    .cp-ob{position:fixed;inset:0;z-index:300;background:rgba(8,12,10,.62);backdrop-filter:blur(6px);display:flex;align-items:center;justify-content:center;padding:22px;}
    .ob-card{width:100%;max-width:420px;background:var(--card);border:1px solid var(--line);border-radius:24px;box-shadow:0 40px 90px -30px rgba(0,0,0,.55);padding:34px 30px 30px;text-align:center;display:flex;flex-direction:column;align-items:center;}
    @media (prefers-reduced-motion: no-preference){.ob-card{animation:obPop .3s cubic-bezier(.2,.8,.2,1);}}
    @keyframes obPop{from{transform:translateY(16px) scale(.97);}to{transform:none;}}
    .ob-dots{display:flex;gap:7px;margin-bottom:22px;}
    .ob-dots span{width:7px;height:7px;border-radius:99px;background:var(--line);transition:.2s;}
    .ob-dots span.on{width:22px;background:var(--primary);}
    .ob-dots span.done{background:var(--primary);opacity:.5;}
    .ob-logo{width:72px;height:72px;border-radius:22px;background:linear-gradient(135deg,#10b981,#0891b2);display:grid;place-items:center;margin-bottom:20px;box-shadow:0 16px 36px -14px rgba(16,185,129,.7);}
    .ob-bell{background:linear-gradient(135deg,#f59e0b,#ea580c);box-shadow:0 16px 36px -14px rgba(245,158,11,.7);}
    .ob-check{background:linear-gradient(135deg,#10b981,#059669);}
    .ob-card h2{font-size:23px;font-weight:800;letter-spacing:-.02em;margin:0 0 8px;color:var(--text);}
    .ob-card p{font-size:14px;color:var(--text3);line-height:1.55;margin:0 0 22px;max-width:300px;}
    .ob-av{margin-bottom:16px;}
    .ob-input{width:100%;border:1px solid var(--line);background:var(--bg);border-radius:12px;padding:12px 14px;font-size:14.5px;font-family:inherit;color:var(--text);outline:none;margin-top:12px;}
    .ob-input:focus{border-color:var(--primary);box-shadow:0 0 0 4px rgba(16,185,129,.12);}
    .ob-ta{resize:none;min-height:58px;line-height:1.5;}
    .ob-status{width:100%;margin-bottom:6px;}
    .ob-toggle{display:flex;align-items:center;justify-content:space-between;gap:12px;width:100%;border:1px solid var(--line);border-radius:14px;padding:14px 16px;margin-bottom:6px;text-align:left;}
    .ob-toggle b{font-size:14px;font-weight:700;display:block;}
    .ob-toggle span{font-size:12.5px;color:var(--text3);}
    .ob-btn{width:100%;height:50px;border-radius:14px;background:var(--primary);color:#fff;font-size:15px;font-weight:800;margin-top:22px;box-shadow:0 12px 26px -10px rgba(16,185,129,.8);transition:.15s;}
    .ob-btn:hover{background:var(--primary-hover);}
    .ob-btn:disabled{background:var(--text3);box-shadow:none;cursor:not-allowed;}
    .ob-skip{margin-top:12px;font-size:13.5px;font-weight:700;color:var(--text3);}
    .ob-skip:hover{color:var(--text2);}
  `;
  document.head.appendChild(st);
  applyPrefsToDoc();
  reflectIdentity();

  window.CPAccount = {
    openEditProfile, openBlocked, openSessions, open2FA, openChangePassword, openExport, openDeleteAccount,
    openWallpaper, openStorage, openQuietHours,
    startOnboarding, isBlocked, block, unblock, blockedCount: () => blocked.length, reflectIdentity, updateProfile: saveProfile,
  };
})();
