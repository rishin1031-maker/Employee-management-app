@extends('employee.layouts.app')
@section('title','Attendance')
@section('page-title','My Attendance')

@section('content')
{{-- Month filter --}}
<div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 mb-5 flex items-center gap-4">
    <form method="GET" action="{{ route('employee.attendance.index') }}" class="flex items-center gap-3">
        <label class="text-sm font-medium text-gray-700">Month</label>
        <input type="month" name="month" value="{{ $month }}"
               class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        <button type="submit" class="bg-indigo-600 text-white text-sm px-4 py-2 rounded-lg hover:bg-indigo-700 transition">View</button>
        <a href="{{ route('employee.attendance.charts') }}"
           class="text-sm text-teal-600 hover:text-teal-700 font-medium">
            <i class="fas fa-chart-bar mr-1"></i> Work hours chart
        </a>
    </form>
</div>

{{-- Summary --}}
<div class="grid grid-cols-4 gap-4 mb-5">
    @php
        $summaryItems = [
            ['label'=>'Present',  'value'=>$summary['present'],  'color'=>'green'],
            ['label'=>'Absent',   'value'=>$summary['absent'],   'color'=>'red'],
            ['label'=>'Half day', 'value'=>$summary['half_day'], 'color'=>'yellow'],
            ['label'=>'On leave', 'value'=>$summary['on_leave'], 'color'=>'blue'],
        ];
    @endphp
    @foreach($summaryItems as $s)
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 text-center">
        <p class="text-2xl font-bold text-gray-900">{{ $s['value'] }}</p>
        <p class="text-xs text-gray-500 mt-0.5">{{ $s['label'] }}</p>
    </div>
    @endforeach
</div>

{{-- Table --}}
<div class="bg-white rounded-xl border border-gray-100 shadow-sm">
    <div class="px-6 py-4 border-b border-gray-100">
        <h3 class="font-semibold text-gray-800 text-sm">Attendance log</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-gray-700">
            <thead class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wider">
                <tr>
                    <th class="px-6 py-3 text-left">Date</th>
                    <th class="px-6 py-3 text-left">Check In</th>
                    <th class="px-6 py-3 text-left">Check Out</th>
                    <th class="px-6 py-3 text-left">Breaks</th>
                    <th class="px-6 py-3 text-left">Hours</th>
                    <th class="px-6 py-3 text-center">Status</th>
                    <th class="px-6 py-3 text-left">Marked by</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
            @forelse($attendances as $att)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-3 font-medium">{{ $att->date->format('D, d M Y') }}</td>
                    <td class="px-6 py-3">{{ $att->check_in ? $att->check_in->format('h:i A') : '—' }}</td>
                    <td class="px-6 py-3">{{ $att->check_out ? $att->check_out->format('h:i A') : '—' }}</td>
                    <td class="px-6 py-3">
                        @if($att->breaks->count())
                            <div class="space-y-1">
                                @foreach($att->breaks as $b)
                                <div class="text-xs text-gray-600 flex items-center gap-1.5">
                                    <i class="fas fa-mug-hot text-orange-400 text-xs"></i>
                                    {{ $b->break_out->format('h:i A') }}
                                    →
                                    {{ $b->break_in ? $b->break_in->format('h:i A') : '<span class="text-orange-500">Ongoing</span>' }}
                                    @if($b->break_in)
                                        <span class="text-gray-400">({{ $b->duration_label }})</span>
                                    @endif
                                </div>
                                @endforeach
                                <p class="text-xs text-red-500 font-medium">
                                    Total: {{ $att->total_break_minutes }}m deducted
                                </p>
                            </div>
                        @else
                            <span class="text-gray-400 text-xs">No breaks</span>
                        @endif
                    </td>
                    <td class="px-6 py-3">
                    @if($att->net_hours_worked)
                        <p class="font-medium text-green-700">{{ $att->net_hours_worked }}</p>
                        @if($att->hours_worked !== $att->net_hours_worked)
                            <p class="text-xs text-gray-400">Gross: {{ $att->hours_worked }}</p>
                        @endif
                        @if($att->is_eight_hours_complete)
                            <span class="text-xs text-green-500"><i class="fas fa-check"></i> Full day</span>
                        @else
                            <span class="text-xs text-red-400">Short {{ $att->remaining_minutes }}m</span>
                        @endif
                    @else
                        <span class="text-gray-400">—</span>
                    @endif
                </td>
                    <td class="px-6 py-3 text-center">
                        <span class="px-2.5 py-1 rounded-full text-xs font-medium
                            {{ $att->status === 'present' ? 'bg-green-100 text-green-700' : ($att->status === 'absent' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700') }}">
                            {{ ucfirst(str_replace('_',' ',$att->status)) }}
                        </span>
                    </td>
                    <td class="px-6 py-3 capitalize text-gray-500 text-sm">{{ $att->marked_by }}</td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-6 py-10 text-center text-gray-400">No attendance records for this month.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
