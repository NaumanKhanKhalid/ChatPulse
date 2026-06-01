@extends('layouts.admin')
@section('page-title', 'Security')
@section('content')
<div class="grid gap-6">
    {{-- Ban IP form --}}
    <div class="bg-white rounded-xl border border-gray-100 p-5">
        <h3 class="font-semibold text-gray-800 mb-4">Ban IP Address</h3>
        <form method="POST" action="{{ route('admin.security.ban-ip') }}" class="flex gap-3 flex-wrap">
            @csrf
            <input type="text" name="ip_address" placeholder="IP Address" required class="input-field w-48">
            <input type="text" name="reason" placeholder="Reason (optional)" class="input-field flex-1">
            <input type="datetime-local" name="expires_at" class="input-field w-48">
            <button type="submit" class="btn-primary text-sm px-4">Ban IP</button>
        </form>
    </div>

    {{-- IP Bans list --}}
    <div class="bg-white rounded-xl border border-gray-100 p-5">
        <h3 class="font-semibold text-gray-800 mb-4">Active IP Bans</h3>
        @forelse($ipBans as $ban)
        <div class="flex items-center justify-between py-2 border-b border-gray-50">
            <div>
                <p class="text-sm font-medium text-gray-800">{{ $ban->ip_address }}</p>
                <p class="text-xs text-gray-400">{{ $ban->reason ?? 'No reason' }} • by {{ $ban->banner?->name }} • {{ $ban->created_at->diffForHumans() }}</p>
            </div>
            <form method="POST" action="{{ route('admin.security.unban-ip', $ban) }}">
                @csrf @method('DELETE')
                <button type="submit" class="text-xs text-green-600 hover:text-green-800">Unban</button>
            </form>
        </div>
        @empty
        <p class="text-sm text-gray-400">No IP bans</p>
        @endforelse
    </div>

    {{-- Banned users --}}
    <div class="bg-white rounded-xl border border-gray-100 p-5">
        <h3 class="font-semibold text-gray-800 mb-4">Banned Users</h3>
        @forelse($bannedUsers as $u)
        <div class="flex items-center justify-between py-2 border-b border-gray-50">
            <div>
                <p class="text-sm font-medium text-gray-800">{{ $u->name }}</p>
                <p class="text-xs text-gray-400">{{ $u->banned_reason ?? 'No reason' }} • {{ $u->banned_at?->diffForHumans() }}</p>
            </div>
            <form method="POST" action="{{ route('admin.users.unban', $u) }}">
                @csrf
                <button type="submit" class="text-xs text-green-600 hover:text-green-800">Unban</button>
            </form>
        </div>
        @empty
        <p class="text-sm text-gray-400">No banned users</p>
        @endforelse
    </div>
</div>
@endsection
