<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin 1
        User::updateOrCreate(
            ['email' => 'admin1@psu.edu.ph'],
            [
                'name' => 'Admin One',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );

        // Admin 2
        User::updateOrCreate(
            ['email' => 'admin2@psu.edu.ph'],
            [
                'name' => 'Admin Two',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );
    }
}
