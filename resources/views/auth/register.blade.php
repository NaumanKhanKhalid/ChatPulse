@extends('layouts.auth')
@section('title', 'Create Account')
@section('content')
<h2 class="text-xl font-semibold text-gray-900 mb-6">Create your account</h2>

<form method="POST" action="{{ route('register') }}" x-data="{ loading: false }" @submit="loading = true">
    @csrf
    <div class="space-y-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
            <input type="text" name="name" value="{{ old('name') }}" required autofocus
                   class="input-field @error('name') border-red-400 @enderror"
                   placeholder="Your full name">
            @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <input type="email" name="email" value="{{ old('email') }}" required
                   class="input-field @error('email') border-red-400 @enderror"
                   placeholder="you@example.com">
            @error('email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
            <input type="password" name="password" required
                   class="input-field @error('password') border-red-400 @enderror"
                   placeholder="At least 8 characters">
            @error('password')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
            <input type="password" name="password_confirmation" required
                   class="input-field" placeholder="Repeat password">
        </div>

        <button type="submit" :disabled="loading" class="w-full btn-primary py-2.5 flex items-center justify-center gap-2">
            <svg x-show="loading" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            Create Account
        </button>
    </div>
</form>

<div class="mt-6 text-center space-y-2">
    <p class="text-sm text-gray-600">
        Already have an account?
        <a href="{{ route('login') }}" class="text-primary hover:text-primary-hover font-medium">Sign in</a>
    </p>
    <p class="text-sm text-gray-600">
        <a href="{{ route('guest-login') }}" class="text-gray-500 hover:text-gray-700">Continue as Guest →</a>
    </p>
</div>
@endsection
