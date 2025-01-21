<?php

namespace Tests\Feature\Helpers;

use App\Enums\Admin\SystemConfigurationKeys;
use App\Helpers\ConfigHelper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConfigHelperTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_default_config_value()
    {
        $value = ConfigHelper::get(SystemConfigurationKeys::APP_NAME);
        $expected = SystemConfigurationKeys::APP_NAME->default();
        $this->assertEquals($expected, $value);
    }

    public function test_set_and_get_config_value()
    {
        ConfigHelper::set(SystemConfigurationKeys::APP_NAME, "My Laravel Test");
        $value = ConfigHelper::get(SystemConfigurationKeys::APP_NAME);
        $this->assertEquals("My Laravel Test", $value);
    }
}
