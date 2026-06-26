@extends('layouts.app')
@section('title', 'Designations')
@section('page-title', 'Designations')

@section('content')
{{-- Filters --}}
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-5">
    <form method="GET" action="{{ route('admin.designations.index') }}" class="flex flex-wrap gap-3 items-end">
        <div class="flex-1 min-w-[180px]">
            <label class="block text-xs font-medium text-gray-600 mb-1">Search</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Designation or department…"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        <div class="min-w-[160px]">
            <label class="block text-xs font-medium text-gray-600 mb-1">Department</label>
            <select name="department_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">All Departments</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                        {{ $dept->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="min-w-[130px]">
            <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
            <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">All Status</option>
                <option value="active"   {{ request('status') === 'active'   ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>
        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
            <i class="fas fa-search mr-1"></i> Filter
        </button>
        <a href="{{ route('admin.designations.index') }}" class="text-sm text-gray-500 hover:text-gray-700 py-2">Clear</a>
    </form>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100">
    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
        <h2 class="font-semibold text-gray-800">All Designations <span class="text-gray-400 font-normal text-sm">({{ $designations->total() }})</span></h2>
        <a href="{{ route('admin.designations.create') }}"
           class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition flex items-center gap-2">
            <i class="fas fa-plus text-xs"></i> Add Designation
        </a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-gray-700">
            <thead class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wider">
                <tr>
                    <th class="px-6 py-3 text-left">#</th>
                    <th class="px-6 py-3 text-left">Designation</th>
                    <th class="px-6 py-3 text-left">Department</th>
                    <th class="px-6 py-3 text-center">Employees</th>
                    <th class="px-6 py-3 text-center">Status</th>
                    <th class="px-6 py-3 text-center">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($designations as $desig)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-3 text-gray-400">{{ $designations->firstItem() + $loop->index }}</td>
                    <td class="px-6 py-3 font-medium text-gray-900">{{ $desig->name }}</td>
                    <td class="px-6 py-3">
                        <span class="bg-blue-50 text-blue-700 text-xs px-2.5 py-1 rounded-full font-medium">{{ $desig->department->name ?? '—' }}</span>
                    </td>
                    <td class="px-6 py-3 text-center">
                        <span class="bg-indigo-100 text-indigo-700 text-xs font-medium px-2.5 py-1 rounded-full">{{ $desig->employees_count }}</span>
                    </td>
                    <td class="px-6 py-3 text-center">
                        <form method="POST" action="{{ route('admin.designations.toggle-status', $desig) }}">
                            @csrf
                            <button type="submit"
                                    class="px-3 py-1 rounded-full text-xs font-medium transition
                                        {{ $desig->status === 'active'
                                            ? 'bg-green-100 text-green-700 hover:bg-green-200'
                                            : 'bg-red-100 text-red-700 hover:bg-red-200' }}">
                                {{ ucfirst($desig->status) }}
                            </button>
                        </form>
                    </td>
                    <td class="px-6 py-3 text-center">
                        <div class="flex items-center justify-center gap-2">
                            <a href="{{ route('admin.designations.edit', $desig) }}"
                               class="p-1.5 rounded-lg text-gray-500 hover:bg-indigo-50 hover:text-indigo-600 transition">
                                <i class="fas fa-pen-to-square text-sm"></i>
                            </a>
                            <form method="POST" action="{{ route('admin.designations.destroy', $desig) }}"
                                  onsubmit="return confirm('Delete this designation?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="p-1.5 rounded-lg text-gray-500 hover:bg-red-50 hover:text-red-600 transition">
                                    <i class="fas fa-trash text-sm"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-6 py-10 text-center text-gray-400">No designations found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($designations->hasPages())
    <div class="px-6 py-4 border-t border-gray-100">{{ $designations->links() }}</div>
    @endif
</div>
@endsection
