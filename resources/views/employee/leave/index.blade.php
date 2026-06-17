@extends('employee.layouts.app')
@section('title','My Leaves')
@section('page-title','My Leave Requests')

@section('content')
{{-- Balance cards --}}
<div class="grid grid-cols-3 gap-4 mb-5">
    @php
        $types = [
            ['key'=>'casual', 'label'=>'Casual','color'=>'blue'],
            ['key'=>'sick',   'label'=>'Sick',  'color'=>'purple'],
            ['key'=>'annual', 'label'=>'Annual','color'=>'teal'],
        ];
        $colors = ['blue'=>'bg-blue-50 text-blue-800 border-blue-100','purple'=>'bg-purple-50 text-purple-800 border-purple-100','teal'=>'bg-teal-50 text-teal-800 border-teal-100'];
    @endphp
    @foreach($types as $t)
    <div class="bg-white rounded-xl border {{ $colors[$t['color']] }} shadow-sm p-4 text-center">
        <p class="text-2xl font-bold">{{ $balance->{$t['key'].'_total'} - $balance->{$t['key'].'_used'} }}</p>
        <p class="text-xs font-medium mt-0.5">{{ $t['label'] }} remaining</p>
        <p class="text-xs opacity-60 mt-0.5">{{ $balance->{$t['key'].'_used'} }} used of {{ $balance->{$t['key'].'_total'} }}</p>
    </div>
    @endforeach
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100">
    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
        <h2 class="font-semibold text-gray-800">All Requests</h2>
        <a href="{{ route('employee.leave.create') }}"
           class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition flex items-center gap-2">
            <i class="fas fa-plus text-xs"></i> Apply Leave
        </a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-gray-700">
            <thead class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wider">
                <tr>
                    <th class="px-6 py-3 text-left">Type</th>
                    <th class="px-6 py-3 text-left">Period</th>
                    <th class="px-6 py-3 text-center">Days</th>
                    <th class="px-6 py-3 text-left">Reason</th>
                    <th class="px-6 py-3 text-center">Status</th>
                    <th class="px-6 py-3 text-left">Admin note</th>
                    <th class="px-6 py-3 text-center">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($leaves as $leave)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-3 capitalize font-medium">{{ $leave->type }}</td>
                    <td class="px-6 py-3">
                        {{ $leave->from_date->format('d M Y') }}
                        @if(!$leave->from_date->equalTo($leave->to_date))
                            – {{ $leave->to_date->format('d M Y') }}
                        @endif
                    </td>
                    <td class="px-6 py-3 text-center">{{ $leave->days }}</td>
                    <td class="px-6 py-3 max-w-[180px] truncate">{{ $leave->reason }}</td>
                    <td class="px-6 py-3 text-center">
                        <span class="px-2.5 py-1 rounded-full text-xs font-medium
                            {{ $leave->status === 'approved' ? 'bg-green-100 text-green-700' : ($leave->status === 'rejected' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700') }}">
                            {{ ucfirst($leave->status) }}
                        </span>
                    </td>
                    <td class="px-6 py-3 text-gray-500 text-xs">{{ $leave->admin_note ?? '—' }}</td>
                    <td class="px-6 py-3 text-center">
                        @if($leave->status === 'pending')
                        <form method="POST" action="{{ route('employee.leave.cancel', $leave) }}"
                            onsubmit="return confirm('Cancel this leave request?')">
                            @csrf @method('DELETE')
                            <button type="submit"
                                    class="text-xs bg-red-50 hover:bg-red-100 text-red-600 border border-red-200 px-3 py-1.5 rounded-lg transition">
                                Cancel
                            </button>
                        </form>
                        @else
                        <span class="text-gray-300 text-xs">—</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-6 py-10 text-center text-gray-400">No leave requests yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($leaves->hasPages())
    <div class="px-6 py-4 border-t border-gray-100">{{ $leaves->links() }}</div>
    @endif
</div>
@endsection
