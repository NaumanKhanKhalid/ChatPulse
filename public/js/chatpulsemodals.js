/* ChatPulse — shared modal module (status, new group, poll, forward) */
window.CPModals = (function () {
  const { users, conversations } = window.CP;
  const esc = s => (s || '').replace(/[&<>"]/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]));
  const av = (u, s) => `<div class="avatar" style="width:${s}px;height:${s}px;background:linear-gradient(135deg,${u.grad[0]},${u.grad[1]});font-size:${s * .38}px">${u.initials}</div>`;

  /* ---- styles (injected once) ---- */
  if (!document.getElementById('cp-modal-style')) {
    const st = document.createElement('style'); st.id = 'cp-modal-style';
    st.textContent = `
    .cp-overlay{position:fixed;inset:0;z-index:100;background:rgba(8,12,10,.5);backdrop-filter:blur(3px);display:flex;align-items:center;justify-content:center;padding:20px;}
    .cp-modal{width:100%;max-width:460px;max-height:88vh;display:flex;flex-direction:column;background:var(--card);border:1px solid var(--line);border-radius:20px;box-shadow:0 30px 70px -20px rgba(0,0,0,.5);overflow:hidden;}
    @media (prefers-reduced-motion: no-preference){.cp-modal{animation:cpPop .26s cubic-bezier(.2,.8,.2,1)}}
    @keyframes cpPop{from{transform:translateY(14px) scale(.97)}to{transform:none}}
    .cp-head{display:flex;align-items:center;gap:10px;padding:18px 20px;border-bottom:1px solid var(--line);}
    .cp-title{font-size:17px;font-weight:800;flex:1;letter-spacing:-.01em;}
    .cp-x{width:32px;height:32px;border-radius:9px;display:grid;place-items:center;color:var(--text3);}
    .cp-x:hover{background:var(--hover);color:var(--text);}
    .cp-body{padding:20px;overflow-y:auto;}
    .cp-foot{display:flex;gap:10px;justify-content:flex-end;padding:16px 20px;border-top:1px solid var(--line);}
    .cp-label{font-size:12.5px;font-weight:800;color:var(--text2);display:block;margin-bottom:7px;text-transform:uppercase;letter-spacing:.03em;}
    .cp-input,.cp-textarea{width:100%;border:1px solid var(--line);background:var(--bg);border-radius:11px;padding:11px 13px;font-size:14px;font-family:inherit;color:var(--text);outline:none;}
    .cp-input:focus,.cp-textarea:focus{border-color:var(--primary);box-shadow:0 0 0 4px rgba(16,185,129,.12);}
    .cp-textarea{resize:none;min-height:64px;line-height:1.5;}
    .cp-btn{height:42px;padding:0 20px;border-radius:11px;font-size:14px;font-weight:700;}
    .cp-btn.primary{background:var(--primary);color:#fff;box-shadow:0 8px 18px -8px rgba(16,185,129,.8);}
    .cp-btn.primary:hover{background:var(--primary-hover);}
    .cp-btn.primary:disabled{background:var(--text3);box-shadow:none;cursor:not-allowed;}
    .cp-btn.ghost{border:1px solid var(--line);color:var(--text2);}
    .cp-btn.ghost:hover{border-color:var(--text3);}
    .cp-row{margin-bottom:18px;}
    .cp-row:last-child{margin-bottom:0;}
    /* status */
    .st-types{display:grid;grid-template-columns:repeat(3,1fr);gap:8px;}
    .st-type{border:1px solid var(--line);border-radius:13px;padding:13px 8px;text-align:center;font-size:13px;font-weight:700;color:var(--text2);}
    .st-type .d{width:10px;height:10px;border-radius:50%;margin:0 auto 7px;}
    .st-type.on{border-color:currentColor;background:var(--hover);}
    .emoji-grid{display:grid;grid-template-columns:repeat(10,1fr);gap:3px;}
    .emoji-grid button{aspect-ratio:1;border-radius:8px;font-size:18px;display:grid;place-items:center;}
    .emoji-grid button:hover{background:var(--hover);}
    .emoji-grid button.on{background:var(--primary-light);}
    .st-input-row{display:flex;gap:8px;align-items:center;}
    .st-emoji-btn{width:46px;height:44px;border:1px solid var(--line);border-radius:11px;font-size:20px;display:grid;place-items:center;flex-shrink:0;}
    .cp-select{width:100%;border:1px solid var(--line);background:var(--bg);border-radius:11px;padding:11px 13px;font-size:14px;font-family:inherit;color:var(--text);outline:none;}
    /* toggle */
    .cp-toggle-row{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:11px 0;}
    .cp-toggle-row .tt{font-size:14px;font-weight:700;}
    .cp-toggle-row .ts{font-size:12.5px;color:var(--text3);margin-top:1px;}
    .cp-switch{width:44px;height:26px;border-radius:99px;background:var(--line);position:relative;flex-shrink:0;transition:.18s;}
    .cp-switch.on{background:var(--primary);}
    .cp-switch::after{content:"";position:absolute;top:3px;left:3px;width:20px;height:20px;border-radius:50%;background:#fff;transition:.18s;box-shadow:0 1px 3px rgba(0,0,0,.2);}
    .cp-switch.on::after{left:21px;}
    /* poll options */
    .poll-opt-row{display:flex;gap:8px;align-items:center;margin-bottom:8px;}
    .poll-opt-row .cp-input{flex:1;}
    .poll-opt-row .rm{width:38px;height:42px;border-radius:10px;color:var(--text3);display:grid;place-items:center;flex-shrink:0;}
    .poll-opt-row .rm:hover{background:var(--hover);color:var(--busy);}
    .add-opt{font-size:13px;font-weight:700;color:var(--primary-dark);display:inline-flex;align-items:center;gap:6px;margin-top:2px;}
    html.dark .add-opt{color:#6ee7b7;}
    /* forward / radio */
    .fwd-item{display:flex;align-items:center;gap:11px;padding:9px 10px;border-radius:12px;cursor:pointer;}
    .fwd-item:hover{background:var(--hover);}
    .fwd-name{font-size:14px;font-weight:700;flex:1;}
    .cp-check{width:22px;height:22px;border-radius:7px;border:2px solid var(--line);display:grid;place-items:center;flex-shrink:0;}
    .fwd-item.on .cp-check{background:var(--primary);border-color:var(--primary);}
    .cp-check svg{opacity:0;}
    .fwd-item.on .cp-check svg{opacity:1;}
    .vis-row{display:flex;gap:10px;}
    .vis-opt{flex:1;border:1px solid var(--line);border-radius:13px;padding:13px;cursor:pointer;}
    .vis-opt.on{border-color:var(--primary);background:var(--hover);}
    .vis-opt .vh{font-size:13.5px;font-weight:800;display:flex;align-items:center;gap:7px;}
    .vis-opt .vp{font-size:12px;color:var(--text3);margin-top:4px;line-height:1.4;}
    /* new chat / contact picker */
    .nc-group{display:flex;align-items:center;gap:12px;width:100%;padding:11px 12px;border-radius:14px;border:1px solid var(--line);text-align:left;}
    .nc-group:hover{background:var(--hover);border-color:var(--text3);}
    .nc-group-ic{width:40px;height:40px;border-radius:50%;display:grid;place-items:center;background:var(--primary-light);color:var(--primary-dark);flex-shrink:0;}
    html.dark .nc-group-ic{color:#6ee7b7;}
    .nc-group-tx{flex:1;display:flex;flex-direction:column;}
    .nc-group-t{font-size:14px;font-weight:800;}
    .nc-group-s{font-size:12px;color:var(--text3);margin-top:1px;}
    .nc-go{color:var(--text3);flex-shrink:0;}
    .nc-item{display:flex;align-items:center;gap:11px;padding:8px 12px;border-radius:13px;cursor:pointer;}
    .nc-item:hover{background:var(--hover);}
    .nc-avwrap{position:relative;flex-shrink:0;}
    .nc-pres{position:absolute;right:-1px;bottom:-1px;width:11px;height:11px;border-radius:50%;border:2.5px solid var(--card);}
    .nc-info{flex:1;display:flex;flex-direction:column;min-width:0;}
    .nc-name{font-size:14px;font-weight:700;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
    .nc-sub{font-size:12px;color:var(--text3);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
    .nc-empty{text-align:center;color:var(--text3);font-size:13.5px;padding:24px 0;}
    /* report */
    .rep-subj{display:flex;align-items:center;gap:10px;background:var(--bg);border:1px solid var(--line);border-radius:12px;padding:10px 12px;margin-bottom:16px;}
    .rep-tag{font-size:10.5px;font-weight:800;text-transform:uppercase;letter-spacing:.04em;color:var(--text2);background:var(--input);padding:3px 8px;border-radius:99px;flex-shrink:0;}
    .rep-subj-tx{display:flex;flex-direction:column;min-width:0;}
    .rep-subj-tx b{font-size:13.5px;font-weight:700;}
    .rep-subj-tx span{font-size:12px;color:var(--text3);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
    .rep-reasons{display:flex;flex-direction:column;gap:8px;}
    .rep-reason{display:flex;align-items:flex-start;gap:11px;text-align:left;padding:11px 12px;border:1px solid var(--line);border-radius:12px;}
    .rep-reason:hover{border-color:var(--text3);}
    .rep-reason.on{border-color:var(--primary);background:var(--hover);}
    .rep-radio{width:18px;height:18px;border-radius:50%;border:2px solid var(--line);flex-shrink:0;margin-top:1px;position:relative;}
    .rep-reason.on .rep-radio{border-color:var(--primary);}
    .rep-reason.on .rep-radio::after{content:"";position:absolute;inset:3px;border-radius:50%;background:var(--primary);}
    .rep-rtx{display:flex;flex-direction:column;}
    .rep-rtx b{font-size:13.5px;font-weight:700;}
    .rep-rtx span{font-size:12px;color:var(--text3);margin-top:1px;}
    .rep-block{display:flex;align-items:center;gap:10px;margin-top:16px;font-size:13.5px;font-weight:600;cursor:pointer;}
    `;
    document.head.appendChild(st);
  }

  /* ---- shell ---- */
  function open(title, body, foot) {
    close();
    const ov = document.createElement('div'); ov.className = 'cp-overlay';
    ov.innerHTML = `<div class="cp-modal"><div class="cp-head"><span class="cp-title">${esc(title)}</span><button class="cp-x" data-close><svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M6 6l12 12M18 6 6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg></button></div><div class="cp-body">${body}</div>${foot ? `<div class="cp-foot">${foot}</div>` : ''}</div>`;
    document.body.appendChild(ov);
    ov.addEventListener('click', e => { if (e.target === ov || e.target.closest('[data-close]')) close(); });
    document.addEventListener('keydown', escClose);
    return ov;
  }
  function close() { document.querySelectorAll('.cp-overlay').forEach(o => o.remove()); document.removeEventListener('keydown', escClose); }
  function escClose(e) { if (e.key === 'Escape') close(); }
  function toast(m) { const t = document.createElement('div'); t.className = 'cp-toast'; t.textContent = m; t.style.cssText = 'position:fixed;bottom:22px;left:50%;transform:translateX(-50%);background:#0c1411;color:#fff;font-size:13.5px;font-weight:600;padding:10px 16px;border-radius:12px;z-index:120;box-shadow:0 14px 36px -12px rgba(0,0,0,.5)'; document.body.appendChild(t); setTimeout(() => t.remove(), 2200); }

  /* ============ STATUS PICKER ============ */
  function openStatus(onSave) {
    const emojis = ['💬', '🎯', '☕️', '🏠', '🌴', '🤒', '🎧', '📵', '🚀', '🍔', '💻', '📚', '🏃', '🌙', '🎉', '🤝', '🔥', '✅', '👀', '💤'];
    const clears = ['Don\u2019t clear', '1 hour', '4 hours', 'Today', 'This week'];
    const types = [['available', 'Available', '#10b981'], ['busy', 'Busy', '#ef4444'], ['away', 'Away', '#f59e0b']];
    let sel = 'available', emo = '💬';
    const body = `
      <div class="cp-row"><span class="cp-label">Status</span>
        <div class="st-types">${types.map(t => `<button class="st-type ${t[0] === sel ? 'on' : ''}" data-st="${t[0]}" style="color:${t[2]}"><span class="d" style="background:${t[2]}"></span>${t[1]}</button>`).join('')}</div></div>
      <div class="cp-row"><span class="cp-label">What's happening?</span>
        <div class="st-input-row"><button class="st-emoji-btn" id="stEmoji">${emo}</button><input class="cp-input" id="stText" maxlength="60" placeholder="Set a custom status…" /></div>
        <div class="emoji-grid" id="stGrid" style="margin-top:10px">${emojis.map(e => `<button data-e="${e}" class="${e === emo ? 'on' : ''}">${e}</button>`).join('')}</div></div>
      <div class="cp-row"><span class="cp-label">Clear after</span>
        <select class="cp-select" id="stClear">${clears.map(c => `<option>${c}</option>`).join('')}</select></div>`;
    const foot = `<button class="cp-btn ghost" data-close>Cancel</button><button class="cp-btn primary" id="stSave">Save status</button>`;
    const ov = open('Set your status', body, foot);
    ov.querySelectorAll('[data-st]').forEach(b => b.addEventListener('click', () => { sel = b.dataset.st; ov.querySelectorAll('.st-type').forEach(x => x.classList.toggle('on', x === b)); }));
    ov.querySelectorAll('#stGrid button').forEach(b => b.addEventListener('click', () => { emo = b.dataset.e; ov.querySelector('#stEmoji').textContent = emo; ov.querySelectorAll('#stGrid button').forEach(x => x.classList.toggle('on', x === b)); }));
    ov.querySelector('#stSave').addEventListener('click', () => {
      const text = ov.querySelector('#stText').value.trim();
      onSave && onSave({ type: sel, emoji: emo, text, clear: ov.querySelector('#stClear').value });
      close(); toast('Status updated ' + emo);
    });
  }

  /* ============ NEW GROUP ============ */
  function openNewGroup(onCreate) {
    let pub = true;
    const body = `
      <div class="cp-row"><span class="cp-label">Group name</span><input class="cp-input" id="ngName" placeholder="e.g. Frontend Guild" /></div>
      <div class="cp-row"><span class="cp-label">Description</span><textarea class="cp-textarea" id="ngDesc" maxlength="160" placeholder="What's this group about?"></textarea></div>
      <div class="cp-row"><span class="cp-label">Visibility</span>
        <div class="vis-row" id="ngVis">
          <button class="vis-opt on" data-vis="public"><span class="vh"><svg width="16" height="16" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="8.3" stroke="currentColor" stroke-width="1.8"/><path d="M4 9.5h16M4 14.5h16M12 4c2.4 2.2 2.4 13.8 0 16" stroke="currentColor" stroke-width="1.5"/></svg>Public</span><span class="vp">Anyone can find &amp; join</span></button>
          <button class="vis-opt" data-vis="private"><span class="vh"><svg width="16" height="16" viewBox="0 0 24 24" fill="none"><rect x="5" y="10" width="14" height="10" rx="2" stroke="currentColor" stroke-width="1.8"/><path d="M8 10V8a4 4 0 0 1 8 0v2" stroke="currentColor" stroke-width="1.8"/></svg>Private</span><span class="vp">Invite-only via link</span></button>
        </div></div>`;
    const foot = `<button class="cp-btn ghost" data-close>Cancel</button><button class="cp-btn primary" id="ngCreate" disabled>Create group</button>`;
    const ov = open('New group', body, foot);
    const name = ov.querySelector('#ngName'), btn = ov.querySelector('#ngCreate');
    name.addEventListener('input', () => btn.disabled = name.value.trim().length < 2);
    ov.querySelectorAll('[data-vis]').forEach(b => b.addEventListener('click', () => { pub = b.dataset.vis === 'public'; ov.querySelectorAll('.vis-opt').forEach(x => x.classList.toggle('on', x === b)); }));
    btn.addEventListener('click', () => { onCreate && onCreate({ name: name.value.trim(), desc: ov.querySelector('#ngDesc').value.trim() || 'New group', pub }); close(); toast('Group created'); });
    setTimeout(() => name.focus(), 50);
  }

  /* ============ CREATE POLL ============ */
  function openPoll(onCreate) {
    let opts = ['', ''], multi = false, anon = false;
    const foot = `<button class="cp-btn ghost" data-close>Cancel</button><button class="cp-btn primary" id="plCreate" disabled>Create poll</button>`;
    const ov = open('Create poll', '<div id="plBody"></div>', foot);
    function render() {
      ov.querySelector('#plBody').innerHTML = `
        <div class="cp-row"><span class="cp-label">Question</span><input class="cp-input" id="plQ" placeholder="Ask something…" value="${esc(ov.querySelector('#plQ')?.value || '')}" /></div>
        <div class="cp-row"><span class="cp-label">Options (${opts.length}/10)</span>
          <div id="plOpts">${opts.map((o, i) => `<div class="poll-opt-row"><input class="cp-input" data-opt="${i}" placeholder="Option ${i + 1}" value="${esc(o)}" />${opts.length > 2 ? `<button class="rm" data-rm="${i}"><svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M6 6l12 12M18 6 6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg></button>` : '<span style="width:38px"></span>'}</div>`).join('')}</div>
          ${opts.length < 10 ? '<button class="add-opt" id="plAdd"><svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>Add option</button>' : ''}</div>
        <div class="cp-row" style="border-top:1px solid var(--line);padding-top:6px">
          <div class="cp-toggle-row"><div><div class="tt">Multiple choice</div><div class="ts">Let people pick more than one</div></div><button class="cp-switch ${multi ? 'on' : ''}" id="plMulti"></button></div>
          <div class="cp-toggle-row"><div><div class="tt">Anonymous</div><div class="ts">Hide who voted for what</div></div><button class="cp-switch ${anon ? 'on' : ''}" id="plAnon"></button></div>
        </div>
        <div class="cp-row"><span class="cp-label">Ends (optional)</span><select class="cp-select" id="plEnd"><option>No end date</option><option>In 1 hour</option><option>In 1 day</option><option>In 1 week</option></select></div>`;
      bind();
    }
    function bind() {
      ov.querySelector('#plQ').addEventListener('input', validate);
      ov.querySelectorAll('[data-opt]').forEach(inp => inp.addEventListener('input', () => { opts[+inp.dataset.opt] = inp.value; validate(); }));
      ov.querySelector('#plAdd')?.addEventListener('click', () => { syncOpts(); opts.push(''); render(); });
      ov.querySelectorAll('[data-rm]').forEach(b => b.addEventListener('click', () => { syncOpts(); opts.splice(+b.dataset.rm, 1); render(); }));
      ov.querySelector('#plMulti').addEventListener('click', e => { multi = !multi; e.currentTarget.classList.toggle('on', multi); });
      ov.querySelector('#plAnon').addEventListener('click', e => { anon = !anon; e.currentTarget.classList.toggle('on', anon); });
      validate();
    }
    function syncOpts() { ov.querySelectorAll('[data-opt]').forEach(inp => opts[+inp.dataset.opt] = inp.value); }
    function validate() { const q = ov.querySelector('#plQ').value.trim(); const filled = [...ov.querySelectorAll('[data-opt]')].filter(i => i.value.trim()).length; ov.querySelector('#plCreate').disabled = !(q && filled >= 2); }
    ov.querySelector('#plCreate').addEventListener('click', () => {
      syncOpts();
      const q = ov.querySelector('#plQ').value.trim();
      const options = opts.filter(o => o.trim()).map(text => ({ text: text.trim(), votes: 0 }));
      onCreate && onCreate({ q, multi, anon, options, total: 0, voted: 0 });
      close(); toast('Poll posted');
    });
    render();
    setTimeout(() => ov.querySelector('#plQ').focus(), 50);
  }

  /* ============ FORWARD ============ */
  function openForward(onForward) {
    const me = window.CP.me;
    let selected = new Set();
    const list = conversations.map(c => {
      const m = c.type === 'direct' ? { name: users[c.with].name, av: users[c.with] } : { name: c.name, av: { initials: c.initials, grad: c.grad } };
      return { id: c.id, name: m.name, av: m.av };
    });
    const body = `
      <input class="cp-input" id="fwdSearch" placeholder="Search conversations…" style="margin-bottom:12px" />
      <div id="fwdList" style="max-height:300px;overflow-y:auto">${list.map(c => `
        <div class="fwd-item" data-fwd="${c.id}">${av(c.av, 38)}<span class="fwd-name">${esc(c.name)}</span><span class="cp-check"><svg width="13" height="13" viewBox="0 0 24 24" fill="none"><path d="m5 12 4 4 10-10" stroke="#fff" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"/></svg></span></div>`).join('')}</div>`;
    const foot = `<button class="cp-btn ghost" data-close>Cancel</button><button class="cp-btn primary" id="fwdSend" disabled>Forward</button>`;
    const ov = open('Forward message', body, foot);
    const send = ov.querySelector('#fwdSend');
    ov.querySelectorAll('[data-fwd]').forEach(it => it.addEventListener('click', () => {
      const id = it.dataset.fwd;
      if (selected.has(id)) { selected.delete(id); it.classList.remove('on'); } else { selected.add(id); it.classList.add('on'); }
      send.disabled = selected.size === 0; send.textContent = selected.size ? `Forward to ${selected.size}` : 'Forward';
    }));
    ov.querySelector('#fwdSearch').addEventListener('input', e => {
      const q = e.target.value.toLowerCase();
      ov.querySelectorAll('[data-fwd]').forEach(it => it.style.display = it.querySelector('.fwd-name').textContent.toLowerCase().includes(q) ? '' : 'none');
    });
    send.addEventListener('click', () => { onForward && onForward([...selected]); close(); toast('Forwarded to ' + selected.size + ' chat' + (selected.size > 1 ? 's' : '')); });
  }

  /* ============ NEW CHAT (contact picker) ============ */
  function openNewChat(onPick, onNewGroup) {
    const me = window.CP.me;
    const statusColor = { available: '#10b981', busy: '#ef4444', away: '#f59e0b' };
    const people = Object.values(users)
      .filter(u => u.id !== me.id && !u.guest)
      .sort((a, b) => (b.online ? 1 : 0) - (a.online ? 1 : 0) || a.name.localeCompare(b.name));
    const row = u => `
      <div class="nc-item" data-pick="${u.id}">
        <span class="nc-avwrap">${av(u, 40)}${u.online ? `<span class="nc-pres" style="background:${statusColor[u.status] || '#10b981'}"></span>` : ''}</span>
        <span class="nc-info"><span class="nc-name">${esc(u.name)}</span><span class="nc-sub">@${esc(u.username)} · ${u.online ? (u.status === 'available' ? 'Active now' : esc(u.status)) : (u.last ? 'last seen ' + esc(u.last) : 'offline')}</span></span>
        <svg class="nc-go" width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="m9 6 6 6-6 6" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/></svg>
      </div>`;
    const body = `
      <input class="cp-input" id="ncSearch" placeholder="Search people by name or @username…" style="margin-bottom:14px" />
      <button class="nc-group" id="ncGroup">
        <span class="nc-group-ic"><svg width="20" height="20" viewBox="0 0 24 24" fill="none"><circle cx="9" cy="9" r="3.2" stroke="currentColor" stroke-width="1.8"/><path d="M3.5 19a5.5 5.5 0 0 1 11 0M16 6.5a3 3 0 0 1 0 5.8M17.5 19a5.5 5.5 0 0 0-2.3-4.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg></span>
        <span class="nc-group-tx"><span class="nc-group-t">New group</span><span class="nc-group-s">Start a conversation with multiple people</span></span>
        <svg class="nc-go" width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="m9 6 6 6-6 6" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/></svg>
      </button>
      <span class="cp-label" style="margin:18px 0 8px">People</span>
      <div id="ncList" style="max-height:320px;overflow-y:auto;margin:0 -6px">${people.map(row).join('')}</div>
      <div class="nc-empty" id="ncEmpty" style="display:none">No people match that search.</div>`;
    const ov = open('New message', body, '');
    ov.querySelector('#ncGroup').addEventListener('click', () => { close(); onNewGroup && onNewGroup(); });
    ov.querySelectorAll('[data-pick]').forEach(it => it.addEventListener('click', () => { close(); onPick && onPick(+it.dataset.pick); }));
    ov.querySelector('#ncSearch').addEventListener('input', e => {
      const q = e.target.value.toLowerCase().replace(/^@/, '');
      let shown = 0;
      ov.querySelectorAll('[data-pick]').forEach(it => {
        const u = users[+it.dataset.pick];
        const hit = u.name.toLowerCase().includes(q) || u.username.toLowerCase().includes(q);
        it.style.display = hit ? '' : 'none'; if (hit) shown++;
      });
      ov.querySelector('#ncEmpty').style.display = shown ? 'none' : 'block';
    });
    setTimeout(() => ov.querySelector('#ncSearch').focus(), 50);
  }

  /* ============ REPORT (user-side) ============ */
  function openReport(subject, onSubmit) {
    // subject: { kind:'message'|'user', name, preview }
    const reasons = [
      ['spam', 'Spam or scam', 'Unwanted promotions, scams or phishing'],
      ['harassment', 'Harassment or bullying', 'Targeted abuse or threats'],
      ['inappropriate', 'Inappropriate content', 'Nudity, violence or hateful content'],
      ['impersonation', 'Impersonation', 'Pretending to be someone else'],
      ['other', 'Something else', 'Doesn\u2019t fit the categories above'],
    ];
    let sel = 'spam';
    const body = `
      <div class="rep-subj">${subject.kind === 'message' ? '<span class="rep-tag">Message</span>' : '<span class="rep-tag">User</span>'}<div class="rep-subj-tx"><b>${esc(subject.name || '')}</b>${subject.preview ? `<span>${esc(subject.preview.slice(0, 70))}</span>` : ''}</div></div>
      <span class="cp-label" style="margin-bottom:9px">Why are you reporting this?</span>
      <div class="rep-reasons">${reasons.map(r => `<button class="rep-reason ${r[0] === sel ? 'on' : ''}" data-r="${r[0]}"><span class="rep-radio"></span><span class="rep-rtx"><b>${r[1]}</b><span>${r[2]}</span></span></button>`).join('')}</div>
      <div class="cp-row" style="margin-top:16px"><span class="cp-label">Add details (optional)</span><textarea class="cp-textarea" id="repNote" maxlength="280" placeholder="Anything our moderators should know…"></textarea></div>
      ${subject.kind === 'user' ? '<label class="rep-block"><span class="cp-switch on" id="repBlock"></span><span>Also block this user</span></label>' : ''}`;
    const foot = `<button class="cp-btn ghost" data-close>Cancel</button><button class="cp-btn primary" id="repSend">Submit report</button>`;
    const ov = open('Report ' + (subject.kind === 'message' ? 'message' : 'user'), body, foot);
    ov.querySelectorAll('[data-r]').forEach(b => b.addEventListener('click', () => { sel = b.dataset.r; ov.querySelectorAll('.rep-reason').forEach(x => x.classList.toggle('on', x === b)); }));
    let block = subject.kind === 'user';
    ov.querySelector('#repBlock')?.addEventListener('click', e => { block = !block; e.currentTarget.classList.toggle('on', block); });
    ov.querySelector('#repSend').addEventListener('click', () => {
      onSubmit && onSubmit({ reason: sel, note: ov.querySelector('#repNote').value.trim(), block });
      close(); toast('Report submitted — our team will review it');
    });
  }

  return { openStatus, openNewChat, openNewGroup, openPoll, openForward, openReport, close, toast };
})();
