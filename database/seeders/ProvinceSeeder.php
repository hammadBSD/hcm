<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Province;
use App\Models\Country;

class ProvinceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get Pakistan
        $pakistan = Country::where('code', 'PAK')->first();
        if ($pakistan) {
            $provinces = [
                ['name' => 'Punjab', 'code' => 'PUN', 'country_id' => $pakistan->id, 'status' => 'active'],
                ['name' => 'Sindh', 'code' => 'SIN', 'country_id' => $pakistan->id, 'status' => 'active'],
                ['name' => 'Khyber Pakhtunkhwa', 'code' => 'KPK', 'country_id' => $pakistan->id, 'status' => 'active'],
                ['name' => 'Balochistan', 'code' => 'BAL', 'country_id' => $pakistan->id, 'status' => 'active'],
                ['name' => 'Gilgit-Baltistan', 'code' => 'GB', 'country_id' => $pakistan->id, 'status' => 'active'],
                ['name' => 'Azad Jammu and Kashmir', 'code' => 'AJK', 'country_id' => $pakistan->id, 'status' => 'active'],
                ['name' => 'Islamabad Capital Territory', 'code' => 'ICT', 'country_id' => $pakistan->id, 'status' => 'active'],
            ];

            foreach ($provinces as $province) {
                Province::updateOrCreate(
                    ['code' => $province['code'], 'country_id' => $pakistan->id],
                    $province
                );
            }
        }

        // Get UAE
        $uae = Country::where('code', 'ARE')->first();
        if ($uae) {
            $emirates = [
                ['name' => 'Abu Dhabi', 'code' => 'AD', 'country_id' => $uae->id, 'status' => 'active'],
                ['name' => 'Dubai', 'code' => 'DXB', 'country_id' => $uae->id, 'status' => 'active'],
                ['name' => 'Sharjah', 'code' => 'SHJ', 'country_id' => $uae->id, 'status' => 'active'],
                ['name' => 'Ajman', 'code' => 'AJM', 'country_id' => $uae->id, 'status' => 'active'],
                ['name' => 'Umm Al Quwain', 'code' => 'UAQ', 'country_id' => $uae->id, 'status' => 'active'],
                ['name' => 'Ras Al Khaimah', 'code' => 'RAK', 'country_id' => $uae->id, 'status' => 'active'],
                ['name' => 'Fujairah', 'code' => 'FUJ', 'country_id' => $uae->id, 'status' => 'active'],
            ];

            foreach ($emirates as $emirate) {
                Province::updateOrCreate(
                    ['code' => $emirate['code'], 'country_id' => $uae->id],
                    $emirate
                );
            }
        }
    }
}
