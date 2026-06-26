@extends('layouts.admin')
@section('page-title', 'Dashboard')
@section('content')
<div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-6">
    @foreach([['Users', $stats['users'], 'text-blue-600 bg-blue-50'], ['Online', $stats['online_users'], 'text-green-600 bg-green-50'], ['Guests', $stats['guests'], 'text-yellow-600 bg-yellow-50'], ['Conversations', $stats['conversations'], 'text-purple-600 bg-purple-50'], ['Msgs Today', $stats['messages_today'], 'text-emerald-600 bg-emerald-50'], ['Banned', $stats['banned_users'], 'text-red-600 bg-red-50']] as [$label, $val, $cls])
    <div class="bg-white rounded-xl border border-gray-100 p-4">
        <div class="w-10 h-10 rounded-lg {{ $cls }} flex items-center justify-center mb-2">
            <span class="font-bold text-lg">{{ $val }}</span>
        </div>
        <p class="text-sm text-gray-500">{{ $label }}</p>
    </div>
    @endforeach
</div>

<div class="bg-white rounded-xl border border-gray-100 p-4">
    <h3 class="font-semibold text-gray-800 mb-3">Recent Users</h3>
    <table class="w-full text-sm">
        <thead><tr class="text-left text-gray-500 border-b"><th class="pb-2">Name</th><th class="pb-2">Email</th><th class="pb-2">Role</th><th class="pb-2">Joined</th></tr></thead>
        <tbody>
            @foreach($recentUsers as $u)
            <tr class="border-b border-gray-50 hover:bg-gray-50">
                <td class="py-2">{{ $u->name }}@if($u->is_guest)<span class="ml-1 text-xs bg-yellow-100 text-yellow-700 px-1 rounded">Guest</span>@endif</td>
                <td class="py-2 text-gray-500">{{ $u->email ?? '—' }}</td>
                <td class="py-2"><span class="px-2 py-0.5 rounded text-xs font-medium {{ $u->role === 'admin' ? 'bg-primary/10 text-primary' : 'bg-gray-100 text-gray-600' }}">{{ $u->role }}</span></td>
                <td class="py-2 text-gray-400">{{ $u->created_at->diffForHumans() }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
