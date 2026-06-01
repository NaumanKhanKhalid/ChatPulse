@extends('layouts.app')
@section('title', 'Create Group')

@section('left-panel')
<div class="p-4">
    <a href="{{ route('chat.index') }}" class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white mb-4">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Back to Messages
    </a>
</div>
@endsection

@section('content')
<div class="p-6 max-w-lg mx-auto w-full">
    <h1 class="text-xl font-bold text-gray-900 dark:text-white mb-6">Create Group</h1>

    <form method="POST" action="{{ route('groups.store') }}" class="space-y-5 bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-100 dark:border-gray-700">
        @csrf
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Group Name *</label>
            <input type="text" name="name" value="{{ old('name') }}" required class="input-field" placeholder="e.g. Project Team">
            @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
            <textarea name="description" rows="3" class="input-field" placeholder="What is this group about?">{{ old('description') }}</textarea>
        </div>
        <div x-data="{ isPrivate: true }">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Visibility</label>
            <div class="flex gap-3">
                <label class="flex-1 flex items-center gap-3 p-3 border-2 rounded-xl cursor-pointer transition-colors" :class="isPrivate ? 'border-primary bg-primary/5' : 'border-gray-200'">
                    <input type="radio" name="is_private" value="1" x-model="isPrivate" :checked="isPrivate" class="hidden">
                    <div>
                        <p class="font-medium text-sm text-gray-800 dark:text-gray-200">🔒 Private</p>
                        <p class="text-xs text-gray-500">Invite only</p>
                    </div>
                </label>
                <label class="flex-1 flex items-center gap-3 p-3 border-2 rounded-xl cursor-pointer transition-colors" :class="!isPrivate ? 'border-primary bg-primary/5' : 'border-gray-200'">
                    <input type="radio" name="is_private" value="0" x-model="isPrivate" :checked="!isPrivate" class="hidden">
                    <div>
                        <p class="font-medium text-sm text-gray-800 dark:text-gray-200">🌐 Public</p>
                        <p class="text-xs text-gray-500">Anyone can join</p>
                    </div>
                </label>
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Add Members</label>
            <div class="space-y-1 max-h-48 overflow-y-auto">
                @foreach($users as $user)
                <label class="flex items-center gap-3 p-2 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg cursor-pointer">
                    <input type="checkbox" name="member_ids[]" value="{{ $user->id }}" class="rounded text-primary">
                    <img src="{{ $user->avatar_url }}" class="w-7 h-7 rounded-full object-cover">
                    <span class="text-sm text-gray-800 dark:text-gray-200">{{ $user->name }}</span>
                </label>
                @endforeach
            </div>
        </div>
        <button type="submit" class="w-full btn-primary py-2.5">Create Group</button>
    </form>
</div>
@endsection
