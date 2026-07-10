<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\AttendanceBreak;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ContinuousSessionCalculator
{
    /**
     * Continuous working time since check-in or last qualifying break end.
     * Pauses while on break. Short breaks (< min) do not reset the timer.
     *
     * @return array{
     *   continuous_seconds: int,
     *   session_anchor_at: ?Carbon,
     *   on_break: bool,
     *   qualifies_for_reset_on_end: bool
     * }
     */
    public static function forAttendance(
        Attendance $attendance,
        ContinuousSessionPolicy $policy,
        ?Carbon $asOf = null,
    ): array {
        $asOf = $asOf ?? now();

        if (!$attendance->check_in || $attendance->check_out) {
            return [
                'continuous_seconds' => 0,
                'session_anchor_at' => null,
                'on_break' => false,
                'qualifies_for_reset_on_end' => false,
            ];
        }

        $breaks = $attendance->relationLoaded('breaks')
            ? $attendance->breaks
            : $attendance->breaks()->get();

        $minBreakSeconds = $policy->minBreakSeconds();
        $anchor = self::resolveAnchor($attendance->check_in->copy(), $breaks, $minBreakSeconds);
        $activeBreak = $breaks->first(fn (AttendanceBreak $b) => $b->break_in === null);
        $onBreak = $activeBreak !== null;

        $endsAt = $onBreak ? $activeBreak->break_out : $asOf;
        $continuousSeconds = (int) max(0, $anchor->diffInSeconds($endsAt));

        $qualifies = false;
        if ($onBreak && $activeBreak->break_out) {
            $qualifies = (int) $activeBreak->break_out->diffInSeconds($asOf) >= $minBreakSeconds;
        }

        return [
            'continuous_seconds' => $continuousSeconds,
            'session_anchor_at' => $anchor,
            'on_break' => $onBreak,
            'qualifies_for_reset_on_end' => $qualifies,
        ];
    }

    /**
     * Anchor = check-in, or end of the latest completed break that lasted >= min break.
     *
     * @param Collection<int, AttendanceBreak> $breaks
     */
    public static function resolveAnchor(Carbon $checkIn, Collection $breaks, int $minBreakSeconds): Carbon
    {
        $anchor = $checkIn->copy();

        $completed = $breaks
            ->filter(fn (AttendanceBreak $b) => $b->break_out && $b->break_in && $b->break_in->gt($b->break_out))
            ->sortBy(fn (AttendanceBreak $b) => $b->break_in->timestamp)
            ->values();

        foreach ($completed as $break) {
            $duration = (int) $break->break_out->diffInSeconds($break->break_in);
            if ($duration >= $minBreakSeconds) {
                $anchor = $break->break_in->copy();
            }
        }

        return $anchor;
    }
}
