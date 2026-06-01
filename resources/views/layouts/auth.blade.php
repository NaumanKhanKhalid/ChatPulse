<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} — @yield('title', 'Welcome')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="min-h-screen flex" style="background:#f3f4f6;">

    {{-- Left decorative panel --}}
    <div class="hidden lg:flex lg:w-1/2 xl:w-[55%] relative overflow-hidden" style="background: linear-gradient(135deg,#0f172a 0%,#1e293b 60%,#134e4a 100%);">
        {{-- Grid pattern --}}
        <div class="absolute inset-0 opacity-10" style="background-image: linear-gradient(rgba(255,255,255,.15) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.15) 1px,transparent 1px);background-size:40px 40px;"></div>

        {{-- Glow blobs --}}
        <div class="absolute top-32 left-24 w-72 h-72 rounded-full opacity-20" style="background:radial-gradient(circle,#10b981,transparent 70%);"></div>
        <div class="absolute bottom-24 right-16 w-56 h-56 rounded-full opacity-15" style="background:radial-gradient(circle,#06b6d4,transparent 70%);"></div>

        <div class="relative z-10 flex flex-col justify-center px-16 py-12">
            {{-- Logo --}}
            <div class="flex items-center gap-3 mb-16">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:#10b981;">
                    <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
                <span class="text-white text-xl font-semibold tracking-tight">ChatPulse</span>
            </div>

            {{-- Headline --}}
            <h2 class="text-4xl font-bold text-white leading-tight mb-4">
                Connect your team<br>
                <span style="color:#10b981;">in real-time.</span>
            </h2>
            <p class="text-gray-400 text-base leading-relaxed mb-14 max-w-sm">
                Instant messaging, group chats, file sharing and video calls — all in one place.
            </p>

            {{-- Feature pills --}}
            <div class="space-y-3">
                @foreach([['⚡','Real-time messaging'],['🔒','End-to-end secure'],['📁','File & media sharing'],['🎥','Audio & video calls']] as [$icon,$label])
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center text-sm" style="background:rgba(16,185,129,.15);">{{ $icon }}</div>
                    <span class="text-gray-300 text-sm font-medium">{{ $label }}</span>
                </div>
                @endforeach
            </div>

            {{-- Mini chat preview --}}
            <div class="mt-14 bg-white/5 border border-white/10 rounded-2xl p-5 backdrop-blur-sm max-w-sm">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold text-white" style="background:#10b981;">A</div>
                    <div>
                        <p class="text-white text-xs font-medium">Adib Hussain</p>
                        <p class="text-gray-500 text-xs">just now</p>
                    </div>
                    <div class="ml-auto w-2 h-2 rounded-full" style="background:#10b981;"></div>
                </div>
                <div class="space-y-2">
                    <div class="rounded-xl rounded-tl-sm px-3 py-2 text-xs text-gray-200 w-fit" style="background:rgba(255,255,255,.08);">
                        Hey team! Just pushed the new release 🚀
                    </div>
                    <div class="rounded-xl rounded-tr-sm px-3 py-2 text-xs ml-auto w-fit" style="background:#10b981;color:#fff;">
                        Looks great! Testing now ✅
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Right form panel --}}
    <div class="flex-1 flex items-center justify-center px-6 py-12 bg-white">
        <div class="w-full max-w-sm">

            {{-- Mobile logo --}}
            <div class="flex items-center gap-2 mb-10 lg:hidden">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background:#10b981;">
                    <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
                <span class="text-gray-900 text-lg font-semibold">ChatPulse</span>
            </div>

            {{-- Flash messages --}}
            @if(session('error'))
            <div class="mb-5 flex items-start gap-2.5 p-3.5 rounded-xl text-sm border" style="background:#fef2f2;border-color:#fecaca;color:#b91c1c;">
                <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                {{ session('error') }}
            </div>
            @endif
            @if(session('success'))
            <div class="mb-5 flex items-start gap-2.5 p-3.5 rounded-xl text-sm border" style="background:#f0fdf4;border-color:#bbf7d0;color:#15803d;">
                <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                {{ session('success') }}
            </div>
            @endif

            @yield('content')
        </div>
    </div>

</body>
</html>
