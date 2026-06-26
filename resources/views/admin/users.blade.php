@extends('layouts.admin')
@section('page-title', 'Users')
@section('content')
<div class="bg-white rounded-xl border border-gray-100 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-100">
            <tr class="text-left text-gray-500">
                <th class="px-4 py-3">User</th>
                <th class="px-4 py-3">Role</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3">Joined</th>
                <th class="px-4 py-3">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $u)
            <tr class="border-b border-gray-50 hover:bg-gray-50">
                <td class="px-4 py-3">
                    <div class="flex items-center gap-2">
                        <img src="{{ $u->avatar_url }}" class="w-7 h-7 rounded-full">
                        <div>
                            <p class="font-medium text-gray-800">{{ $u->name }}</p>
                            <p class="text-xs text-gray-400">{{ $u->email ?? '@' . $u->username }}</p>
                        </div>
                    </div>
                </td>
                <td class="px-4 py-3">
                    <form method="POST" action="{{ route('admin.users.role', $u) }}" class="flex items-center gap-2">
                        @csrf @method('PATCH')
                        <select name="role" onchange="this.form.submit()" class="text-xs border rounded px-2 py-1">
                            @foreach(['admin','user','guest'] as $r)
                            <option value="{{ $r }}" {{ $u->role === $r ? 'selected' : '' }}>{{ $r }}</option>
                            @endforeach
                        </select>
                    </form>
                </td>
                <td class="px-4 py-3">
                    @if($u->is_banned)
                    <span class="text-xs bg-red-100 text-red-700 px-2 py-0.5 rounded-full">Banned</span>
                    @else
                    <span class="text-xs {{ $u->is_online ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }} px-2 py-0.5 rounded-full">{{ $u->is_online ? 'Online' : 'Offline' }}</span>
                    @endif
                </td>
                <td class="px-4 py-3 text-gray-400 text-xs">{{ $u->created_at->diffForHumans() }}</td>
                <td class="px-4 py-3">
                    @if($u->id !== auth()->id())
                    @if($u->is_banned)
                    <form method="POST" action="{{ route('admin.users.unban', $u) }}" class="inline">
                        @csrf
                        <button type="submit" class="text-xs text-green-600 hover:text-green-800 mr-2">Unban</button>
                    </form>
                    @else
                    <form method="POST" action="{{ route('admin.users.ban', $u) }}" class="inline" onsubmit="return confirm('Ban this user?')">
                        @csrf
                        <button type="submit" class="text-xs text-red-600 hover:text-red-800">Ban</button>
                    </form>
                    @endif
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="p-4">{{ $users->links() }}</div>
</div>
@endsection
