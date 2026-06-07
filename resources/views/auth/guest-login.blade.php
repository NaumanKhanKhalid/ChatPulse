@extends('layouts.auth')
@section('title', 'Guest Access')
@section('route-label', 'guest-login')

@section('header-switch')
<a href="{{ route('login') }}" class="rounded-full border border-line px-4 py-2 font-bold text-primary-dark text-[13.5px] transition hover:border-primary hover:bg-primary-light/40">Sign in</a>
@endsection

@section('content')
<div class="screen" x-data="guestForm()">
  <span class="inline-flex items-center gap-2 rounded-full bg-amber-100 px-3 py-1 text-[12px] font-bold text-amber-700">
    <span class="h-1.5 w-1.5 rounded-full bg-guest"></span> Guest access
  </span>
  <h2 class="mt-4 text-[30px] font-extrabold tracking-tight text-ink-900">Jump in as a guest</h2>
  <p class="mt-2 text-[14.5px] text-ink-500">No email needed. Pick a name and start chatting in public groups.</p>

  <form class="mt-8 space-y-5" method="POST" action="{{ route('guest-login') }}">
    @csrf
    <input type="text" name="website" tabindex="-1" autocomplete="off" style="display:none;" aria-hidden="true" />
    <input type="hidden" name="_loaded_at" :value="loadedAt" />

    <div>
      <label class="mb-1.5 block text-[13px] font-bold text-ink-700">Display name</label>
      <div class="field-wrap flex items-center gap-2.5 rounded-xl border bg-white px-3.5 h-12 transition focus-within:border-primary focus-within:ring-4 focus-within:ring-primary-light/60 {{ $errors->has('name') ? 'border-busy' : 'border-line' }}">
        <div class="grid h-7 w-7 shrink-0 place-items-center rounded-full bg-gradient-to-br from-amber-400 to-orange-500 text-[11px] font-bold text-white"
             x-text="initials || '?'"></div>
        <input type="text" name="name" value="{{ old('name') }}" id="guestNameInput"
               x-model="name" @input="onInput()"
               minlength="2" placeholder="e.g. Curious Cat"
               class="w-full bg-transparent text-[14.5px] focus:outline-none" />
      </div>
      <p class="mt-1.5 text-[12px] text-ink-400">Minimum 2 characters.</p>
      @error('name') <p class="field-err mt-1"><svg width="12" height="12" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2"/><path d="M12 8v4M12 16h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>{{ $message }}</p> @enderror
    </div>

    <div class="rounded-xl border border-amber-200 bg-amber-50/70 p-4">
      <p class="text-[12.5px] font-bold text-amber-800">As a guest you can:</p>
      <ul class="mt-2 grid grid-cols-2 gap-x-4 gap-y-1.5 text-[12.5px]">
        <li class="flex items-center gap-1.5 text-ink-700"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" class="text-primary"><path d="m5 12 4 4 10-10" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg>Join public groups</li>
        <li class="flex items-center gap-1.5 text-ink-700"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" class="text-primary"><path d="m5 12 4 4 10-10" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg>Send messages</li>
        <li class="flex items-center gap-1.5 text-ink-700"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" class="text-primary"><path d="m5 12 4 4 10-10" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg>React &amp; vote</li>
        <li class="flex items-center gap-1.5 text-ink-400"><svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M6 6l12 12M18 6 6 18" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/></svg>Create groups</li>
        <li class="flex items-center gap-1.5 text-ink-400"><svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M6 6l12 12M18 6 6 18" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/></svg>Upload files</li>
        <li class="flex items-center gap-1.5 text-ink-400"><svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M6 6l12 12M18 6 6 18" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/></svg>Access settings</li>
      </ul>
    </div>

    <button type="submit" :disabled="!valid"
            :class="valid ? 'bg-primary shadow-btn hover:bg-primary-hover' : 'bg-ink-400 shadow-none cursor-not-allowed'"
            class="group flex h-12 w-full items-center justify-center gap-2 rounded-xl text-[15px] font-bold text-white transition active:scale-[.99]">
      Start chatting
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" class="transition group-hover:translate-x-0.5"><path d="M5 12h13m0 0-5-5m5 5-5 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
    </button>
  </form>

  <a href="{{ route('login') }}" class="mt-6 flex w-full items-center justify-center gap-1.5 text-[13.5px] font-bold text-ink-500 hover:text-primary-dark">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M19 12H6m0 0 5-5m-5 5 5 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
    Back to sign in
  </a>
</div>

<script>
document.addEventListener('alpine:init', () => {
  Alpine.data('guestForm', () => ({
    name: '{{ old('name') }}',
    loadedAt: Date.now(),
    get initials() {
      const n = this.name.trim();
      if (!n) return '';
      const parts = n.split(/\s+/);
      return ((parts[0]?.[0] || '') + (parts[1]?.[0] || '')).toUpperCase() || n[0].toUpperCase();
    },
    get valid() { return this.name.trim().length >= 2; },
    onInput() {},
  }));
});
</script>
@endsection
