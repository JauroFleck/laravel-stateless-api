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
        $value = ConfigHelper::get(SystemConfigurationKeys::API_THROTTLE_ENABLED);
        $expected = SystemConfigurationKeys::API_THROTTLE_ENABLED->default();
        $this->assertEquals($expected, $value);
    }

    public function test_set_and_get_config_value()
    {
        ConfigHelper::set(SystemConfigurationKeys::API_THROTTLE_ENABLED, true);
        $value = ConfigHelper::get(SystemConfigurationKeys::API_THROTTLE_ENABLED);
        $this->assertTrue($value);

        ConfigHelper::set(SystemConfigurationKeys::API_THROTTLE_TIME, 80);
        $value = ConfigHelper::get(SystemConfigurationKeys::API_THROTTLE_TIME);
        $this->assertEquals(80, $value);

        ConfigHelper::set(SystemConfigurationKeys::APP_NAME, "My Laravel Test");
        $value = ConfigHelper::get(SystemConfigurationKeys::APP_NAME);
        $this->assertEquals("My Laravel Test", $value);
    }
}
