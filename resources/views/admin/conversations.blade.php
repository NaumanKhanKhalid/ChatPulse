@extends('layouts.admin')
@section('page-title', 'Conversations')
@section('content')

<form method="GET" class="adm-filters">
    <div class="adm-search">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><circle cx="11" cy="11" r="6.5" stroke="currentColor" stroke-width="1.8"/><path d="m20 20-3.2-3.2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Search by group name or participant…">
    </div>
    <select name="type" onchange="this.form.submit()">
        <option value="">All types</option>
        <option value="direct" {{ request('type')==='direct'?'selected':'' }}>Direct</option>
        <option value="group"  {{ request('type')==='group'?'selected':'' }}>Group</option>
    </select>
    <select name="activity" onchange="this.form.submit()">
        <option value="">Any activity</option>
        <option value="active" {{ request('activity')==='active'?'selected':'' }}>Active this week</option>
        <option value="empty"  {{ request('activity')==='empty'?'selected':'' }}>No messages</option>
    </select>
    <select name="sort" onchange="this.form.submit()">
        <option value="recent"   {{ request('sort','recent')==='recent'?'selected':'' }}>Recently active</option>
        <option value="newest"   {{ request('sort')==='newest'?'selected':'' }}>Newest</option>
        <option value="oldest"   {{ request('sort')==='oldest'?'selected':'' }}>Oldest</option>
        <option value="messages" {{ request('sort')==='messages'?'selected':'' }}>Most messages</option>
        <option value="members"  {{ request('sort')==='members'?'selected':'' }}>Most members</option>
    </select>
    <button type="submit" class="adm-btn">Apply</button>
    @if(request()->hasAny(['q','type','sort','activity']))
    <a href="{{ route('admin.conversations') }}" class="adm-link">Clear</a>
    @endif
</form>

<div class="adm-convos">
    @forelse($conversations as $conv)
    @php
        $isGroup = $conv->type === 'group';
        $people  = $conv->users->take(2);
        $title   = $isGroup ? ($conv->name ?? 'Unnamed group') : $conv->users->pluck('name')->join(' & ');
        $last    = $conv->lastMessage;
        $preview = $last
            ? ($last->body ?: '['.$last->type.']')
            : 'No messages yet';
        $gradG   = ['#818cf8','#7c3aed'];
    @endphp
    <div class="adm-convo" data-conv="{{ $conv->id }}" title="Open read-only view">
        {{-- stacked avatars: two for a DM, one for a group --}}
        <div class="adm-convo-avs {{ $isGroup ? 'single' : '' }}">
            @if($isGroup)
                @php $gi = collect(explode(' ', $title))->map(fn($w)=>strtoupper(substr($w,0,1)))->take(2)->join(''); @endphp
                <div class="avatar" style="width:40px;height:40px;background:linear-gradient(135deg,{{ $gradG[0] }},{{ $gradG[1] }});font-size:14px">{{ $gi }}</div>
            @else
                @foreach($people as $p)
                    @php $g = $p->avatarGradient(); $pi = collect(explode(' ', $p->name))->map(fn($w)=>strtoupper(substr($w,0,1)))->take(2)->join(''); @endphp
                    <div class="avatar" style="width:36px;height:36px;background:linear-gradient(135deg,{{ $g[0] }},{{ $g[1] }});font-size:12.5px">{{ $pi }}</div>
                @endforeach
            @endif
        </div>

        <div class="adm-convo-main">
            <div class="adm-convo-title">
                @if($isGroup)
                    <b>{{ $title }}</b>
                @else
                    @foreach($people as $p)<b>{{ $p->name }}</b>@if(!$loop->last)<i>&amp;</i>@endif @endforeach
                @endif
            </div>
            <div class="adm-convo-sub">
                <span class="adm-convo-type {{ $isGroup ? 'grp' : '' }}">{{ $isGroup ? 'Group' : 'Direct message' }}</span>
                @if($isGroup)<span class="adm-convo-dot">·</span><span>{{ $conv->participants_count }} members</span>@endif
                <span class="adm-convo-dot">·</span>
                <span class="adm-convo-prev">{{ \Illuminate\Support\Str::limit($preview, 60) }}</span>
            </div>
        </div>

        <div class="adm-convo-meta">
            <span class="adm-convo-count">{{ $conv->messages_count }} msgs</span>
            <span class="adm-convo-time">{{ $conv->last_activity_at?->diffForHumans(null, true) ?? $conv->created_at->format('M j') }}</span>
        </div>

        <div class="adm-convo-acts">
            <button type="button" class="adm-act dim" data-view="{{ $conv->id }}">View</button>
            <form method="POST" action="{{ route('admin.conversations.destroy', $conv) }}" class="adm-inline" onsubmit="event.stopPropagation(); return confirm('Delete this conversation and all its messages?')">
                @csrf @method('DELETE')
                <button type="submit" class="adm-act red">Delete</button>
            </form>
        </div>
    </div>
    @empty
    <div class="adm-card"><p class="adm-empty">No conversations found.</p></div>
    @endforelse
</div>

<div class="adm-pager">{{ $conversations->links() }}</div>

{{-- Read-only chat popup --}}
<div class="adm-modal-ov" id="convModal">
    <div class="adm-modal">
        <div class="adm-modal-head">
            <div class="avatar" id="cmAvatar" style="width:38px;height:38px;font-size:14px"></div>
            <div class="adm-modal-tx">
                <h3 id="cmTitle">—</h3>
                <span id="cmSub"></span>
            </div>
            <button class="adm-modal-x" id="cmClose" title="Close">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M6 6l12 12M18 6 6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
            </button>
        </div>
        <div class="adm-modal-body" id="cmBody"></div>
        <div class="adm-modal-foot">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M12 3.5 5 6v5c0 4.5 3 8 7 9.5 4-1.5 7-5 7-9.5V6l-7-2.5Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></svg>
            Admin read-only view · deletions here are logged to the audit trail
        </div>
    </div>
</div>

<script>
(function () {
    const ov = document.getElementById('convModal');
    const body = document.getElementById('cmBody');
    const esc = s => (s || '').replace(/[&<>"]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c]));

    function open(id) {
        ov.classList.add('show');
        document.getElementById('cmTitle').textContent = 'Loading…';
        document.getElementById('cmSub').textContent = '';
        body.innerHTML = '<div class="adm-empty">Loading messages…</div>';

        fetch(`{{ url('admin/conversations') }}/${id}`, { headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(d => {
                document.getElementById('cmTitle').textContent = d.title;
                document.getElementById('cmSub').textContent = d.subtitle;
                const ini = d.title.split(/[\s&]+/).filter(Boolean).map(w => w[0]).slice(0, 2).join('').toUpperCase();
                const av = document.getElementById('cmAvatar');
                av.textContent = ini;
                av.style.background = 'linear-gradient(135deg,#7dd3fc,#2563eb)';

                if (!d.messages.length) { body.innerHTML = '<div class="adm-empty">No messages in this conversation.</div>'; return; }
                body.innerHTML = d.messages.map(m => {
                    const u = m.user;
                    const grad = u ? `linear-gradient(135deg,${u.grad[0]},${u.grad[1]})` : '#8a958f';
                    const text = m.deleted
                        ? `<span class="cm-deleted">${esc(m.body || '')}</span>`
                        : esc(m.body || `[${m.type}]`);
                    return `<div class="cm-row">
                        <div class="avatar" style="width:30px;height:30px;background:${grad};font-size:11px">${u ? esc(u.initials) : '?'}</div>
                        <div class="cm-main">
                            <div class="cm-meta">
                                <b>${esc(u ? u.name : 'Unknown')}</b>
                                <span>${esc(m.time)}</span>
                                ${m.deleted ? '<em class="adm-tag red">deleted</em>' : ''}
                            </div>
                            <div class="cm-bubble ${m.deleted ? 'del' : ''}">${text}</div>
                        </div>
                    </div>`;
                }).join('');
            })
            .catch(() => { body.innerHTML = '<div class="adm-empty">Could not load this conversation.</div>'; });
    }

    document.querySelectorAll('[data-view]').forEach(b =>
        b.addEventListener('click', e => { e.stopPropagation(); open(b.dataset.view); }));
    document.querySelectorAll('[data-conv]').forEach(r =>
        r.addEventListener('click', e => { if (e.target.closest('form,button')) return; open(r.dataset.conv); }));

    const close = () => ov.classList.remove('show');
    document.getElementById('cmClose').addEventListener('click', close);
    ov.addEventListener('click', e => { if (e.target === ov) close(); });
    document.addEventListener('keydown', e => { if (e.key === 'Escape') close(); });
})();
</script>
@endsection
