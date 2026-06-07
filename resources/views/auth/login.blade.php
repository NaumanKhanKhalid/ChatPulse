@extends('layouts.auth')
@section('title', 'Sign in')
@section('route-label', 'login')

@section('header-switch')
<span class="hidden sm:inline text-ink-400 text-[13.5px]">New to ChatPulse?</span>
<a href="{{ route('register') }}" class="rounded-full border border-line px-4 py-2 font-bold text-primary-dark text-[13.5px] transition hover:border-primary hover:bg-primary-light/40">Create account</a>
@endsection

@section('content')
<div class="screen">
  <h2 class="text-[30px] font-extrabold tracking-tight text-ink-900">Welcome back</h2>
  <p class="mt-2 text-[14.5px] text-ink-500">Sign in to pick up right where you left off.</p>

  <form class="mt-8 space-y-5" method="POST" action="{{ route('login') }}" x-data="{ loading: false, showPwd: false }" @submit="loading = true">
    @csrf

    {{-- Email --}}
    <div>
      <label class="mb-1.5 block text-[13px] font-bold text-ink-700">Email</label>
      <div class="field-wrap flex items-center gap-2.5 rounded-xl border bg-white px-3.5 h-12 transition focus-within:border-primary focus-within:ring-4 focus-within:ring-primary-light/60 {{ $errors->has('email') ? 'border-busy' : 'border-line' }}">
        <svg class="text-ink-400 shrink-0" width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M4 7.5C4 6.7 4.7 6 5.5 6h13c.8 0 1.5.7 1.5 1.5v9c0 .8-.7 1.5-1.5 1.5h-13C4.7 18 4 17.3 4 16.5v-9Z" stroke="currentColor" stroke-width="1.7"/><path d="m5 7 7 5 7-5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
        <input type="email" name="email" value="{{ old('email') }}" placeholder="you@company.com" required autofocus autocomplete="email"
               class="w-full bg-transparent text-[14.5px] focus:outline-none" />
      </div>
      @error('email') <p class="field-err mt-1.5"><svg width="12" height="12" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2"/><path d="M12 8v4M12 16h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>{{ $message }}</p> @enderror
    </div>

    {{-- Password --}}
    <div>
      <div class="mb-1.5 flex items-center justify-between">
        <label class="block text-[13px] font-bold text-ink-700">Password</label>
        <a href="#" class="text-[12.5px] font-bold text-primary-dark hover:underline">Forgot?</a>
      </div>
      <div class="field-wrap flex items-center gap-2.5 rounded-xl border bg-white px-3.5 h-12 transition focus-within:border-primary focus-within:ring-4 focus-within:ring-primary-light/60 {{ $errors->has('password') ? 'border-busy' : 'border-line' }}">
        <svg class="text-ink-400 shrink-0" width="18" height="18" viewBox="0 0 24 24" fill="none"><rect x="5" y="10" width="14" height="10" rx="2" stroke="currentColor" stroke-width="1.7"/><path d="M8 10V8a4 4 0 0 1 8 0v2" stroke="currentColor" stroke-width="1.7"/></svg>
        <input :type="showPwd ? 'text' : 'password'" name="password" placeholder="••••••••" required autocomplete="current-password"
               class="w-full bg-transparent text-[14.5px] focus:outline-none" />
        <button type="button" @click="showPwd = !showPwd"
                class="text-[12px] font-bold text-ink-400 hover:text-ink-700 shrink-0"
                x-text="showPwd ? 'Hide' : 'Show'"></button>
      </div>
      @error('password') <p class="field-err mt-1.5"><svg width="12" height="12" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2"/><path d="M12 8v4M12 16h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>{{ $message }}</p> @enderror
    </div>

    {{-- Remember --}}
    <label class="flex items-center gap-2.5 text-[13.5px] text-ink-700 select-none cursor-pointer">
      <input type="checkbox" name="remember" class="cbx sr-only" checked />
      <span class="cbx-box grid h-5 w-5 place-items-center rounded-md border border-line bg-white transition">
        <svg class="opacity-0 transition" width="13" height="13" viewBox="0 0 24 24" fill="none"><path d="m5 12 4 4 10-10" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
      </span>
      Keep me signed in
    </label>

    {{-- Submit --}}
    <button type="submit" :disabled="loading"
            class="group flex h-12 w-full items-center justify-center gap-2 rounded-xl bg-primary text-[15px] font-bold text-white shadow-btn transition hover:bg-primary-hover active:scale-[.99] disabled:opacity-70">
      <svg x-show="loading" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
      <span x-text="loading ? 'Signing in…' : 'Sign in'"></span>
      <svg x-show="!loading" width="18" height="18" viewBox="0 0 24 24" fill="none" class="transition group-hover:translate-x-0.5"><path d="M5 12h13m0 0-5-5m5 5-5 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
    </button>

    <p class="text-center text-[12px] text-ink-400">Protected by rate limiting — 5 attempts/min.</p>
  </form>

  <div class="my-7 flex items-center gap-3 text-[12px] font-semibold text-ink-400">
    <span class="h-px flex-1 bg-line"></span>OR<span class="h-px flex-1 bg-line"></span>
  </div>

  <a href="{{ route('guest-login') }}" class="flex h-12 w-full items-center justify-center gap-2.5 rounded-xl border border-line bg-white text-[14.5px] font-bold text-ink-700 transition hover:border-guest/60 hover:bg-amber-50">
    <span class="grid h-6 w-6 place-items-center rounded-full bg-amber-100 text-guest">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="8" r="3.2" stroke="currentColor" stroke-width="1.8"/><path d="M5.5 19a6.5 6.5 0 0 1 13 0" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
    </span>
    Continue as guest
  </a>

  {{-- Demo credentials --}}
  <div class="mt-7 rounded-xl border border-dashed border-line bg-white/60 p-4">
    <p class="text-[11px] font-extrabold uppercase tracking-widest text-ink-400 mb-2">Demo accounts</p>
    <div class="space-y-1.5 text-[12.5px] text-ink-500">
      <div>Admin: <span class="font-mono text-ink-700">admin@chatpulse.app / password</span></div>
      <div>User: <span class="font-mono text-ink-700">user@chatpulse.app / password</span></div>
    </div>
  </div>
</div>

<script>
// checkbox visual init
document.querySelectorAll('.cbx').forEach(cb => {
  const tick = cb.nextElementSibling?.querySelector('svg');
  if (tick && cb.checked) tick.style.opacity = '1';
  cb.addEventListener('change', () => { if (tick) tick.style.opacity = cb.checked ? '1' : '0'; });
});
</script>
@endsection
