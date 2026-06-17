@extends('layouts.app')
@section('title', 'Create Leave')
@section('page-title', 'Create Leave Request')

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <form method="POST" action="{{ route('admin.leave.store') }}" class="space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Employee <span class="text-red-500">*</span></label>
                <select name="employee_id"
                        class="w-full px-4 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500
                               @error('employee_id') border-red-400 @else border-gray-300 @enderror">
                    <option value="">— Select Employee —</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}" {{ old('employee_id') == $emp->id ? 'selected' : '' }}>
                            {{ $emp->name }} ({{ $emp->employee_id }})
                        </option>
                    @endforeach
                </select>
                @error('employee_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Leave Type <span class="text-red-500">*</span></label>
                <select name="type"
                        class="w-full px-4 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500
                               @error('type') border-red-400 @else border-gray-300 @enderror">
                    <option value="">— Select Type —</option>
                    <option value="casual"  {{ old('type') === 'casual'  ? 'selected' : '' }}>Casual</option>
                    <option value="sick"    {{ old('type') === 'sick'    ? 'selected' : '' }}>Sick</option>
                    <option value="annual"  {{ old('type') === 'annual'  ? 'selected' : '' }}>Annual</option>
                </select>
                @error('type')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">From Date <span class="text-red-500">*</span></label>
                    <input type="date" name="from_date" value="{{ old('from_date') }}"
                           class="w-full px-4 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500
                                  @error('from_date') border-red-400 @else border-gray-300 @enderror">
                    @error('from_date')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">To Date <span class="text-red-500">*</span></label>
                    <input type="date" name="to_date" value="{{ old('to_date') }}"
                           class="w-full px-4 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500
                                  @error('to_date') border-red-400 @else border-gray-300 @enderror">
                    @error('to_date')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status <span class="text-red-500">*</span></label>
                <select name="status"
                        class="w-full px-4 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500
                               @error('status') border-red-400 @else border-gray-300 @enderror">
                    <option value="pending"  {{ old('status','pending') === 'pending'  ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ old('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ old('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
                @error('status')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Reason <span class="text-red-500">*</span></label>
                <textarea name="reason" rows="3"
                          class="w-full px-4 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500
                                 @error('reason') border-red-400 @else border-gray-300 @enderror"
                          placeholder="Reason for leave…">{{ old('reason') }}</textarea>
                @error('reason')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-6 py-2.5 rounded-lg transition">
                    Create Leave Request
                </button>
                <a href="{{ route('admin.leave.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection