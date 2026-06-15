@extends('layouts.app')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
{{-- Stats Cards --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-5 mb-8">
    @php
        $cards = [
            ['label' => 'Total Employees',   'value' => $totalEmployees,    'icon' => 'fa-users',       'color' => 'indigo'],
            ['label' => 'Active',            'value' => $activeEmployees,   'icon' => 'fa-user-check',  'color' => 'green'],
            ['label' => 'Inactive',          'value' => $inactiveEmployees, 'icon' => 'fa-user-xmark',  'color' => 'red'],
            ['label' => 'Departments',       'value' => $totalDepartments,  'icon' => 'fa-building',    'color' => 'blue'],
            ['label' => 'Designations',      'value' => $totalDesignations, 'icon' => 'fa-briefcase',   'color' => 'purple'],
        ];
        $colors = [
            'indigo' => 'bg-indigo-50 text-indigo-600 border-indigo-100',
            'green'  => 'bg-green-50 text-green-600 border-green-100',
            'red'    => 'bg-red-50 text-red-600 border-red-100',
            'blue'   => 'bg-blue-50 text-blue-600 border-blue-100',
            'purple' => 'bg-purple-50 text-purple-600 border-purple-100',
        ];
    @endphp

    @foreach($cards as $card)
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl flex items-center justify-center {{ $colors[$card['color']] }} flex-shrink-0">
            <i class="fas {{ $card['icon'] }} text-lg"></i>
        </div>
        <div>
            <p class="text-2xl font-bold text-gray-900">{{ $card['value'] }}</p>
            <p class="text-xs text-gray-500 mt-0.5">{{ $card['label'] }}</p>
        </div>
    </div>
    @endforeach
</div>

{{-- Recent Employees --}}
<div class="bg-white rounded-xl shadow-sm border border-gray-100">
    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
        <h2 class="font-semibold text-gray-800">Recent Employees</h2>
        <a href="{{ route('employees.index') }}" class="text-sm text-indigo-600 hover:underline">View all</a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-gray-700">
            <thead class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wider">
                <tr>
                    <th class="px-6 py-3 text-left">Employee</th>
                    <th class="px-6 py-3 text-left">Department</th>
                    <th class="px-6 py-3 text-left">Designation</th>
                    <th class="px-6 py-3 text-left">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($recentEmployees as $emp)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-3 flex items-center gap-3">
                        <img src="{{ $emp->image_url }}" alt="{{ $emp->name }}"
                             class="w-8 h-8 rounded-full object-cover border border-gray-200">
                        <div>
                            <p class="font-medium text-gray-900">{{ $emp->name }}</p>
                            <p class="text-xs text-gray-400">{{ $emp->email }}</p>
                        </div>
                    </td>
                    {{-- Add this <td> after the employees_count <td> --}}
                    <td class="px-6 py-3">{{ $emp->department->name ?? '—' }}</td>
                    <td class="px-6 py-3">{{ $emp->designation->name ?? '—' }}</td>
                    <td class="px-6 py-3">
                        <span class="px-2.5 py-1 rounded-full text-xs font-medium
                            {{ $emp->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                            {{ ucfirst($emp->status) }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" class="px-6 py-6 text-center text-gray-400">No employees yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection