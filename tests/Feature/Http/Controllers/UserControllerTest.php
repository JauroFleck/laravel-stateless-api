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
        $user = User::factory()->create();

        $response = $this->getJson(route('users.show', $user));

        $response->assertOk()
            ->assertJsonFragment([
                'email' => $user->email,
            ]);
    }

    public function test_can_update_a_user()
    {
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
}
