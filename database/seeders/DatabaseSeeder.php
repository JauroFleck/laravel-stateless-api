<?php

namespace Database\Seeders;

use App\Enums\User\UserProfiles;
use App\Models\User\User;
use Illuminate\Database\Seeder;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        foreach (UserProfiles::cases() as $profile) {
            User::factory()->create([
                'profile' => $profile,
                'name' => $profile->name,
                'email' => lcfirst($profile->name) . '@example.com',
                'password' => bcrypt('password'),
            ]);
        }
    }
}
