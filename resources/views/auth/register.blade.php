@extends('layouts.auth')
@section('title', 'Create Account')

@section('content')
<h1 style="font-size:24px;font-weight:800;color:var(--text);margin:0 0 6px;letter-spacing:-.02em;">Create your account</h1>
<p style="font-size:14px;color:var(--text2);margin:0 0 28px;">Join your team on ChatPulse</p>

<form method="POST" action="{{ route('register') }}" x-data="{ loading: false, showPwd: false }" @submit="loading = true">
    @csrf

    <div class="field">
        <label>Full name</label>
        <input type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" placeholder="Your full name" class="{{ $errors->has('name') ? 'err' : '' }}">
        @error('name') <span class="err-msg">{{ $message }}</span> @enderror
    </div>

    <div class="field">
        <label>Email address</label>
        <input type="email" name="email" value="{{ old('email') }}" required autocomplete="email" placeholder="you@company.com" class="{{ $errors->has('email') ? 'err' : '' }}">
        @error('email') <span class="err-msg">{{ $message }}</span> @enderror
    </div>

    <div class="field">
        <label>Username</label>
        <div style="position:relative;">
            <span style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:var(--text3);font-size:14.5px;">@</span>
            <input type="text" name="username" value="{{ old('username') }}" required autocomplete="username" placeholder="yourhandle" style="padding-left:28px;" class="{{ $errors->has('username') ? 'err' : '' }}">
        </div>
        @error('username') <span class="err-msg">{{ $message }}</span> @enderror
    </div>

    <div class="field">
        <label>Password</label>
        <div style="position:relative;">
            <input :type="showPwd ? 'text' : 'password'" name="password" required autocomplete="new-password" placeholder="Min. 8 characters" style="padding-right:42px;" class="{{ $errors->has('password') ? 'err' : '' }}">
            <button type="button" @click="showPwd = !showPwd" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);color:var(--text3);line-height:0;background:none;border:none;cursor:pointer;">
                <svg x-show="!showPwd" width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7z" stroke="currentColor" stroke-width="1.8"/><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.8"/></svg>
                <svg x-show="showPwd" width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><line x1="1" y1="1" x2="23" y2="23" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
            </button>
        </div>
        @error('password') <span class="err-msg">{{ $message }}</span> @enderror
    </div>

    <div class="field">
        <label>Confirm password</label>
        <input type="password" name="password_confirmation" required autocomplete="new-password" placeholder="Repeat password">
    </div>

    <button type="submit" class="auth-btn" :disabled="loading" style="margin-top:4px;">
        <span x-show="loading">Creating account…</span>
        <span x-show="!loading">Create account</span>
    </button>
</form>

<p class="auth-link" style="margin-top:20px;font-size:13.5px;color:var(--text2);text-align:center;">
    Already have an account? <a href="{{ route('login') }}" style="color:var(--primary);font-weight:700;text-decoration:none;">Sign in</a>
</p>
@endsection
