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
    <button type="submit" class="adm-btn">Search</button>
    @if(request()->hasAny(['q','type']))
    <a href="{{ route('admin.conversations') }}" class="adm-link">Clear</a>
    @endif
</form>

<div class="adm-card adm-card-flush">
    <table class="adm-table">
        <thead><tr><th>Conversation</th><th>Type</th><th>Members</th><th>Messages</th><th>Last activity</th><th style="text-align:right">Actions</th></tr></thead>
        <tbody>
            @forelse($conversations as $conv)
            @php
                $isGroup = $conv->type === 'group';
                $title   = $isGroup ? ($conv->name ?? 'Unnamed group') : $conv->users->pluck('name')->join(' & ');
                $ini     = collect(explode(' ', $title))->map(fn($w)=>strtoupper(substr($w,0,1)))->take(2)->join('');
            @endphp
            <tr class="adm-row-click" data-conv="{{ $conv->id }}" title="Open read-only view">
                <td>
                    <div class="adm-ucell">
                        <div class="avatar" style="width:32px;height:32px;background:linear-gradient(135deg,{{ $isGroup ? '#818cf8,#7c3aed' : '#7dd3fc,#2563eb' }});font-size:12px">{{ $ini }}</div>
                        <span class="adm-ucell-name">{{ \Illuminate\Support\Str::limit($title, 40) }}</span>
                    </div>
                </td>
                <td><span class="adm-badge {{ $isGroup ? 'dim' : 'green' }}">{{ $isGroup ? 'Group' : 'Direct' }}</span></td>
                <td>{{ $conv->participants_count }}</td>
                <td>{{ $conv->messages_count }}</td>
                <td class="adm-td-dim">{{ $conv->last_activity_at?->diffForHumans() ?? $conv->created_at->format('M j, Y') }}</td>
                <td style="text-align:right">
                    <button type="button" class="adm-act dim" data-view="{{ $conv->id }}">View</button>
                    <form method="POST" action="{{ route('admin.conversations.destroy', $conv) }}" class="adm-inline" onsubmit="event.stopPropagation(); return confirm('Delete this conversation and all its messages?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="adm-act red">Delete</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="adm-empty">No conversations found.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="adm-pager">{{ $conversations->links() }}</div>
</div>

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
