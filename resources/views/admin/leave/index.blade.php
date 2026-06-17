@extends('layouts.app')
@section('title','Leave Requests')
@section('page-title','Leave Management')

@section('content')
{{-- Filters --}}
<div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 mb-5">
    <form method="GET" class="flex flex-wrap gap-3 items-end">
        <div class="min-w-[160px]">
            <label class="block text-xs font-medium text-gray-600 mb-1">Employee</label>
            <select name="employee_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">All employees</option>
                @foreach($employees as $emp)
                    <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>{{ $emp->name }} ({{ $emp->employee_id }})</option>
                @endforeach
            </select>
        </div>
        <div class="min-w-[120px]">
            <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
            <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">All</option>
                <option value="pending"  {{ request('status') === 'pending'  ? 'selected' : '' }}>Pending</option>
                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
            </select>
        </div>
        <div class="min-w-[120px]">
            <label class="block text-xs font-medium text-gray-600 mb-1">Type</label>
            <select name="type" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">All types</option>
                <option value="casual"  {{ request('type') === 'casual'  ? 'selected' : '' }}>Casual</option>
                <option value="sick"    {{ request('type') === 'sick'    ? 'selected' : '' }}>Sick</option>
                <option value="annual"  {{ request('type') === 'annual'  ? 'selected' : '' }}>Annual</option>
            </select>
        </div>
        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm px-4 py-2 rounded-lg transition">Filter</button>
        <a href="{{ route('admin.leave.index') }}" class="text-sm text-gray-500 py-2">Clear</a>
        <a href="{{ route('admin.leave.create') }}" class="ml-auto bg-green-600 hover:bg-green-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition flex items-center gap-2">
            <i class="fas fa-plus text-xs"></i> Create Leave
        </a>
    </form>
</div>

<div class="bg-white rounded-xl border border-gray-100 shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-gray-700">
            <thead class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wider">
                <tr>
                    <th class="px-5 py-3 text-left">Employee</th>
                    <th class="px-5 py-3 text-left">Type</th>
                    <th class="px-5 py-3 text-left">Period</th>
                    <th class="px-5 py-3 text-center">Days</th>
                    <th class="px-5 py-3 text-center">Status</th>
                    <th class="px-5 py-3 text-center">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($leaves as $leave)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3">
                        <p class="font-medium text-gray-900">{{ $leave->employee->name }}</p>
                        <p class="text-xs text-gray-400">{{ $leave->employee->employee_id }} · {{ $leave->employee->department->name ?? '—' }}</p>
                    </td>
                    <td class="px-5 py-3 capitalize">{{ $leave->type }}</td>
                    <td class="px-5 py-3">
                        {{ $leave->from_date->format('d M Y') }}
                        @if(!$leave->from_date->equalTo($leave->to_date)) – {{ $leave->to_date->format('d M Y') }} @endif
                    </td>
                    <td class="px-5 py-3 text-center">{{ $leave->days }}</td>
                    <td class="px-5 py-3 text-center">
                        <span class="px-2.5 py-1 rounded-full text-xs font-medium
                            {{ $leave->status === 'approved' ? 'bg-green-100 text-green-700' : ($leave->status === 'rejected' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700') }}">
                            {{ ucfirst($leave->status) }}
                        </span>
                    </td>
                    <td class="px-5 py-3">
                        <div class="flex items-center justify-center gap-1">
                            <a href="{{ route('admin.leave.show', $leave) }}" class="p-1.5 rounded-lg text-gray-500 hover:bg-blue-50 hover:text-blue-600 transition" title="View">
                                <i class="fas fa-eye text-sm"></i>
                            </a>
                            @if($leave->status === 'pending')
                            <form method="POST" action="{{ route('admin.leave.approve', $leave) }}">
                                @csrf
                                <button type="submit" class="p-1.5 rounded-lg text-gray-500 hover:bg-green-50 hover:text-green-600 transition" title="Approve">
                                    <i class="fas fa-check text-sm"></i>
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.leave.reject', $leave) }}">
                                @csrf
                                <button type="submit" class="p-1.5 rounded-lg text-gray-500 hover:bg-red-50 hover:text-red-600 transition" title="Reject">
                                    <i class="fas fa-times text-sm"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-6 py-10 text-center text-gray-400">No leave requests found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($leaves->hasPages())
    <div class="px-6 py-4 border-t border-gray-100">{{ $leaves->links() }}</div>
    @endif
</div>
@endsection
