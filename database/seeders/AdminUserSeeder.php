<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'hassan@almalki.sa'],
            [
                'name' => 'Hassan Almalki',
                'password' => Hash::make('HssaaN@666666'),
                'is_admin' => true,
                'email_verified_at' => now(),
            ]
        );
    }
}
