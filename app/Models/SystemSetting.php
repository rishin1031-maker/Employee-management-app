<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SystemSetting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function getValue(string $key, mixed $default = null): mixed
    {
        $settings = self::allCached();

        return array_key_exists($key, $settings) ? $settings[$key] : $default;
    }

    public static function setValue(string $key, mixed $value): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => is_bool($value) ? ($value ? '1' : '0') : (string) $value],
        );

        Cache::forget('system_settings');
    }

    /** @return array<string, string|null> */
    public static function allCached(): array
    {
        return Cache::remember('system_settings', 60, function () {
            return static::query()->pluck('value', 'key')->all();
        });
    }

    public static function flushCache(): void
    {
        Cache::forget('system_settings');
    }
}
