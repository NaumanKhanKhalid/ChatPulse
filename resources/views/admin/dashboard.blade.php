@extends('layouts.admin')
@section('page-title', 'Dashboard')
@section('content')

{{-- Stat cards --}}
<div class="adm-stats">
    @php
    $cards = [
        ['label'=>'Total Users','val'=>$stats['users'],'tint'=>'#3b82f6','ic'=>'<circle cx="9" cy="8" r="3.2" stroke="currentColor" stroke-width="1.7"/><path d="M3.5 19a5.5 5.5 0 0 1 11 0M15 5.5a3.2 3.2 0 0 1 0 5M17.5 19a5.5 5.5 0 0 0-3-4.9" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>'],
        ['label'=>'Online Now','val'=>$stats['online_users'],'tint'=>'#10b981','ic'=>'<circle cx="12" cy="12" r="4" fill="currentColor"/><circle cx="12" cy="12" r="8" stroke="currentColor" stroke-width="1.7" opacity=".35"/>'],
        ['label'=>'Messages Today','val'=>$stats['messages_today'],'tint'=>'#8b5cf6','ic'=>'<path d="M4 6.5C4 5.12 5.12 4 6.5 4h11C18.88 4 20 5.12 20 6.5v7c0 1.38-1.12 2.5-2.5 2.5H10l-3.6 3a1 1 0 0 1-1.65-.77V6.5Z" stroke="currentColor" stroke-width="1.7"/>'],
        ['label'=>'Total Messages','val'=>number_format($stats['messages_total']),'tint'=>'#06b6d4','ic'=>'<path d="M7 20V10M12 20V4M17 20v-7" stroke="currentColor" stroke-width="1.9" stroke-linecap="round"/>'],
        ['label'=>'Groups','val'=>$stats['groups'],'tint'=>'#f59e0b','ic'=>'<circle cx="8" cy="9" r="2.8" stroke="currentColor" stroke-width="1.7"/><circle cx="16" cy="9" r="2.8" stroke="currentColor" stroke-width="1.7"/><path d="M2.5 18.5a5.5 5.5 0 0 1 11 0M10.5 18.5a5.5 5.5 0 0 1 11 0" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>'],
        ['label'=>'Conversations','val'=>$stats['conversations'],'tint'=>'#ec4899','ic'=>'<path d="M8 10h8M8 14h5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><path d="M4 6.5C4 5.12 5.12 4 6.5 4h11C18.88 4 20 5.12 20 6.5v7c0 1.38-1.12 2.5-2.5 2.5H10l-3.6 3a1 1 0 0 1-1.65-.77V6.5Z" stroke="currentColor" stroke-width="1.7"/>'],
        ['label'=>'Guests','val'=>$stats['guests'],'tint'=>'#eab308','ic'=>'<circle cx="12" cy="9" r="3.5" stroke="currentColor" stroke-width="1.7"/><path d="M5 20a7 7 0 0 1 14 0" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>'],
        ['label'=>'Banned','val'=>$stats['banned_users'],'tint'=>'#ef4444','ic'=>'<circle cx="12" cy="12" r="8.3" stroke="currentColor" stroke-width="1.7"/><path d="M6.5 6.5l11 11" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>'],
    ];
    @endphp
    @foreach($cards as $c)
    <div class="adm-stat">
        <span class="adm-stat-ic" style="color:{{ $c['tint'] }};background:{{ $c['tint'] }}1a">
            <svg width="19" height="19" viewBox="0 0 24 24" fill="none">{!! $c['ic'] !!}</svg>
        </span>
        <div class="adm-stat-tx">
            <span class="adm-stat-val">{{ $c['val'] }}</span>
            <span class="adm-stat-lbl">{{ $c['label'] }}</span>
        </div>
    </div>
    @endforeach
</div>

{{-- Live system health --}}
<div class="adm-card" style="margin-bottom:16px">
    <div class="adm-card-head">
        <h3>System Health <span class="adm-live-dot" title="Live"></span></h3>
        <span class="adm-card-sub" id="healthTs">updating…</span>
    </div>
    <div class="adm-health" id="healthGrid">
        <div class="adm-hw"><span class="adm-hw-lbl">CPU</span><div class="adm-meter"><span id="hCpuBar" style="width:0%"></span></div><span class="adm-hw-val" id="hCpu">—</span></div>
        <div class="adm-hw"><span class="adm-hw-lbl">Memory</span><div class="adm-meter"><span id="hMemBar" style="width:0%"></span></div><span class="adm-hw-val" id="hMem">—</span></div>
        <div class="adm-hw"><span class="adm-hw-lbl">Disk</span><div class="adm-meter"><span id="hDiskBar" style="width:0%"></span></div><span class="adm-hw-val" id="hDisk">—</span></div>
        <div class="adm-hw"><span class="adm-hw-lbl">Database</span><span class="adm-hstat" id="hDb">—</span></div>
        <div class="adm-hw"><span class="adm-hw-lbl">Reverb WS</span><span class="adm-hstat" id="hReverb">—</span></div>
        <div class="adm-hw"><span class="adm-hw-lbl">Queue</span><span class="adm-hstat" id="hQueue">—</span></div>
        <div class="adm-hw"><span class="adm-hw-lbl">Failed Jobs</span><span class="adm-hstat" id="hFailed">—</span></div>
        <div class="adm-hw"><span class="adm-hw-lbl">Msgs / hour</span><span class="adm-hstat" id="hMph">—</span></div>
    </div>
</div>

{{-- Health trend (from stored snapshots) --}}
<div class="adm-card" style="margin-bottom:16px">
    <div class="adm-card-head">
        <h3>Health Trend</h3>
        <span class="adm-card-sub" id="trendPeak">last 24h</span>
    </div>
    <div class="adm-trend" id="trendChart"><p class="adm-empty">Loading history…</p></div>
</div>

{{-- Top groups + live activity feed --}}
<div class="adm-grid2" style="margin-bottom:16px">
    <div class="adm-card">
        <div class="adm-card-head"><h3>Top groups</h3><span class="adm-card-sub">by message volume</span></div>
        @php $gmax = max(1, $topGroups->max('messages_count') ?? 1); @endphp
        @forelse($topGroups as $tg)
        @php $tgi = collect(explode(' ', $tg->name ?? 'G'))->map(fn($w)=>strtoupper(substr($w,0,1)))->take(2)->join(''); @endphp
        <div class="adm-topg">
            <div class="avatar" style="width:32px;height:32px;background:linear-gradient(135deg,#818cf8,#7c3aed);font-size:12px">{{ $tgi }}</div>
            <span class="adm-topg-name">{{ \Illuminate\Support\Str::limit($tg->name ?? 'Unnamed', 22) }}</span>
            <div class="adm-topg-bar"><span style="width:{{ round($tg->messages_count / $gmax * 100) }}%"></span></div>
            <span class="adm-topg-n">{{ $tg->messages_count }}</span>
        </div>
        @empty
        <p class="adm-empty">No groups yet.</p>
        @endforelse
    </div>

    <div class="adm-card">
        <div class="adm-card-head">
            <h3>Live activity <span class="adm-live-dot" title="Streaming over WebSocket"></span></h3>
            <span class="adm-card-sub" id="feedStatus">real-time</span>
        </div>
        <div class="adm-feed" id="activityFeed">
            @forelse($seedFeed as $f)
            <div class="adm-feed-row">
                <div class="avatar" style="width:30px;height:30px;background:linear-gradient(135deg,{{ $f['grad'][0] }},{{ $f['grad'][1] }});font-size:11px">{{ $f['initials'] }}</div>
                <span class="adm-feed-tx"><b>{{ $f['actor'] }}</b> {{ $f['text'] }} <i>{{ $f['target'] }}</i></span>
                <span class="adm-feed-at">{{ $f['at'] }}</span>
            </div>
            @empty
            <p class="adm-empty" id="feedEmpty">Waiting for activity…</p>
            @endforelse
        </div>
    </div>
</div>

<div class="adm-grid2" style="margin-bottom:16px">
    {{-- Live online users (WebSocket presence) --}}
    <div class="adm-card">
        <div class="adm-card-head">
            <h3>Online Right Now <span class="adm-live-dot" title="Live via WebSocket"></span></h3>
            <span class="adm-card-sub"><b id="onlineCount">{{ $onlineUsers->count() }}</b> users</span>
        </div>
        <div class="adm-online" id="onlineList">
            @foreach($onlineUsers as $u)
            <div class="adm-online-u" data-uid="{{ $u['id'] }}" title="{{ $u['name'] }}">
                <div class="avatar" style="width:36px;height:36px;background:linear-gradient(135deg,{{ $u['grad'][0] }},{{ $u['grad'][1] }});font-size:13px">{{ $u['initials'] }}</div>
                <span class="adm-online-dot"></span>
                <span class="adm-online-name">{{ \Illuminate\Support\Str::limit($u['name'], 10, '') }}</span>
            </div>
            @endforeach
        </div>
        <p class="adm-empty" id="onlineEmpty" style="{{ $onlineUsers->count() ? 'display:none' : '' }}">Nobody online right now.</p>
    </div>

    {{-- 24-hour hourly activity --}}
    <div class="adm-card">
        <div class="adm-card-head">
            <h3>Hourly Activity</h3>
            <span class="adm-card-sub">Last 24 hours</span>
        </div>
        @php $hmax = max(1, $hours->max('count')); @endphp
        <div class="adm-heat">
            @foreach($hours as $h)
            <div class="adm-heat-col" title="{{ $h['label'] }} — {{ $h['count'] }} messages">
                <div class="adm-heat-cell" style="opacity:{{ $h['count'] ? max(.18, round($h['count']/$hmax, 2)) : .06 }}"></div>
                @if($loop->index % 4 === 0)<span class="adm-heat-lbl">{{ $h['label'] }}</span>@endif
            </div>
            @endforeach
        </div>
    </div>
</div>

<div class="adm-grid2">
    {{-- 7-day activity chart --}}
    <div class="adm-card">
        <div class="adm-card-head">
            <h3>Message Activity</h3>
            <span class="adm-card-sub">Last 7 days</span>
        </div>
        @php $max = max(1, $days->max('count')); @endphp
        <div class="adm-chart">
            @foreach($days as $d)
            <div class="adm-bar-col" title="{{ $d['count'] }} messages">
                <span class="adm-bar-val">{{ $d['count'] }}</span>
                <div class="adm-bar" style="height:{{ max(4, round($d['count'] / $max * 130)) }}px"></div>
                <span class="adm-bar-lbl">{{ $d['label'] }}</span>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Recent users --}}
    <div class="adm-card">
        <div class="adm-card-head">
            <h3>Recent Users</h3>
            <a href="{{ route('admin.users') }}" class="adm-link">View all →</a>
        </div>
        <div class="adm-ulist">
            @foreach($recentUsers as $u)
            @php $g = $u->avatarGradient(); $ini = collect(explode(' ',$u->name))->map(fn($w)=>strtoupper(substr($w,0,1)))->take(2)->join(''); @endphp
            <div class="adm-urow">
                <div class="avatar" style="width:32px;height:32px;background:linear-gradient(135deg,{{ $g[0] }},{{ $g[1] }});font-size:12px">{{ $ini }}</div>
                <div class="adm-urow-tx">
                    <span class="adm-urow-name">{{ $u->name }}
                        @if($u->role==='admin')<em class="adm-tag green">admin</em>@endif
                        @if($u->is_guest)<em class="adm-tag amber">guest</em>@endif
                        @if($u->is_banned)<em class="adm-tag red">banned</em>@endif
                    </span>
                    <span class="adm-urow-sub">{{ $u->email ?? '@'.$u->username }}</span>
                </div>
                <span class="adm-urow-time">{{ $u->created_at->diffForHumans(null, true) }}</span>
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- Recent messages --}}
<div class="adm-card" style="margin-top:16px">
    <div class="adm-card-head">
        <h3>Latest Messages</h3>
        <a href="{{ route('admin.messages') }}" class="adm-link">Moderate →</a>
    </div>
    <table class="adm-table">
        <thead><tr><th>From</th><th>Message</th><th>Conversation</th><th>When</th></tr></thead>
        <tbody>
            @foreach($recentMessages as $m)
            <tr>
                <td>{{ $m->user?->name ?? '—' }}</td>
                <td class="adm-td-msg">{{ \Illuminate\Support\Str::limit($m->body ?: '['.$m->type.']', 60) }}</td>
                <td>{{ $m->conversation?->name ?? 'Direct' }}</td>
                <td class="adm-td-dim">{{ $m->created_at->diffForHumans(null, true) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    /* ---- Live system health (10s refresh) ---- */
    const $id = x => document.getElementById(x);
    function meter(barId, valId, pct, suffix) {
        if (pct === null || pct === undefined) { $id(valId).textContent = 'n/a'; return; }
        $id(barId).style.width = pct + '%';
        $id(barId).style.background = pct > 85 ? '#ef4444' : pct > 65 ? '#f59e0b' : 'var(--primary)';
        $id(valId).textContent = pct + '%' + (suffix || '');
    }
    function stat(id, ok, text) {
        const el = $id(id);
        el.textContent = text;
        el.className = 'adm-hstat ' + (ok ? 'ok' : 'bad');
    }
    async function pollHealth() {
        try {
            const r = await fetch('{{ route('admin.health') }}', { headers: { 'Accept': 'application/json' } });
            const d = await r.json();
            meter('hCpuBar', 'hCpu', d.cpu_pct);
            meter('hMemBar', 'hMem', d.mem_pct, d.mem_total_gb ? ' of ' + d.mem_total_gb + 'GB' : '');
            meter('hDiskBar', 'hDisk', d.disk_pct);
            stat('hDb', d.db_ok, d.db_ok ? 'Connected · ' + d.db_ms + 'ms' : 'DOWN');
            stat('hReverb', d.reverb_ok, d.reverb_ok ? 'Running' : 'Offline');
            stat('hQueue', d.pending_jobs < 50, d.pending_jobs + ' pending');
            stat('hFailed', d.failed_jobs === 0, String(d.failed_jobs));
            stat('hMph', true, String(d.messages_last_hour));
            $id('healthTs').textContent = 'updated ' + d.ts;
        } catch (e) { $id('healthTs').textContent = 'fetch failed — retrying'; }
    }
    pollHealth();
    setInterval(pollHealth, 10000);

    /* ---- Health trend from stored snapshots ---- */
    fetch('{{ route('admin.health.history') }}?hours=24', { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(d => {
            const box = $id('trendChart');
            if (!d.points || !d.points.length) {
                box.innerHTML = '<p class="adm-empty">No snapshots yet — they are recorded every 5 minutes by the scheduler.</p>';
                return;
            }
            $id('trendPeak').textContent = `last 24h · peak CPU ${d.peak_cpu ?? 0}% · peak memory ${d.peak_mem ?? 0}%`;
            const pts = d.points.slice(-72); // keep the chart readable
            box.innerHTML = pts.map(p => {
                const cpu = p.cpu ?? 0, mem = p.mem ?? 0;
                const tone = cpu > 85 ? '#ef4444' : cpu > 65 ? '#f59e0b' : 'var(--primary)';
                return `<div class="adm-trend-col" title="${p.at} — CPU ${cpu}%, memory ${mem}%">
                    <div class="adm-trend-bar" style="height:${Math.max(2, cpu)}%;background:${tone}"></div>
                    <div class="adm-trend-bar mem" style="height:${Math.max(2, mem)}%"></div>
                </div>`;
            }).join('');
        })
        .catch(() => { $id('trendChart').innerHTML = '<p class="adm-empty">Could not load history.</p>'; });

    /* ---- Live activity feed over the private admin channel ---- */
    function initFeed() {
        if (!window.Echo) { setTimeout(initFeed, 500); return; }
        const feed = $id('activityFeed');
        const TONE = { message:'#8b5cf6', login:'#10b981', failed:'#ef4444', signup:'#3b82f6', group:'#f59e0b', report:'#ef4444', ban:'#ef4444', call:'#06b6d4' };
        window.Echo.private('admin.activity').listen('AdminActivity', e => {
            $id('feedEmpty')?.remove();
            const row = document.createElement('div');
            row.className = 'adm-feed-row fresh';
            row.style.setProperty('--tone', TONE[e.kind] || 'var(--text3)');
            row.innerHTML = `<div class="avatar" style="width:30px;height:30px;background:linear-gradient(135deg,${e.grad[0]},${e.grad[1]});font-size:11px">${e.initials}</div>`
                + `<span class="adm-feed-tx"><b>${e.actor}</b> ${e.text} <i>${e.target || ''}</i></span>`
                + `<span class="adm-feed-at">just now</span>`;
            feed.prepend(row);
            while (feed.children.length > 25) feed.lastElementChild.remove();
            setTimeout(() => row.classList.remove('fresh'), 1800);
        });
    }
    initFeed();

    /* ---- Live online users via Reverb presence channel (WebSocket push, no polling) ---- */
    function initPresence() {
        if (!window.Echo) { setTimeout(initPresence, 500); return; }
        const list = $id('onlineList'), empty = $id('onlineEmpty'), count = $id('onlineCount');
        const online = new Map();
        document.querySelectorAll('.adm-online-u').forEach(el => online.set(+el.dataset.uid, true));
        function grad(name) {
            let h = 0; for (const ch of name) h = (h * 31 + ch.charCodeAt(0)) % 360;
            return [`hsl(${h},80%,72%)`, `hsl(${h},65%,38%)`];
        }
        function addUser(u) {
            if (online.has(u.id)) return;
            online.set(u.id, true);
            const ini = u.name.split(' ').map(w => w[0]).slice(0, 2).join('').toUpperCase();
            const [g1, g2] = grad(u.name);
            const el = document.createElement('div');
            el.className = 'adm-online-u'; el.dataset.uid = u.id; el.title = u.name;
            el.innerHTML = `<div class="avatar" style="width:36px;height:36px;background:linear-gradient(135deg,${g1},${g2});font-size:13px">${ini}</div><span class="adm-online-dot"></span><span class="adm-online-name">${u.name.slice(0, 10)}</span>`;
            list.appendChild(el);
            refresh();
        }
        function removeUser(u) {
            online.delete(u.id);
            list.querySelector(`[data-uid="${u.id}"]`)?.remove();
            refresh();
        }
        function refresh() {
            count.textContent = online.size;
            empty.style.display = online.size ? 'none' : '';
        }
        window.Echo.join('app')
            .here(users => { list.innerHTML = ''; online.clear(); users.forEach(addUser); refresh(); })
            .joining(addUser)
            .leaving(removeUser);
    }
    initPresence();
});
</script>
@endsection
