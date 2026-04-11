<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SiteSetting extends Model
{
    protected $fillable = ['group', 'key', 'value', 'type', 'label_ar', 'label_en'];

    public static function get(string $key, $default = null): ?string
    {
        return Cache::rememberForever("site_setting.$key", function () use ($key, $default) {
            return static::where('key', $key)->value('value') ?? $default;
        });
    }

    public static function set(string $key, $value): void
    {
        static::where('key', $key)->update(['value' => $value]);
        Cache::forget("site_setting.$key");
        Cache::forget('all_site_settings');
    }

    public static function flushCache(): void
    {
        foreach (static::pluck('key') as $key) {
            Cache::forget("site_setting.$key");
        }
        Cache::forget('all_site_settings');
    }

    protected static function booted(): void
    {
        static::saved(function (SiteSetting $setting) {
            Cache::forget("site_setting.{$setting->key}");
            Cache::forget('all_site_settings');
        });
        static::deleted(function (SiteSetting $setting) {
            Cache::forget("site_setting.{$setting->key}");
            Cache::forget('all_site_settings');
        });
    }
}
