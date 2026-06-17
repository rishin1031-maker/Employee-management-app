@extends('layouts.app')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')

{{-- Stats Row 1 — Employee stats --}}
<div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-5">
    @php
        $cards = [
            ['label' => 'Total Employees',  'value' => $totalEmployees,    'icon' => 'fa-users',       'color' => 'indigo'],
            ['label' => 'Active',           'value' => $activeEmployees,   'icon' => 'fa-user-check',  'color' => 'green'],
            ['label' => 'Inactive',         'value' => $inactiveEmployees, 'icon' => 'fa-user-xmark',  'color' => 'red'],
            ['label' => 'Departments',      'value' => $totalDepartments,  'icon' => 'fa-building',    'color' => 'blue'],
            ['label' => 'Designations',     'value' => $totalDesignations, 'icon' => 'fa-briefcase',   'color' => 'purple'],
        ];
        $colors = [
            'indigo' => 'bg-indigo-50 text-indigo-600',
            'green'  => 'bg-green-50 text-green-600',
            'red'    => 'bg-red-50 text-red-600',
            'blue'   => 'bg-blue-50 text-blue-600',
            'purple' => 'bg-purple-50 text-purple-600',
        ];
    @endphp
    @foreach($cards as $card)
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 flex items-center gap-4">
        <div class="w-11 h-11 rounded-xl flex items-center justify-center {{ $colors[$card['color']] }} flex-shrink-0">
            <i class="fas {{ $card['icon'] }} text-lg"></i>
        </div>
        <div>
            <p class="text-2xl font-bold text-gray-900">{{ $card['value'] }}</p>
            <p class="text-xs text-gray-500 mt-0.5">{{ $card['label'] }}</p>
        </div>
    </div>
    @endforeach
</div>

{{-- Stats Row 2 — Today's attendance + pending leaves --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-5">
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 flex items-center gap-4">
        <div class="w-11 h-11 rounded-xl bg-green-50 text-green-600 flex items-center justify-center flex-shrink-0">
            <i class="fas fa-clock-rotate-left text-lg"></i>
        </div>
        <div>
            <p class="text-2xl font-bold text-gray-900">{{ $todayPresent }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Present today</p>
        </div>
    </div>
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 flex items-center gap-4">
        <div class="w-11 h-11 rounded-xl bg-red-50 text-red-600 flex items-center justify-center flex-shrink-0">
            <i class="fas fa-user-minus text-lg"></i>
        </div>
        <div>
            <p class="text-2xl font-bold text-gray-900">{{ $todayAbsent }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Absent today</p>
        </div>
    </div>
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 flex items-center gap-4">
        <div class="w-11 h-11 rounded-xl bg-gray-100 text-gray-500 flex items-center justify-center flex-shrink-0">
            <i class="fas fa-circle-question text-lg"></i>
        </div>
        <div>
            <p class="text-2xl font-bold text-gray-900">{{ $todayNotMarked }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Not marked</p>
        </div>
    </div>
    <a href="{{ route('admin.leave.index', ['status' => 'pending']) }}"
       class="bg-white rounded-xl border border-yellow-200 shadow-sm p-4 flex items-center gap-4 hover:border-yellow-400 transition">
        <div class="w-11 h-11 rounded-xl bg-yellow-50 text-yellow-600 flex items-center justify-center flex-shrink-0">
            <i class="fas fa-calendar-xmark text-lg"></i>
        </div>
        <div>
            <p class="text-2xl font-bold text-gray-900">{{ $pendingLeaves }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Pending leaves</p>
        </div>
    </a>
</div>

{{-- Tables --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

    {{-- Recent Employees --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
            <h2 class="font-semibold text-gray-800 text-sm">Recent Employees</h2>
            <a href="{{ route('admin.employees.index') }}" class="text-xs text-indigo-600 hover:underline">View all</a>
        </div>
        <div class="divide-y divide-gray-50">
            @forelse($recentEmployees as $emp)
            <div class="px-5 py-3 flex items-center gap-3">
                <img src="{{ $emp->image_url }}" class="w-8 h-8 rounded-full object-cover border border-gray-200">
                <div class="flex-1 min-w-0">
                    <p class="font-medium text-gray-900 text-sm truncate">{{ $emp->name }}</p>
                    <p class="text-xs text-gray-400">{{ $emp->employee_id }} · {{ $emp->department->name ?? '—' }}</p>
                </div>
                <span class="px-2.5 py-1 rounded-full text-xs font-medium flex-shrink-0
                    {{ $emp->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                    {{ ucfirst($emp->status) }}
                </span>
            </div>
            @empty
            <p class="px-5 py-6 text-center text-sm text-gray-400">No employees yet.</p>
            @endforelse
        </div>
    </div>

    {{-- Pending Leave Requests --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
            <h2 class="font-semibold text-gray-800 text-sm">Pending Leave Requests</h2>
            <a href="{{ route('admin.leave.index', ['status' => 'pending']) }}" class="text-xs text-indigo-600 hover:underline">View all</a>
        </div>
        <div class="divide-y divide-gray-50">
            @forelse($recentLeaves as $leave)
            <div class="px-5 py-3 flex items-center justify-between gap-3">
                <div class="flex-1 min-w-0">
                    <p class="font-medium text-gray-900 text-sm">{{ $leave->employee->name }}</p>
                    <p class="text-xs text-gray-400 capitalize">
                        {{ $leave->type }} · {{ $leave->from_date->format('d M') }}
                        @if(!$leave->from_date->equalTo($leave->to_date)) – {{ $leave->to_date->format('d M') }} @endif
                        · {{ $leave->days }} day(s)
                    </p>
                </div>
                <a href="{{ route('admin.leave.show', $leave) }}"
                   class="text-xs bg-indigo-50 hover:bg-indigo-100 text-indigo-600 px-3 py-1.5 rounded-lg transition flex-shrink-0">
                    Review
                </a>
            </div>
            @empty
            <p class="px-5 py-6 text-center text-sm text-gray-400">No pending leave requests.</p>
            @endforelse
        </div>
    </div>

</div>
@endsection