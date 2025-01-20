<?php

namespace App\Helpers;

use App\Enums\Admin\SystemConfigurationKeys;
use App\Models\Admin\SystemConfiguration;
use Illuminate\Support\Facades\Cache;

class ConfigHelper
{
    public static function get(SystemConfigurationKeys $key): mixed
    {
        return Cache::remember("CONFIG_{$key->name}", 3600, function () use ($key) {
            $config = SystemConfiguration::find($key->name);
            return $config ? $config->value : $key->default();
        });
    }

    public static function set(SystemConfigurationKeys $key, $value): void
    {
        SystemConfiguration::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::put("CONFIG_{$key->name}", $value, 3600);
    }
}
