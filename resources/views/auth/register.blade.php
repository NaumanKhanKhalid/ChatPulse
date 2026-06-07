@extends('layouts.auth')
@section('title', 'Create Account')
@section('route-label', 'register')

@section('header-switch')
<span class="hidden sm:inline text-ink-400 text-[13.5px]">Already have an account?</span>
<a href="{{ route('login') }}" class="rounded-full border border-line px-4 py-2 font-bold text-primary-dark text-[13.5px] transition hover:border-primary hover:bg-primary-light/40">Sign in</a>
@endsection

@section('content')
<div class="screen" x-data="registerForm()">
  <h2 class="text-[30px] font-extrabold tracking-tight text-ink-900">Create your account</h2>
  <p class="mt-2 text-[14.5px] text-ink-500">Join the conversation in under a minute.</p>

  <form class="mt-8 space-y-5" method="POST" action="{{ route('register') }}" x-data="{ loading: false, showPwd: false }" @submit="loading = true">
    @csrf

    {{-- Full name --}}
    <div>
      <label class="mb-1.5 block text-[13px] font-bold text-ink-700">Full name</label>
      <div class="field-wrap flex items-center gap-2.5 rounded-xl border bg-white px-3.5 h-12 transition focus-within:border-primary focus-within:ring-4 focus-within:ring-primary-light/60 {{ $errors->has('name') ? 'border-busy' : 'border-line' }}">
        <svg class="text-ink-400 shrink-0" width="18" height="18" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="8" r="3.2" stroke="currentColor" stroke-width="1.7"/><path d="M5.5 19a6.5 6.5 0 0 1 13 0" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
        <input type="text" name="name" value="{{ old('name') }}" x-model="fullName" @input="onNameInput()" placeholder="Sara Karim" required autofocus
               class="w-full bg-transparent text-[14.5px] focus:outline-none" />
      </div>
      <p class="mt-1.5 text-[12px] text-ink-400 items-center gap-1.5" :class="usernamePreview ? 'flex' : 'hidden'">
        Your username will be <span class="rounded-md bg-primary-light px-1.5 py-0.5 font-bold text-primary-dark" x-text="'@' + usernamePreview"></span>
      </p>
      @error('name') <p class="field-err mt-1"><svg width="12" height="12" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2"/><path d="M12 8v4M12 16h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>{{ $message }}</p> @enderror
    </div>

    {{-- Email --}}
    <div>
      <label class="mb-1.5 block text-[13px] font-bold text-ink-700">Email</label>
      <div class="field-wrap flex items-center gap-2.5 rounded-xl border bg-white px-3.5 h-12 transition focus-within:border-primary focus-within:ring-4 focus-within:ring-primary-light/60 {{ $errors->has('email') ? 'border-busy' : 'border-line' }}">
        <svg class="text-ink-400 shrink-0" width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M4 7.5C4 6.7 4.7 6 5.5 6h13c.8 0 1.5.7 1.5 1.5v9c0 .8-.7 1.5-1.5 1.5h-13C4.7 18 4 17.3 4 16.5v-9Z" stroke="currentColor" stroke-width="1.7"/><path d="m5 7 7 5 7-5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
        <input type="email" name="email" value="{{ old('email') }}" placeholder="you@company.com" required autocomplete="email"
               class="w-full bg-transparent text-[14.5px] focus:outline-none" />
      </div>
      @error('email') <p class="field-err mt-1"><svg width="12" height="12" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2"/><path d="M12 8v4M12 16h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>{{ $message }}</p> @enderror
    </div>

    {{-- Password + Confirm (2 col) --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div>
        <label class="mb-1.5 block text-[13px] font-bold text-ink-700">Password</label>
        <div class="field-wrap flex items-center gap-2.5 rounded-xl border border-line bg-white px-3.5 h-12 transition focus-within:border-primary focus-within:ring-4 focus-within:ring-primary-light/60">
          <input :type="showPwd ? 'text' : 'password'" name="password" x-model="password" @input="onPwInput()"
                 placeholder="••••••••" required autocomplete="new-password"
                 class="w-full bg-transparent text-[14.5px] focus:outline-none" />
          <button type="button" @click="showPwd = !showPwd" class="text-[12px] font-bold text-ink-400 hover:text-ink-700 shrink-0" x-text="showPwd ? 'Hide' : 'Show'"></button>
        </div>
      </div>
      <div>
        <label class="mb-1.5 block text-[13px] font-bold text-ink-700">Confirm</label>
        <div class="field-wrap flex items-center gap-2.5 rounded-xl border border-line bg-white px-3.5 h-12 transition focus-within:border-primary focus-within:ring-4 focus-within:ring-primary-light/60"
             :class="confirm && confirm !== password ? '!border-busy/60' : ''">
          <input type="password" name="password_confirmation" x-model="confirm" @input="onConfirmInput()"
                 placeholder="••••••••" required autocomplete="new-password"
                 class="w-full bg-transparent text-[14.5px] focus:outline-none" />
          <svg x-show="confirm && confirm === password" class="text-primary shrink-0" width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="m5 12 4 4 10-10" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </div>
      </div>
    </div>

    {{-- Password strength --}}
    <div x-show="password.length > 0">
      <div class="flex gap-1.5">
        <template x-for="i in 4" :key="i">
          <span class="h-1.5 flex-1 rounded-full transition" :class="i <= strength ? strengthColor : 'bg-line'"></span>
        </template>
      </div>
      <p class="mt-1.5 text-[12px] font-semibold" :class="strengthLabelColor" x-text="strengthLabel"></p>
    </div>

    @error('password') <p class="field-err -mt-2"><svg width="12" height="12" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2"/><path d="M12 8v4M12 16h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>{{ $message }}</p> @enderror

    <button type="submit" :disabled="loading"
            class="group flex h-12 w-full items-center justify-center gap-2 rounded-xl bg-primary text-[15px] font-bold text-white shadow-btn transition hover:bg-primary-hover active:scale-[.99] disabled:opacity-70">
      <svg x-show="loading" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
      <span x-text="loading ? 'Creating account…' : 'Create account'"></span>
      <svg x-show="!loading" width="18" height="18" viewBox="0 0 24 24" fill="none" class="transition group-hover:translate-x-0.5"><path d="M5 12h13m0 0-5-5m5 5-5 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
    </button>

    <p class="text-center text-[12px] leading-relaxed text-ink-400">
      By creating an account you agree to our
      <a href="#" class="font-semibold text-ink-500 underline">Terms</a> &amp;
      <a href="#" class="font-semibold text-ink-500 underline">Privacy Policy</a>.
    </p>
  </form>
</div>

<script>
document.addEventListener('alpine:init', () => {
  Alpine.data('registerForm', () => ({
    fullName: '{{ old('name') }}',
    password: '',
    confirm: '',
    usernamePreview: '',
    get strength() {
      const p = this.password; if (!p) return 0;
      let s = 0;
      if (p.length >= 8) s++;
      if (/[A-Z]/.test(p) && /[a-z]/.test(p)) s++;
      if (/\d/.test(p)) s++;
      if (/[^A-Za-z0-9]/.test(p)) s++;
      return Math.max(1, s);
    },
    get strengthColor() {
      return ['','bg-busy','bg-away','bg-away','bg-primary'][this.strength] || 'bg-line';
    },
    get strengthLabel() {
      return ['','Too short','Weak','Fair','Strong'][this.strength] || '';
    },
    get strengthLabelColor() {
      return ['','text-busy','text-busy','text-away','text-primary-dark'][this.strength] || '';
    },
    onNameInput() {
      const base = this.fullName.trim().toLowerCase().replace(/[^a-z0-9]+/g, '_').replace(/^_+|_+$/g, '');
      this.usernamePreview = base ? base + '_' + (10 + (base.length * 7) % 89) : '';
    },
    onPwInput() {},
    onConfirmInput() {},
  }));
});
</script>
@endsection
