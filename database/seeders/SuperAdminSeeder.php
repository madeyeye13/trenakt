<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'conceptbezalel@gmail.com'],
            [
                'name' => 'Michael Adeyeye',
                'password' => Hash::make('Qwertyuiop123*'),
                'email_verified_at' => now(),
            ]
        );

        $user->assignRole('super_admin');
    }
}