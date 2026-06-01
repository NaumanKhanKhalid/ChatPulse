@extends('layouts.auth')
@section('title', 'Guest Access')
@section('content')
<h2 class="text-xl font-semibold text-gray-900 mb-2">Join as a Guest</h2>
<p class="text-gray-500 text-sm mb-6">No account needed. Enter your name to start chatting.</p>

<form method="POST" action="{{ route('guest-login') }}" x-data="{ loading: false }" @submit="loading = true">
    @csrf
    <div class="space-y-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Display Name</label>
            <input type="text" name="name" value="{{ old('name') }}" required autofocus
                   class="input-field @error('name') border-red-400 @enderror"
                   placeholder="How should we call you?" minlength="2" maxlength="60">
            @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        {{-- Honeypot --}}
        <input type="text" name="website" class="hidden" tabindex="-1" autocomplete="off">

        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 text-xs text-yellow-800">
            <strong>Guest limitations:</strong> Cannot create groups, upload avatar, or access Settings. Create an account for full access.
        </div>

        <button type="submit" :disabled="loading" class="w-full btn-primary py-2.5 flex items-center justify-center gap-2">
            <svg x-show="loading" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            Start Chatting
        </button>
    </div>
</form>

<div class="mt-6 text-center space-y-2">
    <p class="text-sm text-gray-600">
        <a href="{{ route('login') }}" class="text-primary hover:text-primary-hover font-medium">Sign in</a>
        or
        <a href="{{ route('register') }}" class="text-primary hover:text-primary-hover font-medium">Create account</a>
    </p>
</div>
@endsection
