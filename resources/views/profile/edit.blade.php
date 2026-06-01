@extends('layouts.app')
@section('title', 'Edit Profile')

@section('left-panel')
<div class="p-4">
    <a href="{{ route('chat.index') }}" class="flex items-center gap-2 text-sm text-gray-600 hover:text-gray-900">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Back
    </a>
</div>
@endsection

@section('content')
<div class="p-6 max-w-lg mx-auto w-full">
    <h1 class="text-xl font-bold text-gray-900 dark:text-white mb-6">Edit Profile</h1>

    @if(session('success'))
    <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data"
          class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-6 space-y-5">
        @csrf @method('PATCH')

        {{-- Avatar --}}
        @if(!$user->is_guest)
        <div class="flex items-center gap-4">
            <img src="{{ $user->avatar_url }}" class="w-16 h-16 rounded-full object-cover">
            <div>
                <label class="cursor-pointer">
                    <span class="btn-secondary text-sm inline-block">Change Avatar</span>
                    <input type="file" name="avatar" accept="image/*" class="hidden">
                </label>
                <p class="text-xs text-gray-400 mt-1">JPG, PNG, max 2MB</p>
            </div>
        </div>
        @endif

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Display Name</label>
            <input type="text" name="name" value="{{ old('name', $user->name) }}" class="input-field">
            @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Bio</label>
            <textarea name="bio" rows="3" maxlength="160" class="input-field" placeholder="Tell people about yourself...">{{ old('bio', $user->bio) }}</textarea>
        </div>

        <button type="submit" class="w-full btn-primary py-2.5">Save Changes</button>
    </form>
</div>
@endsection
