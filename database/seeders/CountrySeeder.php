<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    public function run(): void
    {
        Country::firstOrCreate(
            ['iso_code' => 'NG'],
            ['name' => 'Nigeria', 'currency_code' => 'NGN', 'is_active' => true]
        );

        Country::firstOrCreate(
            ['iso_code' => 'GH'],
            ['name' => 'Ghana', 'currency_code' => 'GHS', 'is_active' => true]
        );
    }
}