@extends('layouts.app')
@section('title', 'Edit Profile')

@section('left-panel')
<div class="p-4">
    <a href="{{ route('chat.index') }}" class="flex items-center gap-2 text-sm font-medium text-gray-500 hover:text-gray-900 transition-colors group">
        <span class="w-7 h-7 rounded-lg bg-slate-100 group-hover:bg-slate-200 flex items-center justify-center transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        </span>
        Back
    </a>
</div>
@endsection

@section('content')
<div class="flex-1 overflow-y-auto p-6">
    <div class="max-w-lg mx-auto w-full">
        <h1 class="text-lg font-bold text-gray-900 mb-1">Edit Profile</h1>
        <p class="text-sm text-gray-400 mb-6">Update your public information</p>

        @if(session('success'))
        <div class="mb-5 flex items-center gap-3 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-sm">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            {{ session('success') }}
        </div>
        @endif

        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
            @csrf @method('PATCH')

            {{-- Avatar upload --}}
            @if(!$user->is_guest)
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 mb-4">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-4">Profile Photo</p>
                <div class="flex items-center gap-5">
                    <div class="relative">
                        <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-20 h-20 rounded-full object-cover ring-4 ring-slate-50">
                    </div>
                    <div>
                        <label class="cursor-pointer">
                            <span class="bg-slate-100 hover:bg-slate-200 text-gray-700 rounded-xl px-4 py-2 text-sm font-medium transition-colors inline-block">Change Photo</span>
                            <input type="file" name="avatar" accept="image/*" class="hidden">
                        </label>
                        <p class="text-xs text-gray-400 mt-2">JPG or PNG, max 2 MB</p>
                    </div>
                </div>
            </div>
            @endif

            {{-- Form fields --}}
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-5">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Personal Info</p>

                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Display Name</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}"
                        class="w-full border border-slate-200 bg-slate-50 rounded-xl px-4 py-2.5 text-sm text-gray-700 outline-none transition"
                        onfocus="this.style.borderColor='#10b981';this.style.boxShadow='0 0 0 3px rgba(16,185,129,0.12)'"
                        onblur="this.style.borderColor='';this.style.boxShadow=''">
                    @error('name')<p class="text-red-500 text-xs mt-1.5">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Bio</label>
                    <textarea name="bio" rows="3" maxlength="160" placeholder="Tell people about yourself…"
                        class="w-full border border-slate-200 bg-slate-50 rounded-xl px-4 py-2.5 text-sm text-gray-700 outline-none transition resize-none"
                        onfocus="this.style.borderColor='#10b981';this.style.boxShadow='0 0 0 3px rgba(16,185,129,0.12)'"
                        onblur="this.style.borderColor='';this.style.boxShadow=''">{{ old('bio', $user->bio) }}</textarea>
                    @error('bio')<p class="text-red-500 text-xs mt-1.5">{{ $message }}</p>@enderror
                    <p class="text-xs text-gray-400 mt-1 text-right">160 characters max</p>
                </div>

                <div class="pt-1">
                    <button type="submit" class="w-full text-white font-semibold rounded-xl px-4 py-2.5 text-sm transition-opacity hover:opacity-90" style="background:#10b981;">Save Changes</button>
                </div>
            </div>

        </form>
    </div>
</div>
@endsection
