<?php

namespace Tests\Feature\Http\Controllers;

use App\Enums\User\UserProfiles;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_users()
    {
        Sanctum::actingAs(User::factory()->create(['profile' => UserProfiles::Admin]));

        User::factory()->count(15)->create();

        $response = $this->getJson(route('users.index'));

        $response->assertOk()
            ->assertJsonStructure([
                'data',
                'links',
                'meta',
            ])
            ->assertJsonCount(10, 'data');
    }

    public function test_can_store_a_new_user()
    {
        Sanctum::actingAs(User::factory()->create(['profile' => UserProfiles::Admin]));

        $data = [
            'name' => 'John Doe',
            'email' => 'johndoe@example.com',
            'profile' => UserProfiles::Patient->name,
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        $response = $this->postJson(route('users.store'), $data);

        $response->assertCreated()
            ->assertJsonFragment([
                'message' => 'User created successfully',
            ]);
        $this->assertDatabaseHas('users', [
            'email' => $data['email'],
        ]);
    }

    public function test_can_show_a_single_user()
    {
        Sanctum::actingAs(User::factory()->create(['profile' => UserProfiles::Admin]));

        $user = User::factory()->create();

        $response = $this->getJson(route('users.show', $user));

        $response->assertOk()
            ->assertJsonFragment([
                'email' => $user->email,
            ]);
    }

    public function test_can_update_a_user()
    {
        Sanctum::actingAs(User::factory()->create(['profile' => UserProfiles::Admin]));

        $user = User::factory()->create();

        $data = [
            'name' => 'Updated Name',
            'email' => $user->email,
        ];

        $response = $this->putJson(route('users.update', $user), $data);

        $response->assertOk()
            ->assertJsonFragment([
                'message' => 'User updated successfully',
            ]);
        $this->assertDatabaseHas('users', [
            'name' => $data['name'],
        ]);
    }

    public function test_can_delete_a_user()
    {
        Sanctum::actingAs(User::factory()->create(['profile' => UserProfiles::Admin]));

        $user = User::factory()->create();

        $response = $this->deleteJson(route('users.destroy', $user));

        $response->assertNoContent();
        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
        ]);
    }

    public function test_can_login_a_user()
    {
        $password = 'password123';
        $user = User::factory()->create([
            'email' => 'testuser@example.com',
            'password' => Hash::make($password),
            'profile' => UserProfiles::Patient,
        ]);

        $data = [
            'email' => $user->email,
            'password' => $password,
            'device_name' => 'Test Device',
        ];

        $response = $this->postJson(route('users.login'), $data);

        $response->assertOk()
            ->assertJsonStructure([
                'token',
                'device_name',
                'user',
            ]);
    }


    public function test_cannot_login_with_wrong_credentials()
    {
        $data = [
            'email' => 'user@example.com',
            'password' => 'wrong_password',
            'device_name' => 'Test Device',
        ];

        $response = $this->postJson(route('users.login'), $data);

        $response->assertUnauthorized()
            ->assertJsonFragment([
                'error' => 'Invalid credentials',
            ]);
    }

    public function test_can_logout_a_user()
    {
        $user = User::factory()->create(['profile' => UserProfiles::Patient]);

        Sanctum::actingAs($user);

        $response = $this->postJson(route('users.logout'));

        $response->assertOk()
            ->assertJsonFragment([
                'message' => 'Logged out successfully',
            ]);
    }

    public function test_can_logout_from_all_devices()
    {
        $user = User::factory()->create(['profile' => UserProfiles::Patient]);

        Sanctum::actingAs($user);

        // Simulate multiple device tokens for the user
        $user->tokens()->create(['name' => 'Device 1', 'token' => Hash::make('token1')]);
        $user->tokens()->create(['name' => 'Device 2', 'token' => Hash::make('token2')]);

        $response = $this->postJson(route('users.logoutAll'));

        $response->assertOk()
            ->assertJsonFragment([
                'message' => 'Logged out from all devices successfully',
            ]);

        $this->assertCount(0, $user->tokens);
    }

    public function test_cannot_access_login_route_when_already_logged_in()
    {
        $user = User::factory()->create(['profile' => UserProfiles::Patient]);

        // Act as an authenticated user
        Sanctum::actingAs($user);

        $data = [
            'email' => $user->email,
            'password' => 'password123',
            'device_name' => 'Test Device',
        ];

        $response = $this->postJson(route('users.login'), $data);

        $response->assertUnauthorized()
            ->assertJsonFragment([
                'error' => 'You are already logged in'
            ]);
    }


    public function test_can_get_user_devices()
    {
        $user = User::factory()->create(['profile' => UserProfiles::Patient]);

        Sanctum::actingAs($user);

        // Simulate multiple device tokens
        $device1 = $user->tokens()->create(['name' => 'Device 1', 'token' => Hash::make('token1')]);
        $device2 = $user->tokens()->create(['name' => 'Device 2', 'token' => Hash::make('token2')]);

        $response = $this->getJson(route('users.devices'));

        $response->assertOk()
            ->assertJsonFragment([
                'name' => $device1->name,
            ])
            ->assertJsonFragment([
                'name' => $device2->name,
            ]);
    }

    public function test_can_logout_from_a_specific_device()
    {
        $user = User::factory()->create(['profile' => UserProfiles::Patient]);

        Sanctum::actingAs($user);

        // Simulate multiple device tokens
        $device1 = $user->tokens()->create(['name' => 'Device 1', 'token' => Hash::make('token1')]);
        $device2 = $user->tokens()->create(['name' => 'Device 2', 'token' => Hash::make('token2')]);

        $response = $this->postJson(route('users.logoutFromDevice', ['device_id' => $device1->id]));

        $response->assertOk()
            ->assertJsonFragment([
                'message' => 'Logged out from device successfully',
            ]);

        // Ensure only the first device token is deleted
        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $device1->id,
        ]);

        $this->assertDatabaseHas('personal_access_tokens', [
            'id' => $device2->id,
        ]);
    }
}
