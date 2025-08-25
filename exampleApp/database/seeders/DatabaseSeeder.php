<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => env('SEED_USER_NAME', 'Test User'),
            'email' => env('SEED_USER_EMAIL', 'test@example.com'),
            'password' => env('SEED_USER_PASSWORD', bcrypt('password')),
        ]);
    }
}
