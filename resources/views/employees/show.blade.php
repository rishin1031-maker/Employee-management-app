@extends('layouts.app')
@section('title', $employee->name)
@section('page-title', 'Employee Details')

@section('content')
<div class="max-w-3xl">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">

        {{-- Header --}}
        <div class="bg-gradient-to-r from-indigo-600 to-indigo-400 px-8 py-8 flex items-center gap-6">
            <img src="{{ $employee->image_url }}" alt="{{ $employee->name }}"
                 class="w-24 h-24 rounded-full object-cover border-4 border-white shadow-lg flex-shrink-0">
            <div class="text-white">
                <h2 class="text-2xl font-bold">{{ $employee->name }}</h2>
                <p class="text-indigo-200 text-sm font-mono mt-0.5">{{ $employee->employee_id ?? '—' }}</p>
                <p class="text-indigo-100 text-sm mt-0.5">{{ $employee->designation->name ?? '—' }}</p>
                <span class="mt-2 inline-block px-3 py-0.5 rounded-full text-xs font-semibold
                    {{ $employee->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                    {{ ucfirst($employee->status) }}
                </span>
            </div>
        </div>

        {{-- Details Grid --}}
        <div class="p-8">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 text-sm">

                <div class="space-y-0.5">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Employee ID</p>
                    <p class="font-semibold text-indigo-600">{{ $employee->employee_id ?? '—' }}</p>
                </div>

                <div class="space-y-0.5">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Email</p>
                    <p class="font-medium text-gray-800 break-all">{{ $employee->email }}</p>
                </div>

                <div class="space-y-0.5">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Phone</p>
                    <p class="font-medium text-gray-800">{{ $employee->phone ?? '—' }}</p>
                </div>

                <div class="space-y-0.5">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Gender</p>
                    <p class="font-medium text-gray-800">{{ ucfirst($employee->gender) }}</p>
                </div>

                <div class="space-y-0.5">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Date of Birth</p>
                    <p class="font-medium text-gray-800">
                        {{ $employee->dob ? \Carbon\Carbon::parse($employee->dob)->format('d M Y') : '—' }}
                    </p>
                </div>

                <div class="space-y-0.5">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Member Since</p>
                    <p class="font-medium text-gray-800">{{ $employee->created_at->format('d M Y') }}</p>
                </div>

                <div class="space-y-0.5">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Department</p>
                    <p class="font-medium text-gray-800">{{ $employee->department->name ?? '—' }}</p>
                </div>

                <div class="space-y-0.5">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Designation</p>
                    <p class="font-medium text-gray-800">{{ $employee->designation->name ?? '—' }}</p>
                </div>

                <div class="space-y-0.5">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Status</p>
                    <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-semibold
                        {{ $employee->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                        {{ ucfirst($employee->status) }}
                    </span>
                </div>

            </div>
        </div>

        {{-- Action Buttons --}}
        <div class="px-8 pb-8 flex flex-wrap items-center gap-3 border-t border-gray-100 pt-6">
            <a href="{{ route('admin.employees.edit', $employee) }}"
               class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-5 py-2.5 rounded-lg transition flex items-center gap-2">
                <i class="fas fa-pen-to-square"></i> Edit
            </a>

            <button onclick="document.getElementById('reset-modal').classList.remove('hidden')"
                    class="bg-yellow-500 hover:bg-yellow-600 text-white text-sm font-medium px-5 py-2.5 rounded-lg transition flex items-center gap-2">
                <i class="fas fa-key"></i> Reset Password
            </button>

            <a href="{{ route('admin.employees.index') }}"
               class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium px-5 py-2.5 rounded-lg transition flex items-center gap-2">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>

            <form method="POST" action="{{ route('admin.employees.destroy', $employee) }}"
                  onsubmit="return confirm('Are you sure you want to delete {{ $employee->name }}?')"
                  class="ml-auto">
                @csrf @method('DELETE')
                <button type="submit"
                        class="bg-red-50 hover:bg-red-100 text-red-600 text-sm font-medium px-5 py-2.5 rounded-lg transition flex items-center gap-2 border border-red-200">
                    <i class="fas fa-trash"></i> Delete
                </button>
            </form>
        </div>
    </div>
</div>

{{-- Password Reset Modal --}}
<div id="reset-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl p-6 w-full max-w-sm shadow-2xl">
        <div class="flex items-center gap-3 mb-5">
            <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-key text-yellow-600"></i>
            </div>
            <div>
                <h3 class="font-semibold text-gray-900">Reset Password</h3>
                <p class="text-xs text-gray-500">{{ $employee->name }} · {{ $employee->employee_id }}</p>
            </div>
        </div>
        <form method="POST" action="{{ route('admin.employees.reset-password', $employee) }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                <input type="password" name="new_password" required
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                <input type="password" name="new_password_confirmation" required
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400">
            </div>
            <p class="text-xs text-gray-400">Employee will be forced to change this on next login.</p>
            <div class="flex gap-3 pt-1">
                <button type="submit"
                        class="flex-1 bg-yellow-500 hover:bg-yellow-600 text-white text-sm font-medium py-2.5 rounded-lg transition">
                    Reset Password
                </button>
                <button type="button"
                        onclick="document.getElementById('reset-modal').classList.add('hidden')"
                        class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium py-2.5 rounded-lg transition">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>
@endsection