<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SiteSetting extends Model
{
    protected $fillable = [
        'group',
        'key',
        'value',
        'type',
        'label',
    ];

    public static function getValue(string $key, mixed $default = null): mixed
    {
        $all = self::allCached();

        return array_key_exists($key, $all) && $all[$key] !== null && $all[$key] !== ''
            ? $all[$key]
            : $default;
    }

    /**
     * @return array<string, mixed>
     */
    public static function allCached(): array
    {
        return Cache::rememberForever('soohouse_site_settings', function () {
            try {
                return self::query()->pluck('value', 'key')->all();
            } catch (\Throwable) {
                return [];
            }
        });
    }

    public static function setValue(string $key, mixed $value, string $group = 'general', string $type = 'text', ?string $label = null): self
    {
        $setting = self::updateOrCreate(
            ['key' => $key],
            [
                'group' => $group,
                'value' => is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value,
                'type' => $type,
                'label' => $label ?? $key,
            ]
        );

        Cache::forget('soohouse_site_settings');

        return $setting;
    }

    public static function clearCache(): void
    {
        Cache::forget('soohouse_site_settings');
    }
}
