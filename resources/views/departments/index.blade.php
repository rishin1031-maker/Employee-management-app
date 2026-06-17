@extends('layouts.app')
@section('title', 'Departments')
@section('page-title', 'Departments')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-100">
    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
        <h2 class="font-semibold text-gray-800">All Departments</h2>
        <a href="{{ route('admin.departments.create') }}"
           class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition flex items-center gap-2">
            <i class="fas fa-plus text-xs"></i> Add Department
        </a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-gray-700">
            <thead class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wider">
                <tr>
                    <th class="px-6 py-3 text-left">#</th>
                    <th class="px-6 py-3 text-left">Name</th>
                    <th class="px-6 py-3 text-left">Description</th>
                    <th class="px-6 py-3 text-center">Designations</th>
                    <th class="px-6 py-3 text-center">Employees</th>
                    <th class="px-6 py-3 text-center">Status</th>
                    <th class="px-6 py-3 text-center">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($departments as $dept)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-3 text-gray-400">{{ $departments->firstItem() + $loop->index }}</td>
                    <td class="px-6 py-3 font-medium text-gray-900">{{ $dept->name }}</td>
                    <td class="px-6 py-3 text-gray-500">{{ $dept->description ?? '—' }}</td>
                    <td class="px-6 py-3 text-center">
                        <span class="bg-blue-100 text-blue-700 text-xs font-medium px-2.5 py-1 rounded-full">{{ $dept->designations_count }}</span>
                    </td>
                    <td class="px-6 py-3 text-center">
                        <span class="bg-indigo-100 text-indigo-700 text-xs font-medium px-2.5 py-1 rounded-full">{{ $dept->employees_count }}</span>
                    </td>
                    <td class="px-6 py-3 text-center">
                        <form method="POST" action="{{ route('admin.departments.toggle-status', $dept) }}">
                            @csrf
                            <button type="submit"
                                    class="px-3 py-1 rounded-full text-xs font-medium transition
                                        {{ $dept->status === 'active'
                                            ? 'bg-green-100 text-green-700 hover:bg-green-200'
                                            : 'bg-red-100 text-red-700 hover:bg-red-200' }}">
                                {{ ucfirst($dept->status) }}
                            </button>
                        </form>
                    </td>
                    <td class="px-6 py-3 text-center">
                        <div class="flex items-center justify-center gap-2">
                            <a href="{{ route('admin.departments.edit', $dept) }}"
                               class="p-1.5 rounded-lg text-gray-500 hover:bg-indigo-50 hover:text-indigo-600 transition">
                                <i class="fas fa-pen-to-square text-sm"></i>
                            </a>
                            <form method="POST" action="{{ route('admin.departments.destroy', $dept) }}"
                                  onsubmit="return confirm('Delete this department?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="p-1.5 rounded-lg text-gray-500 hover:bg-red-50 hover:text-red-600 transition">
                                    <i class="fas fa-trash text-sm"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-6 py-10 text-center text-gray-400">No departments found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($departments->hasPages())
    <div class="px-6 py-4 border-t border-gray-100">{{ $departments->links() }}</div>
    @endif
</div>
@endsection
