<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta name="csrf-token" content="{{ csrf_token() }}" />
<title>ChatPulse — @yield('title', 'Sign in')</title>
<link rel="preconnect" href="https://fonts.googleapis.com" />
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
@vite(['resources/css/app.css', 'resources/js/app.js'])
<style>
  html, body { height: 100%; margin: 0; overflow: hidden; }
  body { font-family: 'Plus Jakarta Sans', system-ui, sans-serif; background: #fff; color: #0c1411; -webkit-font-smoothing: antialiased; }

  .brand-mesh {
    background-color: #065f46;
    background-image:
      radial-gradient(at 18% 22%, #10b981 0px, transparent 50%),
      radial-gradient(at 82% 12%, #34d399 0px, transparent 45%),
      radial-gradient(at 75% 88%, #047857 0px, transparent 50%),
      radial-gradient(at 25% 85%, #0f766e 0px, transparent 50%),
      radial-gradient(at 50% 50%, #059669 0px, transparent 60%);
  }
  .grain {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='160' height='160'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.85' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.4'/%3E%3C/svg%3E");
  }
  .float-card { animation: floaty 6s ease-in-out infinite; }
  @keyframes floaty { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-10px); } }
  .float-card.delay { animation-delay: -3s; }

  .dot { width: 6px; height: 6px; border-radius: 999px; background: currentColor; display: inline-block; animation: blink 1.4s infinite both; }
  .dot:nth-child(2) { animation-delay: .2s; }
  .dot:nth-child(3) { animation-delay: .4s; }
  @keyframes blink { 0%,60%,100% { opacity:.25; transform:translateY(0); } 30% { opacity:1; transform:translateY(-2px); } }

  .screen { opacity: 1; }
  @media (prefers-reduced-motion: no-preference) {
    .screen { animation: panelIn .35s cubic-bezier(.2,.8,.2,1); }
  }
  @keyframes panelIn { from { transform: translateY(9px); opacity: 0; } to { transform: none; opacity: 1; } }

  input::placeholder { color: #aab2ad; }
  .cbx:checked + .cbx-box { border-color: #10b981; background: #10b981; }
  .cbx:checked + .cbx-box svg { opacity: 1; }

  /* error states */
  .field-wrap.has-error { border-color: #ef4444 !important; }
  .field-err { color: #ef4444; font-size: 12px; margin-top: 6px; display: flex; align-items: center; gap: 5px; }
</style>
</head>
<body>

<div class="flex h-screen w-screen overflow-hidden">

  <!-- ===================== BRAND PANEL ===================== -->
  <aside class="brand-mesh relative hidden lg:flex w-[46%] xl:w-[42%] shrink-0 flex-col justify-between overflow-hidden p-12 xl:p-16 text-white">
    <div class="grain pointer-events-none absolute inset-0 opacity-50 mix-blend-soft-light"></div>
    <div class="pointer-events-none absolute inset-0 bg-gradient-to-t from-primary-dark/70 via-transparent to-transparent"></div>

    <div class="relative flex items-center gap-3">
      <div class="grid h-11 w-11 place-items-center rounded-[14px] bg-white/15 ring-1 ring-white/25 backdrop-blur">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M5 9.5C5 6.46 7.46 4 10.5 4h3C16.54 4 19 6.46 19 9.5S16.54 15 13.5 15H9l-3.2 2.9c-.5.46-1.3.1-1.3-.58V9.5Z" fill="#fff"/><circle cx="9.5" cy="9.5" r="1.25" fill="#10b981"/><circle cx="13.5" cy="9.5" r="1.25" fill="#10b981"/></svg>
      </div>
      <span class="text-[22px] font-extrabold tracking-tight">ChatPulse</span>
    </div>

    <div class="relative">
      <h1 class="max-w-md text-[40px] xl:text-[46px] font-extrabold leading-[1.08] tracking-tight">
        Where your team's conversations find their rhythm.
      </h1>
      <p class="mt-5 max-w-sm text-[15.5px] leading-relaxed text-white/75">
        Real-time messaging, group channels, polls, calls and more — all in one fast, focused workspace.
      </p>

      <div class="relative mt-12 h-[210px] max-w-md">
        <div class="float-card absolute left-0 top-0 w-[330px] rounded-2xl bg-white/12 p-3.5 shadow-glass ring-1 ring-white/20 backdrop-blur-xl">
          <div class="flex items-center gap-2.5">
            <div class="grid h-9 w-9 place-items-center rounded-full bg-gradient-to-br from-rose-300 to-pink-500 text-[12px] font-bold text-white">SK</div>
            <div class="leading-tight">
              <p class="text-[13px] font-bold">Sara Karim</p>
              <p class="text-[11px] text-white/60">Northwind Studio</p>
            </div>
            <span class="ml-auto inline-flex items-center gap-1 rounded-full bg-online/20 px-2 py-0.5 text-[10.5px] font-semibold text-emerald-50"><span class="h-1.5 w-1.5 rounded-full bg-white"></span>online</span>
          </div>
          <div class="mt-3 rounded-xl rounded-tl-md bg-white/85 px-3 py-2 text-[13px] font-medium text-ink-900">Polls just shipped 🎉 vote on the launch date?</div>
          <div class="mt-1.5 flex items-center gap-1.5">
            <span class="rounded-full bg-white/20 px-2 py-0.5 text-[11px] font-semibold">👍 6</span>
            <span class="rounded-full bg-white/20 px-2 py-0.5 text-[11px] font-semibold">🔥 3</span>
          </div>
        </div>

        <div class="float-card delay absolute right-0 bottom-0 w-[230px] rounded-2xl bg-white/12 p-3.5 shadow-glass ring-1 ring-white/20 backdrop-blur-xl">
          <div class="flex items-center gap-2">
            <div class="grid h-7 w-7 place-items-center rounded-full bg-gradient-to-br from-sky-300 to-blue-500 text-[10px] font-bold text-white">AH</div>
            <span class="text-[12px] font-bold">Ahmed</span>
            <span class="ml-auto inline-flex items-center gap-1 text-[12px] text-white/70"><span class="dot"></span><span class="dot"></span><span class="dot"></span></span>
          </div>
          <p class="mt-2 text-[11.5px] text-white/65">typing…</p>
        </div>
      </div>
    </div>

    <div class="relative flex flex-wrap gap-2">
      <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3.5 py-1.5 text-[12.5px] font-semibold text-white/85 ring-1 ring-white/15"><span class="h-1.5 w-1.5 rounded-full bg-emerald-200"></span>Real-time</span>
      <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3.5 py-1.5 text-[12.5px] font-semibold text-white/85 ring-1 ring-white/15"><span class="h-1.5 w-1.5 rounded-full bg-emerald-200"></span>Polls &amp; calls</span>
      <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3.5 py-1.5 text-[12.5px] font-semibold text-white/85 ring-1 ring-white/15"><span class="h-1.5 w-1.5 rounded-full bg-emerald-200"></span>File sharing</span>
      <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3.5 py-1.5 text-[12.5px] font-semibold text-white/85 ring-1 ring-white/15"><span class="h-1.5 w-1.5 rounded-full bg-emerald-200"></span>Dark mode</span>
    </div>
  </aside>

  <!-- ===================== FORM PANEL ===================== -->
  <main class="relative flex min-w-0 flex-1 flex-col bg-white">
    <header class="flex items-center justify-between px-6 sm:px-10 lg:px-14 h-20 shrink-0">
      <div class="flex items-center gap-2.5 lg:hidden">
        <div class="grid h-9 w-9 place-items-center rounded-[11px] bg-primary text-white">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M5 9.5C5 6.46 7.46 4 10.5 4h3C16.54 4 19 6.46 19 9.5S16.54 15 13.5 15H9l-3.2 2.9c-.5.46-1.3.1-1.3-.58V9.5Z" fill="currentColor"/></svg>
        </div>
        <span class="text-[17px] font-extrabold tracking-tight">ChatPulse</span>
      </div>
      <div class="ml-auto flex items-center gap-3 text-[13.5px]" id="headerSwitch">
        @yield('header-switch')
      </div>
    </header>

    <div class="flex flex-1 items-center justify-center overflow-y-auto px-6 sm:px-10 pb-10">
      <div class="w-full max-w-[420px]">

        {{-- Route chip --}}
        <div class="mb-7 flex items-center gap-2 text-[12px] font-semibold text-ink-400">
          <span class="grid h-5 w-5 place-items-center rounded-md bg-primary-light text-primary-dark">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none"><path d="M7 11V8a5 5 0 0 1 10 0v3M5 11h14v8a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2v-8Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/></svg>
          </span>
          <span>chatpulse.app</span><span class="text-ink-400/50">/</span>
          <span class="text-ink-700">@yield('route-label', 'login')</span>
        </div>

        {{-- Flash messages --}}
        @if(session('error'))
        <div class="mb-6 flex items-center gap-2.5 rounded-xl border border-busy/30 bg-busy/8 px-4 py-3 text-[13.5px] font-semibold text-busy">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.8"/><path d="M12 8v4M12 16h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
          {{ session('error') }}
        </div>
        @endif
        @if(session('success'))
        <div class="mb-6 flex items-center gap-2.5 rounded-xl border border-primary/30 bg-primary-light px-4 py-3 text-[13.5px] font-semibold text-primary-dark">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="m5 12 4 4 10-10" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
          {{ session('success') }}
        </div>
        @endif

        @yield('content')

      </div>
    </div>

    <footer class="flex items-center justify-between px-6 sm:px-10 lg:px-14 h-14 shrink-0 text-[12px] text-ink-400">
      <span>© {{ date('Y') }} ChatPulse</span>
      <div class="flex items-center gap-5">
        <a href="#" class="hover:text-ink-700">Help</a>
        <a href="#" class="hover:text-ink-700">Privacy</a>
        <a href="#" class="hover:text-ink-700">Status</a>
      </div>
    </footer>
  </main>
</div>

<div id="cp-toast" style="position:fixed;bottom:24px;right:24px;z-index:99;display:none;"></div>
<script>
function cpToast(msg, err) {
  const t = document.createElement('div');
  t.style.cssText = 'border-radius:12px;padding:12px 16px;font-size:13.5px;font-weight:600;color:#fff;box-shadow:0 20px 60px -20px rgba(0,0,0,.45);font-family:"Plus Jakarta Sans",system-ui,sans-serif;animation:panelIn .3s ease both;' + (err ? 'background:#ef4444;' : 'background:#0c1411;');
  t.textContent = msg;
  document.getElementById('cp-toast').style.display = 'block';
  document.getElementById('cp-toast').appendChild(t);
  setTimeout(() => { t.style.opacity = '0'; t.style.transition = 'opacity .3s'; }, 2200);
  setTimeout(() => { t.remove(); }, 2600);
}
</script>

</body>
</html>
