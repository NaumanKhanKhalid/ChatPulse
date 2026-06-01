@extends('layouts.auth')
@section('title', 'Guest Access')

@section('content')
<div>
    {{-- Header --}}
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900 mb-1">Continue as Guest</h1>
        <p class="text-sm text-gray-500">No account needed — just enter your name</p>
    </div>

    <form method="POST" action="{{ route('guest-login') }}" x-data="{ loading: false }" @submit="loading = true">
        @csrf

        <div class="space-y-4">

            {{-- Display Name --}}
            <div>
                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">
                    Display Name
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <input type="text" name="name" value="{{ old('name') }}"
                           required autofocus autocomplete="off"
                           placeholder="How should we call you?"
                           minlength="2" maxlength="60"
                           class="w-full pl-9 pr-4 py-2.5 text-sm border rounded-xl outline-none transition-all duration-150 {{ $errors->has('name') ? 'border-red-400 bg-red-50' : 'border-gray-200 bg-gray-50' }}"
                           style="font-family:'Inter',sans-serif;"
                           @if(!$errors->has('name'))
                           onfocus="this.style.borderColor='#10b981';this.style.backgroundColor='#fff';this.style.boxShadow='0 0 0 3px rgba(16,185,129,0.12)'"
                           onblur="this.style.borderColor='';this.style.backgroundColor='';this.style.boxShadow=''"
                           @endif>
                </div>
                @error('name')
                <p class="mt-1.5 text-xs text-red-500 flex items-center gap-1">
                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                    {{ $message }}
                </p>
                @enderror
            </div>

            {{-- Honeypot --}}
            <input type="text" name="website" class="hidden" tabindex="-1" autocomplete="off">

            {{-- Guest limitations warning --}}
            <div class="bg-amber-50 border border-amber-200 rounded-xl p-3 flex gap-2.5">
                <svg class="w-4 h-4 text-amber-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                </svg>
                <div class="text-xs text-amber-700 leading-relaxed">
                    <span class="font-semibold">Guest limitations:</span> Cannot create groups, upload avatar, or access Settings. <a href="{{ route('register') }}" class="font-semibold underline underline-offset-1" style="color:#b45309;">Create an account</a> for full access.
                </div>
            </div>

            {{-- Submit --}}
            <button type="submit" :disabled="loading"
                    class="w-full flex items-center justify-center gap-2 py-2.5 px-4 text-sm font-semibold text-white rounded-xl transition-all duration-150 active:scale-95 disabled:opacity-60"
                    style="background:#10b981;">
                <svg x-show="loading" class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                <span x-text="loading ? 'Starting…' : 'Start chatting'"></span>
            </button>

        </div>
    </form>

    {{-- Divider --}}
    <div class="relative my-6">
        <div class="absolute inset-0 flex items-center">
            <div class="w-full border-t border-gray-100"></div>
        </div>
        <div class="relative flex justify-center">
            <span class="bg-white px-3 text-xs text-gray-400 font-medium">or</span>
        </div>
    </div>

    {{-- Auth links --}}
    <div class="flex items-center justify-center gap-4 text-sm text-gray-500">
        <a href="{{ route('login') }}" class="font-semibold" style="color:#10b981;">Sign in</a>
        <span class="text-gray-300">·</span>
        <a href="{{ route('register') }}" class="font-semibold" style="color:#10b981;">Create account</a>
    </div>
</div>
@endsection
