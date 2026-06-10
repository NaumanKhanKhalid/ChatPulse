/* ChatPulse — profile, group detail / invite, and call overlays */
window.CPOverlays = (function () {
  const { me, users, conversations } = window.CP;
  const esc = s => (s || '').replace(/[&<>"]/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]));
  const av = (u, s) => `<div class="avatar" style="width:${s}px;height:${s}px;background:linear-gradient(135deg,${u.grad[0]},${u.grad[1]});font-size:${s * .38}px">${u.initials}</div>`;
  const sc = { available: '#10b981', busy: '#ef4444', away: '#f59e0b' };
  const scLabel = { available: 'Available', busy: 'Busy', away: 'Away' };
  const bios = { 2: 'Frontend engineer. Alpine + Tailwind enthusiast.', 3: 'PM @ Northwind. Turning chaos into roadmaps.', 4: 'Backend & infra. Queues, Reverb, the works.', 5: 'Research lead. Talks to users so you don\u2019t have to.', 6: 'Brand & motion designer. Coffee-powered.', 7: 'Design systems. Pixels with opinions.', 9: 'Keeping ChatPulse running smoothly.', 1: 'Designing calm, fast interfaces.' };

  if (!document.getElementById('cp-overlay-style')) {
    const st = document.createElement('style'); st.id = 'cp-overlay-style';
    st.textContent = `
    .pf-hero{display:flex;flex-direction:column;align-items:center;text-align:center;padding:6px 0 4px;}
    .pf-name{font-size:20px;font-weight:800;margin-top:12px;display:flex;align-items:center;gap:8px;}
    .pf-user{font-size:13px;color:var(--text3);margin-top:1px;}
    .pf-status{display:inline-flex;align-items:center;gap:6px;font-size:12.5px;font-weight:700;padding:4px 11px;border-radius:99px;background:var(--input);color:var(--text2);margin-top:12px;}
    .pf-bio{font-size:13.5px;color:var(--text2);line-height:1.55;text-align:center;margin:16px 0;}
    .pf-actions{display:flex;gap:10px;margin-top:6px;}
    .pf-act{flex:1;height:44px;border-radius:12px;font-size:13.5px;font-weight:700;display:flex;align-items:center;justify-content:center;gap:7px;border:1px solid var(--line);color:var(--text2);}
    .pf-act:hover{border-color:var(--primary);color:var(--primary-dark);}
    .pf-act.primary{background:var(--primary);color:#fff;border-color:var(--primary);box-shadow:0 8px 18px -8px rgba(16,185,129,.8);}
    .pf-act.primary:hover{background:var(--primary-hover);color:#fff;}
    html.dark .pf-act:hover{color:#6ee7b7;}
    .pf-meta{margin-top:18px;border-top:1px solid var(--line);padding-top:14px;}
    .pf-chip{display:inline-flex;align-items:center;gap:6px;font-size:12px;font-weight:700;padding:5px 10px;border-radius:99px;background:var(--input);color:var(--text2);margin:0 6px 6px 0;}
    /* group detail */
    .gd-sec{margin-top:18px;}
    .gd-sec-h{font-size:11.5px;font-weight:800;text-transform:uppercase;letter-spacing:.04em;color:var(--text3);margin-bottom:10px;display:flex;align-items:center;justify-content:space-between;}
    .gd-add{font-size:12px;font-weight:700;color:var(--primary-dark);}
    html.dark .gd-add{color:#6ee7b7;}
    .gd-mem{display:flex;align-items:center;gap:11px;padding:7px 8px;border-radius:11px;}
    .gd-mem:hover{background:var(--hover);}
    .gd-mem-name{font-size:13.5px;font-weight:700;flex:1;display:flex;align-items:center;gap:6px;}
    .gd-role{font-size:9.5px;font-weight:800;text-transform:uppercase;color:var(--primary-dark);background:var(--primary-light);padding:1px 6px;border-radius:99px;}
    html.dark .gd-role{background:rgba(16,185,129,.16);color:#6ee7b7;}
    .gd-rm{width:28px;height:28px;border-radius:8px;color:var(--text3);display:grid;place-items:center;}
    .gd-rm:hover{background:rgba(239,68,68,.12);color:var(--busy);}
    .invite-box{display:flex;gap:8px;align-items:center;border:1px solid var(--line);border-radius:12px;padding:6px 6px 6px 13px;background:var(--bg);}
    .invite-url{flex:1;font-size:13px;font-family:ui-monospace,monospace;color:var(--text2);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
    .invite-copy{height:34px;padding:0 14px;border-radius:9px;background:var(--primary);color:#fff;font-size:12.5px;font-weight:700;flex-shrink:0;}
    .invite-copy:hover{background:var(--primary-hover);}
    .invite-meta{font-size:12px;color:var(--text3);margin-top:8px;display:flex;align-items:center;justify-content:space-between;}
    .invite-regen{font-weight:700;color:var(--text2);}
    .invite-regen:hover{color:var(--primary-dark);}
    .gd-danger{display:flex;gap:10px;margin-top:18px;border-top:1px solid var(--line);padding-top:16px;}
    .gd-danger button{flex:1;height:42px;border-radius:11px;font-size:13px;font-weight:700;border:1px solid var(--line);color:var(--text2);}
    .gd-danger .leave:hover{border-color:var(--away);color:var(--away);}
    .gd-danger .del{color:var(--busy);border-color:#fecaca;}
    html.dark .gd-danger .del{border-color:rgba(239,68,68,.3);}
    .gd-danger .del:hover{background:rgba(239,68,68,.08);}
    /* group mgmt additions */
    .gd-edit{width:34px;height:34px;border-radius:10px;display:grid;place-items:center;color:var(--text3);position:absolute;top:0;right:0;}
    .gd-edit:hover{background:var(--hover);color:var(--text);}
    .gd-mute{display:flex;align-items:center;gap:11px;border:1px solid var(--line);border-radius:13px;padding:11px 13px;margin-top:16px;}
    .gd-mute-ic{width:34px;height:34px;border-radius:9px;background:var(--input);color:var(--text2);display:grid;place-items:center;flex-shrink:0;}
    .gd-mute-tx{flex:1;min-width:0;}
    .gd-mute-tx b{font-size:13.5px;font-weight:700;display:block;}
    .gd-mute-tx span{font-size:12px;color:var(--text3);}
    .gd-dur{margin-top:10px;}
    .gd-kebab{width:28px;height:28px;border-radius:8px;color:var(--text3);display:grid;place-items:center;flex-shrink:0;}
    .gd-kebab:hover{background:var(--hover);color:var(--text);}
    .gd-menu{position:fixed;z-index:210;min-width:184px;background:var(--card);border:1px solid var(--line);border-radius:13px;padding:6px;box-shadow:0 16px 40px -12px rgba(0,0,0,.4);}
    .gd-mi{display:block;width:100%;text-align:left;padding:9px 11px;border-radius:9px;font-size:13.5px;font-weight:600;color:var(--text2);}
    .gd-mi:hover{background:var(--hover);color:var(--text);}
    .gd-mi.danger{color:var(--busy);}
    .gd-mi.danger:hover{background:rgba(239,68,68,.1);}
    .gd-back{display:inline-flex;align-items:center;gap:5px;font-size:13px;font-weight:700;color:var(--text2);margin-bottom:14px;}
    .gd-back:hover{color:var(--primary-dark);}
    html.dark .gd-back:hover{color:#6ee7b7;}
    .gd-add-foot{display:flex;justify-content:flex-end;margin-top:18px;padding-top:14px;border-top:1px solid var(--line);}
    .cp-confirm{z-index:240;}
    .gd-sec-h>span:first-child{white-space:nowrap;}
    /* CALL */
    .call-ov{position:fixed;inset:0;z-index:200;background:radial-gradient(circle at 50% 35%,#1f2937,#070b09);display:flex;flex-direction:column;align-items:center;justify-content:center;color:#fff;animation:cpFadeC .25s both;}
    @keyframes cpFadeC{from{opacity:0}to{opacity:1}}
    .call-remote{position:absolute;inset:0;display:grid;place-items:center;overflow:hidden;}
    .call-remote .vbg{position:absolute;inset:0;opacity:.5;filter:blur(2px);}
    .call-self{position:absolute;bottom:104px;right:24px;width:150px;height:200px;border-radius:18px;overflow:hidden;border:2px solid rgba(255,255,255,.25);display:grid;place-items:center;box-shadow:0 16px 40px -12px rgba(0,0,0,.6);}
    .ring-av{position:relative;display:grid;place-items:center;}
    .ring-av::before,.ring-av::after{content:"";position:absolute;inset:-10px;border-radius:50%;border:2px solid rgba(255,255,255,.3);animation:ring 2s ease-out infinite;}
    .ring-av::after{animation-delay:1s;}
    @keyframes ring{0%{transform:scale(1);opacity:.7}100%{transform:scale(1.7);opacity:0}}
    .call-name{font-size:26px;font-weight:800;margin-top:26px;letter-spacing:-.01em;}
    .call-state{font-size:14.5px;color:rgba(255,255,255,.65);margin-top:7px;font-weight:600;}
    .call-controls{position:absolute;bottom:34px;left:50%;transform:translateX(-50%);display:flex;gap:16px;align-items:center;z-index:2;}
    .cc{width:58px;height:58px;border-radius:50%;background:rgba(255,255,255,.14);color:#fff;display:grid;place-items:center;backdrop-filter:blur(8px);transition:.15s;}
    .cc:hover{background:rgba(255,255,255,.24);}
    .cc.off{background:#fff;color:#111827;}
    .cc.end{background:#ef4444;width:64px;height:64px;}
    .cc.end:hover{background:#dc2626;}
    .cc.accept{background:#10b981;width:64px;height:64px;}
    .cc.accept:hover{background:#059669;}
    .call-ring-actions{position:absolute;bottom:60px;left:50%;transform:translateX(-50%);display:flex;gap:60px;}
    .cra{display:flex;flex-direction:column;align-items:center;gap:9px;font-size:13px;font-weight:600;color:rgba(255,255,255,.8);}
    /* notifications dropdown */
    .cp-notif{position:fixed;width:360px;max-width:calc(100vw - 24px);background:var(--card);border:1px solid var(--line);border-radius:16px;box-shadow:0 20px 50px -16px rgba(0,0,0,.35);z-index:150;overflow:hidden;animation:notifIn .18s ease both;}
    @keyframes notifIn{from{opacity:0;transform:translateY(-8px)}to{opacity:1;transform:none}}
    .cp-notif-head{display:flex;align-items:center;justify-content:space-between;padding:14px 16px;border-bottom:1px solid var(--line2);}
    .cp-notif-head h4{font-size:15px;font-weight:800;margin:0;}
    .cp-notif-clear{font-size:12px;font-weight:700;color:var(--primary-dark);}
    html.dark .cp-notif-clear{color:#6ee7b7;}
    .cp-notif-list{max-height:400px;overflow-y:auto;}
    .cp-notif-sec{font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.04em;color:var(--text3);padding:11px 16px 5px;}
    .cp-notif-item{display:flex;gap:11px;padding:10px 16px;align-items:flex-start;position:relative;cursor:pointer;}
    .cp-notif-item:hover{background:var(--hover);}
    .cp-notif-item.unread{background:var(--primary-light);}
    html.dark .cp-notif-item.unread{background:rgba(16,185,129,.08);}
    .cp-notif-item.unread::before{content:"";position:absolute;left:7px;top:18px;width:6px;height:6px;border-radius:50%;background:var(--primary);}
    .cp-notif-ava{position:relative;flex-shrink:0;}
    .cp-notif-badge{position:absolute;bottom:-2px;right:-2px;width:18px;height:18px;border-radius:50%;display:grid;place-items:center;border:2px solid var(--card);}
    .cp-notif-txt{flex:1;min-width:0;font-size:13px;line-height:1.45;color:var(--text);}
    .cp-notif-txt b{font-weight:800;}
    .cp-notif-time{font-size:11px;color:var(--text3);margin-top:2px;}
    .cp-notif-foot{padding:10px;border-top:1px solid var(--line2);}
    .cp-notif-foot a{display:block;text-align:center;font-size:13px;font-weight:700;color:var(--primary-dark);padding:8px;border-radius:10px;}
    .cp-notif-foot a:hover{background:var(--hover);}
    html.dark .cp-notif-foot a{color:#6ee7b7;}
    `;
    document.head.appendChild(st);
  }
  const M = () => window.CPModals;

  /* ============ PROFILE ============ */
  function openProfile(uid) {
    const u = users[uid]; if (!u) return;
    const mutual = conversations.filter(c => c.type === 'group' && c.members.includes(uid) && c.members.includes(me.id)).map(c => c.name);
    const online = u.online;
    const body = `
      <div class="pf-hero">${av(u, 84)}
        <div class="pf-name">${esc(u.name)}${u.guest ? '<span class="gd-role" style="background:#fef3c7;color:#b45309">guest</span>' : ''}${u.role === 'admin' ? '<span class="gd-role" style="background:var(--text2);color:#fff">admin</span>' : ''}</div>
        <div class="pf-user">@${esc(u.username)}</div>
        <span class="pf-status"><span style="width:8px;height:8px;border-radius:50%;background:${online ? sc[u.status] : '#9ca3af'}"></span>${online ? scLabel[u.status] : 'Offline' + (u.last ? ' \u00b7 ' + u.last : '')}</span>
        <p class="pf-bio">${esc(bios[uid] || 'ChatPulse member.')}</p>
      </div>
      <div class="pf-actions">
        <a class="pf-act primary" href="ChatPulse App.html"><svg width="17" height="17" viewBox="0 0 24 24" fill="none"><path d="M4 6.5C4 5.12 5.12 4 6.5 4h11C18.88 4 20 5.12 20 6.5v7c0 1.38-1.12 2.5-2.5 2.5H10l-3.6 3a1 1 0 0 1-1.65-.77V6.5Z" stroke="currentColor" stroke-width="1.8"/></svg>Message</a>
        <button class="pf-act" data-pcall="audio"><svg width="17" height="17" viewBox="0 0 24 24" fill="none"><path d="M6.2 4.5 8 4l1.6 3.4-1.5 1.3a11 11 0 0 0 4.7 4.7l1.3-1.5L17.5 15l-.5 1.8c-.2.7-.9 1.1-1.6 1A14 14 0 0 1 4.2 6.6c-.1-.7.3-1.4 1-1.6Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/></svg>Call</button>
        <button class="pf-act" data-pcall="video"><svg width="17" height="17" viewBox="0 0 24 24" fill="none"><path d="M15 10.5 20 7v10l-5-3.5M4 7.5C4 6.7 4.7 6 5.5 6h8c.8 0 1.5.7 1.5 1.5v9c0 .8-.7 1.5-1.5 1.5h-8C4.7 18 4 17.3 4 16.5v-9Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/></svg>Video</button>
      </div>
      ${mutual.length ? `<div class="pf-meta"><div class="gd-sec-h" style="margin-bottom:10px">Mutual groups</div>${mutual.map(g => `<span class="pf-chip"><svg width="12" height="12" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="8" stroke="currentColor" stroke-width="1.8"/></svg>${esc(g)}</span>`).join('')}</div>` : ''}
      <div class="pf-danger" style="display:flex;gap:9px;margin-top:18px;padding-top:16px;border-top:1px solid var(--line)">
        <button class="pf-act" data-preport style="flex:1;color:var(--busy)"><svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M5 21V4m0 1h11l-2 4 2 4H5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>Report</button>
        <button class="pf-act" data-pblock style="flex:1;color:var(--busy)"><svg width="16" height="16" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="8.5" stroke="currentColor" stroke-width="1.7"/><path d="m6 6 12 12" stroke="currentColor" stroke-width="1.7"/></svg>Block</button>
      </div>`;
    const ov = M().__open ? M().__open('Profile', body) : open('Profile', body);
    ov.querySelectorAll('[data-pcall]').forEach(b => b.addEventListener('click', () => { M().close(); openCall(u, b.dataset.pcall, false); }));
    ov.querySelector('[data-preport]')?.addEventListener('click', () => CPModals.openReport({ kind: 'user', name: u.name, preview: '@' + u.username }, () => {}));
    ov.querySelector('[data-pblock]')?.addEventListener('click', () => { window.CPAccount && CPAccount.block(u.id); M().close(); CPModals.toast(u.name + ' blocked'); });
  }

  /* ============ GROUP DETAIL + MANAGEMENT ============ */
  const GPALETTES = [['#818cf8', '#7c3aed'], ['#2dd4bf', '#0891b2'], ['#34d399', '#059669'], ['#fcd34d', '#ea580c'], ['#f0abfc', '#a21caf'], ['#fda4af', '#e11d48'], ['#7dd3fc', '#2563eb'], ['#94a3b8', '#334155']];
  function openGroupDetail(g) {
    let conv = null;
    if (typeof g === 'string') { conv = conversations.find(x => x.id === g); g = { name: conv.name, initials: conv.initials, grad: conv.grad, desc: conv.desc, ids: conv.members, pub: conv.public }; }
    const ids = g.ids || (g.ids = []);
    if (!g.admins) g.admins = new Set([ids[0]]);
    const admins = g.admins;
    if (!g.owner) g.owner = ids[0];
    admins.add(g.owner);
    const isAdmin = true; // current user is the group owner/admin in this prototype
    let token = genToken();
    let ov;

    const notify = () => { if (window.CP && CP.__onGroupChange) CP.__onGroupChange(); };
    const syncCount = () => { g.members = ids.length; if (conv) conv.members = ids; };
    const closeMenus = () => document.querySelectorAll('.gd-menu').forEach(m => m.remove());

    function mainBody() {
      const muteSub = g.muted ? ('Muted' + (g.muteUntil ? ' · ' + g.muteUntil : '')) : 'Notifications on';
      return `
      <div class="pf-hero" style="position:relative;padding-bottom:14px;border-bottom:1px solid var(--line)">
        ${isAdmin ? '<button class="gd-edit" data-editgrp title="Edit group"><svg width="17" height="17" viewBox="0 0 24 24" fill="none"><path d="M14.5 4.5 19.5 9.5 9 20l-5 1 1-5L14.5 4.5Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/></svg></button>' : ''}
        ${av(g, 76)}
        <div class="pf-name" style="margin-top:12px;display:block;text-align:center;line-height:1.22">${esc(g.name)}</div>
        <div class="pf-user" style="margin-top:5px"><span class="gd-role" style="background:${g.pub ? 'var(--primary-light)' : '#fee2e2'};color:${g.pub ? 'var(--primary-dark)' : '#b91c1c'}">${g.pub ? 'Public' : 'Private'}</span> ${ids.length} members</div>
        <p class="pf-bio" style="margin-bottom:0">${esc(g.desc || 'No description')}</p>
      </div>
      <div class="gd-mute">
        <span class="gd-mute-ic"><svg width="17" height="17" viewBox="0 0 24 24" fill="none"><path d="M4 9v6h4l5 4V5L8 9H4Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>${g.muted ? '<path d="M16 8l5 8M21 8l-5 8" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>' : '<path d="M16.5 8.5a5 5 0 0 1 0 7" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>'}</svg></span>
        <div class="gd-mute-tx"><b>Mute notifications</b><span>${muteSub}</span></div>
        <button class="cp-switch ${g.muted ? 'on' : ''}" data-mute></button>
      </div>
      ${g.muted ? `<select class="cp-select gd-dur" data-mutedur><option ${g.muteUntil === 'For 8 hours' ? 'selected' : ''}>For 8 hours</option><option ${g.muteUntil === 'For 1 week' ? 'selected' : ''}>For 1 week</option><option ${(!g.muteUntil || g.muteUntil === 'Always') ? 'selected' : ''}>Always</option></select>` : ''}
      <div class="gd-sec"><div class="gd-sec-h"><span>Members · ${ids.length}</span>${isAdmin ? '<button class="gd-add" data-addmem><svg width="13" height="13" viewBox="0 0 24 24" fill="none" style="vertical-align:-2px"><path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg> Add</button>' : ''}</div>
        ${ids.map(id => { const u = users[id]; if (!u) return ''; const meRow = id === me.id; const badge = id === g.owner ? '<span class="gd-role" style="background:#fef3c7;color:#b45309">owner</span>' : (admins.has(id) ? '<span class="gd-role">admin</span>' : ''); return `<div class="gd-mem">${av(u, 34)}<span class="gd-mem-name">${esc(u.name)}${meRow ? ' <span style="color:var(--text3);font-weight:600">(you)</span>' : ''}${badge}</span>${isAdmin && !meRow ? `<button class="gd-kebab" data-memmenu="${id}"><svg width="16" height="16" viewBox="0 0 24 24" fill="none"><circle cx="5" cy="12" r="1.6" fill="currentColor"/><circle cx="12" cy="12" r="1.6" fill="currentColor"/><circle cx="19" cy="12" r="1.6" fill="currentColor"/></svg></button>` : ''}</div>`; }).join('')}
      </div>
      ${isAdmin ? `<div class="gd-sec"><div class="gd-sec-h">Invite link</div>
        <div class="invite-box"><span class="invite-url" id="invUrl">chatpulse.app/join/${token}</span><button class="invite-copy" id="invCopy">Copy</button></div>
        <div class="invite-meta"><span>Expires in 7 days</span><button class="invite-regen" id="invRegen">Regenerate</button></div></div>` : ''}
      <div class="gd-danger"><button class="leave">Leave group</button>${isAdmin ? '<button class="del">Delete group</button>' : ''}</div>`;
    }

    function paint() { ov.querySelector('.cp-title').textContent = 'Group details'; ov.querySelector('.cp-body').innerHTML = mainBody(); bindMain(); }

    function bindMain() {
      ov.querySelector('[data-editgrp]')?.addEventListener('click', showEdit);
      ov.querySelector('[data-addmem]')?.addEventListener('click', showAdd);
      ov.querySelector('[data-mute]')?.addEventListener('click', () => { g.muted = !g.muted; if (g.muted && !g.muteUntil) g.muteUntil = 'Always'; if (conv) conv.muted = g.muted; paint(); M().toast(g.muted ? 'Group muted' : 'Group unmuted'); notify(); });
      ov.querySelector('[data-mutedur]')?.addEventListener('change', e => { g.muteUntil = e.target.value; if (conv) conv.muted = true; M().toast('Muted ' + e.target.value.toLowerCase()); paint(); });
      ov.querySelectorAll('[data-memmenu]').forEach(b => b.addEventListener('click', e => { e.stopPropagation(); memberMenu(b, +b.dataset.memmenu); }));
      ov.querySelector('#invCopy')?.addEventListener('click', () => { navigator.clipboard?.writeText(ov.querySelector('#invUrl').textContent); M().toast('Invite link copied'); });
      ov.querySelector('#invRegen')?.addEventListener('click', () => { token = genToken(); ov.querySelector('#invUrl').textContent = 'chatpulse.app/join/' + token; M().toast('New invite link generated'); });
      ov.querySelector('.leave')?.addEventListener('click', () => confirmDialog('Leave group', `Leave <b>${esc(g.name)}</b>? You’ll stop receiving its messages.`, 'Leave', true, () => { M().close(); if (CP.__onGroupLeave) CP.__onGroupLeave(g); M().toast('You left ' + g.name); }));
      ov.querySelector('.del')?.addEventListener('click', () => confirmDialog('Delete group', `Permanently delete <b>${esc(g.name)}</b> for everyone? This can’t be undone.`, 'Delete', true, () => { M().close(); if (CP.__onGroupDelete) CP.__onGroupDelete(g); M().toast('Group deleted'); }));
    }

    function memberMenu(btn, id) {
      closeMenus();
      const u = users[id];
      const isOwner = id === g.owner;
      const menu = document.createElement('div'); menu.className = 'gd-menu';
      let html = `<button class="gd-mi" data-mi="profile">View profile</button>`;
      if (!isOwner) {
        html += admins.has(id) ? `<button class="gd-mi" data-mi="demote">Remove as admin</button>` : `<button class="gd-mi" data-mi="promote">Make admin</button>`;
        html += `<button class="gd-mi" data-mi="transfer">Transfer ownership</button>`;
        html += `<button class="gd-mi danger" data-mi="remove">Remove from group</button>`;
      }
      menu.innerHTML = html;
      document.body.appendChild(menu);
      const r = btn.getBoundingClientRect();
      menu.style.top = Math.min(r.bottom + 6, window.innerHeight - 190) + 'px';
      menu.style.left = Math.max(12, r.right - 184) + 'px';
      menu.querySelector('[data-mi="profile"]').addEventListener('click', () => { closeMenus(); openProfile(id); });
      menu.querySelector('[data-mi="promote"]')?.addEventListener('click', () => { admins.add(id); closeMenus(); paint(); M().toast(u.name + ' is now an admin'); notify(); });
      menu.querySelector('[data-mi="demote"]')?.addEventListener('click', () => { admins.delete(id); closeMenus(); paint(); M().toast(u.name + ' is no longer an admin'); notify(); });
      menu.querySelector('[data-mi="transfer"]')?.addEventListener('click', () => { closeMenus(); confirmDialog('Transfer ownership', `Make <b>${esc(u.name)}</b> the owner of ${esc(g.name)}? You’ll stay an admin.`, 'Transfer', false, () => { g.owner = id; admins.add(id); paint(); M().toast(u.name + ' is now the owner'); notify(); }); });
      menu.querySelector('[data-mi="remove"]')?.addEventListener('click', () => { closeMenus(); confirmDialog('Remove member', `Remove <b>${esc(u.name)}</b> from ${esc(g.name)}?`, 'Remove', true, () => { const i = ids.indexOf(id); if (i > -1) ids.splice(i, 1); admins.delete(id); syncCount(); paint(); M().toast(u.name + ' removed'); notify(); }); });
      setTimeout(() => document.addEventListener('click', function h(e) { if (!menu.contains(e.target)) { menu.remove(); document.removeEventListener('click', h); } }), 0);
    }

    function showAdd() {
      ov.querySelector('.cp-title').textContent = 'Add members';
      const candidates = Object.values(users).filter(u => !ids.includes(u.id) && !u.guest && u.id !== 50);
      const sel = new Set();
      ov.querySelector('.cp-body').innerHTML = `
        <button class="gd-back" data-back><svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M15 5l-7 7 7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>Back</button>
        <input class="cp-input" id="amSearch" placeholder="Search people…" style="margin-bottom:12px" />
        <div id="amList" style="max-height:300px;overflow-y:auto;margin:0 -6px">${candidates.length ? candidates.map(u => `<div class="nc-item" data-add="${u.id}"><span class="nc-avwrap">${av(u, 38)}</span><span class="nc-info"><span class="nc-name">${esc(u.name)}</span><span class="nc-sub">@${esc(u.username)}</span></span><span class="cp-check"><svg width="13" height="13" viewBox="0 0 24 24" fill="none"><path d="m5 12 4 4 10-10" stroke="#fff" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"/></svg></span></div>`).join('') : '<div class="nc-empty">Everyone’s already in this group.</div>'}</div>
        <div class="gd-add-foot"><button class="cp-btn primary" id="amAdd" disabled>Add selected</button></div>`;
      const addBtn = ov.querySelector('#amAdd');
      ov.querySelector('[data-back]').addEventListener('click', paint);
      ov.querySelectorAll('[data-add]').forEach(it => it.addEventListener('click', () => { const id = +it.dataset.add; if (sel.has(id)) { sel.delete(id); it.classList.remove('on'); } else { sel.add(id); it.classList.add('on'); } addBtn.disabled = !sel.size; addBtn.textContent = sel.size ? `Add ${sel.size}` : 'Add selected'; }));
      ov.querySelector('#amSearch').addEventListener('input', e => { const q = e.target.value.toLowerCase(); ov.querySelectorAll('[data-add]').forEach(it => { const u = users[+it.dataset.add]; it.style.display = (u.name.toLowerCase().includes(q) || u.username.toLowerCase().includes(q)) ? '' : 'none'; }); });
      addBtn.addEventListener('click', () => { sel.forEach(id => { if (!ids.includes(id)) ids.push(id); }); syncCount(); paint(); M().toast(sel.size + ' member' + (sel.size > 1 ? 's' : '') + ' added'); notify(); });
    }

    function showEdit() {
      ov.querySelector('.cp-title').textContent = 'Edit group';
      let grad = g.grad.slice(), pub = g.pub;
      ov.querySelector('.cp-body').innerHTML = `
        <button class="gd-back" data-back><svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M15 5l-7 7 7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>Back</button>
        <div class="ep-top"><div id="egAv">${av({ grad, initials: g.initials }, 72)}</div>
          <div class="ep-sw-wrap" id="egSw">${GPALETTES.map((p, i) => `<button class="ep-sw ${p[0] === grad[0] ? 'on' : ''}" data-g="${i}" style="background:linear-gradient(135deg,${p[0]},${p[1]})"></button>`).join('')}</div></div>
        <div class="cp-row"><span class="cp-label">Group name</span><input class="cp-input" id="egName" maxlength="40" value="${esc(g.name)}" /></div>
        <div class="cp-row"><span class="cp-label">Description</span><textarea class="cp-textarea" id="egDesc" maxlength="160" placeholder="What's this group about?">${esc(g.desc || '')}</textarea></div>
        <div class="cp-row"><span class="cp-label">Visibility</span><div class="vis-row" id="egVis">
          <button class="vis-opt ${pub ? 'on' : ''}" data-vis="public"><span class="vh"><svg width="15" height="15" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="8.3" stroke="currentColor" stroke-width="1.8"/><path d="M4 9.5h16M4 14.5h16" stroke="currentColor" stroke-width="1.5"/></svg>Public</span><span class="vp">Anyone can join</span></button>
          <button class="vis-opt ${!pub ? 'on' : ''}" data-vis="private"><span class="vh"><svg width="15" height="15" viewBox="0 0 24 24" fill="none"><rect x="5" y="10" width="14" height="10" rx="2" stroke="currentColor" stroke-width="1.8"/><path d="M8 10V8a4 4 0 0 1 8 0v2" stroke="currentColor" stroke-width="1.8"/></svg>Private</span><span class="vp">Invite-only</span></button>
        </div></div>
        <div class="gd-add-foot"><button class="cp-btn primary" id="egSave">Save changes</button></div>`;
      const egName = ov.querySelector('#egName');
      const repaintAv = () => { const nm = egName.value.trim(); ov.querySelector('#egAv').innerHTML = av({ grad, initials: (nm.slice(0, 2).toUpperCase() || g.initials) }, 72); };
      ov.querySelector('[data-back]').addEventListener('click', paint);
      ov.querySelectorAll('#egSw [data-g]').forEach(b => b.addEventListener('click', () => { grad = GPALETTES[+b.dataset.g]; ov.querySelectorAll('#egSw .ep-sw').forEach(x => x.classList.toggle('on', x === b)); repaintAv(); }));
      egName.addEventListener('input', repaintAv);
      ov.querySelectorAll('#egVis [data-vis]').forEach(b => b.addEventListener('click', () => { pub = b.dataset.vis === 'public'; ov.querySelectorAll('#egVis .vis-opt').forEach(x => x.classList.toggle('on', x === b)); }));
      ov.querySelector('#egSave').addEventListener('click', () => {
        const nm = egName.value.trim() || g.name;
        g.name = nm; g.initials = nm.slice(0, 2).toUpperCase(); g.desc = ov.querySelector('#egDesc').value.trim(); g.grad = grad; g.pub = pub;
        if (conv) { conv.name = g.name; conv.initials = g.initials; conv.desc = g.desc; conv.grad = grad; conv.public = pub; }
        paint(); M().toast('Group updated'); notify();
      });
    }

    function confirmDialog(title, msg, label, danger, onYes) {
      const c = document.createElement('div'); c.className = 'cp-overlay cp-confirm';
      c.innerHTML = `<div class="cp-modal" style="max-width:380px"><div class="cp-body" style="padding:24px"><h3 style="font-size:17px;font-weight:800;margin:0 0 8px;letter-spacing:-.01em">${esc(title)}</h3><p style="font-size:13.5px;color:var(--text2);line-height:1.55;margin:0 0 18px">${msg}</p><div style="display:flex;gap:10px;justify-content:flex-end"><button class="cp-btn ghost" data-no>Cancel</button><button class="cp-btn primary" data-yes style="${danger ? 'background:var(--busy);box-shadow:none' : ''}">${esc(label)}</button></div></div></div>`;
      document.body.appendChild(c);
      c.addEventListener('click', e => { if (e.target === c || e.target.closest('[data-no]')) c.remove(); });
      c.querySelector('[data-yes]').addEventListener('click', () => { c.remove(); onYes && onYes(); });
    }

    ov = open('Group details', mainBody());
    bindMain();
  }
  function genToken() { const c = 'abcdefghijklmnopqrstuvwxyz0123456789'; let t = ''; for (let i = 0; i < 16; i++) t += c[Math.floor(Math.random() * c.length)]; return t; }

  /* ============ CALL ============ */
  let timer = null;
  function openCall(target, type, incoming) {
    closeCall();
    const ov = document.createElement('div'); ov.className = 'call-ov';
    document.body.appendChild(ov);
    const grad = `linear-gradient(135deg,${target.grad[0]},${target.grad[1]})`;
    let mic = true, cam = type === 'video', screen = false, sec = 0;

    function ringing() {
      ov.innerHTML = `
        <div class="ring-av">${av(target, 120)}</div>
        <div class="call-name">${esc(target.name)}</div>
        <div class="call-state">${incoming ? 'Incoming ' + type + ' call…' : (type === 'video' ? 'Video calling…' : 'Calling…')}</div>
        ${incoming ? `<div class="call-ring-actions">
          <div class="cra"><button class="cc end" data-decline><svg width="26" height="26" viewBox="0 0 24 24" fill="none"><path d="M6.2 4.5 8 4l1.6 3.4-1.5 1.3a11 11 0 0 0 4.7 4.7l1.3-1.5L17.5 15l-.5 1.8c-.2.7-.9 1.1-1.6 1A14 14 0 0 1 4.2 6.6c-.1-.7.3-1.4 1-1.6Z" stroke="#fff" stroke-width="1.8" stroke-linejoin="round" transform="rotate(135 12 12)"/></svg></button>Decline</div>
          <div class="cra"><button class="cc accept" data-accept><svg width="26" height="26" viewBox="0 0 24 24" fill="none"><path d="M6.2 4.5 8 4l1.6 3.4-1.5 1.3a11 11 0 0 0 4.7 4.7l1.3-1.5L17.5 15l-.5 1.8c-.2.7-.9 1.1-1.6 1A14 14 0 0 1 4.2 6.6c-.1-.7.3-1.4 1-1.6Z" stroke="#fff" stroke-width="1.8" stroke-linejoin="round"/></svg></button>Accept</div>
        </div>` : `<div class="call-controls"><button class="cc end" data-end><svg width="26" height="26" viewBox="0 0 24 24" fill="none"><path d="M6.2 4.5 8 4l1.6 3.4-1.5 1.3a11 11 0 0 0 4.7 4.7l1.3-1.5L17.5 15l-.5 1.8c-.2.7-.9 1.1-1.6 1A14 14 0 0 1 4.2 6.6c-.1-.7.3-1.4 1-1.6Z" stroke="#fff" stroke-width="1.8" stroke-linejoin="round" transform="rotate(135 12 12)"/></svg></button></div>`}`;
      ov.querySelector('[data-accept]')?.addEventListener('click', connected);
      ov.querySelector('[data-decline]')?.addEventListener('click', closeCall);
      ov.querySelector('[data-end]')?.addEventListener('click', closeCall);
      if (!incoming) setTimeout(() => { if (document.body.contains(ov)) connected(); }, 1800);
    }

    function connected() {
      clearInterval(timer); sec = 0;
      function fmt() { const m = String(Math.floor(sec / 60)).padStart(2, '0'), s = String(sec % 60).padStart(2, '0'); return m + ':' + s; }
      function paint() {
        ov.innerHTML = `
          <div class="call-remote">${cam ? `<div class="vbg" style="background:${grad}"></div><div style="position:relative;z-index:1;text-align:center">${av(target, 96)}</div>` : `<div style="text-align:center">${av(target, 120)}<div class="call-name">${esc(target.name)}</div></div>`}</div>
          ${type === 'video' && cam ? `<div class="call-self" style="background:${screen ? '#0f172a' : 'linear-gradient(135deg,#f9a8d4,#db2777)'}">${screen ? '<span style="font-size:12px;color:#cbd5e1">Sharing screen</span>' : av(me, 52)}</div>` : ''}
          <div style="position:absolute;top:30px;left:50%;transform:translateX(-50%);text-align:center;z-index:2">
            <div class="call-name" style="font-size:18px;margin:0">${esc(target.name)}</div>
            <div class="call-state" id="callTimer">${fmt()}</div>
          </div>
          <div class="call-controls">
            <button class="cc ${mic ? '' : 'off'}" data-mic title="Mic">${icMic(mic)}</button>
            ${type === 'video' ? `<button class="cc ${cam ? '' : 'off'}" data-cam title="Camera">${icCam(cam)}</button>` : ''}
            <button class="cc ${screen ? 'off' : ''}" data-screen title="Share screen"><svg width="24" height="24" viewBox="0 0 24 24" fill="none"><rect x="3" y="5" width="18" height="12" rx="2" stroke="currentColor" stroke-width="1.8"/><path d="M9 20h6M12 17v3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg></button>
            <button class="cc end" data-end><svg width="26" height="26" viewBox="0 0 24 24" fill="none"><path d="M6.2 4.5 8 4l1.6 3.4-1.5 1.3a11 11 0 0 0 4.7 4.7l1.3-1.5L17.5 15l-.5 1.8c-.2.7-.9 1.1-1.6 1A14 14 0 0 1 4.2 6.6c-.1-.7.3-1.4 1-1.6Z" stroke="#fff" stroke-width="1.8" stroke-linejoin="round" transform="rotate(135 12 12)"/></svg></button>
          </div>`;
        ov.querySelector('[data-mic]').addEventListener('click', () => { mic = !mic; paint(); });
        ov.querySelector('[data-cam]')?.addEventListener('click', () => { cam = !cam; paint(); });
        ov.querySelector('[data-screen]').addEventListener('click', () => { screen = !screen; paint(); });
        ov.querySelector('[data-end]').addEventListener('click', closeCall);
      }
      paint();
      timer = setInterval(() => { sec++; const t = ov.querySelector('#callTimer'); if (t) t.textContent = fmt(); }, 1000);
    }
    function icMic(on) { return on ? '<svg width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M12 4a3 3 0 0 1 3 3v5a3 3 0 0 1-6 0V7a3 3 0 0 1 3-3Z" stroke="currentColor" stroke-width="1.8"/><path d="M6 11a6 6 0 0 0 12 0M12 18v2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>' : '<svg width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M9 9v3a3 3 0 0 0 4.5 2.6M15 11V7a3 3 0 0 0-5.6-1.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><path d="M6 11a6 6 0 0 0 9 5.2M12 18v2M4 4l16 16" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>'; }
    function icCam(on) { return on ? '<svg width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M15 10.5 20 7v10l-5-3.5M4 7.5C4 6.7 4.7 6 5.5 6h8c.8 0 1.5.7 1.5 1.5v9c0 .8-.7 1.5-1.5 1.5h-8C4.7 18 4 17.3 4 16.5v-9Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/></svg>' : '<svg width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M4 8.5C4 7.7 4.7 7 5.5 7H12M16 9l4-2.5v9M4 4l16 16" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>'; }

    ringing();
    document.addEventListener('keydown', callEsc);
  }
  function callEsc(e) { if (e.key === 'Escape') closeCall(); }
  function closeCall() { clearInterval(timer); document.querySelectorAll('.call-ov').forEach(o => o.remove()); document.removeEventListener('keydown', callEsc); }

  /* fallback shell if CPModals.__open not present — use CPModals public open via a thin wrapper */
  function open(title, body) {
    // CPModals doesn't expose open(); replicate minimal shell using its classes
    document.querySelectorAll('.cp-overlay').forEach(o => o.remove());
    const ov = document.createElement('div'); ov.className = 'cp-overlay';
    ov.innerHTML = `<div class="cp-modal"><div class="cp-head"><span class="cp-title">${esc(title)}</span><button class="cp-x" data-close><svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M6 6l12 12M18 6 6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg></button></div><div class="cp-body">${body}</div></div>`;
    document.body.appendChild(ov);
    ov.addEventListener('click', e => { if (e.target === ov || e.target.closest('[data-close]')) ov.remove(); });
    document.addEventListener('keydown', function esc(e) { if (e.key === 'Escape') { ov.remove(); document.removeEventListener('keydown', esc); } });
    return ov;
  }

  /* ============ NOTIFICATIONS DROPDOWN ============ */
  const NOTIF = [
    { group: 'New', uid: 2, txt: 'mentioned you in <b>Northwind Studio</b>', sub: '“@Sara can you review the hover states?”', time: '5m', icon: 'at', col: '#10b981', unread: true },
    { group: 'New', uid: 5, txt: 'started a poll in <b>Northwind Studio</b>', sub: 'Launch date? — 8 votes so far', time: '12m', icon: 'poll', col: '#0891b2', unread: true },
    { group: 'New', uid: 3, txt: 'reacted ❤️ to your message', sub: '', time: '1h', icon: 'heart', col: '#e11d48', unread: true },
    { group: 'Earlier', uid: 4, txt: 'sent you a voice message', sub: '0:24', time: '3h', icon: 'mic', col: '#7c3aed', unread: false },
    { group: 'Earlier', uid: 6, txt: 'added you to <b>Weekend Crew</b>', sub: '', time: 'Yesterday', icon: 'group', col: '#f59e0b', unread: false },
    { group: 'Earlier', uid: 7, txt: 'shared a file in <b>Design Critique</b>', sub: 'crit-notes.md', time: 'Yesterday', icon: 'file', col: '#2563eb', unread: false },
  ];
  const NICONS = {
    at: '<path d="M12 16a4 4 0 1 0-4-4c0 1.5 0 4 3 4 2 0 2.5-1.5 2.5-3.5V11" stroke="#fff" stroke-width="1.7" fill="none" stroke-linecap="round"/><circle cx="12" cy="12" r="9" stroke="#fff" stroke-width="1.5" fill="none"/>',
    poll: '<path d="M7 20V10M12 20V4M17 20v-7" stroke="#fff" stroke-width="2" stroke-linecap="round"/>',
    heart: '<path d="M12 20s-7-4.5-7-9a3.5 3.5 0 0 1 7-1 3.5 3.5 0 0 1 7 1c0 4.5-7 9-7 9Z" fill="#fff"/>',
    mic: '<rect x="9" y="3" width="6" height="11" rx="3" stroke="#fff" stroke-width="1.7"/><path d="M6 11a6 6 0 0 0 12 0M12 17v3" stroke="#fff" stroke-width="1.7" stroke-linecap="round"/>',
    group: '<circle cx="9" cy="9" r="2.4" stroke="#fff" stroke-width="1.6"/><path d="M4.5 17a4.5 4.5 0 0 1 9 0M16 7a2.4 2.4 0 0 1 0 4.6M18.5 17a4.5 4.5 0 0 0-2-3.6" stroke="#fff" stroke-width="1.6" stroke-linecap="round"/>',
    file: '<path d="M14 3v5h5M7 3h8l5 5v11a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z" stroke="#fff" stroke-width="1.6" stroke-linejoin="round"/>',
  };
  function openNotifications(anchor) {
    closeNotif();
    const groups = [...new Set(NOTIF.map(n => n.group))];
    const panel = document.createElement('div'); panel.className = 'cp-notif';
    panel.innerHTML = `
      <div class="cp-notif-head"><h4>Notifications</h4><button class="cp-notif-clear" data-clearall>Mark all read</button></div>
      <div class="cp-notif-list">
        ${groups.map(g => `<div class="cp-notif-sec">${g}</div>` + NOTIF.filter(n => n.group === g).map((n, i) => `
          <div class="cp-notif-item ${n.unread ? 'unread' : ''}" data-ni="${NOTIF.indexOf(n)}">
            <div class="cp-notif-ava">${av(users[n.uid], 38)}<span class="cp-notif-badge" style="background:${n.col}"><svg width="11" height="11" viewBox="0 0 24 24" fill="none">${NICONS[n.icon]}</svg></span></div>
            <div class="cp-notif-txt"><b>${esc(users[n.uid].name)}</b> ${n.txt}${n.sub ? `<div class="cp-notif-time" style="color:var(--text2);font-style:italic">${esc(n.sub)}</div>` : ''}<div class="cp-notif-time">${n.time}</div></div>
          </div>`).join('')).join('')}
      </div>
      <div class="cp-notif-foot"><a href="ChatPulse Screens.html?s=notif">Open notifications page</a></div>`;
    document.body.appendChild(panel);
    const r = anchor.getBoundingClientRect();
    // rail is on the left → place to the right of the bell; fall back to below
    let left = r.right + 10, top = Math.min(r.top, window.innerHeight - 480);
    if (left + 360 > window.innerWidth) left = Math.max(12, window.innerWidth - 372);
    panel.style.left = left + 'px'; panel.style.top = Math.max(12, top) + 'px';
    panel.querySelector('[data-clearall]').addEventListener('click', () => { NOTIF.forEach(n => n.unread = false); panel.querySelectorAll('.cp-notif-item').forEach(el => el.classList.remove('unread')); updateBellDots(); });
    panel.querySelectorAll('[data-ni]').forEach(el => el.addEventListener('click', () => { NOTIF[+el.dataset.ni].unread = false; el.classList.remove('unread'); updateBellDots(); }));
    setTimeout(() => document.addEventListener('click', function h(e) { if (!panel.contains(e.target) && !anchor.contains(e.target) && e.target !== anchor) { panel.remove(); document.removeEventListener('click', h); } }), 0);
  }
  function closeNotif() { document.querySelectorAll('.cp-notif').forEach(p => p.remove()); }
  function updateBellDots() {
    const count = NOTIF.filter(n => n.unread).length;
    document.querySelectorAll('[data-nav="notif"] .rb-badge, [data-mnav="notif"] .mtab-dot').forEach(b => {
      if (b.classList.contains('rb-badge')) { if (count) b.textContent = count; else b.style.display = 'none'; }
      else b.style.display = count ? 'block' : 'none';
    });
  }

  return { openProfile, openGroupDetail, openCall, closeCall, openNotifications };
})();
