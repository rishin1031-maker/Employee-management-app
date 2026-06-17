@extends('layouts.app')
@section('title','Leave Details')
@section('page-title','Leave Request Details')

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 space-y-5">
        <div class="flex items-center gap-4 pb-4 border-b border-gray-100">
            <img src="{{ $leave->employee->image_url }}" class="w-12 h-12 rounded-full object-cover border">
            <div>
                <p class="font-semibold text-gray-900">{{ $leave->employee->name }}</p>
                <p class="text-sm text-gray-500">{{ $leave->employee->employee_id }} · {{ $leave->employee->designation->name ?? '—' }}</p>
            </div>
            <span class="ml-auto px-3 py-1 rounded-full text-xs font-medium
                {{ $leave->status === 'approved' ? 'bg-green-100 text-green-700' : ($leave->status === 'rejected' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700') }}">
                {{ ucfirst($leave->status) }}
            </span>
        </div>

        <div class="grid grid-cols-2 gap-4 text-sm">
            <div><p class="text-gray-500 text-xs mb-0.5">Leave type</p><p class="font-medium capitalize">{{ $leave->type }}</p></div>
            <div><p class="text-gray-500 text-xs mb-0.5">Days</p><p class="font-medium">{{ $leave->days }} day(s)</p></div>
            <div><p class="text-gray-500 text-xs mb-0.5">From</p><p class="font-medium">{{ $leave->from_date->format('d M Y') }}</p></div>
            <div><p class="text-gray-500 text-xs mb-0.5">To</p><p class="font-medium">{{ $leave->to_date->format('d M Y') }}</p></div>
            <div class="col-span-2"><p class="text-gray-500 text-xs mb-0.5">Reason</p><p class="font-medium">{{ $leave->reason }}</p></div>
            @if($leave->admin_note)
            <div class="col-span-2"><p class="text-gray-500 text-xs mb-0.5">Admin note</p><p class="font-medium">{{ $leave->admin_note }}</p></div>
            @endif
            @if($leave->actioned_by)
            <div><p class="text-gray-500 text-xs mb-0.5">Actioned by</p><p class="font-medium">{{ $leave->actionedBy->name ?? '—' }}</p></div>
            <div><p class="text-gray-500 text-xs mb-0.5">Actioned at</p><p class="font-medium">{{ $leave->actioned_at?->format('d M Y h:i A') }}</p></div>
            @endif
        </div>

        @if($leave->status === 'pending')
        <div class="border-t border-gray-100 pt-4 space-y-3">
            <p class="text-sm font-medium text-gray-700">Add a note (optional)</p>
            <textarea id="admin_note" rows="2" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Reason for approval / rejection…"></textarea>
            <div class="flex gap-3">
                <form method="POST" action="{{ route('admin.leave.approve', $leave) }}" class="flex-1">
                    @csrf
                    <input type="hidden" name="admin_note" id="note_approve">
                    <button type="submit" onclick="document.getElementById('note_approve').value=document.getElementById('admin_note').value"
                            class="w-full bg-green-600 hover:bg-green-700 text-white text-sm font-medium py-2.5 rounded-lg transition">
                        Approve
                    </button>
                </form>
                <form method="POST" action="{{ route('admin.leave.reject', $leave) }}" class="flex-1">
                    @csrf
                    <input type="hidden" name="admin_note" id="note_reject">
                    <button type="submit" onclick="document.getElementById('note_reject').value=document.getElementById('admin_note').value"
                            class="w-full bg-red-600 hover:bg-red-700 text-white text-sm font-medium py-2.5 rounded-lg transition">
                        Reject
                    </button>
                </form>
            </div>
        </div>
        @endif

        <a href="{{ route('admin.leave.index') }}" class="inline-block text-sm text-gray-500 hover:text-gray-700 mt-2">← Back to list</a>
    </div>
</div>
@endsection
