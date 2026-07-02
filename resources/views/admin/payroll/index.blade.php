@extends('layouts.app')
@section('title','Payroll Report')
@section('page-title','Payroll Report')

@section('content')
@php
    use App\Services\AttendanceTimeCalculator;
@endphp

<div class="bg-indigo-50 border border-indigo-100 rounded-xl p-4 mb-5 text-sm text-indigo-800">
    <i class="fas fa-circle-info mr-1.5"></i>
    Payroll is calculated from <strong>net work hours</strong>.
    {{ AttendanceTimeCalculator::TARGET_MONTHLY_HOURS }} hours in a month = <strong>100% salary</strong>.
    Charts and totals below reflect <strong>earned</strong> (pro-rated) pay.
</div>

{{-- Filters --}}
<div class="flex flex-wrap items-end gap-3 mb-5">
    <div class="flex items-center gap-2">
        @foreach($years as $y)
            <a href="{{ route('admin.payroll.index', ['year' => $y, 'month' => $month]) }}"
               class="px-4 py-1.5 rounded-lg text-sm font-medium border transition
                      {{ $y == $year ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-600 border-gray-200 hover:border-indigo-400' }}">
                {{ $y }}
            </a>
        @endforeach
    </div>
    <form method="GET" class="flex items-end gap-2">
        <input type="hidden" name="year" value="{{ $year }}">
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Month detail</label>
            <input type="month" name="month" value="{{ $month }}"
                   class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm px-4 py-2 rounded-lg transition">View</button>
    </form>
</div>

{{-- Summary --}}
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-5">
    <div class="ems-stat-card p-5">
        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Earned payroll</p>
        <p class="text-3xl font-bold text-green-700 mt-1">₹ {{ number_format($totalEarnedNet, 2) }}</p>
        <p class="text-xs text-gray-400 mt-1">{{ \Carbon\Carbon::createFromFormat('Y-m', $month)->format('F Y') }} · work-hour based</p>
    </div>
    <div class="ems-stat-card p-5">
        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Employees paid</p>
        <p class="text-3xl font-bold text-indigo-600 mt-1">{{ $earnedPayroll->count() }}</p>
        <p class="text-xs text-gray-400 mt-1">Active staff with salary records</p>
    </div>
</div>

{{-- Charts --}}
<div class="grid grid-cols-1 gap-5 mb-5">
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
        <h3 class="font-semibold text-gray-800 mb-4 text-sm">Monthly earned payroll — {{ $year }}</h3>
        <canvas id="monthlyChart" height="80"></canvas>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
            <h3 class="font-semibold text-gray-800 mb-4 text-sm">Earned net salary — {{ \Carbon\Carbon::createFromFormat('Y-m', $month)->format('M Y') }}</h3>
            <canvas id="empChart" height="200"></canvas>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
            <h3 class="font-semibold text-gray-800 mb-4 text-sm">Department earned payroll — {{ \Carbon\Carbon::createFromFormat('Y-m', $month)->format('M Y') }}</h3>
            <canvas id="deptChart" height="200"></canvas>
        </div>
    </div>
</div>

{{-- Earned payroll table --}}
<div class="bg-white rounded-xl border border-gray-100 shadow-sm mb-5">
    <div class="px-6 py-4 border-b border-gray-100">
        <h3 class="font-semibold text-gray-800 text-sm">Work-hour based payroll — {{ \Carbon\Carbon::createFromFormat('Y-m', $month)->format('F Y') }}</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-gray-700">
            <thead class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wider">
                <tr>
                    <th class="px-5 py-3 text-left">Employee</th>
                    <th class="px-5 py-3 text-left">Department</th>
                    <th class="px-5 py-3 text-right">Work hours</th>
                    <th class="px-5 py-3 text-center">Progress</th>
                    <th class="px-5 py-3 text-right">Full net</th>
                    <th class="px-5 py-3 text-right">Earned net</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($earnedPayroll as $row)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3">
                        <p class="font-medium text-gray-900">{{ $row['name'] }}</p>
                        <p class="text-xs text-gray-400">{{ $row['emp_code'] }}</p>
                    </td>
                    <td class="px-5 py-3 text-gray-600">{{ $row['department'] }}</td>
                    <td class="px-5 py-3 text-right">
                        {{ AttendanceTimeCalculator::formatHoursAndMinutes($row['work_hours']) }}
                        <span class="text-xs text-gray-400 block">/ {{ $row['target_hours'] }}h</span>
                    </td>
                    <td class="px-5 py-3 text-center">
                        <span class="px-2.5 py-1 rounded-full text-xs font-medium {{ $row['is_full_month'] ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' }}">
                            {{ $row['progress_percent'] }}%
                        </span>
                    </td>
                    <td class="px-5 py-3 text-right text-gray-600">₹ {{ number_format($row['base_net'], 2) }}</td>
                    <td class="px-5 py-3 text-right font-semibold text-green-700">₹ {{ number_format($row['earned_net'], 2) }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-6 py-10 text-center text-gray-400">No payroll data for this month.</td></tr>
                @endforelse
            </tbody>
            @if($earnedPayroll->isNotEmpty())
            <tfoot class="bg-gray-50 font-semibold">
                <tr>
                    <td colspan="5" class="px-5 py-3 text-right text-gray-700">Total earned net</td>
                    <td class="px-5 py-3 text-right text-green-700">₹ {{ number_format($totalEarnedNet, 2) }}</td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>

{{-- Salary change log --}}
<div class="bg-white rounded-xl border border-gray-100 shadow-sm">
    <div class="px-6 py-4 border-b border-gray-100">
        <h3 class="font-semibold text-gray-800 text-sm">Salary change log — {{ $year }}</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-gray-700">
            <thead class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wider">
                <tr>
                    <th class="px-5 py-3 text-left">Employee</th>
                    <th class="px-5 py-3 text-left">Effective from</th>
                    <th class="px-5 py-3 text-right">Gross</th>
                    <th class="px-5 py-3 text-right">Deductions</th>
                    <th class="px-5 py-3 text-right">Net salary</th>
                    <th class="px-5 py-3 text-left">Note</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($monthlyTable as $logMonth => $records)
                    <tr class="bg-gray-50">
                        <td colspan="6" class="px-5 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                            {{ \Carbon\Carbon::createFromFormat('Y-m', $logMonth)->format('F Y') }}
                        </td>
                    </tr>
                    @foreach($records as $h)
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-2.5">
                            <p class="font-medium text-gray-900">{{ $h->employee->name }}</p>
                            <p class="text-xs text-gray-400">{{ $h->employee->employee_id }}</p>
                        </td>
                        <td class="px-5 py-2.5">{{ $h->effective_from->format('d M Y') }}</td>
                        <td class="px-5 py-2.5 text-right">₹ {{ number_format($h->gross_salary,2) }}</td>
                        <td class="px-5 py-2.5 text-right text-red-600">₹ {{ number_format($h->pf_deduction+$h->tax_deduction+$h->other_deduction,2) }}</td>
                        <td class="px-5 py-2.5 text-right font-semibold text-green-700">₹ {{ number_format($h->net_salary,2) }}</td>
                        <td class="px-5 py-2.5 text-gray-400 text-xs">{{ $h->note ?? '—' }}</td>
                    </tr>
                    @endforeach
                @empty
                <tr><td colspan="6" class="px-6 py-10 text-center text-gray-400">No salary changes for {{ $year }}.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script>
const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
const monthlyData = @json($monthlyTotals);
const empData     = @json($employeeSalaries);
const deptData    = @json($deptPayroll);
const palette     = ['#6366f1','#0d9488','#f59e0b','#ef4444','#8b5cf6','#10b981','#f97316','#3b82f6','#ec4899','#14b8a6'];

new Chart(document.getElementById('monthlyChart'), {
    type: 'bar',
    data: {
        labels: months,
        datasets: [{
            label: 'Earned payroll (₹)',
            data: monthlyData,
            backgroundColor: '#6366f1',
            borderRadius: 6,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: { ticks: { callback: v => '₹' + v.toLocaleString() }, grid: { color: '#f3f4f6' } },
            x: { grid: { display: false } }
        }
    }
});

new Chart(document.getElementById('empChart'), {
    type: 'bar',
    data: {
        labels: empData.map(e => e.name),
        datasets: [
            { label: 'Full net (₹)', data: empData.map(e => e.base_net), backgroundColor: '#c7d2fe', borderRadius: 4 },
            { label: 'Earned net (₹)', data: empData.map(e => e.net), backgroundColor: '#6366f1', borderRadius: 4 },
        ]
    },
    options: {
        indexAxis: 'y',
        responsive: true,
        plugins: { legend: { position: 'top' } },
        scales: {
            x: { ticks: { callback: v => '₹' + v.toLocaleString() }, grid: { color: '#f3f4f6' } },
            y: { grid: { display: false } }
        }
    }
});

new Chart(document.getElementById('deptChart'), {
    type: 'doughnut',
    data: {
        labels: deptData.map(d => d.name),
        datasets: [{
            data: deptData.map(d => d.total),
            backgroundColor: palette.slice(0, deptData.length),
            borderWidth: 2,
            borderColor: '#fff',
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'right' },
            tooltip: { callbacks: { label: ctx => ' ₹' + ctx.parsed.toLocaleString() } }
        }
    }
});
</script>
@endsection
