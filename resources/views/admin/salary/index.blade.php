@extends('layouts.app')
@section('title','Salary Management')
@section('page-title','Salary Management')

@section('content')
@php
    use App\Services\AttendanceTimeCalculator;
@endphp

<div class="bg-indigo-50 border border-indigo-100 rounded-xl p-4 mb-5 text-sm text-indigo-800">
    <i class="fas fa-circle-info mr-1.5"></i>
    <strong>Work-hour salary rule:</strong>
    Full monthly salary is paid when an employee completes
    <strong>{{ AttendanceTimeCalculator::TARGET_MONTHLY_HOURS }} net work hours</strong>.
    Below that, pay is calculated proportionally (earned = full salary × hours worked ÷ {{ AttendanceTimeCalculator::TARGET_MONTHLY_HOURS }}).
</div>

<div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 mb-5">
    <form method="GET" class="flex flex-wrap gap-3 items-end">
        <div class="min-w-[160px]">
            <label class="block text-xs font-medium text-gray-600 mb-1">Payroll month</label>
            <input type="month" name="month" value="{{ $month }}"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        <div class="flex-1 min-w-[180px]">
            <label class="block text-xs font-medium text-gray-600 mb-1">Search employee</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Name or Employee ID…"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm px-4 py-2 rounded-lg transition">Apply</button>
        <a href="{{ route('admin.payroll.index', ['month' => $month]) }}" class="bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition flex items-center gap-2">
            <i class="fas fa-chart-bar text-xs"></i> Payroll report
        </a>
    </form>
</div>

<div class="bg-white rounded-xl border border-gray-100 shadow-sm">
    <div class="px-6 py-4 border-b border-gray-100">
        <h3 class="font-semibold text-gray-800 text-sm">
            Earned salary — {{ \Carbon\Carbon::createFromFormat('Y-m', $month)->format('F Y') }}
        </h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-gray-700">
            <thead class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wider">
                <tr>
                    <th class="px-5 py-3 text-left">Employee</th>
                    <th class="px-5 py-3 text-right">Work hours</th>
                    <th class="px-5 py-3 text-center">Progress</th>
                    <th class="px-5 py-3 text-right">Full net</th>
                    <th class="px-5 py-3 text-right">Earned net</th>
                    <th class="px-5 py-3 text-left">Effective from</th>
                    <th class="px-5 py-3 text-center">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($employees as $emp)
                @php $earned = $earnedByEmployee[$emp->id] ?? null; @endphp
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3">
                        <p class="font-medium text-gray-900">{{ $emp->name }}</p>
                        <p class="text-xs text-gray-400">{{ $emp->employee_id }} · {{ $emp->department->name ?? '—' }}</p>
                    </td>
                    <td class="px-5 py-3 text-right">
                        @if($earned)
                            <span class="font-medium {{ $earned['is_full_month'] ? 'text-green-700' : 'text-gray-900' }}">
                                {{ AttendanceTimeCalculator::formatHoursAndMinutes($earned['work_hours']) }}
                            </span>
                            <span class="text-xs text-gray-400 block">/ {{ $earned['target_hours'] }}h target</span>
                        @else
                            —
                        @endif
                    </td>
                    <td class="px-5 py-3">
                        @if($earned)
                        <div class="min-w-[100px]">
                            <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full rounded-full {{ $earned['is_full_month'] ? 'bg-green-500' : 'bg-indigo-500' }}"
                                     style="width: {{ $earned['progress_percent'] }}%"></div>
                            </div>
                            <p class="text-xs text-gray-500 mt-1 text-center">{{ $earned['progress_percent'] }}%</p>
                        </div>
                        @else
                            —
                        @endif
                    </td>
                    <td class="px-5 py-3 text-right text-gray-600">
                        {{ $emp->salary ? '₹ '.number_format($emp->salary->net_salary, 2) : '—' }}
                    </td>
                    <td class="px-5 py-3 text-right font-semibold text-green-700">
                        @if($earned)
                            ₹ {{ number_format($earned['earned_net'], 2) }}
                            @if($earned['is_full_month'])
                                <span class="text-xs text-green-600 block">Full salary</span>
                            @else
                                <span class="text-xs text-amber-600 block">{{ AttendanceTimeCalculator::formatHoursAndMinutes($earned['remaining_hours']) }} short</span>
                            @endif
                        @else
                            —
                        @endif
                    </td>
                    <td class="px-5 py-3">{{ $emp->salary ? $emp->salary->effective_from->format('d M Y') : '—' }}</td>
                    <td class="px-5 py-3 text-center">
                        <div class="flex items-center justify-center gap-2">
                            <a href="{{ route('admin.salary.create', $emp) }}" class="p-1.5 rounded-lg text-gray-500 hover:bg-indigo-50 hover:text-indigo-600" title="Manage salary">
                                <i class="fas fa-pen-to-square text-sm"></i>
                            </a>
                            <a href="{{ route('admin.salary.history', $emp) }}" class="p-1.5 rounded-lg text-gray-500 hover:bg-purple-50 hover:text-purple-600" title="History">
                                <i class="fas fa-clock-rotate-left text-sm"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-6 py-10 text-center text-gray-400">No employees found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($employees->hasPages())
    <div class="px-6 py-4 border-t border-gray-100">{{ $employees->links() }}</div>
    @endif
</div>
@endsection
