@extends('layouts.app')
@section('title', 'Settings')

@section('left-panel')
<div class="flex flex-col h-full p-4">
    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-4">Settings</p>
    <nav class="space-y-0.5">
        @php
            $navItems = [
                ['label' => 'Profile',        'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
                ['label' => 'Notifications',  'icon' => 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9'],
                ['label' => 'Appearance',     'icon' => 'M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01'],
                ['label' => 'Account',        'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'],
                ['label' => 'API Tokens',     'icon' => 'M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z'],
            ];
        @endphp
        @foreach($navItems as $item)
        <button class="w-full flex items-center gap-2.5 px-3 py-2 rounded-xl text-sm font-medium text-gray-600 hover:bg-slate-100 hover:text-gray-900 transition-colors text-left">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}"/></svg>
            {{ $item['label'] }}
        </button>
        @endforeach
    </nav>
</div>
@endsection

@section('content')
<div class="flex-1 overflow-y-auto p-6">
    <div class="max-w-xl mx-auto w-full">
        <h1 class="text-lg font-bold text-gray-900 mb-1">Settings</h1>
        <p class="text-sm text-gray-400 mb-6">Manage your account preferences</p>

        {{-- Notifications --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 mb-4">
            <h3 class="font-semibold text-gray-800 mb-5">Notifications</h3>
            <form method="POST" action="{{ route('settings.notifications') }}" x-data="{ submitted: false }" @submit="submitted = true">
                @csrf @method('PATCH')

                <div class="flex items-center justify-between py-3 border-b border-slate-50">
                    <div>
                        <p class="text-sm font-medium text-gray-700">Email Notifications</p>
                        <p class="text-xs text-gray-400 mt-0.5">Receive updates in your inbox</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="email_notifications" value="1" {{ auth()->user()->email_notifications ? 'checked' : '' }} class="sr-only peer">
                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:bg-emerald-500 transition-colors after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-5"></div>
                    </label>
                </div>

                <div class="py-4">
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Email Digest</label>
                    <select name="email_digest"
                        class="w-full border border-slate-200 bg-slate-50 rounded-xl px-4 py-2.5 text-sm text-gray-700 outline-none transition"
                        onfocus="this.style.borderColor='#10b981';this.style.boxShadow='0 0 0 3px rgba(16,185,129,0.12)'"
                        onblur="this.style.borderColor='';this.style.boxShadow=''">
                        <option value="never"  {{ auth()->user()->email_digest === 'never'  ? 'selected' : '' }}>Never</option>
                        <option value="daily"  {{ auth()->user()->email_digest === 'daily'  ? 'selected' : '' }}>Daily</option>
                        <option value="weekly" {{ auth()->user()->email_digest === 'weekly' ? 'selected' : '' }}>Weekly</option>
                    </select>
                </div>

                <button type="submit" class="text-white font-semibold rounded-xl px-4 py-2 text-sm transition-opacity hover:opacity-90" style="background:#10b981;">Save Changes</button>
            </form>
        </div>

        {{-- API Access --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 mb-4">
            <h3 class="font-semibold text-gray-800 mb-1">API Tokens</h3>
            <p class="text-sm text-gray-400 mb-5">Generate a personal API token to integrate with external tools.</p>
            <form method="POST" action="{{ route('profile.update') }}">
                @csrf @method('PATCH')
                <div x-data="{ token: null, loading: false }">
                    <button type="button"
                        @click="async () => { loading = true; const r = await fetch('/sanctum/csrf-cookie'); const res = await fetch('/settings/api/token', {method:'POST', headers:{'X-CSRF-TOKEN':document.querySelector('[name=csrf-token]').content, 'Accept':'application/json'}}); const d = await res.json(); token = d.token; loading = false; }"
                        class="bg-slate-100 hover:bg-slate-200 text-gray-700 rounded-xl px-4 py-2 text-sm font-medium transition-colors"
                        :disabled="loading">
                        <span x-show="!loading">Generate Token</span>
                        <span x-show="loading">Generating…</span>
                    </button>
                    <div x-show="token" class="mt-4 p-4 bg-slate-50 rounded-xl border border-slate-200">
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Your API token — copy now, it won't show again</p>
                        <code class="text-xs text-gray-800 break-all font-mono" x-text="token"></code>
                    </div>
                </div>
            </form>
        </div>

        {{-- Danger Zone --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
            <h3 class="font-semibold text-gray-800 mb-1">Danger Zone</h3>
            <p class="text-sm text-gray-400 mb-5">Irreversible actions — proceed with caution.</p>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-sm font-semibold text-red-500 hover:text-red-700 bg-red-50 hover:bg-red-100 rounded-xl px-4 py-2 transition-colors">Sign out of account</button>
            </form>
        </div>

    </div>
</div>
@endsection
