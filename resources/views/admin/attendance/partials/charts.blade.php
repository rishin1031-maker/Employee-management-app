@php
    use App\Services\AttendanceTimeCalculator;
    $summary = $chartData['summary'];
    $chartParams = array_merge($filterParams, [
        'view'       => 'charts',
        'chart_view' => $chartView,
        'date'       => $date,
        'month'      => $month,
        'year'       => $year,
    ]);
    $targetPeriodLabel = match($chartView) {
        'weekly'  => 'week',
        'monthly' => 'month',
        'yearly'  => 'year',
        default   => 'day',
    };
@endphp

{{-- Chart period tabs --}}
<div class="flex flex-wrap gap-2 mb-5">
    @foreach(['daily' => 'Daily', 'weekly' => 'Weekly', 'monthly' => 'Monthly', 'yearly' => 'Yearly'] as $tab => $label)
    <a href="{{ route('admin.attendance.index', array_merge($filterParams, [
            'view'       => 'charts',
            'chart_view' => $tab,
            'date'       => $date,
            'month'      => $month,
            'year'       => $year,
        ])) }}"
       class="px-4 py-2 rounded-lg text-sm font-medium transition
              {{ $chartView === $tab ? 'bg-indigo-600 text-white shadow-sm' : 'bg-white text-gray-600 border border-gray-200 hover:border-indigo-400' }}">
        {{ $label }}
    </a>
    @endforeach
</div>

{{-- Filters --}}
<div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 mb-5">
    <form method="GET" class="flex flex-wrap gap-3 items-end">
        <input type="hidden" name="view" value="charts">
        <input type="hidden" name="chart_view" value="{{ $chartView }}">

        @if($chartView === 'yearly')
        <div class="min-w-[120px]">
            <label class="block text-xs font-medium text-gray-600 mb-1">Year</label>
            <input type="number" name="year" value="{{ $year }}" min="2020" max="{{ now()->year + 1 }}"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        @elseif($chartView === 'monthly')
        <div class="min-w-[160px]">
            <label class="block text-xs font-medium text-gray-600 mb-1">Month</label>
            <input type="month" name="month" value="{{ $month }}"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        @else
        <div class="min-w-[160px]">
            <label class="block text-xs font-medium text-gray-600 mb-1">
                {{ $chartView === 'weekly' ? 'Week containing' : 'Date' }}
            </label>
            <input type="date" name="date" value="{{ $date }}"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        @endif

        <div class="flex-1 min-w-[160px]">
            <label class="block text-xs font-medium text-gray-600 mb-1">Search employee</label>
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Name, email, or ID"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        <div class="min-w-[150px]">
            <label class="block text-xs font-medium text-gray-600 mb-1">Department</label>
            <select name="department_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">All Departments</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="min-w-[170px]">
            <label class="block text-xs font-medium text-gray-600 mb-1">Designation</label>
            <select name="designation_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">All Designations</option>
                @foreach($designations as $desig)
                    <option value="{{ $desig->id }}" {{ request('designation_id') == $desig->id ? 'selected' : '' }}>
                        {{ $desig->name }}{{ $desig->department ? ' — ' . $desig->department->name : '' }}
                    </option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
            <i class="fas fa-chart-bar mr-1"></i> Update
        </button>
        <a href="{{ route('admin.attendance.index', ['view' => 'charts', 'chart_view' => $chartView]) }}"
           class="text-sm text-gray-500 hover:text-gray-700 py-2">Clear</a>
    </form>
    <p class="text-xs text-gray-500 mt-3">
        <i class="fas fa-calendar-days text-indigo-500 mr-1"></i>
        Showing: <span class="font-medium text-gray-700">{{ $chartData['period_label'] }}</span>
        @if($chartView === 'weekly')
            <span class="text-gray-400">(Mon–Sun week · org-wide totals)</span>
        @elseif($chartView === 'yearly')
            <span class="text-gray-400">(monthly buckets · org-wide totals)</span>
        @else
            <span class="text-gray-400">(org-wide totals for filtered employees)</span>
        @endif
    </p>
</div>

{{-- Summary cards --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-5">
    <div class="ems-stat-card p-4 flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-green-50 text-green-600 flex items-center justify-center">
            <i class="fas fa-user-check"></i>
        </div>
        <div>
            <p class="text-2xl font-bold text-gray-900">{{ $summary['total_present'] }}</p>
            <p class="text-xs text-gray-500">Total present</p>
        </div>
    </div>
    <div class="ems-stat-card p-4 flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
            <i class="fas fa-briefcase"></i>
        </div>
        <div>
            <p class="text-2xl font-bold text-gray-900">{{ AttendanceTimeCalculator::formatHoursAndMinutes($summary['total_work_hours']) }}</p>
            <p class="text-xs text-gray-500">{{ $summary['total_work_minutes'] }} min · Net work</p>
        </div>
    </div>
    <div class="ems-stat-card p-4 flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-orange-50 text-orange-500 flex items-center justify-center">
            <i class="fas fa-mug-hot"></i>
        </div>
        <div>
            <p class="text-2xl font-bold text-gray-900">{{ AttendanceTimeCalculator::formatHoursAndMinutes($summary['total_break_hours']) }}</p>
            <p class="text-xs text-gray-500">{{ $summary['total_break_minutes'] }} min · Break time</p>
        </div>
    </div>
    <div class="ems-stat-card p-4 flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
            <i class="fas fa-calendar-check"></i>
        </div>
        <div>
            <p class="text-2xl font-bold text-gray-900">{{ $summary['days_worked'] }}</p>
            <p class="text-xs text-gray-500">Active {{ $chartView === 'yearly' ? 'months' : 'days' }} with data</p>
        </div>
    </div>
</div>

@if($chartData['has_data'])
<div class="grid grid-cols-1 {{ $chartView === 'daily' ? 'xl:grid-cols-2' : '' }} gap-5 mb-5">
    {{-- Status chart --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
        <h3 class="font-semibold text-gray-800 text-sm mb-4">
            Attendance status
            <span class="text-gray-400 font-normal">({{ ucfirst($chartView) }})</span>
        </h3>
        <div class="relative" style="height: {{ $chartView === 'daily' ? '260px' : '300px' }}">
            <canvas id="adminStatusChart"></canvas>
        </div>
    </div>

    @if($chartView === 'daily' && isset($chartData['status_breakdown']))
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
        <h3 class="font-semibold text-gray-800 text-sm mb-4">Today's breakdown</h3>
        <div class="relative" style="height: 260px">
            <canvas id="adminDailyPieChart"></canvas>
        </div>
    </div>
    @endif
</div>

<div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 mb-5">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
        <h3 class="font-semibold text-gray-800 text-sm">
            Work hours vs break time
            <span class="text-gray-400 font-normal">({{ ucfirst($chartView) }})</span>
        </h3>
        <div class="flex items-center gap-4 text-xs text-gray-500">
            <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-sm bg-indigo-500"></span> Net work</span>
            <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-sm bg-orange-400"></span> Break</span>
        </div>
    </div>
    <div class="relative" style="height: {{ in_array($chartView, ['monthly', 'yearly']) ? '340px' : '300px' }}">
        <canvas id="adminWorkHoursChart"></canvas>
    </div>
</div>
@else
<div class="bg-white rounded-xl border border-gray-100 shadow-sm p-12 text-center text-gray-400 mb-5">
    <i class="fas fa-chart-bar text-4xl mb-3 block opacity-40"></i>
    <p class="text-sm">No attendance data for this period.</p>
    <p class="text-xs mt-1">Try a different date range or clear filters.</p>
</div>
@endif

@if($chartData['has_data'])
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function () {
    const chartData = @json($chartData);
    const chartView = @json($chartView);

    function themeColors() {
        const dark = document.documentElement.classList.contains('dark');
        return { grid: dark ? '#374151' : '#e5e7eb', text: dark ? '#9ca3af' : '#6b7280' };
    }

    function formatDuration(hours) {
        const totalMinutes = Math.round(hours * 60);
        if (totalMinutes === 0) return '0m';
        const h = Math.floor(totalMinutes / 60);
        const m = totalMinutes % 60;
        if (h === 0) return m + 'm';
        if (m === 0) return h + 'h';
        return h + 'h ' + m + 'm';
    }

    const colors = themeColors();
    const charts = [];

    const statusCtx = document.getElementById('adminStatusChart');
    if (statusCtx) {
        const isDaily = chartView === 'daily';
        charts.push(new Chart(statusCtx, {
            type: isDaily ? 'bar' : 'bar',
            data: {
                labels: chartData.labels,
                datasets: [
                    { label: 'Present', data: chartData.present, backgroundColor: 'rgba(34, 197, 94, 0.85)', borderRadius: 4, stack: 'status' },
                    { label: 'Absent', data: chartData.absent, backgroundColor: 'rgba(239, 68, 68, 0.85)', borderRadius: 4, stack: 'status' },
                    { label: 'Half day', data: chartData.half_day, backgroundColor: 'rgba(234, 179, 8, 0.85)', borderRadius: 4, stack: 'status' },
                    { label: 'On leave', data: chartData.on_leave, backgroundColor: 'rgba(59, 130, 246, 0.85)', borderRadius: 4, stack: 'status' },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { color: colors.text, boxWidth: 12 } },
                },
                scales: isDaily ? {
                    x: { grid: { display: false }, ticks: { color: colors.text } },
                    y: { beginAtZero: true, stacked: true, grid: { color: colors.grid }, ticks: { color: colors.text, stepSize: 1 } },
                } : {
                    x: { stacked: true, grid: { color: colors.grid }, ticks: { color: colors.text, maxRotation: chartView === 'monthly' ? 0 : 45, autoSkip: chartView === 'monthly', maxTicksLimit: chartView === 'monthly' ? 15 : undefined } },
                    y: { beginAtZero: true, stacked: true, grid: { color: colors.grid }, ticks: { color: colors.text, stepSize: 1 } },
                },
            },
        }));
    }

    const pieCtx = document.getElementById('adminDailyPieChart');
    if (pieCtx && chartData.status_breakdown) {
        const b = chartData.status_breakdown;
        charts.push(new Chart(pieCtx, {
            type: 'doughnut',
            data: {
                labels: ['Present', 'Absent', 'Half day', 'On leave', 'Not marked'],
                datasets: [{
                    data: [b.present, b.absent, b.half_day, b.on_leave, b.not_marked],
                    backgroundColor: ['#22c55e', '#ef4444', '#eab308', '#3b82f6', '#9ca3af'],
                    borderWidth: 0,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { color: colors.text, boxWidth: 12 } },
                },
            },
        }));
    }

    const workCtx = document.getElementById('adminWorkHoursChart');
    if (workCtx) {
        charts.push(new Chart(workCtx, {
            type: 'bar',
            data: {
                labels: chartData.labels,
                datasets: [
                    { label: 'Net work hours', data: chartData.work_hours, backgroundColor: 'rgba(99, 102, 241, 0.85)', borderColor: 'rgb(99, 102, 241)', borderWidth: 1, borderRadius: 4, maxBarThickness: chartView === 'monthly' || chartView === 'yearly' ? 18 : 40 },
                    { label: 'Break time', data: chartData.break_hours, backgroundColor: 'rgba(251, 146, 60, 0.85)', borderColor: 'rgb(251, 146, 60)', borderWidth: 1, borderRadius: 4, maxBarThickness: chartView === 'monthly' || chartView === 'yearly' ? 18 : 40 },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: ctx => ctx.dataset.label + ': ' + formatDuration(ctx.parsed.y) + ' (' + Math.round(ctx.parsed.y * 60) + ' min)',
                        },
                    },
                },
                scales: {
                    x: { grid: { color: colors.grid }, ticks: { color: colors.text, maxRotation: chartView === 'monthly' ? 0 : 45, autoSkip: chartView === 'monthly', maxTicksLimit: chartView === 'monthly' ? 15 : undefined } },
                    y: { beginAtZero: true, grid: { color: colors.grid }, ticks: { color: colors.text, callback: v => formatDuration(v) }, title: { display: true, text: 'Hours & minutes', color: colors.text } },
                },
            },
        }));
    }

    document.querySelectorAll('.ems-theme-toggle, [data-theme-toggle]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            setTimeout(function () {
                const c = themeColors();
                charts.forEach(function (chart) {
                    if (chart.options.scales?.x) {
                        chart.options.scales.x.grid.color = c.grid;
                        chart.options.scales.x.ticks.color = c.text;
                    }
                    if (chart.options.scales?.y) {
                        chart.options.scales.y.grid.color = c.grid;
                        chart.options.scales.y.ticks.color = c.text;
                        if (chart.options.scales.y.title) chart.options.scales.y.title.color = c.text;
                    }
                    if (chart.options.plugins?.legend?.labels) {
                        chart.options.plugins.legend.labels.color = c.text;
                    }
                    chart.update();
                });
            }, 50);
        });
    });
})();
</script>
@endif
