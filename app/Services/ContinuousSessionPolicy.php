<?php

namespace App\Services;

use App\Models\SystemSetting;

class ContinuousSessionPolicy
{
    public const KEY_ENABLED = 'continuous_session_enabled';
    public const KEY_LIMIT_MINUTES = 'continuous_session_limit_minutes';
    public const KEY_REMINDER_BEFORE_MINUTES = 'continuous_session_reminder_before_minutes';
    public const KEY_GRACE_MINUTES = 'continuous_session_grace_minutes';
    public const KEY_MIN_BREAK_MINUTES = 'continuous_session_min_break_minutes';

    public const DEFAULTS = [
        self::KEY_ENABLED => true,
        self::KEY_LIMIT_MINUTES => 465,
        self::KEY_REMINDER_BEFORE_MINUTES => 15,
        self::KEY_GRACE_MINUTES => 5,
        self::KEY_MIN_BREAK_MINUTES => 2,
    ];

    public function enabled(): bool
    {
        return $this->bool(self::KEY_ENABLED, self::DEFAULTS[self::KEY_ENABLED]);
    }

    public function limitMinutes(): int
    {
        return max(1, $this->int(self::KEY_LIMIT_MINUTES, self::DEFAULTS[self::KEY_LIMIT_MINUTES]));
    }

    public function reminderBeforeMinutes(): int
    {
        return max(0, $this->int(self::KEY_REMINDER_BEFORE_MINUTES, self::DEFAULTS[self::KEY_REMINDER_BEFORE_MINUTES]));
    }

    public function graceMinutes(): int
    {
        return max(0, $this->int(self::KEY_GRACE_MINUTES, self::DEFAULTS[self::KEY_GRACE_MINUTES]));
    }

    public function minBreakMinutes(): int
    {
        return max(1, $this->int(self::KEY_MIN_BREAK_MINUTES, self::DEFAULTS[self::KEY_MIN_BREAK_MINUTES]));
    }

    public function limitSeconds(): int
    {
        return $this->limitMinutes() * 60;
    }

    public function reminderAtSeconds(): int
    {
        return max(0, $this->limitSeconds() - ($this->reminderBeforeMinutes() * 60));
    }

    public function graceSeconds(): int
    {
        return $this->graceMinutes() * 60;
    }

    public function minBreakSeconds(): int
    {
        return $this->minBreakMinutes() * 60;
    }

    public function autoCheckoutAtSeconds(): int
    {
        return $this->limitSeconds() + $this->graceSeconds();
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'enabled' => $this->enabled(),
            'limit_minutes' => $this->limitMinutes(),
            'reminder_before_minutes' => $this->reminderBeforeMinutes(),
            'grace_minutes' => $this->graceMinutes(),
            'min_break_minutes' => $this->minBreakMinutes(),
            'limit_seconds' => $this->limitSeconds(),
            'reminder_at_seconds' => $this->reminderAtSeconds(),
            'grace_seconds' => $this->graceSeconds(),
            'min_break_seconds' => $this->minBreakSeconds(),
            'auto_checkout_at_seconds' => $this->autoCheckoutAtSeconds(),
        ];
    }

    /** @param array<string, mixed> $data */
    public function update(array $data): array
    {
        if (array_key_exists('enabled', $data)) {
            SystemSetting::setValue(self::KEY_ENABLED, (bool) $data['enabled']);
        }
        if (array_key_exists('limit_minutes', $data)) {
            SystemSetting::setValue(self::KEY_LIMIT_MINUTES, (int) $data['limit_minutes']);
        }
        if (array_key_exists('reminder_before_minutes', $data)) {
            SystemSetting::setValue(self::KEY_REMINDER_BEFORE_MINUTES, (int) $data['reminder_before_minutes']);
        }
        if (array_key_exists('grace_minutes', $data)) {
            SystemSetting::setValue(self::KEY_GRACE_MINUTES, (int) $data['grace_minutes']);
        }
        if (array_key_exists('min_break_minutes', $data)) {
            SystemSetting::setValue(self::KEY_MIN_BREAK_MINUTES, (int) $data['min_break_minutes']);
        }

        SystemSetting::flushCache();

        return $this->toArray();
    }

    private function bool(string $key, bool $default): bool
    {
        $value = SystemSetting::getValue($key);

        if ($value === null) {
            return $default;
        }

        return in_array(strtolower((string) $value), ['1', 'true', 'yes', 'on'], true);
    }

    private function int(string $key, int $default): int
    {
        $value = SystemSetting::getValue($key);

        if ($value === null || $value === '') {
            return $default;
        }

        return (int) $value;
    }
}
