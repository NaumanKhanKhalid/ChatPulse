@extends('layouts.app')
@section('title', 'Create Group')

@section('left-panel')
<div class="p-4">
    <a href="{{ route('chat.index') }}" class="flex items-center gap-2 text-sm font-medium text-gray-500 hover:text-gray-900 transition-colors group">
        <span class="w-7 h-7 rounded-lg bg-slate-100 group-hover:bg-slate-200 flex items-center justify-center transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        </span>
        Back to Messages
    </a>
</div>
@endsection

@section('content')
<div class="flex-1 overflow-y-auto p-6">
    <div class="max-w-md mx-auto w-full">
        <h1 class="text-lg font-bold text-gray-900 mb-1">Create a Group</h1>
        <p class="text-sm text-gray-400 mb-6">Start a new group conversation</p>

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
            <form method="POST" action="{{ route('groups.store') }}" class="space-y-5">
                @csrf

                {{-- Name --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Group Name <span class="text-red-400">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. Project Team"
                        class="w-full border border-slate-200 bg-slate-50 rounded-xl px-4 py-2.5 text-sm text-gray-700 outline-none transition"
                        onfocus="this.style.borderColor='#10b981';this.style.boxShadow='0 0 0 3px rgba(16,185,129,0.12)'"
                        onblur="this.style.borderColor='';this.style.boxShadow=''">
                    @error('name')<p class="text-red-500 text-xs mt-1.5">{{ $message }}</p>@enderror
                </div>

                {{-- Description --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Description</label>
                    <textarea name="description" rows="3" placeholder="What is this group about?"
                        class="w-full border border-slate-200 bg-slate-50 rounded-xl px-4 py-2.5 text-sm text-gray-700 outline-none transition resize-none"
                        onfocus="this.style.borderColor='#10b981';this.style.boxShadow='0 0 0 3px rgba(16,185,129,0.12)'"
                        onblur="this.style.borderColor='';this.style.boxShadow=''">{{ old('description') }}</textarea>
                </div>

                {{-- Visibility --}}
                <div x-data="{ isPrivate: true }">
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Visibility</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="flex items-center gap-3 p-3.5 border-2 rounded-xl cursor-pointer transition-all"
                            :class="isPrivate ? 'border-emerald-500 bg-emerald-50/60' : 'border-slate-200 hover:border-slate-300'">
                            <input type="radio" name="is_private" value="1" x-model="isPrivate" :checked="isPrivate" class="hidden">
                            <div>
                                <p class="font-semibold text-sm text-gray-800">🔒 Private</p>
                                <p class="text-xs text-gray-400 mt-0.5">Invite only</p>
                            </div>
                        </label>
                        <label class="flex items-center gap-3 p-3.5 border-2 rounded-xl cursor-pointer transition-all"
                            :class="!isPrivate ? 'border-emerald-500 bg-emerald-50/60' : 'border-slate-200 hover:border-slate-300'">
                            <input type="radio" name="is_private" value="0" x-model="isPrivate" :checked="!isPrivate" class="hidden">
                            <div>
                                <p class="font-semibold text-sm text-gray-800">🌐 Public</p>
                                <p class="text-xs text-gray-400 mt-0.5">Anyone can join</p>
                            </div>
                        </label>
                    </div>
                </div>

                {{-- Members --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Add Members</label>
                    <div class="border border-slate-200 bg-slate-50 rounded-xl overflow-hidden divide-y divide-slate-100 max-h-52 overflow-y-auto">
                        @foreach($users as $user)
                        <label class="flex items-center gap-3 px-4 py-2.5 hover:bg-white cursor-pointer transition-colors">
                            <input type="checkbox" name="member_ids[]" value="{{ $user->id }}"
                                class="w-4 h-4 rounded border-slate-300 accent-emerald-500">
                            <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-7 h-7 rounded-full object-cover flex-shrink-0">
                            <span class="text-sm text-gray-800 font-medium">{{ $user->name }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>

                <button type="submit" class="w-full text-white font-semibold rounded-xl px-4 py-2.5 text-sm transition-opacity hover:opacity-90" style="background:#10b981;">
                    Create Group
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
