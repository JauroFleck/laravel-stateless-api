<?php

namespace Tests\Feature\Http\Middlewares;

use App\Enums\User\UserProfiles;
use App\Models\User\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Tests\TestCase;

class ThrottleTest extends TestCase
{
    use RefreshDatabase;

    public function test_general_throttle()
    {
        Sanctum::actingAs(User::factory()->create(['profile' => UserProfiles::Patient]));
        for ($i = 0; $i < 60; $i++) {
            $response = $this->getJson(route('users.devices'));
            $response->assertStatus(HttpResponse::HTTP_OK);
        }
        $response = $this->getJson(route('users.devices'));
        $response->assertStatus(HttpResponse::HTTP_TOO_MANY_REQUESTS);
    }

    public function test_user_login_throttle()
    {
        $data = [
            'email' => 'user@example.com',
            'password' => 'password',
            'device_name' => 'test_device',
        ];
        for ($i = 0; $i < 10; $i++) {
            $response = $this->postJson(route('users.login'), $data);
            $response->assertStatus(HttpResponse::HTTP_UNAUTHORIZED);
        }
        $response = $this->postJson(route('users.login'), $data);
        $response->assertStatus(HttpResponse::HTTP_TOO_MANY_REQUESTS);
    }

    public function test_admin_login_throttle()
    {
        $data = [
            'email' => 'admin@example.com',
            'password' => 'password',
        ];
        for ($i = 0; $i < 5; $i++) {
            $response = $this->postJson(route('admin.login'), $data);
            $response->assertStatus(HttpResponse::HTTP_UNAUTHORIZED);
        }
        $response = $this->postJson(route('admin.login'), $data);
        $response->assertStatus(HttpResponse::HTTP_TOO_MANY_REQUESTS);
    }
}
