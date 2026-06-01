<!DOCTYPE html>
<html lang="en" x-data x-bind:class="{ 'dark': $store.app.darkMode }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} - @yield('title', 'Chat')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex h-screen overflow-hidden bg-gray-50 dark:bg-gray-900" x-data="teamflowApp()">

    {{-- Toast container --}}
    <div class="fixed bottom-4 right-4 z-50 flex flex-col gap-2 pointer-events-none">
        <template x-for="toast in toasts" :key="toast.id">
            <div class="pointer-events-auto flex items-center gap-3 px-4 py-3 rounded-lg shadow-lg text-sm font-medium text-white transition-all duration-300"
                 :class="{
                     'bg-green-500': toast.type === 'success',
                     'bg-red-500': toast.type === 'error',
                     'bg-blue-500': toast.type === 'info',
                     'bg-yellow-500': toast.type === 'warning',
                 }"
                 x-show="toast.visible"
                 x-transition>
                <span x-text="toast.message"></span>
                <button @click="removeToast(toast.id)" class="ml-2 opacity-70 hover:opacity-100">✕</button>
            </div>
        </template>
    </div>

    {{-- Incoming call overlay --}}
    <div x-show="incomingCall" x-transition
         class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center" style="display:none">
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-8 shadow-2xl text-center max-w-sm w-full mx-4">
            <div class="w-20 h-20 rounded-full bg-primary/10 flex items-center justify-center mx-auto mb-4">
                <svg class="w-10 h-10 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-1" x-text="incomingCall?.caller?.name + ' is calling...'"></h3>
            <p class="text-sm text-gray-500 mb-6" x-text="incomingCall?.type === 'video' ? 'Video call' : 'Voice call'"></p>
            <div class="flex gap-4 justify-center">
                <button @click="declineCall()" class="w-14 h-14 rounded-full bg-red-500 hover:bg-red-600 text-white flex items-center justify-center transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2M5 3a2 2 0 00-2 2v1c0 8.284 6.716 15 15 15h1a2 2 0 002-2v-3.28a1 1 0 00-.684-.948l-4.493-1.498a1 1 0 00-1.21.502l-1.13 2.257a11.042 11.042 0 01-5.516-5.517l2.257-1.13a1 1 0 00.502-1.21L9.228 3.683A1 1 0 008.279 3H5z"/></svg>
                </button>
                <button @click="acceptCall()" class="w-14 h-14 rounded-full bg-primary hover:bg-primary-hover text-white flex items-center justify-center transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Active call floating window --}}
    <div x-show="activeCall" x-transition
         class="fixed bottom-6 right-6 z-40 bg-gray-900 rounded-2xl shadow-2xl overflow-hidden w-80" style="display:none">
        <video x-ref="remoteVideo" autoplay playsinline class="w-full h-48 object-cover bg-gray-800"></video>
        <video x-ref="localVideo" autoplay playsinline muted class="absolute bottom-16 right-3 w-24 h-18 rounded-lg object-cover border-2 border-white"></video>
        <div class="flex items-center justify-center gap-3 p-3 bg-gray-800">
            <button @click="toggleMic()" :class="isMuted ? 'bg-red-500' : 'bg-gray-600'" class="w-10 h-10 rounded-full flex items-center justify-center text-white hover:opacity-80 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/></svg>
            </button>
            <button @click="toggleCamera()" :class="isCameraOff ? 'bg-red-500' : 'bg-gray-600'" class="w-10 h-10 rounded-full flex items-center justify-center text-white hover:opacity-80 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.069A1 1 0 0121 8.87v6.26a1 1 0 01-1.447.894L15 14M3 8a2 2 0 012-2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V8z"/></svg>
            </button>
            <button @click="endCall()" class="w-12 h-12 rounded-full bg-red-500 hover:bg-red-600 flex items-center justify-center text-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2M5 3a2 2 0 00-2 2v1c0 8.284 6.716 15 15 15h1a2 2 0 002-2v-3.28a1 1 0 00-.684-.948l-4.493-1.498a1 1 0 00-1.21.502l-1.13 2.257a11.042 11.042 0 01-5.516-5.517l2.257-1.13a1 1 0 00.502-1.21L9.228 3.683A1 1 0 008.279 3H5z"/></svg>
            </button>
        </div>
    </div>

    {{-- 4-column layout --}}
    <div class="flex h-screen w-full overflow-hidden">
        {{-- Rail (64px dark) --}}
        @include('layouts.partials.rail')

        {{-- Left panel --}}
        <div class="w-72 bg-sidebar-bg border-r border-sidebar-border flex flex-col h-full dark:bg-gray-800 dark:border-gray-700 flex-shrink-0">
            @yield('left-panel')
        </div>

        {{-- Main content area --}}
        <div class="flex-1 flex flex-col h-full overflow-hidden">
            @yield('content')
        </div>

        {{-- Right panel (optional) --}}
        @hasSection('right-panel')
        <div class="w-72 bg-white border-l border-gray-100 flex flex-col h-full dark:bg-gray-800 dark:border-gray-700 flex-shrink-0 overflow-y-auto" id="right-panel">
            @yield('right-panel')
        </div>
        @endif
    </div>
</body>
</html>
