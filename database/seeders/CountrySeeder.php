<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Country;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $countries = [
            ['name' => 'Pakistan', 'code' => 'PAK', 'phone_code' => '+92', 'status' => 'active'],
            ['name' => 'United Arab Emirates', 'code' => 'ARE', 'phone_code' => '+971', 'status' => 'active'],
            ['name' => 'United States', 'code' => 'USA', 'phone_code' => '+1', 'status' => 'active'],
            ['name' => 'United Kingdom', 'code' => 'GBR', 'phone_code' => '+44', 'status' => 'active'],
            ['name' => 'Saudi Arabia', 'code' => 'SAU', 'phone_code' => '+966', 'status' => 'active'],
            ['name' => 'India', 'code' => 'IND', 'phone_code' => '+91', 'status' => 'active'],
            ['name' => 'Canada', 'code' => 'CAN', 'phone_code' => '+1', 'status' => 'active'],
            ['name' => 'Australia', 'code' => 'AUS', 'phone_code' => '+61', 'status' => 'active'],
        ];

        foreach ($countries as $country) {
            Country::updateOrCreate(
                ['code' => $country['code']],
                $country
            );
        }
    }
}
