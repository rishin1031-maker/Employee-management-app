@extends('employee.layouts.app')
@section('title','Dashboard')
@section('page-title','My Dashboard')

@section('content')

{{-- Welcome + check-in/out panel --}}
<div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 mb-5">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">

        {{-- Employee info --}}
        <div class="flex items-center gap-4">
            <img src="{{ $employee->image_url }}"
                 class="w-14 h-14 rounded-full object-cover border-2 border-indigo-100">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">{{ $employee->name }}</h2>
                <p class="text-sm text-gray-500">
                    {{ $employee->employee_id }} · {{ $employee->designation->name ?? '—' }}
                </p>
            </div>
        </div>

        {{-- Action buttons --}}
        @php $att = $employee->todayAttendance; if ($att) $att->load('breaks'); @endphp

        <div class="flex flex-wrap gap-3 items-center">

            @if(!$att)
                {{-- Not checked in --}}
                <form method="POST" action="{{ route('employee.attendance.checkin') }}">
                    @csrf
                    <button type="submit"
                            class="bg-green-600 hover:bg-green-700 text-white text-sm font-medium px-5 py-2.5 rounded-lg transition flex items-center gap-2">
                        <i class="fas fa-circle-check"></i> Check In
                    </button>
                </form>

            @elseif(!$att->check_out)
                {{-- Live timer cards --}}
                <div class="text-center px-4 py-2 bg-gray-50 rounded-xl border border-gray-100">
                    <p class="text-xs text-gray-500 mb-0.5">Net work time</p>
                    <p id="live-work-timer" class="text-xl font-bold text-indigo-600 font-mono">
                        --:--:--
                    </p>
                    <p id="work-status-label" class="text-xs text-green-500 mt-0.5">Loading…</p>
                </div>

                <div class="text-center px-4 py-2 bg-orange-50 rounded-xl border border-orange-100">
                    <p class="text-xs text-gray-500 mb-0.5">Total break</p>
                    <p id="live-break-timer" class="text-xl font-bold text-orange-500 font-mono">
                        --:--:--
                    </p>
                    <p id="break-count-label" class="text-xs text-gray-400 mt-0.5">
                        {{ $att->breaks->count() }} break(s)
                    </p>
                </div>

                {{-- Break buttons --}}
                <div id="break-btn-wrap">
                    @if($att->on_break)
                        <button type="button" id="break-action-btn"
                                data-action="end"
                                class="bg-blue-500 hover:bg-blue-600 text-white text-sm font-medium px-5 py-2.5 rounded-lg transition flex items-center gap-2">
                            <i class="fas fa-mug-hot"></i> End Break
                        </button>
                        <p id="break-since-label" class="text-xs text-orange-500 font-medium mt-1 text-center">
                            Since {{ $att->activeBreak?->break_out?->format('h:i A') }}
                        </p>
                    @else
                        <button type="button" id="break-action-btn"
                                data-action="start"
                                class="bg-orange-400 hover:bg-orange-500 text-white text-sm font-medium px-5 py-2.5 rounded-lg transition flex items-center gap-2">
                            <i class="fas fa-mug-hot"></i> Take Break
                        </button>
                        <p id="break-since-label" class="text-xs text-orange-500 font-medium mt-1 text-center hidden"></p>
                    @endif
                </div>

                {{-- Checkout --}}
                <button onclick="openCheckoutModal()"
                        class="bg-red-500 hover:bg-red-600 text-white text-sm font-medium px-5 py-2.5 rounded-lg transition flex items-center gap-2">
                    <i class="fas fa-circle-xmark"></i> Check Out
                </button>

            @else
                {{-- Checked out summary --}}
                @php $att->load('breaks'); @endphp
                <div class="text-sm text-gray-600 space-y-0.5">
                    <p>
                        <span class="text-gray-400">In:</span> {{ $att->check_in->format('h:i A') }}
                        &nbsp;·&nbsp;
                        <span class="text-gray-400">Out:</span> {{ $att->check_out->format('h:i A') }}
                    </p>
                    <p>
                        <span class="text-gray-400">Net work:</span>
                        <span class="font-semibold text-green-700">{{ $att->net_hours_worked }}</span>
                    </p>
                    <p>
                        <span class="text-gray-400">Total break:</span>
                        <span class="font-medium text-orange-500">{{ $att->total_break_minutes }}m</span>
                    </p>
                    @if($att->is_eight_hours_complete)
                        <p class="text-green-600 text-xs font-medium">
                            <i class="fas fa-circle-check"></i> Full day completed
                        </p>
                    @else
                        <p class="text-red-500 text-xs font-medium">
                            <i class="fas fa-triangle-exclamation"></i>
                            Short by {{ $att->remaining_minutes }}m
                        </p>
                    @endif
                </div>
            @endif
        </div>
    </div>

    {{-- Progress bar (only when checked in and not checked out) --}}
    @if($att && !$att->check_out)
    <div class="mt-5">
        <div class="flex justify-between text-xs text-gray-500 mb-1.5">
            <span>Work progress</span>
            <span id="progress-label">Loading…</span>
        </div>
        <div class="w-full bg-gray-100 rounded-full h-2.5">
            <div id="progress-bar"
                 class="h-2.5 rounded-full transition-all duration-1000 bg-indigo-600"
                 style="width: 0%"></div>
        </div>
        <div class="flex justify-between text-xs text-gray-400 mt-1">
            <span>{{ $att->check_in->format('h:i A') }}</span>
            <span>8h target</span>
        </div>
    </div>
    @endif
</div>

{{-- Stats --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-5">
    @php
        $balance = $employee->leaveBalance;
        $stats = [
            ['label' => 'Casual leave left',  'value' => $balance ? $balance->casual_total - $balance->casual_used : 12, 'color' => 'bg-blue-50 text-blue-600'],
            ['label' => 'Sick leave left',    'value' => $balance ? $balance->sick_total   - $balance->sick_used   : 10, 'color' => 'bg-purple-50 text-purple-600'],
            ['label' => 'Annual leave left',  'value' => $balance ? $balance->annual_total - $balance->annual_used : 15, 'color' => 'bg-teal-50 text-teal-600'],
            ['label' => 'Pending requests',   'value' => $leaveStats['pending'],                                         'color' => 'bg-yellow-50 text-yellow-600'],
        ];
    @endphp
    @foreach($stats as $s)
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 flex items-center gap-4">
        <div class="w-11 h-11 rounded-xl {{ $s['color'] }} flex items-center justify-center text-xl font-bold flex-shrink-0">
            {{ $s['value'] }}
        </div>
        <p class="text-xs text-gray-500 leading-tight">{{ $s['label'] }}</p>
    </div>
    @endforeach
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
    {{-- Recent Leaves --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
            <h3 class="font-semibold text-gray-800 text-sm">Recent leave requests</h3>
            <a href="{{ route('employee.leave.index') }}" class="text-xs text-indigo-600 hover:underline">View all</a>
        </div>
        <div class="divide-y divide-gray-50">
            @forelse($recentLeaves as $leave)
            <div class="px-5 py-3 flex items-center justify-between text-sm">
                <div>
                    <p class="font-medium text-gray-800 capitalize">{{ $leave->type }} leave</p>
                    <p class="text-xs text-gray-400">{{ $leave->from_date->format('d M') }} – {{ $leave->to_date->format('d M Y') }} · {{ $leave->days }} day(s)</p>
                </div>
                <span class="px-2.5 py-1 rounded-full text-xs font-medium
                    {{ $leave->status === 'approved' ? 'bg-green-100 text-green-700' : ($leave->status === 'rejected' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700') }}">
                    {{ ucfirst($leave->status) }}
                </span>
            </div>
            @empty
            <p class="px-5 py-6 text-center text-sm text-gray-400">No leave requests yet.</p>
            @endforelse
        </div>
    </div>

    {{-- Recent Attendance --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
            <h3 class="font-semibold text-gray-800 text-sm">Recent attendance</h3>
            <a href="{{ route('employee.attendance.index') }}" class="text-xs text-indigo-600 hover:underline">View all</a>
        </div>
        <div class="divide-y divide-gray-50">
            @forelse($recentAttendance as $att_row)
            <div class="px-5 py-3 flex items-center justify-between text-sm">
                <div>
                    <p class="font-medium text-gray-800">{{ $att_row->date->format('D, d M Y') }}</p>
                    <p class="text-xs text-gray-400">
                        {{ $att_row->check_in ? $att_row->check_in->format('h:i A') : '—' }}
                        {{ $att_row->check_out ? '→ ' . $att_row->check_out->format('h:i A') : '' }}
                        @if($att_row->net_hours_worked)
                            · Net: {{ $att_row->net_hours_worked }}
                        @endif
                        @if($att_row->total_break_minutes > 0)
                            · Break: {{ $att_row->total_break_minutes }}m
                        @endif
                    </p>
                </div>
                <span class="px-2.5 py-1 rounded-full text-xs font-medium
                    {{ $att_row->status === 'present' ? 'bg-green-100 text-green-700' : ($att_row->status === 'absent' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700') }}">
                    {{ ucfirst(str_replace('_',' ', $att_row->status)) }}
                </span>
            </div>
            @empty
            <p class="px-5 py-6 text-center text-sm text-gray-400">No attendance records.</p>
            @endforelse
        </div>
    </div>
</div>

{{-- Checkout warning modal --}}
@if($att && !$att->check_out)

<div id="checkout-modal"
     class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl p-6 w-full max-w-md shadow-2xl">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 rounded-lg flex items-center justify-center" id="modal-icon-wrap">
                <i class="fas fa-circle-xmark text-lg" id="modal-icon"></i>
            </div>
            <div>
                <h3 class="font-semibold text-gray-900" id="modal-title">Confirm Check Out</h3>
                <p class="text-xs text-gray-500" id="modal-subtitle"></p>
            </div>
        </div>

        <div class="bg-gray-50 rounded-lg p-3 mb-4 text-sm space-y-1.5">
            <div class="flex justify-between">
                <span class="text-gray-500">Check in</span>
                <span class="font-medium">{{ $att->check_in->format('h:i A') }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Total break</span>
                <span class="font-medium text-orange-500" id="modal-break">—</span>
            </div>
            <div class="flex justify-between border-t border-gray-200 pt-1.5">
                <span class="text-gray-500">Net work time</span>
                <span class="font-bold" id="modal-net-time">—</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Remaining for 8h</span>
                <span class="font-medium" id="modal-remaining">—</span>
            </div>
        </div>

        <form method="POST" action="{{ route('employee.attendance.checkout') }}"
              id="checkout-form">
            @csrf
            <div id="early-reason-wrap" class="hidden mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Reason for early checkout <span class="text-red-500">*</span>
                </label>
                <textarea name="early_reason" id="early-reason-input" rows="2"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                          placeholder="Please explain why you are checking out early…"></textarea>
                @error('early_reason')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex gap-3">
                <button type="submit"
                        class="flex-1 bg-red-500 hover:bg-red-600 text-white text-sm font-medium py-2.5 rounded-lg transition">
                    Confirm Check Out
                </button>
                <button type="button" onclick="closeCheckoutModal()"
                        class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium py-2.5 rounded-lg transition">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<script>
const LIVE_STATUS_URL  = '{{ route("employee.attendance.live-status") }}';
const BREAK_START_URL  = '{{ route("employee.attendance.breakout") }}';
const BREAK_END_URL    = '{{ route("employee.attendance.breakin") }}';
const CSRF_TOKEN       = '{{ csrf_token() }}';
const TARGET_SECONDS   = {{ \App\Services\AttendanceTimeCalculator::TARGET_SECONDS }};

// Initial stats from server (no waiting for first AJAX poll)
const INITIAL_STATS = @json($liveStats ?? null);

let polledAt        = Math.floor(Date.now() / 1000);
let polledNetSecs   = INITIAL_STATS?.net_seconds ?? 0;
let polledBreakSecs = INITIAL_STATS?.total_break_seconds ?? 0;
let isOnBreak       = INITIAL_STATS?.on_break ?? false;
let isComplete      = INITIAL_STATS?.is_complete ?? false;
let breakCount      = INITIAL_STATS?.break_count ?? 0;
let activeBreakSince = INITIAL_STATS?.active_break_since ?? null;

function formatSeconds(totalSecs) {
    totalSecs = Math.max(0, Math.floor(totalSecs));
    const h = Math.floor(totalSecs / 3600);
    const m = Math.floor((totalSecs % 3600) / 60);
    const s = totalSecs % 60;
    return String(h).padStart(2,'0') + ':' +
           String(m).padStart(2,'0') + ':' +
           String(s).padStart(2,'0');
}

function formatMins(totalSecs) {
    const mins = Math.floor(totalSecs / 60);
    if (mins <= 0) return '0m';
    const h = Math.floor(mins / 60);
    const m = mins % 60;
    if (h === 0) return m + 'm';
    return h + 'h ' + (m > 0 ? m + 'm' : '');
}

function secondsSincePoll() {
    return Math.max(0, Math.floor(Date.now() / 1000 - polledAt));
}

function applyLiveData(data) {
    polledAt         = data.server_time ?? Math.floor(Date.now() / 1000);
    polledNetSecs    = data.net_seconds ?? 0;
    polledBreakSecs  = data.total_break_seconds ?? 0;
    isOnBreak        = !!data.on_break;
    isComplete       = !!data.is_complete;
    breakCount       = data.break_count ?? 0;
    activeBreakSince = data.active_break_since ?? null;
    updateBreakButton();
}

async function pollServer() {
    try {
        const res  = await fetch(LIVE_STATUS_URL, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        });
        if (!res.ok) return;
        const data = await res.json();
        if (!data.checked_in || data.checked_out) return;
        applyLiveData(data);
    } catch (e) {
        // keep extrapolating from last snapshot
    }
}

function updateBreakButton() {
    const btn   = document.getElementById('break-action-btn');
    const label = document.getElementById('break-since-label');
    if (!btn) return;

    if (isOnBreak) {
        btn.dataset.action = 'end';
        btn.className = 'bg-blue-500 hover:bg-blue-600 text-white text-sm font-medium px-5 py-2.5 rounded-lg transition flex items-center gap-2';
        btn.innerHTML = '<i class="fas fa-mug-hot"></i> End Break';
        if (label) {
            label.textContent = activeBreakSince ? 'Since ' + activeBreakSince : '';
            label.classList.toggle('hidden', !activeBreakSince);
        }
    } else {
        btn.dataset.action = 'start';
        btn.className = 'bg-orange-400 hover:bg-orange-500 text-white text-sm font-medium px-5 py-2.5 rounded-lg transition flex items-center gap-2';
        btn.innerHTML = '<i class="fas fa-mug-hot"></i> Take Break';
        if (label) label.classList.add('hidden');
    }
}

function tickDisplay() {
    const elapsed = secondsSincePoll();
    // Work timer pauses on break; break timer counts only during active break
    const displayNetSecs   = isOnBreak ? polledNetSecs : polledNetSecs + elapsed;
    const displayBreakSecs = isOnBreak ? polledBreakSecs + elapsed : polledBreakSecs;
    const displayProgress  = isComplete ? 100 : Math.min(100, (displayNetSecs / TARGET_SECONDS) * 100);
    const displayRemaining = Math.max(0, TARGET_SECONDS - displayNetSecs);

    const workEl = document.getElementById('live-work-timer');
    if (workEl) {
        workEl.textContent = formatSeconds(displayNetSecs);
        workEl.className   = 'text-xl font-bold font-mono ' +
                             (isOnBreak ? 'text-orange-400' : 'text-indigo-600');
    }

    const breakEl = document.getElementById('live-break-timer');
    if (breakEl) breakEl.textContent = formatSeconds(displayBreakSecs);

    const countEl = document.getElementById('break-count-label');
    if (countEl) countEl.textContent = breakCount + ' break(s)';

    const statusEl = document.getElementById('work-status-label');
    if (statusEl) {
        statusEl.textContent = isOnBreak ? '⏸ On break — timer paused' : '● Working';
        statusEl.className   = 'text-xs mt-0.5 ' + (isOnBreak ? 'text-orange-500' : 'text-green-500');
    }

    const bar = document.getElementById('progress-bar');
    if (bar) {
        bar.style.width      = displayProgress + '%';
        bar.style.background = isComplete ? '#10b981' : (isOnBreak ? '#f97316' : '#6366f1');
    }

    const labelEl = document.getElementById('progress-label');
    if (labelEl) {
        if (isOnBreak) {
            labelEl.textContent = '☕ On break — timer paused';
            labelEl.className   = 'text-xs text-orange-500 font-medium';
        } else if (isComplete) {
            labelEl.textContent = '✓ 8 hours completed!';
            labelEl.className   = 'text-xs text-green-600 font-medium';
        } else {
            labelEl.textContent = formatMins(displayRemaining) + ' remaining';
            labelEl.className   = 'text-xs text-gray-500';
        }
    }
}

async function handleBreakAction() {
    const btn = document.getElementById('break-action-btn');
    if (!btn || btn.disabled) return;

    const isEnd   = btn.dataset.action === 'end';
    const url     = isEnd ? BREAK_END_URL : BREAK_START_URL;
    btn.disabled  = true;
    btn.classList.add('opacity-60', 'cursor-not-allowed');

    try {
        const res  = await fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
        });
        const data = await res.json();

        if (!res.ok || !data.success) {
            alert(data.message || 'Could not update break. Please try again.');
            return;
        }

        applyLiveData(data);
        tickDisplay();
    } catch (e) {
        alert('Network error. Please try again.');
    } finally {
        btn.disabled = false;
        btn.classList.remove('opacity-60', 'cursor-not-allowed');
    }
}

document.getElementById('break-action-btn')?.addEventListener('click', handleBreakAction);

// Render immediately with server-provided stats, then poll every 3 seconds
tickDisplay();
pollServer();
setInterval(pollServer, 3000);
setInterval(tickDisplay, 1000);

// ── Checkout modal ───────────────────────────────────────────────────────────
function openCheckoutModal() {
    if (isOnBreak) {
        alert('Please end your break before checking out.');
        return;
    }

    const elapsed   = secondsSincePoll();
    const netSecs   = polledNetSecs + elapsed;
    const breakSecs = polledBreakSecs;
    const isEarly   = netSecs < TARGET_SECONDS;
    const remSecs   = Math.max(0, TARGET_SECONDS - netSecs);

    document.getElementById('modal-net-time').textContent  = formatSeconds(netSecs);
    document.getElementById('modal-net-time').className    = 'font-bold ' + (isEarly ? 'text-red-600' : 'text-green-600');
    document.getElementById('modal-break').textContent     = formatMins(breakSecs);
    document.getElementById('modal-remaining').textContent = isEarly ? formatMins(remSecs) + ' short' : '✓ Completed';
    document.getElementById('modal-remaining').className   = 'font-medium ' + (isEarly ? 'text-red-500' : 'text-green-600');

    if (isEarly) {
        document.getElementById('modal-title').textContent    = 'Early Checkout Warning';
        document.getElementById('modal-subtitle').textContent = 'You have not completed 8 hours of net work yet.';
        document.getElementById('modal-icon-wrap').className  = 'w-10 h-10 rounded-lg flex items-center justify-center bg-yellow-100';
        document.getElementById('modal-icon').className       = 'fas fa-triangle-exclamation text-lg text-yellow-600';
        document.getElementById('early-reason-wrap').classList.remove('hidden');
        document.getElementById('early-reason-input').required = true;
    } else {
        document.getElementById('modal-title').textContent    = 'Confirm Check Out';
        document.getElementById('modal-subtitle').textContent = 'You have completed your 8-hour workday. Great work!';
        document.getElementById('modal-icon-wrap').className  = 'w-10 h-10 rounded-lg flex items-center justify-center bg-green-100';
        document.getElementById('modal-icon').className       = 'fas fa-circle-check text-lg text-green-600';
        document.getElementById('early-reason-wrap').classList.add('hidden');
        document.getElementById('early-reason-input').required = false;
    }

    document.getElementById('checkout-modal').classList.remove('hidden');
}

function closeCheckoutModal() {
    document.getElementById('checkout-modal').classList.add('hidden');
}

document.getElementById('checkout-modal')?.addEventListener('click', function(e) {
    if (e.target === this) closeCheckoutModal();
});
</script>

@endif

@endsection