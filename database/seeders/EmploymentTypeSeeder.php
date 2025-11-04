<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EmploymentType;

class EmploymentTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            ['name' => 'Full Time', 'code' => 'FT', 'description' => 'Full-time employment', 'status' => 'active'],
            ['name' => 'Part Time', 'code' => 'PT', 'description' => 'Part-time employment', 'status' => 'active'],
            ['name' => 'Contract', 'code' => 'CON', 'description' => 'Contract-based employment', 'status' => 'active'],
            ['name' => 'Internship', 'code' => 'INT', 'description' => 'Internship position', 'status' => 'active'],
            ['name' => 'Temporary', 'code' => 'TEMP', 'description' => 'Temporary employment', 'status' => 'active'],
            ['name' => 'Consultant', 'code' => 'CONS', 'description' => 'Consultant position', 'status' => 'active'],
        ];

        foreach ($types as $type) {
            EmploymentType::updateOrCreate(
                ['code' => $type['code']],
                $type
            );
        }
    }
}
