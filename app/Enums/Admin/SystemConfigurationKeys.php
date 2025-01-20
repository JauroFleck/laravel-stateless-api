<?php

namespace App\Enums\Admin;

enum SystemConfigurationKeys
{
    case APP_NAME;
    case API_THROTTLE_ENABLED;
    case API_THROTTLE_LIMIT; // Requests per user, per second
    case API_THROTTLE_TIME; // Timeout duration in seconds

    public function default(): string|int|bool
    {
        return match($this) {
            self::APP_NAME => 'My Laravel API',
            self::API_THROTTLE_ENABLED => false,
            self::API_THROTTLE_LIMIT => 10,
            self::API_THROTTLE_TIME => 60,
        };
    }
}
