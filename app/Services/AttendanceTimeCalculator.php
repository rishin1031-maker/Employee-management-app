<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\AttendanceBreak;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AttendanceTimeCalculator
{
    public const TARGET_DAILY_HOURS   = 8;
    public const TARGET_WEEKLY_HOURS  = 48;
    public const TARGET_MONTHLY_HOURS = 200;

    public const TARGET_SECONDS = self::TARGET_DAILY_HOURS * 3600;

    public static function targetHoursForView(string $view): float
    {
        return match ($view) {
            'weekly'  => self::TARGET_WEEKLY_HOURS,
            'monthly' => self::TARGET_MONTHLY_HOURS,
            default   => self::TARGET_DAILY_HOURS,
        };
    }

    public static function forAttendance(Attendance $attendance, ?Carbon $asOf = null): array
    {
        $asOf = $asOf ?? now();

        if (!$attendance->check_in) {
            return self::emptyStats();
        }

        $endsAt       = $attendance->check_out ?? $asOf;
        $grossSeconds = (int) max(0, $attendance->check_in->diffInSeconds($endsAt));

        $breaks = $attendance->relationLoaded('breaks')
            ? $attendance->breaks
            : $attendance->breaks()->get();

        $completedBreakSeconds = self::completedBreakSeconds($breaks);
        $activeBreak           = $breaks->first(fn (AttendanceBreak $b) => $b->break_in === null);
        $onBreak               = $activeBreak !== null;
        $activeBreakSeconds    = $onBreak
            ? (int) $activeBreak->break_out->diffInSeconds($asOf)
            : 0;

        $totalBreakSeconds = $completedBreakSeconds + $activeBreakSeconds;
        $netSeconds        = max(0, $grossSeconds - $completedBreakSeconds - ($onBreak ? $activeBreakSeconds : 0));

        return [
            'gross_seconds'             => $grossSeconds,
            'completed_break_seconds'   => $completedBreakSeconds,
            'active_break_seconds'      => $activeBreakSeconds,
            'total_break_seconds'       => $totalBreakSeconds,
            'net_seconds'               => $netSeconds,
            'on_break'                  => $onBreak,
            'active_break_since'        => $onBreak ? $activeBreak->break_out->format('h:i A') : null,
            'break_count'               => $breaks->count(),
            'completed_break_count'     => $breaks->filter(fn ($b) => $b->break_in !== null)->count(),
            'remaining_seconds'         => max(0, self::TARGET_SECONDS - $netSeconds),
            'progress_percent'          => min(100, round(($netSeconds / self::TARGET_SECONDS) * 100, 1)),
            'is_complete'               => $netSeconds >= self::TARGET_SECONDS,
        ];
    }

    public static function completedBreakSeconds(Collection $breaks): int
    {
        return (int) $breaks
            ->filter(fn (AttendanceBreak $b) => $b->break_in && $b->break_out && $b->break_in->gt($b->break_out))
            ->sum(fn (AttendanceBreak $b) => (int) $b->break_out->diffInSeconds($b->break_in));
    }

    public static function totalBreakMinutes(Collection $breaks): int
    {
        return (int) floor(self::completedBreakSeconds($breaks) / 60);
    }

    public static function netSecondsForCompletedDay(Attendance $attendance): int
    {
        if (!$attendance->check_in || !$attendance->check_out) {
            return 0;
        }

        $stats = self::forAttendance($attendance, $attendance->check_out);

        return $stats['net_seconds'];
    }

    private static function emptyStats(): array
    {
        return [
            'gross_seconds'             => 0,
            'completed_break_seconds'   => 0,
            'active_break_seconds'      => 0,
            'total_break_seconds'       => 0,
            'net_seconds'               => 0,
            'on_break'                  => false,
            'active_break_since'        => null,
            'break_count'               => 0,
            'completed_break_count'     => 0,
            'remaining_seconds'         => self::TARGET_SECONDS,
            'progress_percent'          => 0,
            'is_complete'               => false,
        ];
    }

    public static function hoursToMinutes(float $hours): int
    {
        return (int) round($hours * 60);
    }

    public static function formatHoursAndMinutes(float $hours): string
    {
        $totalMinutes = self::hoursToMinutes($hours);

        if ($totalMinutes === 0) {
            return '0m';
        }

        $h = intdiv($totalMinutes, 60);
        $m = $totalMinutes % 60;

        if ($h === 0) {
            return "{$m}m";
        }

        if ($m === 0) {
            return "{$h}h";
        }

        return "{$h}h {$m}m";
    }
}
