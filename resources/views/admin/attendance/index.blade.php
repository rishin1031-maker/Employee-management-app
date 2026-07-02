@extends('layouts.app')
@section('title','Attendance')
@section('page-title','Attendance Management')

@section('content')
@php
    $filterParams = request()->only(['search', 'department_id', 'designation_id', 'employee_id']);
@endphp

{{-- Attendance statistics --}}
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-5">
    <div class="ems-stat-card p-5">
        <div class="flex items-start justify-between gap-3">
            <div>
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Present Today</p>
                <p class="text-3xl font-bold text-green-600 mt-1">{{ $statistics['today']['present'] }}</p>
                <p class="text-xs text-gray-400 mt-1">{{ $statistics['today']['label'] }}</p>
            </div>
            <div class="w-11 h-11 rounded-xl bg-green-50 text-green-600 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-user-check text-lg"></i>
            </div>
        </div>
        <div class="mt-4 pt-4 border-t border-gray-100 grid grid-cols-2 gap-2 text-xs">
            <div class="flex justify-between gap-2">
                <span class="text-gray-500">Active staff</span>
                <span class="font-semibold text-gray-800">{{ $statistics['active_employees'] }}</span>
            </div>
            <div class="flex justify-between gap-2">
                <span class="text-gray-500">Absent</span>
                <span class="font-semibold text-red-600">{{ $statistics['today']['absent'] }}</span>
            </div>
            <div class="flex justify-between gap-2">
                <span class="text-gray-500">On leave</span>
                <span class="font-semibold text-blue-600">{{ $statistics['today']['on_leave'] }}</span>
            </div>
            <div class="flex justify-between gap-2">
                <span class="text-gray-500">Not marked</span>
                <span class="font-semibold text-gray-600">{{ $statistics['today']['not_marked'] }}</span>
            </div>
        </div>
    </div>

    <div class="ems-stat-card p-5">
        <div class="flex items-start justify-between gap-3">
            <div>
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Present This Week</p>
                <p class="text-3xl font-bold text-indigo-600 mt-1">{{ $statistics['week']['present'] }}</p>
                <p class="text-xs text-gray-400 mt-1">{{ $statistics['week']['label'] }}</p>
            </div>
            <div class="w-11 h-11 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-calendar-week text-lg"></i>
            </div>
        </div>
        <p class="mt-4 pt-4 border-t border-gray-100 text-xs text-gray-500">
            Total present records from Monday through today{{ request()->hasAny(['search', 'department_id', 'designation_id', 'employee_id']) ? ' (filtered)' : '' }}.
        </p>
    </div>

    <div class="ems-stat-card p-5">
        <div class="flex items-start justify-between gap-3">
            <div>
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Present This Month</p>
                <p class="text-3xl font-bold text-purple-600 mt-1">{{ $statistics['month']['present'] }}</p>
                <p class="text-xs text-gray-400 mt-1">{{ $statistics['month']['label'] }}</p>
            </div>
            <div class="w-11 h-11 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-calendar-days text-lg"></i>
            </div>
        </div>
        <p class="mt-4 pt-4 border-t border-gray-100 text-xs text-gray-500">
            Total present records from the 1st of the month through today{{ request()->hasAny(['search', 'department_id', 'designation_id', 'employee_id']) ? ' (filtered)' : '' }}.
        </p>
    </div>

    <div class="ems-stat-card p-5">
        <div class="flex items-start justify-between gap-3">
            <div>
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Present This Year</p>
                <p class="text-3xl font-bold text-amber-600 mt-1">{{ $statistics['year']['present'] }}</p>
                <p class="text-xs text-gray-400 mt-1">{{ $statistics['year']['label'] }}</p>
            </div>
            <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-calendar text-lg"></i>
            </div>
        </div>
        <p class="mt-4 pt-4 border-t border-gray-100 text-xs text-gray-500">
            Total present records from January through today{{ request()->hasAny(['search', 'department_id', 'designation_id', 'employee_id']) ? ' (filtered)' : '' }}.
        </p>
    </div>
</div>

{{-- Tabs --}}
<div class="ems-tabs mb-5">
    <a href="{{ route('admin.attendance.index', array_merge($filterParams, ['view' => 'daily', 'date' => $date])) }}"
       class="ems-tab {{ $activeView === 'daily' ? 'is-active' : '' }}">
        <i class="fas fa-calendar-day mr-1.5 text-xs"></i> Daily View
    </a>
    <a href="{{ route('admin.attendance.index', array_merge($filterParams, ['view' => 'monthly', 'month' => $month])) }}"
       class="ems-tab {{ $activeView === 'monthly' ? 'is-active' : '' }}">
        <i class="fas fa-calendar mr-1.5 text-xs"></i> Monthly Report
    </a>
    <a href="{{ route('admin.attendance.index', array_merge($filterParams, ['view' => 'charts', 'chart_view' => $chartView ?? 'weekly', 'date' => $date, 'month' => $month, 'year' => $year ?? now()->year])) }}"
       class="ems-tab {{ $activeView === 'charts' ? 'is-active' : '' }}">
        <i class="fas fa-chart-line mr-1.5 text-xs"></i> Analytics
    </a>
</div>

@if($activeView === 'charts')

@include('admin.attendance.partials.charts')

@elseif($activeView === 'daily')

{{-- ============================================================ --}}
{{-- DAILY VIEW --}}
{{-- ============================================================ --}}

<div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 mb-5">
    <form method="GET" class="flex flex-wrap gap-3 items-end">
        <input type="hidden" name="view" value="daily">
        <div class="min-w-[140px]">
            <label class="block text-xs font-medium text-gray-600 mb-1">Date</label>
            <input type="date" name="date" value="{{ $date }}"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        <div class="flex-1 min-w-[180px]">
            <label class="block text-xs font-medium text-gray-600 mb-1">Search employee</label>
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Name, email, or ID (e.g. EMP001)"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        <div class="min-w-[160px]">
            <label class="block text-xs font-medium text-gray-600 mb-1">Department</label>
            <select name="department_id"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">All Departments</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                        {{ $dept->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="min-w-[180px]">
            <label class="block text-xs font-medium text-gray-600 mb-1">Designation</label>
            <select name="designation_id"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">All Designations</option>
                @foreach($designations as $desig)
                    <option value="{{ $desig->id }}" {{ request('designation_id') == $desig->id ? 'selected' : '' }}>
                        {{ $desig->name }}{{ $desig->department ? ' — ' . $desig->department->name : '' }}
                    </option>
                @endforeach
            </select>
        </div>
        <button type="submit"
                class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
            <i class="fas fa-search mr-1"></i> Apply
        </button>
        <a href="{{ route('admin.attendance.index', ['view' => 'daily', 'date' => $date]) }}"
           class="text-sm text-gray-500 hover:text-gray-700 py-2">Clear</a>
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
                    <th class="px-5 py-3 text-center">Status & Hours</th>
                    <th class="px-5 py-3 text-left">Check In</th>
                    <th class="px-5 py-3 text-left">Check Out</th>
                    <th class="px-5 py-3 text-left">Mark / Breaks</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($employees as $emp)
                @php $att = $emp->attendances->first(); @endphp
                <tr class="hover:bg-gray-50">

                    {{-- Employee --}}
                    <td class="px-5 py-3">
                        <p class="font-medium text-gray-900">{{ $emp->name }}</p>
                        <p class="text-xs text-gray-400">{{ $emp->employee_id }}</p>
                        @if($emp->department || $emp->designation)
                        <p class="text-xs text-gray-500 mt-0.5">
                            {{ $emp->department?->name ?? '—' }}
                            @if($emp->designation)
                                · {{ $emp->designation->name }}
                            @endif
                        </p>
                        @endif
                    </td>

                    {{-- Status & hours --}}
                    <td class="px-5 py-3 text-center">
                        @if($att)
                            <span class="px-2.5 py-1 rounded-full text-xs font-medium
                                {{ $att->status === 'present'  ? 'bg-green-100 text-green-700'  :
                                  ($att->status === 'absent'   ? 'bg-red-100 text-red-700'     :
                                   'bg-yellow-100 text-yellow-700') }}">
                                {{ ucfirst(str_replace('_', ' ', $att->status)) }}
                            </span>

                            @if($att->check_in)
                            <div class="mt-1.5 space-y-0.5 text-xs text-left">
                                @if($att->check_out)
                                    <p class="text-gray-500">
                                        Net: <span class="font-medium text-green-700">{{ $att->net_hours_worked }}</span>
                                    </p>
                                    <p class="text-gray-500">
                                        Break: <span class="font-medium text-orange-500">{{ $att->total_break_minutes }}m</span>
                                    </p>
                                    @if($att->is_eight_hours_complete)
                                        <p class="text-green-600 font-medium">
                                            <i class="fas fa-circle-check text-xs"></i> Full day
                                        </p>
                                    @else
                                        <p class="text-red-500 font-medium">
                                            <i class="fas fa-triangle-exclamation text-xs"></i>
                                            Short by {{ $att->remaining_minutes }}m
                                        </p>
                                    @endif

                                    {{-- Early checkout reason --}}
                                    @if($att->note && str_starts_with($att->note, 'Early checkout:'))
                                        <div class="mt-1 bg-yellow-50 border border-yellow-200 rounded px-2 py-1 max-w-[180px]">
                                            <p class="text-yellow-700 font-medium text-xs">
                                                <i class="fas fa-triangle-exclamation text-xs"></i> Early checkout reason:
                                            </p>
                                            <p class="text-yellow-600 text-xs mt-0.5">
                                                {{ str_replace('Early checkout: ', '', $att->note) }}
                                            </p>
                                        </div>
                                    @endif

                                @else
                                    <p class="text-blue-500 font-medium">
                                        <i class="fas fa-circle animate-pulse text-xs"></i> Currently working
                                    </p>
                                @endif
                            </div>
                            @endif
                        @else
                            <span class="text-gray-400 text-xs">Not marked</span>
                        @endif
                    </td>

                    {{-- Check in --}}
                    <td class="px-5 py-3 text-xs">
                        {{ $att?->check_in?->format('h:i A') ?? '—' }}
                    </td>

                    {{-- Check out --}}
                    <td class="px-5 py-3 text-xs">
                        {{ $att?->check_out?->format('h:i A') ?? '—' }}
                    </td>

                    {{-- Mark attendance + break management --}}
                    <td class="px-5 py-3">
                        <div class="space-y-3">

                            {{-- Mark attendance form --}}
                            <form method="POST" action="{{ route('admin.attendance.mark') }}"
                                  class="flex items-center gap-2 flex-wrap">
                                @csrf
                                <input type="hidden" name="employee_id" value="{{ $emp->id }}">
                                <input type="hidden" name="date" value="{{ $date }}">
                                <select name="status"
                                        class="px-2 py-1.5 border border-gray-300 rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    @foreach(['present','absent','half_day','on_leave'] as $s)
                                        <option value="{{ $s }}" {{ $att?->status === $s ? 'selected' : '' }}>
                                            {{ ucfirst(str_replace('_', ' ', $s)) }}
                                        </option>
                                    @endforeach
                                </select>
                                <input type="time" name="check_in" value="{{ $att?->check_in?->format('H:i') }}"
                                       class="px-2 py-1.5 border border-gray-300 rounded-lg text-xs w-24 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <input type="time" name="check_out" value="{{ $att?->check_out?->format('H:i') }}"
                                       class="px-2 py-1.5 border border-gray-300 rounded-lg text-xs w-24 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <button type="submit"
                                        class="bg-indigo-600 hover:bg-indigo-700 text-white text-xs px-3 py-1.5 rounded-lg transition">
                                    Save
                                </button>
                            </form>

                            {{-- Break management (only if attendance exists) --}}
                            @if($att)

                                {{-- Existing breaks --}}
                                @if($att->breaks->count())
                                <div class="space-y-1 pl-1">
                                    @foreach($att->breaks as $b)
                                    <div class="flex items-center gap-2 text-xs text-gray-600">
                                        <i class="fas fa-mug-hot text-orange-400"></i>
                                        {{ $b->break_out->format('h:i A') }} →
                                        @if($b->break_in)
                                            {{ $b->break_in->format('h:i A') }}
                                            <span class="text-gray-400">({{ $b->duration_label }})</span>
                                        @else
                                            <span class="text-orange-500 font-medium">Ongoing</span>
                                        @endif
                                        <form method="POST"
                                              action="{{ route('admin.attendance.break.delete', $b) }}">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                                    class="text-red-400 hover:text-red-600 ml-1"
                                                    title="Remove break">
                                                <i class="fas fa-times text-xs"></i>
                                            </button>
                                        </form>
                                    </div>
                                    @endforeach
                                    <p class="text-xs text-orange-500 font-medium pl-1">
                                        Total break: {{ $att->total_break_minutes }}m
                                        @if($att->net_hours_worked)
                                            | Net: {{ $att->net_hours_worked }}
                                        @endif
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
                                    <input type="time" name="break_out"
                                           class="px-2 py-1.5 border border-orange-200 rounded-lg text-xs w-24 focus:outline-none focus:ring-2 focus:ring-orange-400">
                                    <input type="time" name="break_in"
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
                <tr>
                    <td colspan="5" class="px-6 py-12 text-center text-gray-400">
                        <i class="fas fa-user-slash text-2xl mb-2 block"></i>
                        No employees match your filters.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@else

{{-- ============================================================ --}}
{{-- MONTHLY REPORT --}}
{{-- ============================================================ --}}

<div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 mb-5">
    <form method="GET" class="flex flex-wrap gap-3 items-end">
        <input type="hidden" name="view" value="monthly">
        <div class="min-w-[140px]">
            <label class="block text-xs font-medium text-gray-600 mb-1">Month</label>
            <input type="month" name="month" value="{{ $month }}"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        <div class="flex-1 min-w-[180px]">
            <label class="block text-xs font-medium text-gray-600 mb-1">Search employee</label>
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Name, email, or ID (e.g. EMP001)"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        <div class="min-w-[160px]">
            <label class="block text-xs font-medium text-gray-600 mb-1">Department</label>
            <select name="department_id"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">All Departments</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                        {{ $dept->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="min-w-[180px]">
            <label class="block text-xs font-medium text-gray-600 mb-1">Designation</label>
            <select name="designation_id"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">All Designations</option>
                @foreach($designations as $desig)
                    <option value="{{ $desig->id }}" {{ request('designation_id') == $desig->id ? 'selected' : '' }}>
                        {{ $desig->name }}{{ $desig->department ? ' — ' . $desig->department->name : '' }}
                    </option>
                @endforeach
            </select>
        </div>
        <button type="submit"
                class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
            <i class="fas fa-search mr-1"></i> Apply
        </button>
        <a href="{{ route('admin.attendance.index', ['view' => 'monthly', 'month' => $month]) }}"
           class="text-sm text-gray-500 hover:text-gray-700 py-2">Clear</a>
    </form>
</div>

<div class="bg-white rounded-xl border border-gray-100 shadow-sm">
    <div class="px-6 py-4 border-b border-gray-100">
        <h3 class="font-semibold text-gray-800 text-sm">
            Monthly Report —
            {{ \Carbon\Carbon::createFromFormat('Y-m', $month)->format('F Y') }}
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
                    <th class="px-5 py-3 text-center">Early Checkout</th>
                    <th class="px-5 py-3 text-center">Details</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($monthlyReport as $row)
                <tr class="hover:bg-gray-50">

                    {{-- Employee --}}
                    <td class="px-5 py-3">
                        <p class="font-medium text-gray-900">{{ $row['employee']->name }}</p>
                        <p class="text-xs text-gray-400">{{ $row['employee']->employee_id }}</p>
                        @if($row['employee']->department || $row['employee']->designation)
                        <p class="text-xs text-gray-500 mt-0.5">
                            {{ $row['employee']->department?->name ?? '—' }}
                            @if($row['employee']->designation)
                                · {{ $row['employee']->designation->name }}
                            @endif
                        </p>
                        @endif
                    </td>

                    {{-- Present --}}
                    <td class="px-5 py-3 text-center">
                        <span class="bg-green-100 text-green-700 text-xs font-medium px-2.5 py-1 rounded-full">
                            {{ $row['present'] }}
                        </span>
                    </td>

                    {{-- Absent --}}
                    <td class="px-5 py-3 text-center">
                        <span class="bg-red-100 text-red-700 text-xs font-medium px-2.5 py-1 rounded-full">
                            {{ $row['absent'] }}
                        </span>
                    </td>

                    {{-- Half day --}}
                    <td class="px-5 py-3 text-center">
                        <span class="bg-yellow-100 text-yellow-700 text-xs font-medium px-2.5 py-1 rounded-full">
                            {{ $row['half_day'] }}
                        </span>
                    </td>

                    {{-- On leave --}}
                    <td class="px-5 py-3 text-center">
                        <span class="bg-blue-100 text-blue-700 text-xs font-medium px-2.5 py-1 rounded-full">
                            {{ $row['on_leave'] }}
                        </span>
                    </td>

                    {{-- Not marked --}}
                    <td class="px-5 py-3 text-center">
                        <span class="bg-gray-100 text-gray-600 text-xs font-medium px-2.5 py-1 rounded-full">
                            {{ $row['not_marked'] }}
                        </span>
                    </td>

                    {{-- Early checkouts --}}
                    <td class="px-5 py-3 text-center">
                        <span class="{{ ($row['early_checkouts'] ?? 0) > 0
                                        ? 'bg-yellow-100 text-yellow-700'
                                        : 'bg-gray-100 text-gray-400' }}
                                      text-xs font-medium px-2.5 py-1 rounded-full">
                            {{ $row['early_checkouts'] ?? 0 }}
                        </span>
                    </td>

                    {{-- Details link --}}
                    <td class="px-5 py-3 text-center">
                        <a href="{{ route('admin.attendance.index', array_merge($filterParams, [
                                'view'        => 'daily',
                                'date'        => now()->format('Y-m') === $month
                                                    ? today()->toDateString()
                                                    : \Carbon\Carbon::createFromFormat('Y-m', $month)->startOfMonth()->toDateString(),
                                'employee_id' => $row['employee']->id,
                           ])) }}"
                           class="text-xs bg-indigo-50 hover:bg-indigo-100 text-indigo-600 px-3 py-1.5 rounded-lg transition">
                            View days
                        </a>
                    </td>

                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-6 py-12 text-center text-gray-400">
                        <i class="fas fa-user-slash text-2xl mb-2 block"></i>
                        No employees match your filters for this month.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endif
@endsection