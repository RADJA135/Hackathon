<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create a test user – login with phone number
        User::updateOrCreate(
            ['phone' => '+99999991003'],   // <-- this is the login phone
            [
                'name' => 'Test User',
                'password' => Hash::make('password'), // default password
                // If you have other required fields, add them here
            ]
        );
    }
}