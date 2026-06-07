@extends('layouts.auth')
@section('title', 'Guest Access')

@section('content')
<h1 style="font-size:24px;font-weight:800;color:var(--text);margin:0 0 6px;letter-spacing:-.02em;">Continue as Guest</h1>
<p style="font-size:14px;color:var(--text2);margin:0 0 28px;">No account needed — just enter your name</p>

<form method="POST" action="{{ route('guest-login') }}" x-data="{ loading: false }" @submit="loading = true">
    @csrf

    {{-- Honeypot --}}
    <input type="text" name="website" style="display:none;" tabindex="-1" autocomplete="off">

    <div class="field">
        <label>Display name</label>
        <input type="text" name="name" value="{{ old('name') }}"
               required autofocus autocomplete="off"
               placeholder="How should we call you?"
               minlength="2" maxlength="60"
               class="{{ $errors->has('name') ? 'err' : '' }}">
        @error('name') <span class="err-msg">{{ $message }}</span> @enderror
    </div>

    {{-- Guest limitations --}}
    <div style="margin-bottom:20px;padding:12px 14px;border-radius:12px;border:1px solid var(--line);background:var(--input);display:flex;gap:10px;align-items:flex-start;">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" style="color:#f59e0b;flex-shrink:0;margin-top:1px;"><path d="M12 9v4M12 17h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
        <p style="font-size:13px;color:var(--text2);margin:0;line-height:1.5;">
            <strong style="color:var(--text);">Guest limitations:</strong> Cannot create groups, upload avatar, or access Settings.
            <a href="{{ route('register') }}" style="color:var(--primary);font-weight:700;text-decoration:none;">Create an account</a> for full access.
        </p>
    </div>

    <button type="submit" class="auth-btn" :disabled="loading">
        <span x-show="loading">Starting…</span>
        <span x-show="!loading">Start chatting</span>
    </button>
</form>

<div class="auth-divider">or</div>

<div style="display:flex;align-items:center;justify-content:center;gap:16px;font-size:14px;">
    <a href="{{ route('login') }}" style="color:var(--primary);font-weight:700;text-decoration:none;">Sign in</a>
    <span style="color:var(--line);">·</span>
    <a href="{{ route('register') }}" style="color:var(--primary);font-weight:700;text-decoration:none;">Create account</a>
</div>
@endsection
