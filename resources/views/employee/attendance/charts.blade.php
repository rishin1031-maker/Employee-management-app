@extends('employee.layouts.app')
@section('title', 'Work Hours')
@section('page-title', 'Work Hours Chart')

@section('content')
@php
    use App\Services\AttendanceTimeCalculator;
@endphp

{{-- View tabs --}}
<div class="flex flex-wrap gap-2 mb-5">
    @foreach(['daily' => 'Daily', 'weekly' => 'Weekly', 'monthly' => 'Monthly'] as $tab => $label)
    <a href="{{ route('employee.attendance.charts', array_merge(request()->only(['date', 'month']), ['view' => $tab])) }}"
       class="px-4 py-2 rounded-lg text-sm font-medium transition
              {{ $view === $tab ? 'bg-teal-600 text-white' : 'bg-white text-gray-600 border border-gray-200 hover:border-teal-400' }}">
        {{ $label }}
    </a>
    @endforeach
</div>

{{-- Period filter --}}
<div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 mb-5">
    <form method="GET" action="{{ route('employee.attendance.charts') }}" class="flex flex-wrap gap-3 items-end">
        <input type="hidden" name="view" value="{{ $view }}">

        @if($view === 'monthly')
        <div class="min-w-[160px]">
            <label class="block text-xs font-medium text-gray-600 mb-1">Month</label>
            <input type="month" name="month" value="{{ $month }}"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500">
        </div>
        @else
        <div class="min-w-[160px]">
            <label class="block text-xs font-medium text-gray-600 mb-1">
                {{ $view === 'weekly' ? 'Week containing' : 'Date' }}
            </label>
            <input type="date" name="date" value="{{ $date }}"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500">
        </div>
        @endif

        <button type="submit"
                class="bg-teal-600 hover:bg-teal-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
            <i class="fas fa-chart-bar mr-1"></i> Update
        </button>
        <a href="{{ route('employee.attendance.index') }}"
           class="text-sm text-gray-500 hover:text-gray-700 py-2">
            <i class="fas fa-list mr-1"></i> Attendance log
        </a>
    </form>
    <p class="text-xs text-gray-500 mt-3">
        <i class="fas fa-calendar-days text-teal-500 mr-1"></i>
        Showing: <span class="font-medium text-gray-700">{{ $chartData['period_label'] }}</span>
        @if($view === 'weekly' && isset($chartData['week_start']))
            <span class="text-gray-400">(Mon–Sun week)</span>
        @endif
    </p>
</div>

{{-- Summary cards --}}
@php
    $summary = $chartData['summary'];
    $targetPeriodLabel = match($view) {
        'weekly'  => 'week',
        'monthly' => 'month',
        default   => 'day',
    };
@endphp
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-5">
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center">
                <i class="fas fa-briefcase"></i>
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-900">{{ AttendanceTimeCalculator::formatHoursAndMinutes($summary['total_work_hours']) }}</p>
                <p class="text-xs text-gray-500">{{ $summary['total_work_minutes'] }} min · Total work</p>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-orange-50 text-orange-500 flex items-center justify-center">
                <i class="fas fa-mug-hot"></i>
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-900">{{ AttendanceTimeCalculator::formatHoursAndMinutes($summary['total_break_hours']) }}</p>
                <p class="text-xs text-gray-500">{{ $summary['total_break_minutes'] }} min · Total break</p>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                <i class="fas fa-chart-line"></i>
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-900">{{ AttendanceTimeCalculator::formatHoursAndMinutes($summary['avg_work_hours']) }}</p>
                <p class="text-xs text-gray-500">{{ $summary['avg_work_minutes'] }} min · Avg / active day</p>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
        <div class="flex items-center gap-3">
            @if($view === 'daily')
            <div class="w-10 h-10 rounded-xl {{ $summary['target_complete'] ? 'bg-green-50 text-green-600' : 'bg-amber-50 text-amber-600' }} flex items-center justify-center">
                <i class="fas {{ $summary['target_complete'] ? 'fa-circle-check' : 'fa-hourglass-half' }}"></i>
            </div>
            <div>
                @if($summary['target_complete'])
                    <p class="text-2xl font-bold text-green-700">Done</p>
                    <p class="text-xs text-gray-500">Daily target met (8h)</p>
                @else
                    <p class="text-2xl font-bold text-gray-900">{{ AttendanceTimeCalculator::formatHoursAndMinutes($summary['remaining_hours']) }}</p>
                    <p class="text-xs text-gray-500">{{ $summary['remaining_minutes'] }} min left · 8h target</p>
                @endif
            </div>
            @else
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                <i class="fas fa-calendar-check"></i>
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-900">{{ $summary['days_worked'] }}</p>
                <p class="text-xs text-gray-500">Days with work logged</p>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Target progress --}}
@if($view === 'daily' || $chartData['has_data'])
<div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 mb-5">
    <div class="flex flex-wrap items-center justify-between gap-2 mb-2 text-sm">
        <span class="font-medium text-gray-800">
            {{ ucfirst($view) }} work target
        </span>
        <span class="text-gray-500">
            {{ AttendanceTimeCalculator::formatHoursAndMinutes($summary['total_work_hours']) }}
            ({{ $summary['total_work_minutes'] }} min)
            of
            {{ AttendanceTimeCalculator::formatHoursAndMinutes($summary['target_hours']) }}
            ({{ $summary['target_minutes'] }} min)/{{ $targetPeriodLabel }}
            · <span class="font-semibold text-teal-600">{{ $summary['progress_percent'] }}%</span>
            @if($view === 'daily')
                @if($summary['target_complete'])
                    · <span class="font-semibold text-green-600">Target complete</span>
                @else
                    · <span class="font-semibold text-amber-600">
                        {{ AttendanceTimeCalculator::formatHoursAndMinutes($summary['remaining_hours']) }}
                        ({{ $summary['remaining_minutes'] }} min) left
                    </span>
                @endif
            @endif
        </span>
    </div>
    <div class="h-2.5 bg-gray-100 rounded-full overflow-hidden">
        <div class="h-full {{ $summary['target_complete'] && $view === 'daily' ? 'bg-green-500' : 'bg-teal-500' }} rounded-full transition-all duration-500"
             style="width: {{ $summary['progress_percent'] }}%"></div>
    </div>
    @if($view === 'daily' && !$summary['target_complete'])
    <p class="text-xs text-gray-500 mt-2">
        <i class="fas fa-hourglass-half text-amber-500 mr-1"></i>
        Keep going — <span class="font-medium text-gray-700">{{ AttendanceTimeCalculator::formatHoursAndMinutes($summary['remaining_hours']) }}</span>
        ({{ $summary['remaining_minutes'] }} minutes) remaining to reach your 8-hour daily target.
    </p>
    @elseif($view === 'daily' && $summary['target_complete'])
    <p class="text-xs text-green-600 mt-2">
        <i class="fas fa-circle-check mr-1"></i>
        You've met your 8-hour daily work target.
    </p>
    @endif
</div>
@endif

{{-- Chart --}}
<div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 mb-5">
    <div class="flex items-center justify-between mb-4">
        <h3 class="font-semibold text-gray-800 text-sm">
            Work hours vs break time
            <span class="text-gray-400 font-normal">({{ ucfirst($view) }})</span>
        </h3>
        <div class="flex items-center gap-4 text-xs text-gray-500">
            <span class="flex items-center gap-1.5">
                <span class="w-3 h-3 rounded-sm bg-teal-500"></span> Net work
            </span>
            <span class="flex items-center gap-1.5">
                <span class="w-3 h-3 rounded-sm bg-orange-400"></span> Break
            </span>
            <span class="text-gray-400">
                Target: {{ AttendanceTimeCalculator::formatHoursAndMinutes($summary['target_hours']) }}
                ({{ $summary['target_minutes'] }} min)/{{ $targetPeriodLabel }}
            </span>
        </div>
    </div>

    @if(!$chartData['has_data'])
    <div class="py-16 text-center text-gray-400">
        <i class="fas fa-chart-bar text-4xl mb-3 block opacity-40"></i>
        <p class="text-sm">No attendance data for this period.</p>
        <p class="text-xs mt-1">Check in and check out to see your work hours here.</p>
    </div>
    @else
    <div class="relative" style="height: {{ $view === 'monthly' ? '320px' : '280px' }}">
        <canvas id="workHoursChart"></canvas>
    </div>
    @endif
</div>

{{-- Daily breakdown table --}}
@if($chartData['has_data'])
<div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden {{ $view === 'daily' ? 'mb-5' : '' }}">
    <div class="px-6 py-4 border-b border-gray-100">
        <h3 class="font-semibold text-gray-800 text-sm">Daily breakdown</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-gray-700">
            <thead class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wider">
                <tr>
                    <th class="px-6 py-3 text-left">Period</th>
                    <th class="px-6 py-3 text-right">Work time</th>
                    <th class="px-6 py-3 text-right">Break time</th>
                    <th class="px-6 py-3 text-right">Gross time</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($chartData['labels'] as $i => $label)
                @php
                    $work  = $chartData['work_hours'][$i];
                    $break = $chartData['break_hours'][$i];
                    $gross = round($work + $break, 2);
                    $workMins  = AttendanceTimeCalculator::hoursToMinutes($work);
                    $breakMins = AttendanceTimeCalculator::hoursToMinutes($break);
                    $grossMins = $workMins + $breakMins;
                @endphp
                @if($work > 0 || $break > 0)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-3 font-medium text-gray-900">{{ $label }}</td>
                    <td class="px-6 py-3 text-right">
                        <span class="text-teal-700 font-medium">{{ AttendanceTimeCalculator::formatHoursAndMinutes($work) }}</span>
                        <span class="text-xs text-gray-400 block">{{ $workMins }} min</span>
                    </td>
                    <td class="px-6 py-3 text-right">
                        <span class="text-orange-600 font-medium">{{ AttendanceTimeCalculator::formatHoursAndMinutes($break) }}</span>
                        <span class="text-xs text-gray-400 block">{{ $breakMins }} min</span>
                    </td>
                    <td class="px-6 py-3 text-right">
                        <span class="text-gray-700">{{ AttendanceTimeCalculator::formatHoursAndMinutes($gross) }}</span>
                        <span class="text-xs text-gray-400 block">{{ $grossMins }} min</span>
                    </td>
                </tr>
                @endif
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@if($chartData['has_data'])
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function () {
    const chartData = @json($chartData);

    function themeColors() {
        const dark = document.documentElement.classList.contains('dark');
        return {
            grid: dark ? '#374151' : '#e5e7eb',
            text: dark ? '#9ca3af' : '#6b7280',
        };
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
    const ctx = document.getElementById('workHoursChart');

    const workHoursChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: chartData.labels,
            datasets: [
                {
                    label: 'Net work hours',
                    data: chartData.work_hours,
                    backgroundColor: 'rgba(20, 184, 166, 0.85)',
                    borderColor: 'rgb(20, 184, 166)',
                    borderWidth: 1,
                    borderRadius: 4,
                    maxBarThickness: chartData.view === 'monthly' ? 14 : 40,
                },
                {
                    label: 'Break time',
                    data: chartData.break_hours,
                    backgroundColor: 'rgba(251, 146, 60, 0.85)',
                    borderColor: 'rgb(251, 146, 60)',
                    borderWidth: 1,
                    borderRadius: 4,
                    maxBarThickness: chartData.view === 'monthly' ? 14 : 40,
                },
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
                        label: function (ctx) {
                            const h = ctx.parsed.y;
                            const mins = Math.round(h * 60);
                            const label = ctx.dataset.label;
                            return label + ': ' + formatDuration(h) + ' (' + mins + ' min)';
                        },
                    },
                },
            },
            scales: {
                x: {
                    grid: { color: colors.grid },
                    ticks: {
                        color: colors.text,
                        maxRotation: chartData.view === 'monthly' ? 0 : 45,
                        autoSkip: chartData.view === 'monthly',
                        maxTicksLimit: chartData.view === 'monthly' ? 15 : undefined,
                    },
                },
                y: {
                    beginAtZero: true,
                    grid: { color: colors.grid },
                    ticks: {
                        color: colors.text,
                        callback: function (v) { return formatDuration(v); },
                    },
                    title: {
                        display: true,
                        text: 'Hours & minutes',
                        color: colors.text,
                    },
                },
            },
        },
    });

    document.querySelectorAll('.ems-theme-toggle').forEach(function (btn) {
        btn.addEventListener('click', function () {
            setTimeout(function () {
                const c = themeColors();
                workHoursChart.options.scales.x.grid.color = c.grid;
                workHoursChart.options.scales.x.ticks.color = c.text;
                workHoursChart.options.scales.y.grid.color = c.grid;
                workHoursChart.options.scales.y.ticks.color = c.text;
                workHoursChart.options.scales.y.title.color = c.text;
                workHoursChart.update();
            }, 50);
        });
    });
})();
</script>
@endif

@endsection
