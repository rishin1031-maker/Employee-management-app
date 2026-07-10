<?php

namespace App\Services;

use App\Contracts\Repositories\AttendanceRepositoryInterface;
use App\Models\Attendance;
use App\Notifications\ContinuousSessionAutoCheckoutNotification;
use App\Notifications\ContinuousSessionReminderNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ContinuousSessionService
{
    public function __construct(
        private ContinuousSessionPolicy $policy,
        private AttendanceRepositoryInterface $attendanceRepo,
    ) {}

    public function policy(): ContinuousSessionPolicy
    {
        return $this->policy;
    }

    /** @return array<string, mixed> */
    public function liveFields(?Attendance $attendance): array
    {
        $policy = $this->policy->toArray();

        if (!$attendance?->check_in || $attendance->check_out || !$this->policy->enabled()) {
            return [
                'continuous_policy' => $policy,
                'continuous_seconds' => 0,
                'continuous_remaining_seconds' => $policy['limit_seconds'],
                'continuous_warning' => false,
                'continuous_in_grace' => false,
                'continuous_session_anchor_at' => null,
            ];
        }

        $calc = ContinuousSessionCalculator::forAttendance($attendance, $this->policy);
        $continuous = $calc['continuous_seconds'];
        $limit = $this->policy->limitSeconds();
        $reminderAt = $this->policy->reminderAtSeconds();
        $autoAt = $this->policy->autoCheckoutAtSeconds();

        return [
            'continuous_policy' => $policy,
            'continuous_seconds' => $continuous,
            'continuous_remaining_seconds' => max(0, $limit - $continuous),
            'continuous_warning' => !$calc['on_break'] && $continuous >= $reminderAt,
            'continuous_in_grace' => !$calc['on_break'] && $continuous >= $limit && $continuous < $autoAt,
            'continuous_session_anchor_at' => $calc['session_anchor_at']?->toIso8601String(),
        ];
    }

    /**
     * @return array{reminders: int, auto_checkouts: int}
     */
    public function enforce(): array
    {
        if (!$this->policy->enabled()) {
            return ['reminders' => 0, 'auto_checkouts' => 0];
        }

        $reminders = 0;
        $autoCheckouts = 0;

        $open = Attendance::query()
            ->with(['breaks', 'employee'])
            ->whereDate('date', today())
            ->whereNotNull('check_in')
            ->whereNull('check_out')
            ->get();

        foreach ($open as $attendance) {
            try {
                $result = $this->enforceOne($attendance);
                $reminders += $result['reminder'] ? 1 : 0;
                $autoCheckouts += $result['auto_checkout'] ? 1 : 0;
            } catch (\Throwable $e) {
                Log::warning('Continuous session enforce failed', [
                    'attendance_id' => $attendance->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return ['reminders' => $reminders, 'auto_checkouts' => $autoCheckouts];
    }

    /** @return array{reminder: bool, auto_checkout: bool} */
    private function enforceOne(Attendance $attendance): array
    {
        $calc = ContinuousSessionCalculator::forAttendance($attendance, $this->policy);
        $anchor = $calc['session_anchor_at'];
        $continuous = $calc['continuous_seconds'];

        if ($anchor && (
            !$attendance->continuous_session_anchor_at
            || abs($attendance->continuous_session_anchor_at->diffInSeconds($anchor)) > 1
        )) {
            $attendance->forceFill([
                'continuous_session_anchor_at' => $anchor,
                'continuous_reminder_sent_at' => null,
            ])->save();
            $attendance->refresh();
        }

        if ($calc['on_break']) {
            return ['reminder' => false, 'auto_checkout' => false];
        }

        $reminderAt = $this->policy->reminderAtSeconds();
        $autoAt = $this->policy->autoCheckoutAtSeconds();
        $sentReminder = false;

        if ($continuous >= $reminderAt && !$attendance->continuous_reminder_sent_at) {
            $this->sendReminder($attendance, $continuous);
            $attendance->forceFill(['continuous_reminder_sent_at' => now()])->save();
            $sentReminder = true;
        }

        if ($continuous >= $autoAt) {
            $this->autoCheckout($attendance);

            return ['reminder' => $sentReminder, 'auto_checkout' => true];
        }

        return ['reminder' => $sentReminder, 'auto_checkout' => false];
    }

    private function sendReminder(Attendance $attendance, int $continuousSeconds): void
    {
        $employee = $attendance->employee;
        if (!$employee) {
            return;
        }

        $limitLabel = $this->formatDuration($this->policy->limitMinutes());
        $remaining = max(0, $this->policy->limitSeconds() - $continuousSeconds);
        $remainingLabel = $this->formatDuration((int) ceil($remaining / 60));

        $employee->notify(new ContinuousSessionReminderNotification(
            limitLabel: $limitLabel,
            remainingLabel: $remainingLabel,
            graceMinutes: $this->policy->graceMinutes(),
            minBreakMinutes: $this->policy->minBreakMinutes(),
        ));
    }

    private function autoCheckout(Attendance $attendance): void
    {
        DB::transaction(function () use ($attendance) {
            $locked = Attendance::query()->lockForUpdate()->find($attendance->id);
            if (!$locked || $locked->check_out) {
                return;
            }

            $locked->load('breaks');
            if ($locked->on_break) {
                $this->attendanceRepo->endBreak($locked->id);
            }

            $limitLabel = $this->formatDuration($this->policy->limitMinutes());
            $note = "Auto checkout: Continuous working session exceeded {$limitLabel} without a qualifying break "
                . "(min {$this->policy->minBreakMinutes()} min).";

            $this->attendanceRepo->checkOutWithData($locked->employee_id, [
                'check_out' => now(),
                'note' => $note,
            ]);
        });

        $fresh = Attendance::with('employee')->find($attendance->id);
        $employee = $fresh?->employee;
        if ($employee && $fresh?->check_out) {
            $employee->notify(new ContinuousSessionAutoCheckoutNotification(
                limitLabel: $this->formatDuration($this->policy->limitMinutes()),
                checkoutAt: $fresh->check_out->format('h:i A'),
            ));
        }
    }

    private function formatDuration(int $minutes): string
    {
        $h = intdiv($minutes, 60);
        $m = $minutes % 60;
        if ($h === 0) {
            return "{$m}m";
        }
        if ($m === 0) {
            return "{$h}h";
        }

        return "{$h}h {$m}m";
    }
}
