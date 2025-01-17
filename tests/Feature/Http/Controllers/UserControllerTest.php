<?php

namespace Tests\Feature\Http\Controllers;

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
        Sanctum::actingAs(User::factory()->create());

        User::factory()->count(15)->create();

        $response = $this->getJson(route('users.index'));

        $response->assertOk()
            ->assertJsonStructure([
                'data',
                'links',
                'meta',
            ])
            ->assertJsonCount(10, 'data'); // Por padrão, a paginação exibe 10 por página.
    }

    public function test_can_store_a_new_user()
    {
        Sanctum::actingAs(User::factory()->create());

        $data = [
            'name' => 'John Doe',
            'email' => 'johndoe@example.com',
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
        Sanctum::actingAs(User::factory()->create());

        $user = User::factory()->create();

        $response = $this->getJson(route('users.show', $user));

        $response->assertOk()
            ->assertJsonFragment([
                'email' => $user->email,
            ]);
    }

    public function test_can_update_a_user()
    {
        Sanctum::actingAs(User::factory()->create());

        $user = User::factory()->create();

        $data = [
            'name' => 'Updated Name',
            'email' => $user->email, // O email permanece o mesmo.
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
        Sanctum::actingAs(User::factory()->create());

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

    public function test_can_logout_a_user()
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->postJson(route('users.logout'));

        $response->assertOk()
            ->assertJsonFragment([
                'message' => 'Logged out successfully',
            ]);
    }

    public function test_can_logout_from_all_devices()
    {
        $user = User::factory()->create();

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
        $user = User::factory()->create();

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
}
