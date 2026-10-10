<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SiteSetting extends Model
{
    protected $table = 'site_settings';
    protected $primaryKey = 'key';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['key', 'value'];

    /**
     * Get a setting by key, cached.
     */
    public static function get($key, $default = null)
    {
        $settings = Cache::rememberForever('site_settings_cache', function () {
            return self::pluck('value', 'key')->all();
        });

        return $settings[$key] ?? $default;
    }

    /**
     * Set/Update a setting.
     */
    public static function set($key, $value)
    {
        self::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget('site_settings_cache');
    }

    /**
     * Get all settings as an associative array.
     */
    public static function allSettings()
    {
        return Cache::rememberForever('site_settings_cache', function () {
            return self::pluck('value', 'key')->all();
        });
    }

    /**
     * Clear the cache manually.
     */
    public static function clearCache()
    {
        Cache::forget('site_settings_cache');
    }
}
