@extends('employee.layouts.app')
@section('title','Dashboard')
@section('page-title','My Dashboard')

@section('content')
{{-- Welcome + check-in/out --}}
<div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 mb-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
    <div class="flex items-center gap-4">
        <img src="{{ $employee->image_url }}" class="w-14 h-14 rounded-full object-cover border-2 border-indigo-100">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">{{ $employee->name }}</h2>
            <p class="text-sm text-gray-500">{{ $employee->employee_id }} · {{ $employee->designation->name ?? '—' }}</p>
        </div>
    </div>
    <div class="flex gap-3">
        @if(!$employee->todayAttendance)
            <form method="POST" action="{{ route('employee.attendance.checkin') }}">
                @csrf
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white text-sm font-medium px-5 py-2 rounded-lg transition flex items-center gap-2">
                    <i class="fas fa-circle-check"></i> Check In
                </button>
            </form>
        @elseif(!$employee->todayAttendance->check_out)
            <div class="text-xs text-gray-500 text-right">
                <p>Checked in at {{ $employee->todayAttendance->check_in->format('h:i A') }}</p>
            </div>
            <form method="POST" action="{{ route('employee.attendance.checkout') }}">
                @csrf
                <button type="submit" class="bg-red-500 hover:bg-red-600 text-white text-sm font-medium px-5 py-2 rounded-lg transition flex items-center gap-2">
                    <i class="fas fa-circle-xmark"></i> Check Out
                </button>
            </form>
        @else
            <div class="text-xs text-gray-500 text-right">
                <p>In: {{ $employee->todayAttendance->check_in->format('h:i A') }} · Out: {{ $employee->todayAttendance->check_out->format('h:i A') }}</p>
                <p class="text-green-600 font-medium">{{ $employee->todayAttendance->hours_worked }} worked today</p>
            </div>
        @endif
    </div>
</div>

{{-- Stats --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-5">
    @php
        $balance = $employee->leaveBalance;
        $stats = [
            ['label' => 'Casual leave left',  'value' => $balance ? $balance->casual_total - $balance->casual_used : 12, 'color' => 'blue'],
            ['label' => 'Sick leave left',    'value' => $balance ? $balance->sick_total - $balance->sick_used : 10,     'color' => 'purple'],
            ['label' => 'Annual leave left',  'value' => $balance ? $balance->annual_total - $balance->annual_used : 15, 'color' => 'teal'],
            ['label' => 'Pending requests',   'value' => $leaveStats['pending'],                                          'color' => 'yellow'],
        ];
        $colors = [
            'blue'   => 'bg-blue-50 text-blue-600',
            'purple' => 'bg-purple-50 text-purple-600',
            'teal'   => 'bg-teal-50 text-teal-600',
            'yellow' => 'bg-yellow-50 text-yellow-600',
        ];
    @endphp
    @foreach($stats as $s)
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 flex items-center gap-4">
        <div class="w-11 h-11 rounded-xl {{ $colors[$s['color']] }} flex items-center justify-center text-xl font-bold flex-shrink-0">
            {{ $s['value'] }}
        </div>
        <p class="text-xs text-gray-500 leading-tight">{{ $s['label'] }}</p>
    </div>
    @endforeach
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
    {{-- Recent Leaves --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
            <h3 class="font-semibold text-gray-800 text-sm">Recent leave requests</h3>
            <a href="{{ route('employee.leave.index') }}" class="text-xs text-indigo-600 hover:underline">View all</a>
        </div>
        <div class="divide-y divide-gray-50">
            @forelse($recentLeaves as $leave)
            <div class="px-5 py-3 flex items-center justify-between text-sm">
                <div>
                    <p class="font-medium text-gray-800 capitalize">{{ $leave->type }} leave</p>
                    <p class="text-xs text-gray-400">{{ $leave->from_date->format('d M') }} – {{ $leave->to_date->format('d M Y') }} · {{ $leave->days }} day(s)</p>
                </div>
                <span class="px-2.5 py-1 rounded-full text-xs font-medium
                    {{ $leave->status === 'approved' ? 'bg-green-100 text-green-700' : ($leave->status === 'rejected' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700') }}">
                    {{ ucfirst($leave->status) }}
                </span>
            </div>
            @empty
            <p class="px-5 py-6 text-center text-sm text-gray-400">No leave requests yet.</p>
            @endforelse
        </div>
    </div>

    {{-- Recent Attendance --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
            <h3 class="font-semibold text-gray-800 text-sm">Recent attendance</h3>
            <a href="{{ route('employee.attendance.index') }}" class="text-xs text-indigo-600 hover:underline">View all</a>
        </div>
        <div class="divide-y divide-gray-50">
            @forelse($recentAttendance as $att)
            <div class="px-5 py-3 flex items-center justify-between text-sm">
                <div>
                    <p class="font-medium text-gray-800">{{ $att->date->format('D, d M Y') }}</p>
                    <p class="text-xs text-gray-400">
                        {{ $att->check_in ? $att->check_in->format('h:i A') : '—' }}
                        {{ $att->check_out ? '→ ' . $att->check_out->format('h:i A') : '' }}
                        {{ $att->hours_worked ? '(' . $att->hours_worked . ')' : '' }}
                    </p>
                </div>
                <span class="px-2.5 py-1 rounded-full text-xs font-medium
                    {{ $att->status === 'present' ? 'bg-green-100 text-green-700' : ($att->status === 'absent' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700') }}">
                    {{ ucfirst(str_replace('_',' ',$att->status)) }}
                </span>
            </div>
            @empty
            <p class="px-5 py-6 text-center text-sm text-gray-400">No attendance records.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
