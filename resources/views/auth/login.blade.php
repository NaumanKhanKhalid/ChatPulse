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

  <form class="mt-8 space-y-5" method="POST" action="{{ route('login') }}" id="loginForm">
    @csrf

    {{-- Email --}}
    <div>
      <label class="mb-1.5 block text-[13px] font-bold text-ink-700">Email</label>
      <div class="flex items-center gap-2.5 rounded-xl border bg-white px-3.5 h-12 transition focus-within:border-primary focus-within:ring-4 focus-within:ring-primary-light/60 {{ $errors->has('email') ? 'border-busy' : 'border-line' }}">
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
      <div class="flex items-center gap-2.5 rounded-xl border bg-white px-3.5 h-12 transition focus-within:border-primary focus-within:ring-4 focus-within:ring-primary-light/60 {{ $errors->has('password') ? 'border-busy' : 'border-line' }}">
        <svg class="text-ink-400 shrink-0" width="18" height="18" viewBox="0 0 24 24" fill="none"><rect x="5" y="10" width="14" height="10" rx="2" stroke="currentColor" stroke-width="1.7"/><path d="M8 10V8a4 4 0 0 1 8 0v2" stroke="currentColor" stroke-width="1.7"/></svg>
        <input type="password" name="password" id="loginPw" placeholder="••••••••" required autocomplete="current-password"
               class="w-full bg-transparent text-[14.5px] focus:outline-none" />
        <button type="button" id="pwToggle" class="text-[12px] font-bold text-ink-400 hover:text-ink-700 shrink-0">Show</button>
      </div>
      @error('password') <p class="field-err mt-1.5"><svg width="12" height="12" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2"/><path d="M12 8v4M12 16h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>{{ $message }}</p> @enderror
    </div>

    {{-- Remember --}}
    <label class="flex items-center gap-2.5 text-[13.5px] text-ink-700 select-none cursor-pointer">
      <input type="checkbox" name="remember" id="rememberCbx" class="cbx sr-only" checked />
      <span class="cbx-box grid h-5 w-5 place-items-center rounded-md border border-line bg-white transition">
        <svg class="transition" width="13" height="13" viewBox="0 0 24 24" fill="none" id="rememberTick"><path d="m5 12 4 4 10-10" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
      </span>
      Keep me signed in
    </label>

    {{-- Submit --}}
    <button type="submit" id="loginSubmit"
            class="group flex h-12 w-full items-center justify-center gap-2 rounded-xl bg-primary text-[15px] font-bold text-white shadow-btn transition hover:bg-primary-hover active:scale-[.99]">
      <svg id="loginSpinner" class="animate-spin w-4 h-4 hidden" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
      <span id="loginBtnText">Sign in</span>
      <svg id="loginArrow" width="18" height="18" viewBox="0 0 24 24" fill="none" class="transition group-hover:translate-x-0.5"><path d="M5 12h13m0 0-5-5m5 5-5 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
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

  {{-- Quick-fill demo accounts --}}
  <div style="margin-top:24px;border-radius:14px;border:1px dashed #e6e9e7;padding:14px 16px;">
    <p style="font-size:10.5px;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:#8a958f;margin:0 0 10px;">Quick sign-in — demo accounts</p>
    <div style="display:flex;flex-direction:column;gap:6px;">
      <button type="button" onclick="fillDemo('admin@chatpulse.app')"
              style="display:flex;align-items:center;gap:10px;width:100%;text-align:left;padding:9px 11px;border-radius:10px;border:1px solid #e6e9e7;background:#fff;cursor:pointer;font-family:inherit;transition:.12s;"
              onmouseover="this.style.borderColor='#10b981';this.style.background='#d1fae5'" onmouseout="this.style.borderColor='#e6e9e7';this.style.background='#fff'">
        <div style="width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,#10b981,#059669);display:grid;place-items:center;flex-shrink:0;">
          <span style="font-size:11px;font-weight:800;color:#fff;">AU</span>
        </div>
        <div>
          <div style="font-size:13px;font-weight:700;color:#0c1411;">Admin User</div>
          <div style="font-size:11.5px;color:#8a958f;">admin@chatpulse.app</div>
        </div>
        <span style="margin-left:auto;font-size:11.5px;font-weight:700;color:#10b981;background:#d1fae5;padding:2px 8px;border-radius:99px;">Admin</span>
      </button>
      <button type="button" onclick="fillDemo('sara@chatpulse.app')"
              style="display:flex;align-items:center;gap:10px;width:100%;text-align:left;padding:9px 11px;border-radius:10px;border:1px solid #e6e9e7;background:#fff;cursor:pointer;font-family:inherit;transition:.12s;"
              onmouseover="this.style.borderColor='#10b981';this.style.background='#d1fae5'" onmouseout="this.style.borderColor='#e6e9e7';this.style.background='#fff'">
        <div style="width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,#f9a8d4,#db2777);display:grid;place-items:center;flex-shrink:0;">
          <span style="font-size:11px;font-weight:800;color:#fff;">SK</span>
        </div>
        <div>
          <div style="font-size:13px;font-weight:700;color:#0c1411;">Sara Karim</div>
          <div style="font-size:11.5px;color:#8a958f;">sara@chatpulse.app</div>
        </div>
        <span style="margin-left:auto;font-size:11.5px;font-weight:700;color:#56635d;background:#f1f4f2;padding:2px 8px;border-radius:99px;">User</span>
      </button>
      <button type="button" onclick="fillDemo('ahmed@chatpulse.app')"
              style="display:flex;align-items:center;gap:10px;width:100%;text-align:left;padding:9px 11px;border-radius:10px;border:1px solid #e6e9e7;background:#fff;cursor:pointer;font-family:inherit;transition:.12s;"
              onmouseover="this.style.borderColor='#10b981';this.style.background='#d1fae5'" onmouseout="this.style.borderColor='#e6e9e7';this.style.background='#fff'">
        <div style="width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,#7dd3fc,#2563eb);display:grid;place-items:center;flex-shrink:0;">
          <span style="font-size:11px;font-weight:800;color:#fff;">AR</span>
        </div>
        <div>
          <div style="font-size:13px;font-weight:700;color:#0c1411;">Ahmed Raza</div>
          <div style="font-size:11.5px;color:#8a958f;">ahmed@chatpulse.app</div>
        </div>
        <span style="margin-left:auto;font-size:11.5px;font-weight:700;color:#56635d;background:#f1f4f2;padding:2px 8px;border-radius:99px;">User</span>
      </button>
    </div>
    <p style="font-size:11.5px;color:#8a958f;margin:10px 0 0;text-align:center;">All passwords: <code style="font-size:11.5px;background:#f1f4f2;padding:1px 6px;border-radius:5px;">password</code></p>
  </div>
</div>

@push('scripts')
<script>
function fillDemo(email) {
  document.querySelector('input[name="email"]').value = email;
  document.querySelector('input[name="password"]').value = 'password';
  // show pw briefly so user sees it filled
  const pw = document.getElementById('loginPw');
  const toggle = document.getElementById('pwToggle');
  pw.type = 'text'; toggle.textContent = 'Hide';
  setTimeout(() => { document.getElementById('loginForm').submit(); }, 300);
}

(function() {
  // pw toggle
  const pw = document.getElementById('loginPw');
  const toggle = document.getElementById('pwToggle');
  toggle.addEventListener('click', () => {
    pw.type = pw.type === 'password' ? 'text' : 'password';
    toggle.textContent = pw.type === 'password' ? 'Show' : 'Hide';
  });

  // checkbox
  const cbx = document.getElementById('rememberCbx');
  const tick = document.getElementById('rememberTick');
  tick.style.opacity = cbx.checked ? '1' : '0';
  cbx.addEventListener('change', () => { tick.style.opacity = cbx.checked ? '1' : '0'; });

  // loading state
  const form = document.getElementById('loginForm');
  const spinner = document.getElementById('loginSpinner');
  const btnText = document.getElementById('loginBtnText');
  const arrow = document.getElementById('loginArrow');
  form.addEventListener('submit', () => {
    spinner.classList.remove('hidden');
    arrow.classList.add('hidden');
    btnText.textContent = 'Signing in…';
  });
})();
</script>
@endpush
@endsection
