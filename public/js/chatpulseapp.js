/* ChatPulse — rendering + interactivity (front-end prototype) */
(function () {
  const { me, users, conversations, reactionsPool, scheduled } = window.CP;
  const $ = (s, r) => (r || document).querySelector(s);
  const $$ = (s, r) => [...(r || document).querySelectorAll(s)];
  const esc = (s) => (s || '').replace(/[&<>"]/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]));

  let activeId = (window.CP && window.CP.activeId && conversations.find(c => c.id === window.CP.activeId)) ? window.CP.activeId : conversations[0]?.id;
  let listView = 'chats'; // chats | people | saved | scheduled

  // Until WebSocket (Reverb) is wired, only the current logged-in user is reliably online.
  // Overwrite stale is_online flags from page-load snapshot.
  Object.values(users).forEach(u => { if (u.id !== me.id) u.online = false; });

  /* ---------- avatar ---------- */
  function avatar(u, size) {
    const s = size || 36;
    return `<div class="avatar" style="width:${s}px;height:${s}px;background:linear-gradient(135deg,${u.grad[0]},${u.grad[1]});font-size:${s * 0.38}px">${u.initials}</div>`;
  }
  function convoMeta(c) {
    if (c.type === 'direct') { const u = users[c.with]; return { name: u.name, sub: '@' + u.username, av: u, online: u.online, status: u.status, u }; }
    return { name: c.name, sub: c.desc, av: { initials: c.initials, grad: c.grad }, group: true };
  }
  const statusColor = { available: '#10b981', busy: '#ef4444', away: '#f59e0b' };

  /* ---------- conversation list ---------- */
  function renderList(filter) {
    if (listView === 'people') return renderPeople(filter);
    if (listView === 'saved') return renderSaved();
    if (listView === 'scheduled') return renderScheduled();
    const f = (filter || '').toLowerCase();
    const wrap = $('#convoList');
    const items = conversations.filter(c => {
      if (c.archived) return false;
      const m = convoMeta(c);
      return !f || m.name.toLowerCase().includes(f) || (c.last || '').toLowerCase().includes(f);
    });
    if (!items.length) {
      wrap.innerHTML = `<div class="list-empty"><div class="estate-ic"><svg width="26" height="26" viewBox="0 0 24 24" fill="none"><circle cx="11" cy="11" r="6.5" stroke="currentColor" stroke-width="1.8"/><path d="m20 20-3.2-3.2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg></div><h4>No conversations found</h4><p>No matches for “${esc(f)}”. Try a different name or keyword.</p></div>`;
      return;
    }
    renderListItems(items);
  }
  function renderListItems(items) {
    const wrap = $('#convoList');
    wrap.innerHTML = items.map(c => {
      const m = convoMeta(c);
      const active = c.id === activeId;
      const unread = c.unread > 0;
      const dot = (c.type === 'direct' && m.online) ? `<span class="pres" style="background:${statusColor[m.status] || '#10b981'}"></span>` : '';
      const badge = unread ? `<span class="badge">${c.unread}</span>` : (c.read ? `<svg class="ticks read" width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="m3 13 3.2 3.2L13 9.5M11 13l3 3 7-7.5" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/></svg>` : '');
      const typing = c.typing ? `<span class="typing-mini"><span class="d"></span><span class="d"></span><span class="d"></span></span>` : esc(c.last || '');
      const muted = c.muted ? `<svg class="muted-ico" width="13" height="13" viewBox="0 0 24 24" fill="none"><path d="M4 9v6h4l5 4V5L8 9H4Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="m17 9 4 6m0-6-4 6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>` : '';
      const flags = `<span class="convo-flags">${c.fav ? '<svg class="fav-star" viewBox="0 0 24 24" fill="currentColor"><path d="M12 4l2.4 5 5.4.6-4 3.7 1.1 5.3L12 16.9 7.1 18.6l1.1-5.3-4-3.7 5.4-.6L12 4Z"/></svg>' : ''}${c.pinned ? '<svg class="pin-mark" viewBox="0 0 24 24" fill="currentColor"><path d="M9 3h6l-1 6 3 3v2H7v-2l3-3-1-6Z"/></svg>' : ''}</span>`;
      return `
      <div class="convo ${active ? 'active' : ''}" data-convo="${c.id}" role="button" tabindex="0">
        <span class="avwrap">${avatar(m.av, 44)}${dot}</span>
        <span class="convo-main">
          <span class="convo-top"><span class="convo-name ${unread ? 'un' : ''}">${esc(m.name)} ${muted}${flags}</span><span class="convo-time ${unread ? 'un' : ''}">${esc(c.time)}</span></span>
          <span class="convo-bot"><span class="convo-last ${unread ? 'un' : ''}">${typing}</span><span class="convo-badge">${badge}</span></span>
        </span>
        <button class="convo-menu" data-cmenu="${c.id}" title="Options"><svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="m6 9 6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></button>
      </div>`;
    }).join('');
    $$('[data-convo]', wrap).forEach(b => {
      b.addEventListener('click', e => { if (e.target.closest('[data-cmenu]')) return; selectConvo(b.dataset.convo); });
      b.addEventListener('contextmenu', e => { e.preventDefault(); convoMenu(conversations.find(x => x.id === b.dataset.convo), b); });
    });
    $$('[data-cmenu]', wrap).forEach(b => b.addEventListener('click', e => { e.stopPropagation(); convoMenu(conversations.find(x => x.id === b.dataset.cmenu), b); }));
  }

  /* ---------- list views: People / Saved / Scheduled ---------- */
  function setFilterPill(name) { $$('.filters .filter').forEach(x => x.classList.toggle('on', x.textContent.trim() === name)); }
  function openView(v) {
    listView = v;
    $('.filters').style.display = v === 'chats' || v === 'people' ? '' : 'none';
    if (v === 'people') setFilterPill('People');
    document.body.classList.remove('mobile-chat');
    renderList($('#search').value);
  }
  function backToChats() {
    if (listView === 'chats') return;
    listView = 'chats'; $('.filters').style.display = ''; setFilterPill('All');
  }
  function viewHeader(title, sub) {
    return `<div class="view-head"><div class="vh-tx"><h3>${esc(title)}</h3><span>${esc(sub)}</span></div><button class="vh-back" id="viewBack" title="Back to chats"><svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M6 6l12 12M18 6 6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg></button></div>`;
  }
  function bindViewBack() { $('#viewBack')?.addEventListener('click', () => { backToChats(); renderList(''); }); }

  function renderPeople(filter) {
    const f = (filter || '').toLowerCase().replace(/^@/, '');
    const wrap = $('#convoList');
    const people = Object.values(users)
      .filter(u => u.id !== me.id && !u.guest)
      .filter(u => !f || u.name.toLowerCase().includes(f) || u.username.toLowerCase().includes(f))
      .sort((a, b) => (b.online ? 1 : 0) - (a.online ? 1 : 0) || a.name.localeCompare(b.name));
    if (!people.length) { wrap.innerHTML = `<div class="list-empty"><div class="estate-ic"><svg width="26" height="26" viewBox="0 0 24 24" fill="none"><circle cx="9" cy="8" r="3" stroke="currentColor" stroke-width="1.8"/><path d="M3.5 19a5.5 5.5 0 0 1 11 0" stroke="currentColor" stroke-width="1.8"/></svg></div><h4>No people found</h4><p>No matches for “${esc(f)}”.</p></div>`; return; }
    wrap.innerHTML = people.map(u => `
      <div class="convo person" data-person="${u.id}" role="button" tabindex="0">
        <span class="avwrap">${avatar(u, 44)}${u.online ? `<span class="pres" style="background:${statusColor[u.status] || '#10b981'}"></span>` : ''}</span>
        <span class="convo-main">
          <span class="convo-top"><span class="convo-name">${esc(u.name)}</span></span>
          <span class="convo-bot"><span class="convo-last">@${esc(u.username)} · ${u.online ? (u.status === 'available' ? 'Active now' : esc(u.status)) : (u.last ? 'last seen ' + esc(u.last) : 'offline')}</span></span>
        </span>
        <span class="person-msg" title="Message"><svg width="17" height="17" viewBox="0 0 24 24" fill="none"><path d="M4 6.5C4 5.12 5.12 4 6.5 4h11C18.88 4 20 5.12 20 6.5v7c0 1.38-1.12 2.5-2.5 2.5H10l-3.6 3a1 1 0 0 1-1.65-.77V6.5Z" stroke="currentColor" stroke-width="1.7"/></svg></span>
      </div>`).join('');
    $$('[data-person]', wrap).forEach(b => b.addEventListener('click', () => { backToChats(); startDirect(+b.dataset.person); }));
  }

  function renderSaved() {
    const wrap = $('#convoList');
    const items = [];
    conversations.forEach(c => (c.messages || []).forEach(m => { if (m.bookmarked && !m.deleted) items.push({ c, m }); }));
    let html = viewHeader('Saved messages', items.length + (items.length === 1 ? ' saved message' : ' saved messages'));
    if (!items.length) html += `<div class="list-empty"><div class="estate-ic"><svg width="26" height="26" viewBox="0 0 24 24" fill="none"><path d="M7 4h10v16l-5-3.5L7 20V4Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/></svg></div><h4>No saved messages</h4><p>Hover any message and tap the bookmark icon to save it here.</p></div>`;
    else html += `<div class="saved-list">${items.map(({ c, m }) => {
      const u = users[m.user]; const meta = convoMeta(c);
      const body = m.text || (m.voice ? '🎤 Voice message' : m.image ? '📷 Photo' : m.file ? '📎 ' + m.file.name : 'Message');
      return `<button class="saved-item" data-saved="${c.id}|${m.id}">
        <span class="saved-top">${avatar(u, 26)}<span class="saved-by">${esc(u.name)}</span><span class="saved-in">in ${esc(meta.name)}</span><span class="saved-time">${esc(m.t)}</span></span>
        <span class="saved-text">${esc(body.slice(0, 120))}</span>
        <span class="saved-unbm" data-unbm="${c.id}|${m.id}" title="Remove"><svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M7 4h10v16l-5-3.5L7 20V4Z"/></svg></span>
      </button>`;
    }).join('')}</div>`;
    wrap.innerHTML = html;
    bindViewBack();
    $$('[data-saved]', wrap).forEach(b => b.addEventListener('click', e => {
      if (e.target.closest('[data-unbm]')) return;
      const [cid, mid] = b.dataset.saved.split('|'); backToChats(); selectConvo(cid); setTimeout(() => jumpTo(mid), 60);
    }));
    $$('[data-unbm]', wrap).forEach(b => b.addEventListener('click', e => {
      e.stopPropagation();
      const [cid, mid] = b.dataset.unbm.split('|');
      const c = conversations.find(x => x.id === cid); const m = c.messages.find(x => x.id === mid);
      if (m) m.bookmarked = false;
      renderSaved(); toast('Removed from saved');
    }));
  }

  function renderScheduled() {
    const wrap = $('#convoList');
    let html = `<div class="sched-wrap">`;
    html += `<div class="sched-subhead">${scheduled.length ? scheduled.length + ' scheduled message' + (scheduled.length === 1 ? '' : 's') : ''}</div>`;
    if (!scheduled.length) html += `<div class="list-empty"><div class="estate-ic"><svg width="26" height="26" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="8.5" stroke="currentColor" stroke-width="1.8"/><path d="M12 8v4.5l3 2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg></div><h4>Nothing scheduled</h4><p>Type a message, then tap the clock in the composer to schedule it for later.</p></div>`;
    else html += scheduled.map((s, i) => {
      const c = conversations.find(x => x.id === s.convoId); const meta = c ? convoMeta(c) : { name: 'Conversation', av: { initials: '?', grad: ['#9ca3af', '#4b5563'] } };
      return `<div class="sched-card">
        <div class="sched-card-top">
          <span class="sched-when"><svg width="13" height="13" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="8.5" stroke="currentColor" stroke-width="1.9"/><path d="M12 8v4.5l3 2" stroke="currentColor" stroke-width="1.9" stroke-linecap="round"/></svg>${esc(s.when)}</span>
          <button class="sched-x" data-cancsched="${i}" title="Cancel"><svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M6 6l12 12M18 6 6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg></button>
        </div>
        <div class="sched-to">${avatar(meta.av, 20)}<span>${esc(meta.name)}</span></div>
        <p class="sched-text">${esc(s.text)}</p>
        <div class="sched-acts">
          <button class="sched-now" data-sendnow="${i}"><svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M5 12 19 5l-4 14-3.5-5.5L5 12Z" stroke="currentColor" stroke-width="1.9" stroke-linejoin="round"/></svg>Send now</button>
          <button class="sched-edit" data-editsched="${i}">Edit</button>
        </div>
      </div>`;
    }).join('');
    html += `</div>`;
    wrap.innerHTML = html;
    $$('[data-cancsched]', wrap).forEach(b => b.addEventListener('click', () => {
      const i = +b.dataset.cancsched;
      const s = scheduled[i];
      if (s && s.dbId) {
        const url = (R.scheduleDel || '/scheduled/{msg}').replace('{msg}', s.dbId);
        apiDelete(url).catch(() => {});
      }
      scheduled.splice(i, 1); renderScheduled(); toast('Scheduled message cancelled');
    }));
    $$('[data-editsched]', wrap).forEach(b => b.addEventListener('click', () => editScheduled(+b.dataset.editsched)));
    $$('[data-sendnow]', wrap).forEach(b => b.addEventListener('click', () => {
      const i = +b.dataset.sendnow;
      const s = scheduled.splice(i, 1)[0];
      const c = conversations.find(x => x.id === s.convoId);
      if (c) {
        const msg = { id: 'x' + Date.now(), user: me.id, t: nowTime(), text: s.text, status: 'sending' };
        c.messages.push(msg); c.last = 'You: ' + s.text; c.time = nowTime();
        deliverMessage(c, msg);
        // Delete scheduled record from DB
        if (s.dbId) {
          const url = (R.scheduleDel || '/scheduled/{msg}').replace('{msg}', s.dbId);
          apiDelete(url).catch(() => {});
        }
      }
      renderScheduled(); renderList($('#search').value);
    }));
  }
  function editScheduled(i) {
    const s = scheduled[i]; if (!s) return;
    scheduled.splice(i, 1);
    selectConvo(s.convoId);
    const comp = $('#composer'); comp.innerText = s.text; updateSendMic(); comp.focus();
    toast('Edit your message, then reschedule or send');
  }

  /* ---------- chat header ---------- */
  function renderHeader(c) {
    const m = convoMeta(c);
    let sub;
    if (m.group) sub = `${c.members.length} members · ${c.public ? 'Public' : 'Private'}`;
    else if (c.typing || m.online && m.status) sub = m.online ? `<span class="hdr-on">${m.status === 'available' ? 'Active now' : m.status}</span>` : (m.u.last ? 'last seen ' + m.u.last : 'offline');
    const stack = m.group ? `<div class="hdr-stack">${c.members.slice(0, 3).map(id => `<span class="mini-av" style="background:linear-gradient(135deg,${users[id].grad[0]},${users[id].grad[1]})">${users[id].initials}</span>`).join('')}${c.members.length > 3 ? `<span class="mini-av more">+${c.members.length - 3}</span>` : ''}</div>` : '';
    $('#chatHeader').innerHTML = `
      <button id="backBtn" title="Back"><svg width="20" height="20" viewBox="0 0 24 24" fill="none"><path d="M15 5l-7 7 7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></button>
      <span class="avwrap hdr-id" style="cursor:pointer">${avatar(m.av, 42)}${(!m.group && m.online) ? `<span class="pres" style="background:${statusColor[m.status]}"></span>` : ''}</span>
      <div class="hdr-info" style="cursor:pointer"><p class="hdr-name">${esc(m.name)}</p><p class="hdr-sub">${sub || ''}</p></div>
      ${stack}
      <div class="hdr-actions">
        ${iconBtn('search', 'Search in conversation', 'hdrSearch')}
        ${iconBtn('phone', 'Audio call', 'callAudio')}
        ${iconBtn('video', 'Video call', 'callVideo')}
        ${iconBtn('panel', 'Toggle info', 'togglePanel')}
      </div>`;
    $('#hdrSearch')?.addEventListener('click', openThreadSearch);
    $('#togglePanel')?.addEventListener('click', () => $('#rightPanel').classList.toggle('collapsed'));
    $('#backBtn')?.addEventListener('click', () => document.body.classList.remove('mobile-chat'));
    const openDetail = () => m.group ? CPOverlays.openGroupDetail(c.id) : CPOverlays.openProfile(c.with);
    $('#chatHeader .hdr-id')?.addEventListener('click', openDetail);
    $('#chatHeader .hdr-info')?.addEventListener('click', openDetail);
    const ct = callTarget(c);
    $('#callAudio')?.addEventListener('click', () => CPOverlays.openCall(ct, 'audio', false, c.id));
    $('#callVideo')?.addEventListener('click', () => CPOverlays.openCall(ct, 'video', false, c.id));
  }
  function callTarget(c) { return c.type === 'direct' ? users[c.with] : { name: c.name, initials: c.initials, grad: c.grad }; }

  /* ---------- thread ---------- */
  function renderThread(c) {
    const t = $('#thread');
    if (!c.messages.length) {
      t.innerHTML = emptyThread(c);
      $('#emptyHi')?.addEventListener('click', () => $('#composer')?.focus());
      return;
    }
    let html = '';
    html += c._noMore
      ? `<div class="thread-start"><svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M5 9.5C5 6.5 7.5 4 10.5 4h3C16.5 4 19 6.5 19 9.5S16.5 15 13.5 15H9l-3.2 2.9c-.5.5-1.3.1-1.3-.6V9.5Z" stroke="currentColor" stroke-width="1.6"/></svg>This is the beginning of your conversation</div>`
      : `<button class="load-earlier" id="loadEarlier"><svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M12 19V5m0 0-6 6m6-6 6 6" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/></svg>Load earlier messages</button>`;
    html += `<div class="day"><span>Today</span></div>`;
    let lastUser = null, lastT = null;
    c.messages.forEach(msg => {
      if (c.firstUnreadId && msg.id === c.firstUnreadId) { html += `<div class="unread-div"><span>New messages</span></div>`; lastUser = null; }
      const grouped = msg.user === lastUser && !msg.reply;
      lastUser = msg.user; lastT = msg.t;
      html += bubble(c, msg, grouped);
    });
    if (c.typing) {
      const who = c.type === 'group' ? users[c.members.find(id => id !== me.id)] : users[c.with];
      html += `<div class="typing-row"><div class="b-av">${avatar(who, 38)}</div><div class="typing-bubble"><span class="d"></span><span class="d"></span><span class="d"></span></div></div>`;
    }
    t.innerHTML = html;
    if (suppressThreadScroll) { suppressThreadScroll = false; } else { t.scrollTop = t.scrollHeight; }
    bindThread(c);
  }
  let suppressThreadScroll = false;
  const earlierPool = [
    'Hey — quick one before standup, you around?',
    'Yep, what\u2019s up?',
    'Did everyone get the calendar invite for the review?',
    'Got it 👍 see you all there.',
    'Pushed the latest build, let me know if anything looks off.',
    'Looks clean on my end so far.',
  ];
  function makeOlderMessages(c) {
    const day = c._olderCount ? '2 days ago' : 'Yesterday';
    const o = (c._olderCount || 0) * 2;
    if (c.type === 'direct') {
      return [
        { id: 'old' + Date.now() + 'a', user: c.with, t: day, text: earlierPool[o % earlierPool.length] },
        { id: 'old' + Date.now() + 'b', user: me.id, t: day, text: earlierPool[(o + 1) % earlierPool.length], status: 'read' },
      ];
    }
    const mem = c.members.filter(id => id !== me.id);
    return [
      { id: 'old' + Date.now() + 'a', user: mem[0], t: day, text: earlierPool[o % earlierPool.length] },
      { id: 'old' + Date.now() + 'b', user: mem[1] || mem[0], t: day, text: earlierPool[(o + 1) % earlierPool.length] },
    ];
  }
  function loadEarlier(c) {
    const t = $('#thread'); const prevH = t.scrollHeight;
    const btn = $('#loadEarlier');
    if (btn) btn.outerHTML = `<div class="load-earlier-sk"><div class="sk" style="height:36px;width:46%;border-radius:14px"></div><div class="sk" style="height:36px;width:58%;border-radius:14px;margin-left:auto"></div></div>`;
    setTimeout(() => {
      c.messages = [...makeOlderMessages(c), ...c.messages];
      c._olderCount = (c._olderCount || 0) + 1;
      if (c._olderCount >= 2) c._noMore = true;
      suppressThreadScroll = true;
      renderThread(c);
      const t2 = $('#thread'); t2.scrollTop = Math.max(0, t2.scrollHeight - prevH);
    }, 850);
  }

  function fileMsg(msg) {
    const f = msg.file;
    const colors = { pdf: '#ef4444', doc: '#2563eb', docx: '#2563eb', zip: '#f59e0b', fig: '#a855f7' };
    const ext = (f.name.split('.').pop() || 'file').toLowerCase();
    const col = colors[ext] || '#10b981';
    let sub, right;
    if (msg.uploadFailed) {
      sub = `<div class="file-sub fail">Upload failed</div>`;
      right = `<button class="up-retry" data-upretry="${msg.id}" title="Retry upload"><svg width="17" height="17" viewBox="0 0 24 24" fill="none"><path d="M4 12a8 8 0 1 1 2.3 5.6M4 17v-4h4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></button>`;
    } else if (msg.uploading) {
      sub = `<div class="up-bar"><span class="up-bar-fill" style="width:${msg.progress || 0}%"></span></div>`;
      right = `<span class="up-pct">${Math.round(msg.progress || 0)}%</span>`;
    } else {
      sub = `<div class="file-sub">${esc(ext.toUpperCase())} · ${esc(f.size)}</div>`;
      right = `<button class="file-dl" title="Download"><svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M12 4v11m0 0 4-4m-4 4-4-4M5 19h14" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></button>`;
    }
    return `<div class="file-msg ${msg.uploadFailed ? 'failed' : ''}">
      <div class="file-ic" style="background:${col}"><svg width="20" height="20" viewBox="0 0 24 24" fill="none"><path d="M14 3v5h5M7 3h8l5 5v11a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z" stroke="#fff" stroke-width="1.7" stroke-linejoin="round"/></svg></div>
      <div class="file-meta"><div class="file-name">${esc(f.name)}</div>${sub}</div>
      ${right}
    </div>`;
  }
  function imageMsg(msg) {
    const busy = msg.uploading || msg.uploadFailed;
    let overlay = '';
    if (msg.uploading) overlay = `<div class="up-overlay"><div class="up-bar wide"><span class="up-bar-fill" style="width:${msg.progress || 0}%"></span></div><span class="up-pct light">${Math.round(msg.progress || 0)}%</span></div>`;
    else if (msg.uploadFailed) overlay = `<button class="up-overlay failed" data-upretry="${msg.id}"><svg width="20" height="20" viewBox="0 0 24 24" fill="none"><path d="M4 12a8 8 0 1 1 2.3 5.6M4 17v-4h4" stroke="#fff" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg><span>Upload failed · Retry</span></button>`;
    return `<a class="img-msg ${busy ? 'uploading' : ''}" ${busy ? '' : `data-lightbox="${msg.image.src}"`}><img src="${msg.image.src}" alt="${esc(msg.image.name || 'image')}" />${overlay}</a>`;
  }

  /* ---------- spam / bot detection (heuristic) ---------- */
  const SPAM_SIGNALS = [/\bfree\b/i, /click here/i, /\bwinner?\b/i, /\$\$\$|\$\s?\d{3,}/, /https?:\/\/|bit\.ly|tinyurl|\.xyz|\.io\b/i, /crypto|giveaway|airdrop|\bprize\b|bonus/i, /claim (your|now|it)/i, /congratulations|act now|limited time|guarantee/i, /dm me|whatsapp \+?\d/i];
  function spamScore(text) {
    if (!text) return 0;
    let s = 0; SPAM_SIGNALS.forEach(rx => { if (rx.test(text)) s++; });
    if (/[A-Z]{8,}/.test(text)) s++;
    if (((text.match(/[!💰🎁🔥]/g) || []).length) >= 4) s++;
    return s;
  }
  function isSuspiciousUser(u) { return !!(u && (u.guest || u.suspicious)); }
  function isSpam(c, msg) {
    if (msg.user === me.id || msg.revealed || msg.deleted) return false;
    const score = spamScore(msg.text);
    return score >= 2 || (score >= 1 && isSuspiciousUser(users[msg.user]));
  }

  function bubble(c, msg, grouped) {
    const u = users[msg.user];
    const mine = msg.user === me.id;
    const spam = isSpam(c, msg);
    const av = grouped ? `<span class="b-gutter"></span>` : avatar(u, 38);
    const spamBadge = (!mine && isSuspiciousUser(u)) ? '<span class="spam-badge" title="Flagged account">⚠ spam?</span>' : '';
    const head = grouped ? '' : `<div class="b-head"><span class="b-name">${esc(u.name)}</span>${u.guest ? '<span class="g-badge">guest</span>' : ''}${u.role === 'admin' ? '<span class="a-badge">admin</span>' : ''}${spamBadge}<span class="b-time">${esc(msg.t)}</span></div>`;

    let body = '';
    if (spam) {
      body = `<div class="spam-warn">
        <div class="spam-warn-h"><svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M12 3 3 19h18L12 3Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="M12 10v3.5M12 16.5h.01" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>Hidden — flagged as possible spam</div>
        <div class="spam-warn-acts"><button class="spam-show" data-spamshow="${msg.id}">Show message</button><button class="spam-report" data-spamreport="${msg.id}">Report</button></div>
      </div>`;
    } else {
    if (msg.forwarded) body += `<div class="fwd-label"><svg width="12" height="12" viewBox="0 0 24 24" fill="none"><path d="M13 7l5 5-5 5M18 12H7a3 3 0 0 0-3 3v2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>Forwarded</div>`;
    if (msg.reply) {
      const r = c.messages.find(x => x.id === msg.reply); const ru = r ? users[r.user] : null;
      if (r) body += `<div class="reply-quote"><span class="reply-bar"></span><div><span class="reply-name">${esc(ru.name)}</span><span class="reply-text">${esc((r.text || '🎤 Voice message').slice(0, 60))}</span></div></div>`;
    }
    if (msg.deleted) body += `<div class="b-text deleted"><svg width="15" height="15" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="8.5" stroke="currentColor" stroke-width="1.6"/><path d="M9 9l6 6m0-6-6 6" stroke="currentColor" stroke-width="1.6"/></svg>This message was deleted</div>`;
    else if (msg.text) body += `<div class="b-text">${linkify(msg.text)}${msg.reported ? '<span class="reported-tag">reported</span>' : ''}${msg.edited ? '<span class="edited-tag">edited</span>' : ''}</div>`;
    if (msg.image) body += imageMsg(msg);
    if (msg.file) body += fileMsg(msg);
    if (msg.voice) body += voice(msg, mine);
    if (msg.poll) body += poll(msg);
    if (msg.link) body += linkCard(msg.link);
    }

    const reax = renderReax(msg);
    const pin = msg.pinned ? `<span class="pin-flag"><svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><path d="M9 3h6l-1 6 3 3v2H7v-2l3-3-1-6Z"/><path d="M12 14v7" stroke="currentColor" stroke-width="1.6"/></svg>Pinned</span>` : '';
    const foot = (mine && !msg.deleted) ? (msg.uploading ? `<div class="b-foot"><span class="up-status">Uploading…</span></div>` : (msg.uploadFailed ? '' : `<div class="b-foot">${statusTick(msg)}</div>`)) : '';

    return `
    <div class="msg ${grouped ? 'grouped' : ''} ${mine ? 'mine' : ''} ${msg.status === 'failed' ? 'failed' : ''}" data-msg="${msg.id}">
      <div class="b-av">${av}</div>
      <div class="b-body">
        ${pin}${head}
        ${body}
        ${foot}
        ${reax}
      </div>
      <div class="msg-tools">
        ${tool('react', 'React')}${tool('reply', 'Reply')}${tool('forward', 'Forward')}${mine ? tool('edit', 'Edit') : ''}${tool('pin', 'Pin')}${tool('more', 'More')}
      </div>
    </div>`;
  }
  function statusTick(msg) {
    const s = msg.status || 'sent';
    if (s === 'failed') return `<button class="b-retry" data-retry="${msg.id}"><svg width="13" height="13" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.8"/><path d="M12 7.5v5M12 16h.01" stroke="currentColor" stroke-width="1.9" stroke-linecap="round"/></svg>Not delivered · Retry</button>`;
    const t = esc(msg.t || '');
    let ic;
    if (s === 'sending') {
      ic = `<svg class="tick sending" width="14" height="14" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="8" stroke="currentColor" stroke-width="1.7"/><path d="M12 8v4.2l2.6 1.6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>`;
    } else if (s === 'sent') {
      // single grey tick
      ic = `<svg class="tick" width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="m5 12.5 4 4 10-10" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/></svg>`;
    } else if (s === 'delivered') {
      // double grey tick
      ic = `<svg class="tick" width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="m3 13 3.2 3.2L13 9.5M11 13l3 3 7-7.5" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/></svg>`;
    } else {
      // read — double blue tick
      ic = `<svg class="tick read" width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="m3 13 3.2 3.2L13 9.5M11 13l3 3 7-7.5" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/></svg>`;
    }
    return `<span class="b-time-s">${t}</span><span class="b-status-ic msg-info-trigger" data-msginfo="${msg.id}" title="${s}">${ic}</span>`;
  }

  function openMsgInfo(c, msgId) {
    const msg = c.messages.find(m => m.id === msgId); if (!msg) return;
    document.querySelectorAll('.msg-info-panel').forEach(p => p.remove());
    const el = $(`[data-msg="${msgId}"]`); if (!el) return;
    const s = msg.status || 'sent';
    const rows = [
      { label: 'Sent', time: msg.t, ic: '#10b981', always: true },
      { label: 'Delivered', time: msg.deliveredAt || null, ic: '#6b7280', always: false },
      { label: 'Read', time: msg.readAt || null, ic: '#3b82f6', always: false },
    ];
    const panel = document.createElement('div');
    panel.className = 'msg-info-panel';
    panel.innerHTML = `
      <div class="mip-head">Message Info</div>
      ${rows.map(r => {
        const done = r.label === 'Sent' || (r.label === 'Delivered' && (s === 'delivered' || s === 'read')) || (r.label === 'Read' && s === 'read');
        const col = done ? r.ic : 'var(--text3)';
        return `<div class="mip-row ${done ? 'done' : ''}">
          <span class="mip-dot" style="background:${col}"></span>
          <span class="mip-label">${r.label}</span>
          <span class="mip-time">${done ? (r.time || '—') : '—'}</span>
        </div>`;
      }).join('')}`;
    document.body.appendChild(panel);
    const msgR = el.getBoundingClientRect();
    const pw = 200, ph = 140;
    let left = msgR.right + 8;
    let top = msgR.top;
    if (left + pw > window.innerWidth) left = msgR.left - pw - 8;
    if (top + ph > window.innerHeight) top = window.innerHeight - ph - 10;
    panel.style.left = Math.max(8, left) + 'px';
    panel.style.top = Math.max(8, top) + 'px';
    setTimeout(() => document.addEventListener('click', function h(e) {
      if (!panel.contains(e.target)) { panel.remove(); document.removeEventListener('click', h); }
    }), 0);
  }

  function linkify(text) {
    let t = esc(text);
    t = t.replace(/(loop\.design\/[^\s]+)/g, '<a href="#" class="ln">$1</a>');
    t = t.replace(/@(\w+)/g, '<span class="mention">@$1</span>');
    return t;
  }

  function durToSec(s) { const p = (s || '0:00').split(':'); return (+p[0]) * 60 + (+p[1] || 0); }
  function fmtDur(sec) { sec = Math.max(0, Math.round(sec)); return Math.floor(sec / 60) + ':' + String(sec % 60).padStart(2, '0'); }
  function voice(msg, mine) {
    const heights = [7, 12, 18, 10, 22, 14, 8, 20, 12, 16, 6, 19, 13, 9, 23, 11, 17, 7, 15, 21, 10, 16, 8, 18, 12, 14, 9, 20, 11, 7];
    const bars = heights.map(h => `<span style="height:${h}px"></span>`).join('');
    return `<div class="voice" data-voice="${msg.id}" data-dur="${durToSec(msg.voice)}">
      <button class="v-play" title="Play"><svg class="v-ic-play" width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5.5v13l11-6.5L8 5.5Z"/></svg><svg class="v-ic-pause" width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5h3v14H8zM13 5h3v14h-3z"/></svg></button>
      <div class="v-bars">${bars}</div><span class="v-dur">${msg.voice}</span></div>`;
  }

  function poll(msg) {
    const p = msg.poll;
    return `<div class="poll"><div class="poll-q">${esc(p.q)}</div>${p.options.map((o, i) => {
      const pct = p.total ? Math.round(o.votes / p.total * 100) : 0;
      const chosen = p.voted === i + 1;
      return `<button class="poll-opt ${chosen ? 'chosen' : ''}" data-poll="${msg.id}" data-opt="${i}"><span class="poll-fill" style="width:${pct}%"></span><span class="poll-label">${chosen ? '<svg width=14 height=14 viewBox="0 0 24 24" fill=none><path d="m5 12 4 4 10-10" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg>' : ''}${esc(o.text)}</span><span class="poll-pct">${pct}%</span></button>`;
    }).join('')}<div class="poll-foot">${p.total} votes · ${p.multi ? 'Multiple choice' : 'Single choice'}</div></div>`;
  }

  function linkCard(l) {
    return `<a href="#" class="link-card"><div class="link-thumb"><svg width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M10 13a5 5 0 0 0 7 0l2-2a5 5 0 0 0-7-7l-1 1" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/><path d="M14 11a5 5 0 0 0-7 0l-2 2a5 5 0 0 0 7 7l1-1" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg></div><div class="link-meta"><span class="link-site">${esc(l.site)}</span><span class="link-title">${esc(l.title)}</span><span class="link-desc">${esc(l.desc)}</span></div></a>`;
  }

  function renderReax(msg) {
    if (!msg.reactions || !Object.keys(msg.reactions).length) return '';
    return `<div class="reax">${Object.entries(msg.reactions).map(([emo, ids]) => `<button class="reax-pill ${ids.includes(me.id) ? 'mine' : ''}" data-react="${msg.id}" data-emo="${emo}">${emo}<span>${ids.length}</span></button>`).join('')}<button class="reax-add" data-addreact="${msg.id}"><svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M9 13a3 3 0 0 0 6 0M9 9h.01M15 9h.01" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><circle cx="12" cy="12" r="8.3" stroke="currentColor" stroke-width="1.6"/></svg></button></div>`;
  }

  /* ---------- right panel ---------- */
  function renderPanel(c) {
    const m = convoMeta(c);
    const pinned = c.messages.filter(x => (c.pinnedIds || []).includes(x.id));
    let html = `
      <div class="panel-hero">
        ${avatar(m.av, 72)}
        <p class="panel-name">${esc(m.name)}</p>
        <p class="panel-sub">${m.group ? esc(c.desc) : '@' + m.u.username}</p>
        <div class="panel-quick">
          ${m.group ? '' : `${quick('phone', 'Call', 'qCall')}${quick('video', 'Video', 'qVideo')}`}
          ${quick('bell', 'Mute')}${quick('search', 'Search', 'qSearch')}
        </div>
      </div>`;

    if (pinned.length) html += section('Pinned', `<svg width=14 height=14 viewBox="0 0 24 24" fill="currentColor"><path d="M9 3h6l-1 6 3 3v2H7v-2l3-3-1-6Z"/></svg>`, pinned.map(p => `<button class="pin-item" data-jump="${p.id}"><span class="pin-au">${users[p.user].name.split(' ')[0]}</span><span class="pin-tx">${esc((p.text || '').slice(0, 48))}</span></button>`).join(''));

    if (m.group) html += section('Members · ' + c.members.length, '', c.members.map(id => { const u = users[id]; return `<div class="mem" data-uid="${id}" style="cursor:pointer"><span class="avwrap">${avatar(u, 32)}${u.online ? `<span class="pres sm" style="background:${statusColor[u.status]}"></span>` : ''}</span><div class="mem-info"><span class="mem-name">${esc(u.name)}${id === c.members[0] ? '<span class="role">admin</span>' : ''}</span><span class="mem-sub">${u.online ? 'online' : 'offline'}</span></div></div>`; }).join(''));

    html += section('Shared files', '', `
      ${fileItem('brand-palette.fig', '2.4 MB', '#7c3aed')}
      ${fileItem('onboarding-v3.pdf', '1.1 MB', '#ef4444')}
      ${fileItem('crit-notes.md', '12 KB', '#10b981')}`);

    $('#rightPanel .panel-scroll').innerHTML = html;
    $$('[data-jump]').forEach(b => b.addEventListener('click', () => jumpTo(b.dataset.jump)));
    $$('#rightPanel [data-uid]').forEach(b => b.addEventListener('click', () => CPOverlays.openProfile(+b.dataset.uid)));
    if (c.type === 'direct') {
      const peer = users[c.with];
      $('#qCall')?.addEventListener('click', () => CPOverlays.openCall(peer, 'audio', true, c.id));
      $('#qVideo')?.addEventListener('click', () => CPOverlays.openCall(peer, 'video', true, c.id));
    }
    $('#qSearch')?.addEventListener('click', openThreadSearch);
  }
  function applyFilter(name) {
    const f = (name || 'All').toLowerCase();
    const wrap = $('#convoList');
    const items = conversations.filter(c => {
      if (c.archived) return false;
      if (f === 'unread') return c.unread > 0;
      if (f === 'groups') return c.type === 'group';
      if (f === 'dms') return c.type === 'direct';
      return true;
    });
    if (!items.length) { wrap.innerHTML = `<div class="list-empty"><div class="estate-ic"><svg width="26" height="26" viewBox="0 0 24 24" fill="none"><path d="M4 6.5C4 5.12 5.12 4 6.5 4h11C18.88 4 20 5.12 20 6.5v7c0 1.38-1.12 2.5-2.5 2.5H10l-3.6 3a1 1 0 0 1-1.65-.77V6.5Z" stroke="currentColor" stroke-width="1.8"/></svg></div><h4>Nothing here</h4><p>No ${esc(f)} conversations right now.</p></div>`; return; }
    renderListItems(items);
  }

  /* ---------- small builders ---------- */
  function section(title, ico, body) { return `<div class="psec"><div class="psec-h">${ico}<span>${title}</span></div><div class="psec-b">${body}</div></div>`; }
  function fileItem(name, size, color) { return `<button class="file-item"><span class="file-ic" style="background:${color}1a;color:${color}"><svg width=16 height=16 viewBox="0 0 24 24" fill=none><path d="M7 3h7l5 5v13H7V3Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="M14 3v5h5" stroke="currentColor" stroke-width="1.7"/></svg></span><span class="file-meta"><span class="file-name">${name}</span><span class="file-size">${size}</span></span></button>`; }
  function quick(ic, label, id) { return `<button class="qbtn" title="${label}" ${id ? `id="${id}"` : ''}>${svg(ic)}</button>`; }
  function iconBtn(ic, label, id) { return `<button class="hbtn" title="${label}" ${id ? `id="${id}"` : ''}>${svg(ic)}</button>`; }
  function tool(ic, label, on) { return `<button class="tbtn${on ? ' on' : ''}" title="${label}" data-tool="${ic}">${svg(ic)}</button>`; }

  function svg(name) {
    const p = {
      search: '<circle cx="11" cy="11" r="6.5" stroke="currentColor" stroke-width="1.7"/><path d="m20 20-3.2-3.2" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>',
      phone: '<path d="M6.2 4.5 8 4l1.6 3.4-1.5 1.3a11 11 0 0 0 4.7 4.7l1.3-1.5L17.5 15l-.5 1.8c-.2.7-.9 1.1-1.6 1A14 14 0 0 1 4.2 6.6c-.1-.7.3-1.4 1-1.6Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round" fill="none"/>',
      video: '<path d="M15 10.5 20 7v10l-5-3.5M4 7.5C4 6.7 4.7 6 5.5 6h8c.8 0 1.5.7 1.5 1.5v9c0 .8-.7 1.5-1.5 1.5h-8C4.7 18 4 17.3 4 16.5v-9Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round" fill="none"/>',
      panel: '<rect x="4" y="5" width="16" height="14" rx="2" stroke="currentColor" stroke-width="1.7"/><path d="M15 5v14" stroke="currentColor" stroke-width="1.7"/>',
      bell: '<path d="M6 9a6 6 0 0 1 12 0c0 5 2 6 2 6H4s2-1 2-6Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="M10 19a2 2 0 0 0 4 0" stroke="currentColor" stroke-width="1.7"/>',
      react: '<circle cx="12" cy="12" r="8.3" stroke="currentColor" stroke-width="1.6"/><path d="M9 13a3 3 0 0 0 6 0M9 9.5h.01M15 9.5h.01" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>',
      reply: '<path d="M9 7 4 12l5 5M4 12h10a6 6 0 0 1 6 6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>',
      edit: '<path d="M5 19h4L19 9l-4-4L5 15v4Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>',
      pin: '<path d="M9 3h6l-1 6 3 3v2H7v-2l3-3-1-6Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/><path d="M12 14v7" stroke="currentColor" stroke-width="1.6"/>',
      bookmark: '<path d="M7 4h10v16l-5-3.5L7 20V4Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>',
      more: '<circle cx="5" cy="12" r="1.6" fill="currentColor"/><circle cx="12" cy="12" r="1.6" fill="currentColor"/><circle cx="19" cy="12" r="1.6" fill="currentColor"/>',
      forward: '<path d="M13 7l5 5-5 5M18 12H7a3 3 0 0 0-3 3v2" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>',
      copy: '<rect x="8" y="8" width="12" height="12" rx="2" stroke="currentColor" stroke-width="1.7"/><path d="M16 8V6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h2" stroke="currentColor" stroke-width="1.7"/>',
      trash: '<path d="M5 7h14M10 7V5h4v2M6 7l1 13h10l1-13" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>',
      poll: '<path d="M7 20V10M12 20V4M17 20v-7" stroke="currentColor" stroke-width="1.9" stroke-linecap="round"/>',
      file: '<path d="M7 3h7l5 5v13H7V3Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="M14 3v5h5" stroke="currentColor" stroke-width="1.7"/>',
      clock: '<circle cx="12" cy="12" r="8.3" stroke="currentColor" stroke-width="1.7"/><path d="M12 8v4.5l3 2" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>',
      unpin: '<path d="M9 3h6l-1 6 3 3v2H7v-2l3-3-1-6Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/><path d="M12 14v7M4 4l16 16" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>',
      star: '<path d="M12 4l2.4 5 5.4.6-4 3.7 1.1 5.3L12 16.9 7.1 18.6l1.1-5.3-4-3.7 5.4-.6L12 4Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/>',
      read: '<path d="m3 13 3.2 3.2L13 9.5M11 13l3 3 7-7.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>',
      unread: '<circle cx="12" cy="12" r="5" fill="currentColor"/>',
      archive: '<rect x="4" y="5" width="16" height="4" rx="1" stroke="currentColor" stroke-width="1.7"/><path d="M5 9v9a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V9M10 13h4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>',
      eraser: '<path d="M4 16 14 6l4 4-7 7H7l-3-3Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="M9 21h11" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>',
      eye: '<path d="M2.5 12S6 5.5 12 5.5 21.5 12 21.5 12 18 18.5 12 18.5 2.5 12 2.5 12Z" stroke="currentColor" stroke-width="1.7"/><circle cx="12" cy="12" r="2.6" stroke="currentColor" stroke-width="1.7"/>',
      flag: '<path d="M5 21V4m0 1h11l-2 4 2 4H5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>',
    }[name] || '';
    return `<svg width="18" height="18" viewBox="0 0 24 24" fill="none">${p}</svg>`;
  }

  /* ---------- interactions ---------- */
  function bindThread(c) {
    $$('[data-react]').forEach(b => b.addEventListener('click', () => toggleReact(c, b.dataset.react, b.dataset.emo)));
    $$('[data-addreact]').forEach(b => b.addEventListener('click', e => openEmoji(c, b.dataset.addreact, e.currentTarget)));
    $$('[data-poll]').forEach(b => b.addEventListener('click', () => votePoll(c, b.dataset.poll, +b.dataset.opt)));
    $$('.msg-tools [data-tool]').forEach(b => b.addEventListener('click', e => {
      const id = e.currentTarget.closest('[data-msg]').dataset.msg, kind = e.currentTarget.dataset.tool;
      if (kind === 'react') openEmoji(c, id, e.currentTarget);
      else if (kind === 'reply') startReply(c, id);
      else if (kind === 'pin') togglePin(c, id);
      else if (kind === 'edit') startEdit(c, id);
      else if (kind === 'forward') forwardMessage(c, id);
      else if (kind === 'bookmark') toggleBookmark(c, id, e.currentTarget);
      else moreMenu(c, id, e.currentTarget);
    }));
    $$('[data-voice]').forEach(v => v.querySelector('.v-play').addEventListener('click', () => toggleVoice(v)));
    $$('[data-retry]').forEach(b => b.addEventListener('click', () => retryMessage(c, b.dataset.retry)));
    $$('[data-upretry]').forEach(b => b.addEventListener('click', () => retryUpload(c, b.dataset.upretry)));
    $('#loadEarlier')?.addEventListener('click', () => loadEarlier(c));
    $$('[data-spamshow]').forEach(b => b.addEventListener('click', () => { const m = c.messages.find(x => x.id === b.dataset.spamshow); if (m) { m.revealed = true; renderThread(c); } }));
    $$('[data-spamreport]').forEach(b => b.addEventListener('click', () => { const m = c.messages.find(x => x.id === b.dataset.spamreport); if (m) reportMessage(c, m); }));
    $$('[data-lightbox]').forEach(b => b.addEventListener('click', () => openLightbox(b.dataset.lightbox)));
    $$('.msg-info-trigger[data-msginfo]').forEach(b => b.addEventListener('click', e => { e.stopPropagation(); openMsgInfo(c, b.dataset.msginfo); }));
  }
  function openLightbox(src) {
    const ov = document.createElement('div'); ov.className = 'lightbox-ov';
    ov.innerHTML = `<button class="lb-close"><svg width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M6 6l12 12M18 6 6 18" stroke="#fff" stroke-width="2" stroke-linecap="round"/></svg></button><img src="${src}" />`;
    document.body.appendChild(ov);
    ov.addEventListener('click', () => ov.remove());
  }

  /* ---------- voice playback ---------- */
  let playingVoice = null;
  function toggleVoice(v) {
    if (playingVoice && playingVoice !== v) stopVoice(playingVoice);
    if (v._timer) pauseVoice(v); else playVoice(v);
  }
  function playVoice(v) {
    const total = +v.dataset.dur || 1;
    const bars = [...v.querySelectorAll('.v-bars span')];
    const durEl = v.querySelector('.v-dur');
    v.classList.add('playing'); playingVoice = v;
    let start = Date.now() - (v._elapsed || 0) * 1000;
    v._timer = setInterval(() => {
      let el = (Date.now() - start) / 1000;
      if (el >= total) el = total;
      const p = el / total;
      bars.forEach((b, i) => b.classList.toggle('played', (i + 1) / bars.length <= p));
      durEl.textContent = fmtDur(el);
      v._elapsed = el;
      if (el >= total) { clearInterval(v._timer); v._timer = null; setTimeout(() => stopVoice(v), 200); }
    }, 60);
  }
  function pauseVoice(v) {
    clearInterval(v._timer); v._timer = null; v.classList.remove('playing');
    if (playingVoice === v) playingVoice = null;
  }
  function stopVoice(v) {
    clearInterval(v._timer); v._timer = null; v._elapsed = 0; v.classList.remove('playing');
    v.querySelectorAll('.v-bars span').forEach(b => b.classList.remove('played'));
    v.querySelector('.v-dur').textContent = fmtDur(+v.dataset.dur);
    if (playingVoice === v) playingVoice = null;
  }

  function toggleReact(c, msgId, emo) {
    const msg = c.messages.find(m => m.id === msgId); if (!msg || !msgId.startsWith('db')) return;
    msg.reactions = msg.reactions || {};
    const arr = msg.reactions[emo] = msg.reactions[emo] || [];
    const i = arr.indexOf(me.id);
    if (i > -1) { arr.splice(i, 1); if (!arr.length) delete msg.reactions[emo]; } else arr.push(me.id);
    renderThread(c);
    const url = (R.react || '/messages/{msg}/reactions').replace('{msg}', msgDbId(msgId));
    apiPost(url, { emoji: emo }).catch(() => {});
  }
  function openEmoji(c, msgId, anchor) {
    closePops();
    const pop = document.createElement('div'); pop.className = 'emoji-pop';
    pop.innerHTML = reactionsPool.map(e => `<button data-e="${e}">${e}</button>`).join('');
    document.body.appendChild(pop);
    const r = anchor.getBoundingClientRect();
    pop.style.top = (r.top - 48) + 'px'; pop.style.left = Math.min(r.left, window.innerWidth - 260) + 'px';
    $$('button', pop).forEach(b => b.addEventListener('click', () => { toggleReact(c, msgId, b.dataset.e); closePops(); }));
    setTimeout(() => document.addEventListener('click', closePops, { once: true }), 0);
  }
  function closePops() { $$('.emoji-pop, .pop-menu').forEach(p => p.remove()); }

  function votePoll(c, msgId, opt) {
    const msg = c.messages.find(m => m.id === msgId);
    const p = msg.poll;
    if (!p) return;
    // Optimistic update
    if (p.voted === opt + 1) return;
    if (p.voted) p.options[p.voted - 1].votes--; else p.total++;
    p.options[opt].votes++; p.voted = opt + 1;
    renderThread(c);
    // Backend sync — need poll DB id stored on poll object
    if (p.dbId) {
      const optionId = p.options[opt].dbId;
      if (optionId) {
        const url = (R.pollVote || '/polls/{poll}/vote').replace('{poll}', p.dbId);
        apiPost(url, { option_id: optionId }).then(data => {
          if (data && data.options) {
            // Update with real counts from server
            data.options.forEach((o, i) => { if (p.options[i]) p.options[i].votes = o.votes_count; });
            p.total = data.total_votes;
            renderThread(c);
          }
        }).catch(() => {});
      }
    }
  }
  function togglePin(c, msgId) {
    c.pinnedIds = c.pinnedIds || [];
    const msg = c.messages.find(m => m.id === msgId);
    const i = c.pinnedIds.indexOf(msgId);
    const dbMsg = msgDbId(msgId); const dbConv = convDbId(c);
    if (i > -1) {
      c.pinnedIds.splice(i, 1); msg.pinned = false; toast('Message unpinned');
      const url = (R.pinRemove || '/conversations/{conv}/pins/{msg}').replace('{conv}', dbConv).replace('{msg}', dbMsg);
      apiDelete(url).catch(() => {});
    } else {
      if (c.pinnedIds.length >= 10) { toast('Max 10 pins reached', true); return; }
      c.pinnedIds.push(msgId); msg.pinned = true; toast('Message pinned');
      const url = (R.pinAdd || '/conversations/{conv}/pins').replace('{conv}', dbConv);
      apiPost(url, { message_id: +dbMsg }).catch(() => {});
    }
    renderThread(c); renderPanel(c);
  }

  let replyTo = null;
  function startReply(c, msgId) {
    const msg = c.messages.find(m => m.id === msgId); replyTo = msgId;
    const u = users[msg.user];
    $('#replyBar').innerHTML = `<span class="reply-bar"></span><div class="rb-text"><span class="rb-name">Replying to ${esc(u.name)}</span><span class="rb-msg">${esc((msg.text || 'Voice message').slice(0, 70))}</span></div><button id="rbCancel">✕</button>`;
    $('#replyBar').classList.add('show');
    $('#rbCancel').addEventListener('click', cancelReply);
    $('#composer').focus();
  }
  function cancelReply() { replyTo = null; $('#replyBar').classList.remove('show'); }

  function jumpTo(msgId) {
    const el = $(`[data-msg="${msgId}"]`); if (!el) return;
    el.scrollIntoView ? null : null; // avoid scrollIntoView per guidance
    const t = $('#thread'); t.scrollTop = el.offsetTop - 80;
    el.classList.add('flash'); setTimeout(() => el.classList.remove('flash'), 1500);
  }

  /* ---------- send ---------- */
  function send() {
    const comp = $('#composer'); const text = comp.innerText.trim();
    const c = conversations.find(x => x.id === activeId);
    const now = new Date().toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });
    // flush any pending attachments first
    if (pendingAtt.length) {
      flushAttachments(c);
      if (text) { c.messages.push({ id: 'x' + Date.now(), user: me.id, t: now, text, read: false }); c.last = 'You: ' + text; }
      c.time = now; comp.innerHTML = '';
      renderThread(c); renderList($('#search').value);
      updateSendMic();
      return;
    }
    if (!text) return;
    const msg = { id: 'x' + Date.now(), user: me.id, t: now, text, status: 'sending' };
    if (replyTo) { msg.reply = replyTo; cancelReply(); }
    c.messages.push(msg);
    c.last = 'You: ' + text; c.time = now;
    comp.innerHTML = '';
    renderThread(c); renderList($('#search').value);
    updateSendMic();
    if (window.Echo) {
      clearTimeout(typingTimer);
      typingTimer = null;
      window.Echo.private('conversation.' + convDbId(c)).whisper('stop-typing', { user_id: me.id });
    }
    deliverMessage(c, msg);
  }

  /* ---------- delivery lifecycle — real backend ---------- */
  const R = window.CP_ROUTES || {};
  function apiPost(url, body) {
    return fetch(url, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': R.csrf || '', 'Accept': 'application/json' },
      body: JSON.stringify(body),
    });
  }
  function apiPatch(url, body) {
    return fetch(url, {
      method: 'PATCH',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': R.csrf || '', 'Accept': 'application/json' },
      body: JSON.stringify(body),
    });
  }
  function apiDelete(url) {
    return fetch(url, {
      method: 'DELETE',
      headers: { 'X-CSRF-TOKEN': R.csrf || '', 'Accept': 'application/json' },
    });
  }
  function convDbId(c) { return c.id.replace(/^c/, ''); }
  function msgDbId(msgId) { return msgId.replace(/^db/, ''); }

  function deliverMessage(c, msg) {
    if (!navigator.onLine) { msg.status = 'failed'; if (c.id === activeId) renderThread(c); renderList($('#search').value); return; }
    const url = (R.sendMessage || '/conversations/{conv}/messages').replace('{conv}', convDbId(c));
    const body = { body: msg.text };
    if (msg.reply) body.parent_id = msg.reply.replace(/^db/, '');
    apiPost(url, body).then(res => {
      if (!res.ok) { msg.status = 'failed'; if (c.id === activeId) renderThread(c); renderList($('#search').value); return; }
      return res.json();
    }).then(data => {
      if (!data) return;
      const saved = data.message;
      msg.id = 'db' + saved.id;
      msg.status = 'sent';
      if (c.id === activeId) renderThread(c);
      // 'delivered' and 'read' only via real-time (Reverb WebSocket) — not faked
    }).catch(() => { msg.status = 'failed'; if (c.id === activeId) renderThread(c); renderList($('#search').value); });
  }

  function retryMessage(c, msgId) {
    const msg = c.messages.find(m => m.id === msgId); if (!msg) return;
    msg.status = 'sending'; msg.t = nowTime();
    if (c.id === activeId) renderThread(c);
    deliverMessage(c, msg);
  }


  /* ---------- toast ---------- */
  function toast(msg, err) {
    const t = document.createElement('div'); t.className = 'toast ' + (err ? 'err' : '');
    t.textContent = msg; $('#toasts').appendChild(t);
    setTimeout(() => t.classList.add('out'), 2200); setTimeout(() => t.remove(), 2600);
  }

  /* ---------- select ---------- */
  function selectConvo(id) {
    // clear the unread divider on the conversation we're leaving
    if (activeId && activeId !== id) { const prev = conversations.find(x => x.id === activeId); if (prev) prev.firstUnreadId = null; }
    backToChats();
    activeId = id; const c = conversations.find(x => x.id === id); c.unread = 0;
    cancelReply();
    // mark as read on backend
    markConvRead(c);
    $('.composer-wrap').style.display = '';
    renderList($('#search').value); renderHeader(c); renderThread(c); renderPanel(c);
    document.body.classList.add('mobile-chat');
  }
  function startDraft() {
    CPModals.openNewChat(startDirect, startGroup);
  }
  function startDirect(uid) {
    const existing = conversations.find(c => c.type === 'direct' && c.with === uid && !c.archived);
    if (existing) { selectConvo(existing.id); return; }
    const u = users[uid];
    apiPost(R.startDirect || '/conversations/direct', { user_id: uid }).then(r => r.json()).then(data => {
      // server may have found existing or created new
      const existNow = conversations.find(c => c.id === data.id);
      if (existNow) { selectConvo(existNow.id); return; }
      const newConv = { id: data.id, type: 'direct', with: uid, unread: 0, time: 'now', last: '', read: true, messages: [], pinnedIds: [] };
      conversations.unshift(newConv);
      subscribeConv(newConv);
      selectConvo(data.id);
      toast('Chat started with ' + u.name.split(' ')[0]);
    }).catch(() => toast('Could not start chat', true));
  }
  function startGroup() {
    CPModals.openNewGroup(g => {
      const payload = { name: g.name, description: g.desc || '', is_private: !g.pub, member_ids: g.members || [] };
      apiPost(R.createGroup || '/groups', payload).then(r => {
        if (!r.ok) { toast('Could not create group', true); return; }
        // server redirects; reload to get the new group in conversation list
        location.href = R.chat || '/chat';
      }).catch(() => toast('Could not create group', true));
    });
  }

  /* ---------- user menu (rail avatar) ---------- */
  function openUserMenu() {
    closePops();
    const anchor = $('.rail-ava');
    const statusColors = { available: '#10b981', busy: '#ef4444', away: '#f59e0b' };
    const statusLabels = { available: 'Available', busy: 'Busy', away: 'Away' };
    const curStatus = me.status || 'available';

    const menu = document.createElement('div');
    menu.className = 'pop-menu user-menu';
    menu.style.cssText = 'min-width:220px;padding:8px;';
    menu.innerHTML = `
      <div class="um-hero">
        <div class="avatar" style="width:40px;height:40px;background:linear-gradient(135deg,${me.grad[0]},${me.grad[1]});font-size:15px;border-radius:50%;display:grid;place-items:center;color:#fff;font-weight:700">${me.initials}</div>
        <div class="um-info">
          <div class="um-name">${esc(me.name)}</div>
          <div class="um-handle">@${esc(me.username)}</div>
        </div>
      </div>
      <div class="pm-sep"></div>
      <div class="um-status-row">
        ${['available','busy','away'].map(s => `<button class="um-st ${s === curStatus ? 'active' : ''}" data-st="${s}" style="--stc:${statusColors[s]}"><span class="um-dot" style="background:${statusColors[s]}"></span>${statusLabels[s]}</button>`).join('')}
      </div>
      <div class="pm-sep"></div>
      <button class="pm-item" id="umEditProfile"><svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M16.5 4.5 19.5 7.5 9 18l-3.6.6.6-3.6L16.5 4.5Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/></svg><span>Edit profile</span></button>
      <button class="pm-item" id="umSettings"><svg width="16" height="16" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.8"/><path d="M12 3.5v2M12 18.5v2M5.5 5.5l1.4 1.4M17.1 17.1l1.4 1.4M3.5 12h2M18.5 12h2M5.5 18.5l1.4-1.4M17.1 6.9l1.4-1.4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg><span>Settings</span></button>
      <div class="pm-sep"></div>
      <button class="pm-item danger" id="umLogout"><svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M16 17l5-5-5-5M21 12H9M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg><span>Log out</span></button>`;

    document.body.appendChild(menu);
    const r = anchor.getBoundingClientRect();
    menu.style.left = (r.right + 10) + 'px';
    menu.style.top = Math.min(r.top, window.innerHeight - menu.offsetHeight - 10) + 'px';

    // status buttons
    menu.querySelectorAll('[data-st]').forEach(b => b.addEventListener('click', () => {
      const st = b.dataset.st;
      me.status = st;
      const dot = $('.rail-ava .pres');
      if (dot) dot.style.background = statusColors[st];
      menu.querySelectorAll('[data-st]').forEach(x => x.classList.toggle('active', x.dataset.st === st));
      apiPost(R.heartbeat || '/presence/heartbeat', { status: st }).catch(() => {});
    }));

    menu.querySelector('#umEditProfile')?.addEventListener('click', () => { closePops(); CPAccount?.openEditProfile(); });
    menu.querySelector('#umSettings')?.addEventListener('click', () => { closePops(); location.href = R.settings || '/settings'; });
    menu.querySelector('#umLogout')?.addEventListener('click', () => {
      closePops();
      const f = document.createElement('form'); f.method = 'POST'; f.action = '/logout';
      const t = document.createElement('input'); t.type = 'hidden'; t.name = '_token'; t.value = R.csrf || '';
      f.appendChild(t); document.body.appendChild(f); f.submit();
    });

    setTimeout(() => document.addEventListener('click', closePops, { once: true }), 0);
  }

  /* ---------- dark mode ---------- */
  function initDark() {
    const saved = localStorage.getItem('cp-dark');
    if (saved === '1') document.documentElement.classList.add('dark');
    $('#darkToggle')?.addEventListener('click', () => {
      const on = document.documentElement.classList.toggle('dark');
      localStorage.setItem('cp-dark', on ? '1' : '0');
    });
  }

  /* ---------- nav rail ---------- */
  function initRail() {
    const routes = { admin: 'admin', settings: 'settings' };
    const inline = { chat: 'chats' };
    const handleNav = (nav, btn, group) => {
      if (routes[nav]) { location.href = (window.CP_ROUTES && window.CP_ROUTES[nav]) ? window.CP_ROUTES[nav] : '/' + nav; return; }
      if (inline[nav]) {
        $$(group).forEach(n => n.classList.remove('active')); btn.classList.add('active');
        if (nav === 'chat') { backToChats(); renderList($('#search').value); document.body.classList.remove('mobile-chat'); }
        else openView(inline[nav]);
      }
    };
    $$('#rail [data-nav]').forEach(b => b.addEventListener('click', () => handleNav(b.dataset.nav, b, '#rail [data-nav]')));
    $('#backBtn')?.addEventListener('click', () => document.body.classList.remove('mobile-chat'));
    $$('#mobileTabs [data-mnav]').forEach(b => b.addEventListener('click', () => {
      if (routes[b.dataset.mnav]) { location.href = (window.CP_ROUTES && window.CP_ROUTES[b.dataset.mnav]) ? window.CP_ROUTES[b.dataset.mnav] : '/' + b.dataset.mnav; return; }
      handleNav(b.dataset.mnav, b, '#mobileTabs .mtab');
    }));
  }

  /* ---------- composer ---------- */
  /* ---------- typing indicator ---------- */
  let typingTimer = null;
  const typingConvChannels = {};

  function sendTypingWhisper() {
    if (!window.Echo || !activeId) return;
    const c = conversations.find(x => x.id === activeId);
    if (!c) return;
    const dbId = convDbId(c);
    // whisper on the private channel
    const ch = window.Echo.private('conversation.' + dbId);
    ch.whisper('typing', { user_id: me.id, name: me.name.split(' ')[0] });
    // auto-clear typing on this end after 3s of no input
    clearTimeout(typingTimer);
    typingTimer = setTimeout(() => {
      ch.whisper('stop-typing', { user_id: me.id });
    }, 800);
  }

  function initComposer() {
    $('#sendBtn').addEventListener('click', send);
    $('#composer').addEventListener('keydown', e => {
      if (e.key !== 'Enter') return;
      const enterSends = !window.CP.prefs || CP.prefs.enterToSend;
      if (enterSends ? !e.shiftKey : (e.metaKey || e.ctrlKey)) { e.preventDefault(); send(); }
    });
    $('#composer').addEventListener('input', () => { updateSendMic(); sendTypingWhisper(); });
    $('#search').addEventListener('input', e => renderList(e.target.value));
    $('.list-new')?.addEventListener('click', startDraft);
    initVoiceRecorder();
    $$('#composer-tools [data-ct]').forEach(b => b.addEventListener('click', e => {
      const k = b.dataset.ct;
      if (k === 'plus') plusMenu(e.currentTarget);
      else if (k === 'schedule') scheduleCurrent();
      else if (k === 'emoji') openComposerEmoji(e.currentTarget);
    }));
    // file input
    $('#fileInput').addEventListener('change', e => { handleFiles(e.target.files); e.target.value = ''; });
    // filter tabs
    $$('.filters .filter').forEach(b => b.addEventListener('click', () => {
      $$('.filters .filter').forEach(x => x.classList.remove('on')); b.classList.add('on');
      const name = b.textContent.trim();
      if (name === 'Scheduled') { listView = 'scheduled'; renderScheduled(); }
      else { listView = 'chats'; applyFilter(name); }
    }));
    // rail avatar → user menu
    $('.rail-ava')?.addEventListener('click', (e) => {
      e.stopPropagation();
      openUserMenu();
    });
    updateSendMic();
  }

  /* ---------- send / mic morph ---------- */
  function updateSendMic() {
    const hasText = $('#composer').innerText.trim().length > 0 || pendingAtt.length > 0;
    $('#sendBtn').style.display = hasText ? 'grid' : 'none';
    $('#micBtn').style.display = hasText ? 'none' : 'grid';
  }

  /* ---------- voice recorder ---------- */
  let recTimer = null, recStart = 0, recSec = 0;
  let _mediaRecorder = null, _recChunks = [];

  function initVoiceRecorder() {
    $('#micBtn').addEventListener('click', startRecording);
    $('#recCancel').addEventListener('click', () => stopRecording(false));
    $('#recSend').addEventListener('click', () => stopRecording(true));
  }

  function startRecording() {
    if (!activeId) { toast('Open a conversation first'); return; }
    navigator.mediaDevices.getUserMedia({ audio: true }).then(stream => {
      _recChunks = [];
      _mediaRecorder = new MediaRecorder(stream);
      _mediaRecorder.ondataavailable = e => { if (e.data.size > 0) _recChunks.push(e.data); };
      _mediaRecorder.start(100);
      recSec = 0; recStart = Date.now();
      $('#composer-tools').style.display = 'none';
      $('#recBar').classList.add('show');
      $('#recTime').textContent = '0:00';
      recTimer = setInterval(() => {
        recSec = Math.floor((Date.now() - recStart) / 1000);
        $('#recTime').textContent = fmtDur(recSec);
      }, 250);
    }).catch(() => toast('Microphone access denied'));
  }

  function stopRecording(sendIt) {
    clearInterval(recTimer); recTimer = null;
    $('#recBar').classList.remove('show');
    $('#composer-tools').style.display = '';
    if (!_mediaRecorder) { if (!sendIt) toast('Recording discarded'); return; }
    const secs = Math.max(1, Math.round((Date.now() - recStart) / 1000));
    _mediaRecorder.onstop = () => {
      const blob = new Blob(_recChunks, { type: 'audio/webm' });
      _mediaRecorder.stream.getTracks().forEach(t => t.stop());
      _mediaRecorder = null; _recChunks = [];
      if (sendIt) sendVoice(blob, secs);
    };
    _mediaRecorder.stop();
    if (!sendIt) toast('Recording discarded');
  }

  function sendVoice(blob, secs) {
    const c = conversations.find(x => x.id === activeId);
    if (!c) return;
    const msg = { id: 'v' + Date.now(), user: me.id, t: nowTime(), voice: fmtDur(secs), status: 'sending', uploading: true, progress: 0, _voiceBlob: blob };
    c.messages.push(msg); c.last = 'You: 🎤 Voice message'; c.time = nowTime();
    renderThread(c); renderList($('#search').value);
    const R = window.CP_ROUTES || {};
    const convDbId = c.id.replace(/^c/, '');
    const url = (R.sendMessage || '/conversations/{conv}/messages').replace('{conv}', convDbId);
    const fd = new FormData();
    fd.append('attachments[]', blob, 'voice-' + Date.now() + '.webm');
    fd.append('type', 'voice');
    const xhr = new XMLHttpRequest();
    xhr.open('POST', url);
    xhr.setRequestHeader('X-CSRF-TOKEN', R.csrf || '');
    xhr.setRequestHeader('Accept', 'application/json');
    xhr.upload.onprogress = e => { if (e.lengthComputable) { msg.progress = (e.loaded / e.total) * 100; updateUploadProgress(c, msg); } };
    xhr.onload = () => {
      if (xhr.status >= 200 && xhr.status < 300) {
        const data = JSON.parse(xhr.responseText);
        msg.id = 'db' + data.message.id; msg.uploading = false; msg.status = 'sent';
        if (c.id === activeId) renderThread(c);
      } else { msg.uploading = false; msg.uploadFailed = true; msg.status = 'failed'; if (c.id === activeId) renderThread(c); }
    };
    xhr.onerror = () => { msg.uploading = false; msg.uploadFailed = true; msg.status = 'failed'; if (c.id === activeId) renderThread(c); };
    xhr.send(fd);
  }

  /* ---------- schedule a message ---------- */
  function scheduleCurrent() {
    const comp = $('#composer'); const text = comp.innerText.trim();
    if (!activeId) { toast('Open a conversation first'); return; }
    if (!text) { toast('Type a message to schedule'); return; }
    const anchor = $('#composer-tools [data-ct="schedule"]');
    popMenu(anchor, [
      { ic: 'clock', label: 'In 1 hour', fn: () => addScheduled(text, 'In 1 hour') },
      { ic: 'clock', label: 'Tonight · 8:00 PM', fn: () => addScheduled(text, 'Tonight · 8:00 PM') },
      { ic: 'clock', label: 'Tomorrow · 9:00 AM', fn: () => addScheduled(text, 'Tomorrow · 9:00 AM') },
      { ic: 'clock', label: 'Next week · Mon 9:00 AM', fn: () => addScheduled(text, 'Mon · 9:00 AM') },
    ]);
  }
  function addScheduled(text, when) {
    // Map label to actual datetime
    const now = new Date();
    const whenMap = {
      'In 1 hour': new Date(now.getTime() + 3600000),
      'Tonight · 8:00 PM': (() => { const d = new Date(now); d.setHours(20,0,0,0); return d; })(),
      'Tomorrow · 9:00 AM': (() => { const d = new Date(now); d.setDate(d.getDate()+1); d.setHours(9,0,0,0); return d; })(),
      'Mon · 9:00 AM': (() => { const d = new Date(now); const day = d.getDay(); d.setDate(d.getDate() + ((8-day)%7||7)); d.setHours(9,0,0,0); return d; })(),
    };
    const scheduledAt = whenMap[when] || new Date(now.getTime() + 3600000);
    const entry = { convoId: activeId, uid: me.id, when, text };
    scheduled.unshift(entry);
    $('#composer').innerHTML = ''; updateSendMic();
    toast('Message scheduled · ' + when);
    // Save to DB
    const c = conversations.find(x => x.id === activeId);
    if (c) {
      const url = (R.scheduleMsg || '/conversations/{conv}/messages').replace('{conv}', convDbId(c));
      fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': R.csrf || '', 'Accept': 'application/json' },
        body: JSON.stringify({ body: text, is_scheduled: true, scheduled_at: scheduledAt.toISOString() }),
      }).then(r => r.json()).then(data => {
        if (data && data.message) entry.dbId = data.message.id;
      }).catch(() => {});
    }
  }

  /* ---------- popover menu ---------- */
  function popMenu(anchor, items) {
    closePops();
    const m = document.createElement('div'); m.className = 'pop-menu';
    m.innerHTML = items.map((it, i) => it.sep ? '<div class="pm-sep"></div>' : `<button class="pm-item ${it.danger ? 'danger' : ''}" data-i="${i}">${svg(it.ic)}<span>${it.label}</span></button>`).join('');
    document.body.appendChild(m);
    const r = anchor.getBoundingClientRect();
    let top = r.bottom + 6, left = r.left;
    m.style.left = Math.min(left, window.innerWidth - 210) + 'px';
    m.style.top = Math.min(top, window.innerHeight - m.offsetHeight - 10) + 'px';
    items.forEach((it, i) => { if (it.fn) m.querySelector(`[data-i="${i}"]`)?.addEventListener('click', () => { closePops(); it.fn(); }); });
    setTimeout(() => document.addEventListener('click', closePops, { once: true }), 0);
  }
  function plusMenu(anchor) {
    const c = conversations.find(x => x.id === activeId);
    popMenu(anchor, [
      { ic: 'poll', label: 'Create poll', fn: () => CPModals.openPoll(p => {
        const tempId = 'p' + Date.now();
        const msg = { id: tempId, user: me.id, t: nowTime(), text: '', poll: p };
        c.messages.push(msg); c.last = 'You: created a poll';
        renderThread(c); renderList($('#search').value);
        const url = (R.pollStore || '/conversations/{conv}/polls').replace('{conv}', convDbId(c));
        apiPost(url, { question: p.q, options: p.options.map(o => o.text), is_multiple_choice: p.multi, is_anonymous: p.anon })
          .then(data => {
            if (data && data.poll) {
              p.dbId = data.poll.id;
              data.poll.options.forEach((o, i) => { if (p.options[i]) p.options[i].dbId = o.id; });
              msg.id = 'db' + data.message_id;
              renderThread(c);
            }
          }).catch(() => {});
      }) },
      { ic: 'file', label: 'Upload file', fn: () => $('#fileInput').click() },
      { ic: 'clock', label: 'Schedule message', fn: () => toast('Schedule picker') },
    ]);
  }
  function moreMenu(c, msgId, anchor) {
    const msg = c.messages.find(m => m.id === msgId); const mine = msg.user === me.id;
    const items = [
      { ic: 'forward', label: 'Forward', fn: () => forwardMessage(c, msgId) },
      { ic: 'copy', label: 'Copy text', fn: () => { navigator.clipboard?.writeText(msg.text || ''); toast('Copied to clipboard'); } },
    ];
    if (mine) items.push({ sep: true }, { ic: 'trash', label: 'Delete message', danger: true, fn: () => {
      msg.deleted = true; msg.text = 'This message was deleted'; delete msg.poll; delete msg.voice; delete msg.link;
      renderThread(c); toast('Message deleted');
      if (msgId.startsWith('db')) {
        const url = (R.deleteMessage || '/messages/{msg}').replace('{msg}', msgDbId(msgId));
        apiDelete(url).catch(() => {});
      }
    } });
    else items.push({ sep: true }, { ic: 'flag', label: 'Report message', danger: true, fn: () => reportMessage(c, msg) });
    popMenu(anchor, items);
  }
  function reportMessage(c, msg) {
    const u = users[msg.user];
    CPModals.openReport({ kind: 'message', name: u.name, preview: msg.text || '🎤 Voice message' }, res => {
      msg.reported = res.reason;
      if (res.block) { blockedUsers.add(msg.user); toast(u.name + ' blocked'); }
      renderThread(c);
    });
  }
  const blockedUsers = new Set();
  function forwardMessage(c, msgId) {
    const src = c.messages.find(m => m.id === msgId);
    CPModals.openForward(ids => {
      ids.forEach(cid => {
        const target = conversations.find(x => x.id === cid);
        target.messages.push({ id: 'fw' + Date.now() + cid, user: me.id, t: nowTime(), text: src.text || '', forwarded: true });
        target.last = 'You: forwarded a message';
      });
      renderList($('#search').value);
      if (msgId.startsWith('db')) {
        const url = (R.forward || '/messages/{msg}/forward').replace('{msg}', msgDbId(msgId));
        const convDbIds = ids.map(cid => +cid.replace(/^c/, ''));
        apiPost(url, { conversation_ids: convDbIds }).catch(() => {});
      }
      toast('Forwarded to ' + ids.length + ' conversation' + (ids.length > 1 ? 's' : ''));
    });
  }
  function toggleBookmark(c, msgId, btn) {
    const msg = c.messages.find(m => m.id === msgId); if (!msg) return;
    msg.bookmarked = !msg.bookmarked;
    if (btn) btn.classList.toggle('on', msg.bookmarked);
    toast(msg.bookmarked ? 'Saved to bookmarks' : 'Removed from saved');
    if (msgId.startsWith('db')) {
      const url = (R.bookmark || '/messages/{msg}/bookmark').replace('{msg}', msgDbId(msgId));
      apiPost(url, {}).catch(() => {});
    }
  }
  function nowTime() { return new Date().toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' }); }

  /* ---------- inline message edit ---------- */
  function startEdit(c, msgId) {
    const msg = c.messages.find(m => m.id === msgId);
    if (!msg || msg.user !== me.id || msg.deleted) { toast('You can only edit your own messages'); return; }
    const row = $(`[data-msg="${msgId}"] .b-body`);
    const textEl = $('.b-text', row);
    if (!textEl) { toast('Nothing to edit here'); return; }
    const box = document.createElement('div'); box.className = 'edit-box';
    box.innerHTML = `<textarea>${msg.text || ''}</textarea><div class="edit-acts"><button class="edit-cancel">Cancel</button><button class="edit-save">Save</button></div>`;
    textEl.replaceWith(box);
    const ta = $('textarea', box); ta.focus(); ta.setSelectionRange(ta.value.length, ta.value.length);
    ta.style.height = ta.scrollHeight + 'px';
    ta.addEventListener('input', () => { ta.style.height = 'auto'; ta.style.height = ta.scrollHeight + 'px'; });
    const save = () => {
      const v = ta.value.trim(); if (!v) return;
      msg.text = v; msg.edited = true; renderThread(c);
      if (msgId.startsWith('db')) {
        const url = (R.editMessage || '/messages/{msg}').replace('{msg}', msgDbId(msgId));
        apiPatch(url, { body: v }).catch(() => {});
      }
    };
    $('.edit-save', box).addEventListener('click', save);
    $('.edit-cancel', box).addEventListener('click', () => renderThread(c));
    ta.addEventListener('keydown', e => { if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); save(); } if (e.key === 'Escape') renderThread(c); });
  }

  /* ---------- file / image upload ---------- */
  let pendingAtt = [];
  function fmtSize(b) { return b < 1024 ? b + ' B' : b < 1048576 ? (b / 1024).toFixed(0) + ' KB' : (b / 1048576).toFixed(1) + ' MB'; }
  function handleFiles(files) {
    [...files].forEach(f => {
      const isImg = f.type.startsWith('image/');
      const att = { name: f.name, size: fmtSize(f.size), isImg, src: null, _file: f };
      if (isImg) { const url = URL.createObjectURL(f); att.src = url; }
      pendingAtt.push(att);
    });
    renderAttachTray();
  }
  function renderAttachTray() {
    const tray = $('#attachTray');
    if (!pendingAtt.length) { tray.className = ''; tray.innerHTML = ''; updateSendMic(); return; }
    tray.className = 'show';
    const colors = { pdf: '#ef4444', doc: '#2563eb', docx: '#2563eb', zip: '#f59e0b', fig: '#a855f7' };
    tray.innerHTML = pendingAtt.map((a, i) => {
      const ext = (a.name.split('.').pop() || '').toLowerCase();
      const thumb = a.isImg ? `<img class="att-thumb" src="${a.src}" />` : `<span class="att-ic" style="background:${colors[ext] || '#10b981'}"><svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M14 3v5h5M7 3h8l5 5v11a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z" stroke="#fff" stroke-width="1.8" stroke-linejoin="round"/></svg></span>`;
      return `<div class="att-chip">${thumb}<span class="att-nm">${esc(a.name)}</span><button class="att-x" data-rmatt="${i}"><svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M6 6l12 12M18 6 6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg></button></div>`;
    }).join('');
    $$('[data-rmatt]', tray).forEach(b => b.addEventListener('click', () => { pendingAtt.splice(+b.dataset.rmatt, 1); renderAttachTray(); }));
    updateSendMic();
  }
  function flushAttachments(c) {
    pendingAtt.forEach(a => {
      const m = { id: 'a' + Date.now() + Math.random().toString(36).slice(2, 5), user: me.id, t: nowTime(), uploading: true, progress: 0, status: 'sending', _file: a._file };
      if (a.isImg) { m.image = { src: a.src, name: a.name }; c.last = 'You: 📷 Photo'; }
      else { m.file = { name: a.name, size: a.size }; c.last = 'You: 📎 ' + a.name; }
      c.messages.push(m);
      startUpload(c, m);
    });
    pendingAtt = []; renderAttachTray();
  }
  function startUpload(c, m) {
    if (!navigator.onLine) { failUpload(c, m); return; }
    m.uploading = true; m.uploadFailed = false;
    const R = window.CP_ROUTES || {};
    const convDbId = c.id.replace(/^c/, '');
    const url = (R.sendMessage || '/conversations/{conv}/messages').replace('{conv}', convDbId);
    const fd = new FormData();
    if (m._file) fd.append('attachments[]', m._file, m._file.name);
    const xhr = new XMLHttpRequest();
    xhr.open('POST', url);
    xhr.setRequestHeader('X-CSRF-TOKEN', R.csrf || '');
    xhr.setRequestHeader('Accept', 'application/json');
    xhr.upload.onprogress = e => { if (e.lengthComputable) { m.progress = (e.loaded / e.total) * 100; updateUploadProgress(c, m); } };
    xhr.onload = () => {
      if (xhr.status >= 200 && xhr.status < 300) {
        const data = JSON.parse(xhr.responseText);
        m.id = 'db' + data.message.id; m.uploading = false; m.status = 'sent';
        if (c.id === activeId) renderThread(c);
      } else { failUpload(c, m); }
    };
    xhr.onerror = () => { failUpload(c, m); };
    xhr.send(fd);
  }
  function updateUploadProgress(c, m) {
    if (c.id !== activeId) return;
    const el = $(`[data-msg="${m.id}"]`); if (!el) return;
    el.querySelectorAll('.up-bar-fill').forEach(b => b.style.width = m.progress + '%');
    el.querySelectorAll('.up-pct').forEach(p => p.textContent = Math.round(m.progress) + '%');
  }
  function failUpload(c, m) {
    clearInterval(m._iv); m.uploading = false; m.uploadFailed = true; m.status = 'failed';
    if (c.id === activeId) renderThread(c);
  }
  function retryUpload(c, msgId) {
    const m = c.messages.find(x => x.id === msgId); if (!m) return;
    m.uploadFailed = false; m.uploading = true; m.progress = 0; m.status = 'sending';
    if (c.id === activeId) renderThread(c);
    startUpload(c, m);
  }

  /* ---------- emoji picker (composer) ---------- */
  const EMOJI = {
    '😀': ['😀','😃','😄','😁','😆','😅','🤣','😂','🙂','🙃','😉','😊','😇','🥰','😍','🤩','😘','😗','😋','😜','🤪','😝','🤗','🤔','🤨','😐','😶','🙄','😏','😬','😴','🤤','😪','😵','🥳','🥺','😎','🤓','🧐','🙁','😤','😢','😭','😱','😡'],
    '👍': ['👍','👎','👌','🤌','✌️','🤞','🤟','🤘','👏','🙌','🙏','💪','👋','🤙','✊','👊','🫶','🤝','💅','👀','🫡','🤷','🤦','💯','🔥','✨','⭐','🎉','🎊','❤️','🧡','💛','💚','💙','💜','🖤','🤍','💔','💖','💕'],
    '🐶': ['🐶','🐱','🐭','🐹','🐰','🦊','🐻','🐼','🐨','🐯','🦁','🐮','🐷','🐸','🐵','🐔','🐧','🐦','🦆','🦉','🦄','🐝','🦋','🐢','🐠','🐬','🌸','🌼','🌻','🌹','🌳','🌴','🌵','🍀','🌙','⛅','🌧️','❄️','🌈','🔥'],
    '🍎': ['🍎','🍐','🍊','🍋','🍌','🍉','🍇','🍓','🫐','🍒','🍑','🥭','🍍','🥥','🥝','🍅','🥑','🥦','🌽','🥕','🍞','🧀','🍗','🍔','🍟','🍕','🌭','🌮','🍣','🍜','🍦','🍰','🎂','🍫','🍬','☕','🍵','🍺','🍷','🥂'],
    '⚽': ['⚽','🏀','🏈','⚾','🎾','🏐','🏉','🎱','🏓','🏸','🥅','⛳','🎯','🎮','🎲','🎸','🎺','🎻','🥁','🎨','✈️','🚗','🚀','🏆','🥇','📱','💻','⌚','📷','🎧','💡','🔑','🎁','📌','📎','✏️','📚','💰','💎','🔔'],
  };
  function openComposerEmoji(anchor) {
    closePops();
    const panel = document.createElement('div'); panel.className = 'emoji-panel';
    const cats = Object.keys(EMOJI);
    panel.innerHTML = `<div class="emoji-cats">${cats.map((c, i) => `<button class="emoji-cat ${i === 0 ? 'on' : ''}" data-cat="${c}">${c}</button>`).join('')}</div><div class="emoji-grid"></div>`;
    document.body.appendChild(panel);
    const grid = $('.emoji-grid', panel);
    const fill = cat => { grid.innerHTML = EMOJI[cat].map(e => `<button>${e}</button>`).join(''); $$('button', grid).forEach(b => b.addEventListener('click', () => insertEmoji(b.textContent))); };
    fill(cats[0]);
    $$('.emoji-cat', panel).forEach(b => b.addEventListener('click', () => { $$('.emoji-cat', panel).forEach(x => x.classList.remove('on')); b.classList.add('on'); fill(b.dataset.cat); }));
    const r = anchor.getBoundingClientRect();
    panel.style.left = Math.min(r.left - 270, window.innerWidth - 324) + 'px';
    panel.style.top = Math.max(12, r.top - 268) + 'px';
    setTimeout(() => document.addEventListener('click', function h(e) { if (!panel.contains(e.target) && e.target !== anchor && !anchor.contains(e.target)) { panel.remove(); document.removeEventListener('click', h); } }), 0);
  }
  function insertEmoji(emo) {
    const comp = $('#composer'); comp.focus();
    const sel = window.getSelection();
    if (sel.rangeCount && comp.contains(sel.anchorNode)) {
      const range = sel.getRangeAt(0); range.deleteContents();
      const node = document.createTextNode(emo); range.insertNode(node);
      range.setStartAfter(node); range.collapse(true); sel.removeAllRanges(); sel.addRange(range);
    } else { comp.textContent += emo; }
    comp.dispatchEvent(new Event('input'));
  }

  /* ---------- in-thread search ---------- */
  let tsHits = [], tsIndex = 0;
  function openThreadSearch() {
    const bar = $('#threadSearch'); bar.classList.add('show');
    const inp = $('#threadSearchInput'); inp.value = ''; inp.focus();
    runThreadSearch('');
  }
  function closeThreadSearch() {
    $('#threadSearch').classList.remove('show');
    tsHits = []; tsIndex = 0;
    const c = conversations.find(x => x.id === activeId); if (c) renderThread(c);
  }
  function runThreadSearch(q) {
    const c = conversations.find(x => x.id === activeId); if (!c) return;
    renderThread(c);
    tsHits = []; tsIndex = 0;
    if (!q) { $('#tsCount').textContent = ''; return; }
    const rx = new RegExp('(' + q.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'gi');
    $$('#thread .b-text').forEach(el => {
      if (el.classList.contains('deleted')) return;
      if (rx.test(el.textContent)) {
        el.innerHTML = el.innerHTML.replace(rx, '<mark>$1</mark>');
        tsHits.push(el.closest('[data-msg]'));
      }
    });
    $('#tsCount').textContent = tsHits.length ? `1/${tsHits.length}` : 'No results';
    if (tsHits.length) focusHit(0);
  }
  function focusHit(i) {
    if (!tsHits.length) return;
    tsIndex = (i + tsHits.length) % tsHits.length;
    $$('#thread .msg').forEach(m => m.classList.remove('search-current'));
    const el = tsHits[tsIndex]; el.classList.add('search-current');
    el.scrollIntoView ? el.scrollTop : null;
    const t = $('#thread'); t.scrollTop = el.offsetTop - t.clientHeight / 2;
    $('#tsCount').textContent = `${tsIndex + 1}/${tsHits.length}`;
  }
  function initThreadSearch() {
    $('#threadSearchInput').addEventListener('input', e => runThreadSearch(e.target.value.trim()));
    $('#tsNext').addEventListener('click', () => focusHit(tsIndex + 1));
    $('#tsPrev').addEventListener('click', () => focusHit(tsIndex - 1));
    $('#tsClose').addEventListener('click', closeThreadSearch);
    $('#threadSearchInput').addEventListener('keydown', e => { if (e.key === 'Enter') focusHit(tsIndex + (e.shiftKey ? -1 : 1)); if (e.key === 'Escape') closeThreadSearch(); });
  }

  /* ---------- sidebar conversation context menu ---------- */
  function convoMenu(c, anchor) {
    const items = [
      { ic: c.pinned ? 'unpin' : 'pin', label: c.pinned ? 'Unpin chat' : 'Pin chat', fn: () => { c.pinned = !c.pinned; sortConvos(); renderList($('#search').value); toast(c.pinned ? 'Chat pinned' : 'Chat unpinned'); } },
      { ic: 'star', label: c.fav ? 'Remove favourite' : 'Add to favourites', fn: () => { c.fav = !c.fav; renderList($('#search').value); toast(c.fav ? 'Added to favourites' : 'Removed from favourites'); } },
      { ic: c.unread ? 'read' : 'unread', label: c.unread ? 'Mark as read' : 'Mark as unread', fn: () => { c.unread = c.unread ? 0 : 1; c.read = !c.unread ? true : c.read; renderList($('#search').value); } },
      { ic: 'bell', label: c.muted ? 'Unmute' : 'Mute notifications', fn: () => { c.muted = !c.muted; renderList($('#search').value); toast(c.muted ? 'Muted' : 'Unmuted'); } },
      { ic: 'archive', label: 'Archive chat', fn: () => { c.archived = true; if (activeId === c.id) showWelcome(); renderList($('#search').value); toast('Chat archived'); } },
      { sep: true },
      { ic: 'eraser', label: 'Clear messages', fn: () => confirmAction('Clear all messages?', 'This empties the conversation but keeps the chat.', () => { c.messages = []; c.last = ''; if (activeId === c.id) renderThread(c); renderList($('#search').value); toast('Messages cleared'); }) },
      { ic: 'trash', label: 'Delete chat', danger: true, fn: () => confirmAction('Delete this chat?', 'This permanently removes the conversation for you.', () => { const i = conversations.indexOf(c); conversations.splice(i, 1); if (activeId === c.id) { if (conversations[0]) selectConvo(conversations[0].id); else showWelcome(); } renderList($('#search').value); toast('Chat deleted'); }) },
    ];
    popMenu(anchor, items);
  }
  function sortConvos() { conversations.sort((a, b) => (b.pinned ? 1 : 0) - (a.pinned ? 1 : 0)); }
  function confirmAction(title, body, onYes) {
    closePops();
    const ov = document.createElement('div'); ov.className = 'cp-overlay';
    ov.innerHTML = `<div class="cp-modal" style="max-width:340px"><div class="cp-body" style="padding:22px"><h3 style="font-size:16px;font-weight:800;margin:0 0 7px">${esc(title)}</h3><p style="font-size:13.5px;color:var(--text2);line-height:1.5;margin:0 0 18px">${esc(body)}</p><div style="display:flex;gap:9px;justify-content:flex-end"><button class="cf-no" style="height:38px;padding:0 16px;border-radius:10px;font-weight:700;font-size:13px;color:var(--text2)">Cancel</button><button class="cf-yes" style="height:38px;padding:0 16px;border-radius:10px;font-weight:700;font-size:13px;background:var(--busy);color:#fff">Confirm</button></div></div></div>`;
    document.body.appendChild(ov);
    ov.addEventListener('click', e => { if (e.target === ov || e.target.closest('.cf-no')) ov.remove(); });
    $('.cf-yes', ov).addEventListener('click', () => { onYes(); ov.remove(); });
  }


  /* ---------- empty states ---------- */
  function emptyThread(c) {
    const m = convoMeta(c);
    return `<div class="estate">
      <div class="estate-ic"><svg width="34" height="34" viewBox="0 0 24 24" fill="none"><path d="M4 6.5C4 5.12 5.12 4 6.5 4h11C18.88 4 20 5.12 20 6.5v7c0 1.38-1.12 2.5-2.5 2.5H10l-3.6 3a1 1 0 0 1-1.65-.77V6.5Z" stroke="currentColor" stroke-width="1.7"/></svg></div>
      <h3>No messages yet</h3>
      <p>This is the beginning of your conversation with <b>${esc(m.name)}</b>. Say hello 👋</p>
      <button class="estate-cta" id="emptyHi"><svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M5 12 19 5l-4 14-3.5-5.5L5 12Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/></svg>Send a message</button>
    </div>`;
  }
  function showWelcome() {
    activeId = null;
    $('#chatHeader').innerHTML = '';
    $('#thread').innerHTML = `<div class="estate welcome">
      <div class="estate-ic"><svg width="40" height="40" viewBox="0 0 24 24" fill="none"><path d="M5 9.5C5 6.46 7.46 4 10.5 4h3C16.54 4 19 6.46 19 9.5S16.54 15 13.5 15H9l-3.2 2.9c-.5.46-1.3.1-1.3-.58V9.5Z" stroke="#fff" stroke-width="1.7"/></svg></div>
      <h3>Select a conversation</h3>
      <p>Choose a chat from the list to start messaging, or create a new conversation.</p>
      <button class="estate-cta" id="welcomeNew"><svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>New message</button>
    </div>`;
    $('.composer-wrap').style.display = 'none';
    $('#welcomeNew')?.addEventListener('click', startDraft);
    renderList($('#search').value);
  }

  /* ---------- loading skeletons ---------- */
  function listSkeleton() {
    $('#convoList').innerHTML = Array.from({ length: 7 }).map(() => `
      <div class="sk-convo"><div class="sk sk-circle"></div><div class="sk-lines"><div class="sk" style="height:11px;width:55%"></div><div class="sk" style="height:10px;width:80%"></div></div></div>`).join('');
  }
  function threadSkeleton() {
    const rows = [['58%', 38], ['44%', 38], ['66%', 56], ['38%', 38], ['52%', 44]];
    $('#thread').innerHTML = `<div class="day"><span>Today</span></div>` + rows.map(([w, h]) => `
      <div class="sk-msg-row"><div class="sk sk-circle" style="width:38px;height:38px"></div><div class="sk sk-bubble" style="width:${w};height:${h}px"></div></div>`).join('');
  }

  /* ---------- offline / reconnecting banner ---------- */
  const netBanner = () => $('#netBanner');
  function setNet(state) {
    const b = netBanner();
    if (state === 'online') { b.className = ''; return; }
    if (state === 'offline') { b.className = 'show offline'; b.innerHTML = `<svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M3 3l18 18M8.5 8.6A8 8 0 0 0 5 11M12 5c2.6 0 5 1 6.8 2.6M2 8.8A12 12 0 0 1 6 6.3m11.5 5.2A8 8 0 0 0 15.4 10M9 16a4 4 0 0 1 5.7 0M12 20h.01" stroke="#fff" stroke-width="1.8" stroke-linecap="round"/></svg>You're offline — messages will send when you reconnect`; }
    if (state === 'reconnecting') { b.className = 'show reconnecting'; b.innerHTML = `<span class="spin"></span>Reconnecting…`; }
  }
  function initNet() {
    window.addEventListener('offline', () => setNet('offline'));
    window.addEventListener('online', () => { setNet('reconnecting'); setTimeout(() => setNet('online'), 1400); });
    if (!navigator.onLine) setNet('offline');
    // expose for demo/testing
    window.CPsimNet = setNet;
  }

  /* ---------- Reverb real-time ---------- */
  const subscribedConvs = new Set();

  function initReverb() {
    if (!window.Echo) return;

    // Join presence channel — track who is online
    window.Echo.join('app')
      .here(members => {
        members.forEach(m => { if (users[m.id]) users[m.id].online = true; });
        renderList($('#search').value);
      })
      .joining(m => {
        if (users[m.id]) { users[m.id].online = true; renderList($('#search').value); }
        const c = activeId && conversations.find(x => x.id === activeId);
        if (c && (c.with === m.id || (c.members && c.members.includes(m.id)))) renderHeader(c);
      })
      .leaving(m => {
        if (users[m.id]) { users[m.id].online = false; users[m.id].last = 'just now'; renderList($('#search').value); }
        const c = activeId && conversations.find(x => x.id === activeId);
        if (c && (c.with === m.id || (c.members && c.members.includes(m.id)))) renderHeader(c);
      })
      .listen('UserPresenceUpdated', e => {
        const u = users[e.user_id];
        if (!u) return;
        u.online = e.is_online;
        if (!e.is_online) u.last = 'just now';
        renderList($('#search').value);
        const c = activeId && conversations.find(x => x.id === activeId);
        if (c && (c.with === e.user_id || (c.members && c.members.includes(e.user_id)))) renderHeader(c);
      })
      .error(() => {});

    window.Echo.private('user.' + me.id)
      .listen('CallInitiated', e => {
        const caller = users[e.caller.id] || { name: e.caller.name, initials: e.caller.name.split(' ').map(w=>w[0]).join('').toUpperCase().slice(0,2), grad: ['#1a6b3a','#10b981'] };
        CPOverlays.openCall(caller, e.type, true, 'c' + e.conversation_id, e.call_id);
      });
  }

  function subscribeConv(c) {
    if (!window.Echo || subscribedConvs.has(c.id)) return;
    subscribedConvs.add(c.id);
    const dbId = convDbId(c);

    window.Echo.private('conversation.' + dbId)
      // New message from another user
      .listen('MessageSent', e => {
        const msg = e.message;
        if (!msg || msg.user_id === me.id) return; // own message already in list
        const existing = c.messages.find(m => m.id === 'db' + msg.id);
        if (existing) return;
        const t = msg.created_at ? new Date(msg.created_at).toLocaleTimeString('en-US',{hour:'numeric',minute:'2-digit'}) : nowTime();
        const cp = { id: 'db' + msg.id, user: msg.user_id, t, text: msg.body || '', status: null };
        if (msg.parent_id) cp.reply = 'db' + msg.parent_id;
        if (msg.forwarded_from_id) cp.forwarded = true;
        if (msg.type === 'voice') { cp.voice = '0:30'; delete cp.text; }
        if (msg.attachments && msg.attachments.length) {
          const att = msg.attachments[0];
          if (att.file_type && att.file_type.startsWith('image/')) cp.image = { src: att.url, name: att.original_name };
          else cp.file = { name: att.original_name, size: att.formatted_size || '?' };
        }
        c.messages.push(cp);
        const u = users[msg.user_id];
        const firstName = u ? u.name.split(' ')[0] : 'Someone';
        c.last = firstName + ': ' + (msg.body || (msg.type === 'voice' ? '🎤 Voice' : '📎 File'));
        c.time = t;
        // Clear typing indicator for this sender immediately
        if (c._typers && msg.user_id) {
          delete c._typers[msg.user_id];
          if (c._typingTimers?.[msg.user_id]) { clearTimeout(c._typingTimers[msg.user_id]); delete c._typingTimers[msg.user_id]; }
          c.typing = Object.keys(c._typers).length > 0;
        }
        if (c.id !== activeId) c.unread = (c.unread || 0) + 1;
        if (c.id === activeId) { renderThread(c); markConvRead(c); } else renderList($('#search').value);
        renderList($('#search').value);
        // Whisper delivered receipt back to sender
        if (window.Echo) {
          window.Echo.private('conversation.' + dbId).whisper('message-delivered', { message_id: msg.id, user_id: me.id, at: nowTime() });
        }
      })
      // Message edited
      .listen('MessageUpdated', e => {
        const msg = c.messages.find(m => m.id === 'db' + e.message.id);
        if (msg) { msg.text = e.message.body; msg.edited = true; if (c.id === activeId) renderThread(c); }
      })
      // Message deleted
      .listen('MessageDeleted', e => {
        const idx = c.messages.findIndex(m => m.id === 'db' + e.message_id);
        if (idx > -1) { c.messages[idx].deleted = true; c.messages[idx].text = 'This message was deleted'; if (c.id === activeId) renderThread(c); }
      })
      // Reaction toggled
      .listen('ReactionToggled', e => {
        const msg = c.messages.find(m => m.id === 'db' + e.message_id);
        if (msg) { msg.reactions = e.reactions; if (c.id === activeId) renderThread(c); }
      })
      .listenForWhisper('typing', e => {
        if (e.user_id === me.id) return;
        c._typers = c._typers || {};
        c._typers[e.user_id] = e.name;
        c.typing = Object.keys(c._typers).length > 0;
        clearTimeout(c._typingTimers?.[e.user_id]);
        c._typingTimers = c._typingTimers || {};
        c._typingTimers[e.user_id] = setTimeout(() => {
          if (c._typers) delete c._typers[e.user_id];
          c.typing = Object.keys(c._typers || {}).length > 0;
          if (c.id === activeId) renderThread(c);
          renderList($('#search').value);
        }, 1200);
        if (c.id === activeId) renderThread(c);
        renderList($('#search').value);
      })
      .listenForWhisper('stop-typing', e => {
        if (e.user_id === me.id) return;
        if (c._typers) delete c._typers[e.user_id];
        if (c._typingTimers?.[e.user_id]) { clearTimeout(c._typingTimers[e.user_id]); delete c._typingTimers[e.user_id]; }
        c.typing = Object.keys(c._typers || {}).length > 0;
        if (c.id === activeId) renderThread(c);
        renderList($('#search').value);
      })
      .listen('ConversationRead', e => {
        if (e.user_id === me.id) return;
        c.messages.forEach(m => {
          if (m.user === me.id && m.status && m.status !== 'read') {
            m.status = 'read';
            m.readAt = e.read_at ? new Date(e.read_at).toLocaleTimeString('en-US',{hour:'numeric',minute:'2-digit'}) : nowTime();
          }
        });
        if (c.id === activeId) renderThread(c);
      })
      .listenForWhisper('message-delivered', e => {
        const msg = c.messages.find(m => m.id === 'db' + e.message_id || m.id === e.message_id);
        if (!msg || msg.user !== me.id) return;
        if (msg.status === 'sent') {
          msg.status = 'delivered';
          msg.deliveredAt = e.at || nowTime();
          if (c.id === activeId) renderThread(c);
        }
      });
  }

  function markConvRead(c) {
    const url = (R.markRead || '/conversations/{conv}/read').replace('{conv}', convDbId(c));
    apiPost(url, {}).catch(() => {});
  }

  /* ---------- boot ---------- */
  function initHeartbeat() {
    const url = R.heartbeat;
    if (!url) return;
    const ping = () => apiPost(url, {}).catch(() => {});
    ping();
    setInterval(ping, 60000); // every 60s
    document.addEventListener('visibilitychange', () => { if (!document.hidden) ping(); });
  }

  function boot() {
    initDark(); initRail(); initComposer(); initNet(); initThreadSearch(); initHeartbeat();
    listSkeleton(); threadSkeleton();
    setTimeout(() => {
      initReverb();
      conversations.forEach(c => subscribeConv(c));
      const c = conversations.find(x => x.id === activeId);
      renderList();
      if (c) { renderHeader(c); renderThread(c); renderPanel(c); }
      else if (conversations.length) { selectConvo(conversations[0].id); }
      else { showWelcome(); }
    }, 750);
    setTimeout(() => { if (window.CPAccount) CPAccount.startOnboarding(/[?&]onboarding=1/.test(location.search)); }, 950);
  }
  document.addEventListener('DOMContentLoaded', boot);
})();
