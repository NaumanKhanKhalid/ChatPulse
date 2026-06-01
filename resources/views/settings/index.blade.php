@extends('layouts.app')
@section('title', 'Settings')

@section('left-panel')
<div class="p-4">
    <h2 class="font-semibold text-gray-900 dark:text-white text-sm">Settings</h2>
</div>
@endsection

@section('content')
<div class="p-6 max-w-xl mx-auto w-full">
    <h1 class="text-xl font-bold text-gray-900 dark:text-white mb-6">Settings</h1>

    <div class="space-y-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-5">
            <h3 class="font-semibold text-gray-800 dark:text-white mb-4">Notifications</h3>
            <form method="POST" action="{{ route('settings.notifications') }}" x-data="{ submitted: false }" @submit="submitted = true">
                @csrf @method('PATCH')
                <div class="flex items-center justify-between mb-3">
                    <label class="text-sm text-gray-700 dark:text-gray-300">Email Notifications</label>
                    <input type="checkbox" name="email_notifications" value="1" {{ auth()->user()->email_notifications ? 'checked' : '' }} class="rounded text-primary">
                </div>
                <div class="mb-4">
                    <label class="block text-sm text-gray-700 dark:text-gray-300 mb-1">Email Digest</label>
                    <select name="email_digest" class="input-field">
                        <option value="never" {{ auth()->user()->email_digest === 'never' ? 'selected' : '' }}>Never</option>
                        <option value="daily" {{ auth()->user()->email_digest === 'daily' ? 'selected' : '' }}>Daily</option>
                        <option value="weekly" {{ auth()->user()->email_digest === 'weekly' ? 'selected' : '' }}>Weekly</option>
                    </select>
                </div>
                <button type="submit" class="btn-primary text-sm py-2">Save</button>
            </form>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-5">
            <h3 class="font-semibold text-gray-800 dark:text-white mb-4">API Access</h3>
            <p class="text-sm text-gray-500 mb-3">Generate a personal API token to integrate with external tools.</p>
            <form method="POST" action="{{ route('profile.update') }}">
                @csrf @method('PATCH')
                <div x-data="{ token: null, loading: false }">
                    <button type="button" @click="async () => { loading = true; const r = await fetch('/sanctum/csrf-cookie'); const res = await fetch('/settings/api/token', {method:'POST', headers:{'X-CSRF-TOKEN':document.querySelector('[name=csrf-token]').content, 'Accept':'application/json'}}); const d = await res.json(); token = d.token; loading = false; }"
                            class="btn-secondary text-sm py-2" :disabled="loading">
                        <span x-show="!loading">Generate Token</span>
                        <span x-show="loading">Generating...</span>
                    </button>
                    <div x-show="token" class="mt-3 p-3 bg-gray-50 rounded-lg">
                        <p class="text-xs text-gray-500 mb-1">Your API token (copy now — won't show again):</p>
                        <code class="text-xs text-gray-800 break-all" x-text="token"></code>
                    </div>
                </div>
            </form>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-5">
            <h3 class="font-semibold text-gray-800 dark:text-white mb-2">Danger Zone</h3>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-sm text-red-500 hover:text-red-700">Sign out</button>
            </form>
        </div>
    </div>
</div>
@endsection
