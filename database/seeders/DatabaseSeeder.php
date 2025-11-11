<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed organization structure data first
        $this->call([
            CountrySeeder::class,
            ProvinceSeeder::class,
            CurrencySeeder::class,
            EmploymentTypeSeeder::class,
            EmploymentStatusSeeder::class,
        ]);

        // Seed roles and permissions
        $this->call([
            RolesAndPermissionsSeeder::class,
        ]);

        // Create a super admin user
        $superAdmin = User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'admin@hcrm.com',
        ]);
        
        // Assign super admin role
        $superAdmin->assignRole('Super Admin');

        // Create a test HR Manager
        $hrManager = User::factory()->create([
            'name' => 'HR Manager',
            'email' => 'hr@hcrm.com',
        ]);
        
        $hrManager->assignRole('HR Manager');

        // Create a test employee
        $employee = User::factory()->create([
            'name' => 'Test Employee',
            'email' => 'employee@hcrm.com',
        ]);

        $employee->assignRole('Employee');
    }
}
