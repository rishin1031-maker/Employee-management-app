@extends('layouts.app')
@section('title','Attendance')
@section('page-title','Attendance Management')

@section('content')
{{-- Tabs --}}
<div class="flex gap-2 mb-5">
    <a href="{{ route('admin.attendance.index', ['view' => 'daily', 'date' => $date]) }}"
       class="px-4 py-2 rounded-lg text-sm font-medium transition
              {{ $activeView === 'daily' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-600 border border-gray-200 hover:border-indigo-400' }}">
        Daily View
    </a>
    <a href="{{ route('admin.attendance.index', ['view' => 'monthly', 'month' => $month]) }}"
       class="px-4 py-2 rounded-lg text-sm font-medium transition
              {{ $activeView === 'monthly' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-600 border border-gray-200 hover:border-indigo-400' }}">
        Monthly Report
    </a>
</div>

@if($activeView === 'daily')
{{-- Date picker --}}
<div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 mb-5">
    <form method="GET" class="flex items-center gap-3">
        <input type="hidden" name="view" value="daily">
        <label class="text-sm font-medium text-gray-700">Date</label>
        <input type="date" name="date" value="{{ $date }}"
               class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm px-4 py-2 rounded-lg transition">Load</button>
    </form>
</div>

<div class="bg-white rounded-xl border border-gray-100 shadow-sm">
    <div class="px-6 py-4 border-b border-gray-100">
        <h3 class="font-semibold text-gray-800 text-sm">
            Attendance for {{ \Carbon\Carbon::parse($date)->format('D, d M Y') }}
            <span class="text-gray-400 font-normal">({{ $employees->count() }} employees)</span>
        </h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-gray-700">
            <thead class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wider">
                <tr>
                    <th class="px-5 py-3 text-left">Employee</th>
                    <th class="px-5 py-3 text-center">Status</th>
                    <th class="px-5 py-3 text-left">Check In</th>
                    <th class="px-5 py-3 text-left">Check Out</th>
                    <th class="px-5 py-3 text-left">Mark Attendance</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($employees as $emp)
                @php $att = $emp->attendances->first(); @endphp
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3">
                        <p class="font-medium text-gray-900">{{ $emp->name }}</p>
                        <p class="text-xs text-gray-400">{{ $emp->employee_id }}</p>
                    </td>
                    <td class="px-5 py-3 text-center">
                        @if($att)
                        <span class="px-2.5 py-1 rounded-full text-xs font-medium
                            {{ $att->status === 'present' ? 'bg-green-100 text-green-700' : ($att->status === 'absent' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700') }}">
                            {{ ucfirst(str_replace('_',' ',$att->status)) }}
                        </span>
                        @else
                        <span class="text-gray-400 text-xs">Not marked</span>
                        @endif
                    </td>
                    <td class="px-5 py-3 text-xs">{{ $att?->check_in?->format('h:i A') ?? '—' }}</td>
                    <td class="px-5 py-3 text-xs">{{ $att?->check_out?->format('h:i A') ?? '—' }}</td>
                    <td class="px-5 py-3">
                        <form method="POST" action="{{ route('admin.attendance.mark') }}" class="flex items-center gap-2 flex-wrap">
                            @csrf
                            <input type="hidden" name="employee_id" value="{{ $emp->id }}">
                            <input type="hidden" name="date" value="{{ $date }}">
                            <select name="status" class="px-2 py-1.5 border border-gray-300 rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                @foreach(['present','absent','half_day','on_leave'] as $s)
                                    <option value="{{ $s }}" {{ $att?->status === $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                                @endforeach
                            </select>
                            <input type="time" name="check_in"  value="{{ $att?->check_in?->format('H:i') }}"
                                   class="px-2 py-1.5 border border-gray-300 rounded-lg text-xs w-24 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <input type="time" name="check_out" value="{{ $att?->check_out?->format('H:i') }}"
                                   class="px-2 py-1.5 border border-gray-300 rounded-lg text-xs w-24 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white text-xs px-3 py-1.5 rounded-lg transition">Save</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@else
{{-- Monthly Report --}}
<div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 mb-5">
    <form method="GET" class="flex items-center gap-3">
        <input type="hidden" name="view" value="monthly">
        <label class="text-sm font-medium text-gray-700">Month</label>
        <input type="month" name="month" value="{{ $month }}"
               class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm px-4 py-2 rounded-lg transition">View</button>
    </form>
</div>

<div class="bg-white rounded-xl border border-gray-100 shadow-sm">
    <div class="px-6 py-4 border-b border-gray-100">
        <h3 class="font-semibold text-gray-800 text-sm">
            Monthly Report — {{ \Carbon\Carbon::createFromFormat('Y-m', $month)->format('F Y') }}
        </h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-gray-700">
            <thead class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wider">
                <tr>
                    <th class="px-5 py-3 text-left">Employee</th>
                    <th class="px-5 py-3 text-center">Present</th>
                    <th class="px-5 py-3 text-center">Absent</th>
                    <th class="px-5 py-3 text-center">Half Day</th>
                    <th class="px-5 py-3 text-center">On Leave</th>
                    <th class="px-5 py-3 text-center">Not Marked</th>
                    <th class="px-5 py-3 text-center">Details</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($monthlyReport as $row)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3">
                        <p class="font-medium text-gray-900">{{ $row['employee']->name }}</p>
                        <p class="text-xs text-gray-400">{{ $row['employee']->employee_id }}</p>
                    </td>
                    <td class="px-5 py-3 text-center"><span class="bg-green-100 text-green-700 text-xs font-medium px-2.5 py-1 rounded-full">{{ $row['present'] }}</span></td>
                    <td class="px-5 py-3 text-center"><span class="bg-red-100 text-red-700 text-xs font-medium px-2.5 py-1 rounded-full">{{ $row['absent'] }}</span></td>
                    <td class="px-5 py-3 text-center"><span class="bg-yellow-100 text-yellow-700 text-xs font-medium px-2.5 py-1 rounded-full">{{ $row['half_day'] }}</span></td>
                    <td class="px-5 py-3 text-center"><span class="bg-blue-100 text-blue-700 text-xs font-medium px-2.5 py-1 rounded-full">{{ $row['on_leave'] }}</span></td>
                    <td class="px-5 py-3 text-center"><span class="bg-gray-100 text-gray-600 text-xs font-medium px-2.5 py-1 rounded-full">{{ $row['not_marked'] }}</span></td>
                    <td class="px-5 py-3">
                        <div class="space-y-2">
                            {{-- Existing mark attendance form --}}
                            <form method="POST" action="{{ route('admin.attendance.mark') }}" class="flex items-center gap-2 flex-wrap">
                                @csrf
                                <input type="hidden" name="employee_id" value="{{ $emp->id }}">
                                <input type="hidden" name="date" value="{{ $date }}">
                                <select name="status" class="px-2 py-1.5 border border-gray-300 rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    @foreach(['present','absent','half_day','on_leave'] as $s)
                                        <option value="{{ $s }}" {{ $att?->status === $s ? 'selected' : '' }}>
                                            {{ ucfirst(str_replace('_',' ',$s)) }}
                                        </option>
                                    @endforeach
                                </select>
                                <input type="time" name="check_in"  value="{{ $att?->check_in?->format('H:i') }}"
                                    class="px-2 py-1.5 border border-gray-300 rounded-lg text-xs w-24">
                                <input type="time" name="check_out" value="{{ $att?->check_out?->format('H:i') }}"
                                    class="px-2 py-1.5 border border-gray-300 rounded-lg text-xs w-24">
                                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white text-xs px-3 py-1.5 rounded-lg transition">Save</button>
                            </form>

                            {{-- Break management (only if attendance exists) --}}
                            @if($att)
                                {{-- Show existing breaks --}}
                                @if($att->breaks->count())
                                <div class="space-y-1 pl-1">
                                    @foreach($att->breaks as $b)
                                    <div class="flex items-center gap-2 text-xs text-gray-600">
                                        <i class="fas fa-mug-hot text-orange-400"></i>
                                        {{ $b->break_out->format('h:i A') }} →
                                        {{ $b->break_in ? $b->break_in->format('h:i A') : '<span class="text-orange-500 font-medium">Ongoing</span>' }}
                                        @if($b->break_in)
                                            <span class="text-gray-400">({{ $b->duration_label }})</span>
                                        @endif
                                        <form method="POST" action="{{ route('admin.attendance.break.delete', $b) }}">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-red-400 hover:text-red-600 ml-1" title="Remove break">
                                                <i class="fas fa-times text-xs"></i>
                                            </button>
                                        </form>
                                    </div>
                                    @endforeach
                                    <p class="text-xs text-red-400 font-medium pl-1">
                                        Total break: {{ $att->total_break_minutes }}m
                                        @if($att->net_hours_worked) | Net: {{ $att->net_hours_worked }} @endif
                                    </p>
                                </div>
                                @endif

                                {{-- Add break form --}}
                                <form method="POST" action="{{ route('admin.attendance.break.add') }}"
                                    class="flex items-center gap-2 flex-wrap border-t border-gray-100 pt-2">
                                    @csrf
                                    <input type="hidden" name="attendance_id" value="{{ $att->id }}">
                                    <span class="text-xs text-gray-500 font-medium">
                                        <i class="fas fa-mug-hot text-orange-400"></i> Add break:
                                    </span>
                                    <input type="time" name="break_out" placeholder="Out"
                                        class="px-2 py-1.5 border border-orange-200 rounded-lg text-xs w-24 focus:outline-none focus:ring-2 focus:ring-orange-400">
                                    <input type="time" name="break_in" placeholder="In"
                                        class="px-2 py-1.5 border border-orange-200 rounded-lg text-xs w-24 focus:outline-none focus:ring-2 focus:ring-orange-400">
                                    <button type="submit"
                                            class="bg-orange-400 hover:bg-orange-500 text-white text-xs px-3 py-1.5 rounded-lg transition">
                                        Add
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-6 py-10 text-center text-gray-400">No attendance records for this month.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endif
@endsection