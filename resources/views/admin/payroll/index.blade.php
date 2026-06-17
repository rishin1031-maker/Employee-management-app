@extends('layouts.app')
@section('title','Payroll Report')
@section('page-title','Payroll Report')

@section('content')
{{-- Year filter --}}
<div class="flex items-center gap-3 mb-5">
    @foreach($years as $y)
        <a href="{{ route('admin.payroll.index', ['year' => $y]) }}"
           class="px-4 py-1.5 rounded-lg text-sm font-medium border transition
                  {{ $y == $year ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-600 border-gray-200 hover:border-indigo-400' }}">
            {{ $y }}
        </a>
    @endforeach
</div>

{{-- 3 Charts --}}
<div class="grid grid-cols-1 gap-5 mb-5">
    {{-- Monthly total payroll --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
        <h3 class="font-semibold text-gray-800 mb-4 text-sm">Monthly total payroll — {{ $year }}</h3>
        <canvas id="monthlyChart" height="80"></canvas>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        {{-- Employee-wise --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
            <h3 class="font-semibold text-gray-800 mb-4 text-sm">Employee-wise net salary</h3>
            <canvas id="empChart" height="200"></canvas>
        </div>
        {{-- Department-wise --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
            <h3 class="font-semibold text-gray-800 mb-4 text-sm">Department-wise payroll cost</h3>
            <canvas id="deptChart" height="200"></canvas>
        </div>
    </div>
</div>

{{-- Monthly breakdown table --}}
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
                @forelse($monthlyTable as $month => $records)
                    <tr class="bg-gray-50">
                        <td colspan="6" class="px-5 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                            {{ \Carbon\Carbon::createFromFormat('Y-m', $month)->format('F Y') }}
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
                <tr><td colspan="6" class="px-6 py-10 text-center text-gray-400">No payroll data for {{ $year }}.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script>
const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
const monthlyData   = @json($monthlyTotals);
const empData       = @json($employeeSalaries);
const deptData      = @json($deptPayroll);

const palette = ['#6366f1','#0d9488','#f59e0b','#ef4444','#8b5cf6','#10b981','#f97316','#3b82f6','#ec4899','#14b8a6'];

// Monthly total bar chart
new Chart(document.getElementById('monthlyChart'), {
    type: 'bar',
    data: {
        labels: months,
        datasets: [{
            label: 'Net payroll (₹)',
            data: monthlyData,
            backgroundColor: '#6366f1',
            borderRadius: 6,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: {
                ticks: { callback: v => '₹' + v.toLocaleString() },
                grid: { color: '#f3f4f6' }
            },
            x: { grid: { display: false } }
        }
    }
});

// Employee-wise horizontal bar
new Chart(document.getElementById('empChart'), {
    type: 'bar',
    data: {
        labels: empData.map(e => e.name),
        datasets: [
            { label: 'Gross (₹)', data: empData.map(e => e.gross), backgroundColor: '#c7d2fe', borderRadius: 4 },
            { label: 'Net (₹)',   data: empData.map(e => e.net),   backgroundColor: '#6366f1', borderRadius: 4 },
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

// Department-wise doughnut
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
