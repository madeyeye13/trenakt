<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
     public function run(): void
    {
        Role::firstOrCreate(['name' => 'participant']);
        Role::firstOrCreate(['name' => 'business']);
        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'super_admin']);
    }
}