@extends('layouts.auth')
@section('title', 'Sign In')

@section('content')
<h1>Welcome back</h1>
<p class="sub">Sign in to your ChatPulse account</p>

<form method="POST" action="{{ route('login') }}" x-data="{ loading: false, showPwd: false }" @submit="loading = true">
    @csrf

    <div class="field">
        <label>Email address</label>
        <input type="email" name="email" value="{{ old('email') }}"
               required autofocus autocomplete="email"
               placeholder="you@company.com"
               class="{{ $errors->has('email') ? 'err' : '' }}">
        @error('email') <span class="err-msg">{{ $message }}</span> @enderror
    </div>

    <div class="field">
        <label>Password</label>
        <div style="position:relative;">
            <input :type="showPwd ? 'text' : 'password'" name="password"
                   required autocomplete="current-password"
                   placeholder="••••••••"
                   class="{{ $errors->has('password') ? 'err' : '' }}"
                   style="padding-right:42px;">
            <button type="button" @click="showPwd = !showPwd"
                    style="position:absolute;right:12px;top:50%;transform:translateY(-50%);color:var(--text3);line-height:0;">
                <svg x-show="!showPwd" width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7z" stroke="currentColor" stroke-width="1.8"/><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.8"/></svg>
                <svg x-show="showPwd" width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><line x1="1" y1="1" x2="23" y2="23" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
            </button>
        </div>
        @error('password') <span class="err-msg">{{ $message }}</span> @enderror
    </div>

    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;color:var(--text2);">
            <input type="checkbox" name="remember" style="accent-color:var(--primary);">
            Remember me
        </label>
        <a href="#" style="font-size:13px;color:var(--primary);font-weight:700;text-decoration:none;">Forgot password?</a>
    </div>

    <button type="submit" class="auth-btn" :disabled="loading">
        <span x-show="loading">Signing in…</span>
        <span x-show="!loading">Sign in</span>
    </button>
</form>

<div class="auth-divider">or</div>

<a href="{{ route('guest-login') }}"
   style="display:flex;align-items:center;justify-content:center;gap:8px;width:100%;height:46px;border-radius:13px;border:1.5px solid var(--line);background:var(--bg);color:var(--text2);font-size:14px;font-weight:700;text-decoration:none;font-family:inherit;cursor:pointer;transition:border-color .15s;"
   onmouseover="this.style.borderColor='var(--text3)'" onmouseout="this.style.borderColor='var(--line)'">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="8" r="4" stroke="currentColor" stroke-width="1.8"/><path d="M4 20c0-4 3.58-7 8-7s8 3 8 7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
    Continue as Guest
</a>

<p class="auth-link">Don't have an account? <a href="{{ route('register') }}">Create account</a></p>

<div style="margin-top:24px;padding:14px;border-radius:13px;border:1px dashed var(--line);background:var(--input);">
    <p style="font-size:11px;font-weight:800;color:var(--text3);text-transform:uppercase;letter-spacing:.05em;margin:0 0 10px;">Demo accounts</p>
    <div style="font-size:12.5px;color:var(--text2);display:flex;flex-direction:column;gap:6px;">
        <div>Admin: <span style="font-family:monospace;color:var(--text);">admin@chatpulse.app / password</span></div>
        <div>User: <span style="font-family:monospace;color:var(--text);">user@chatpulse.app / password</span></div>
    </div>
</div>
@endsection
