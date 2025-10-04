<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Post;
use App\Models\User;

class PostSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first(); // Make sure at least one user exists

        Post::create([
            'user_id' => $user->id,
            'title' => 'Welcome to the system!',
            'content' => 'This is your first announcement.',
        ]);
    }
}
