<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EmploymentStatus;

class EmploymentStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            ['name' => 'Active', 'code' => 'ACT', 'description' => 'Currently active employee', 'status' => 'active'],
            ['name' => 'Inactive', 'code' => 'INACT', 'description' => 'Inactive employee', 'status' => 'active'],
            ['name' => 'On Leave', 'code' => 'LEAVE', 'description' => 'Employee on leave', 'status' => 'active'],
            ['name' => 'Suspended', 'code' => 'SUSP', 'description' => 'Suspended employee', 'status' => 'active'],
            ['name' => 'Terminated', 'code' => 'TERM', 'description' => 'Terminated employee', 'status' => 'active'],
            ['name' => 'Resigned', 'code' => 'RES', 'description' => 'Resigned employee', 'status' => 'active'],
        ];

        foreach ($statuses as $status) {
            EmploymentStatus::updateOrCreate(
                ['code' => $status['code']],
                $status
            );
        }
    }
}
