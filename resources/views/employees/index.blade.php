@extends('layouts.app')
@section('title', 'Employees')
@section('page-title', 'Employees')

@section('content')
{{-- Filters --}}
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-5">
    <form method="GET" action="{{ route('employees.index') }}" class="flex flex-wrap gap-3 items-end">
        <div class="flex-1 min-w-[180px]">
            <label class="block text-xs font-medium text-gray-600 mb-1">Search</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Name or email…"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        <div class="min-w-[160px]">
            <label class="block text-xs font-medium text-gray-600 mb-1">Department</label>
            <select name="department_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">All Departments</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="min-w-[160px]">
            <label class="block text-xs font-medium text-gray-600 mb-1">Designation</label>
            <select name="designation_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">All Designations</option>
                @foreach($designations as $desig)
                    <option value="{{ $desig->id }}" {{ request('designation_id') == $desig->id ? 'selected' : '' }}>{{ $desig->name }}</option>
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
        <a href="{{ route('employees.index') }}" class="text-sm text-gray-500 hover:text-gray-700 py-2">Clear</a>
    </form>
</div>

{{-- Table --}}
<div class="bg-white rounded-xl shadow-sm border border-gray-100">
    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
        <h2 class="font-semibold text-gray-800">All Employees <span class="text-gray-400 font-normal text-sm">({{ $employees->total() }})</span></h2>
        <a href="{{ route('employees.create') }}"
           class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition flex items-center gap-2">
            <i class="fas fa-plus text-xs"></i> Add Employee
        </a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-gray-700">
            <thead class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wider">
                <tr>
                    <th class="px-6 py-3 text-left">Employee</th>
                    <th class="px-6 py-3 text-left">Contact</th>
                    <th class="px-6 py-3 text-left">Department</th>
                    <th class="px-6 py-3 text-left">Designation</th>
                    <th class="px-6 py-3 text-center">Status</th>
                    <th class="px-6 py-3 text-center">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($employees as $emp)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-3">
                        <div class="flex items-center gap-3">
                            <img src="{{ $emp->image_url }}" alt="{{ $emp->name }}"
                                 class="w-9 h-9 rounded-full object-cover border border-gray-200">
                            <div>
                                <p class="font-medium text-gray-900">{{ $emp->name }}</p>
                                <p class="text-xs text-gray-400">{{ ucfirst($emp->gender) }} · {{ $emp->dob ? \Carbon\Carbon::parse($emp->dob)->format('d M Y') : '—' }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-3">
                        <p class="text-gray-700">{{ $emp->email }}</p>
                        <p class="text-xs text-gray-400">{{ $emp->phone ?? '—' }}</p>
                    </td>
                    <td class="px-6 py-3">{{ $emp->department->name ?? '—' }}</td>
                    <td class="px-6 py-3">{{ $emp->designation->name ?? '—' }}</td>
                    <td class="px-6 py-3 text-center">
                        <span class="px-2.5 py-1 rounded-full text-xs font-medium
                            {{ $emp->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                            {{ ucfirst($emp->status) }}
                        </span>
                    </td>
                    <td class="px-6 py-3">
                        <div class="flex items-center justify-center gap-1">
                            <a href="{{ route('employees.show', $emp) }}"
                               class="p-1.5 rounded-lg text-gray-500 hover:bg-blue-50 hover:text-blue-600 transition" title="View">
                                <i class="fas fa-eye text-sm"></i>
                            </a>
                            <a href="{{ route('employees.edit', $emp) }}"
                               class="p-1.5 rounded-lg text-gray-500 hover:bg-indigo-50 hover:text-indigo-600 transition" title="Edit">
                                <i class="fas fa-pen-to-square text-sm"></i>
                            </a>
                            <form method="POST" action="{{ route('employees.destroy', $emp) }}"
                                  onsubmit="return confirm('Delete {{ $emp->name }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="p-1.5 rounded-lg text-gray-500 hover:bg-red-50 hover:text-red-600 transition" title="Delete">
                                    <i class="fas fa-trash text-sm"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-6 py-12 text-center text-gray-400">No employees found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($employees->hasPages())
    <div class="px-6 py-4 border-t border-gray-100">{{ $employees->links() }}</div>
    @endif
</div>
@endsection