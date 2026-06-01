@extends('layouts.admin')
@section('page-title', 'Groups')
@section('content')
<div class="bg-white rounded-xl border border-gray-100 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b">
            <tr class="text-left text-gray-500">
                <th class="px-4 py-3">Name</th>
                <th class="px-4 py-3">Type</th>
                <th class="px-4 py-3">Members</th>
                <th class="px-4 py-3">Created</th>
                <th class="px-4 py-3">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($groups as $group)
            <tr class="border-b border-gray-50 hover:bg-gray-50">
                <td class="px-4 py-3 font-medium text-gray-800">{{ $group->name }}</td>
                <td class="px-4 py-3"><span class="text-xs {{ $group->is_private ? 'bg-gray-100 text-gray-600' : 'bg-green-100 text-green-700' }} px-2 py-0.5 rounded-full">{{ $group->is_private ? 'Private' : 'Public' }}</span></td>
                <td class="px-4 py-3 text-gray-500">{{ $group->participants_count }}</td>
                <td class="px-4 py-3 text-gray-400 text-xs">{{ $group->created_at->diffForHumans() }}</td>
                <td class="px-4 py-3">
                    <form method="POST" action="{{ route('admin.groups.destroy', $group) }}" onsubmit="return confirm('Delete this group?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-xs text-red-600 hover:text-red-800">Delete</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="p-4">{{ $groups->links() }}</div>
</div>
@endsection
