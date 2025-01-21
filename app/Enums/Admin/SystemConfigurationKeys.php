<?php

namespace App\Enums\Admin;

enum SystemConfigurationKeys
{
    case APP_NAME;

    public function default(): string|int|bool
    {
        return match($this) {
            self::APP_NAME => 'My Laravel API',
        };
    }
}
