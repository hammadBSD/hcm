<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Pakistan Income Tax Slabs for salaried persons (2025-2026).
 * Progressive: tax = base + (annual_income - exempted_tax_amount) × (additional_tax_amount/100).
 * Surcharge 9% when annual income > 10,000,000 is applied in PayrollCalculationService.
 */
return new class extends Migration
{
    private function slabs(): array
    {
        return [
            ['salary_from' => 0, 'salary_to' => 600_000, 'tax' => 0, 'exempted_tax_amount' => 0, 'additional_tax_amount' => 0],
            ['salary_from' => 600_001, 'salary_to' => 1_200_000, 'tax' => 0, 'exempted_tax_amount' => 600_000, 'additional_tax_amount' => 1],
            ['salary_from' => 1_200_001, 'salary_to' => 2_200_000, 'tax' => 6_000, 'exempted_tax_amount' => 1_200_000, 'additional_tax_amount' => 11],
            ['salary_from' => 2_200_001, 'salary_to' => 3_200_000, 'tax' => 116_000, 'exempted_tax_amount' => 2_200_000, 'additional_tax_amount' => 23],
            ['salary_from' => 3_200_001, 'salary_to' => 4_100_000, 'tax' => 346_000, 'exempted_tax_amount' => 3_200_000, 'additional_tax_amount' => 30],
            ['salary_from' => 4_100_001, 'salary_to' => 999_999_999, 'tax' => 616_000, 'exempted_tax_amount' => 4_100_000, 'additional_tax_amount' => 35],
        ];
    }

    public function up(): void
    {
        foreach ([2025, 2026] as $year) {
            DB::table('taxes')->where('tax_year', $year)->delete();
            foreach ($this->slabs() as $row) {
                DB::table('taxes')->insert([
                    'tax_year' => $year,
                    'salary_from' => $row['salary_from'],
                    'salary_to' => $row['salary_to'],
                    'tax' => $row['tax'],
                    'exempted_tax_amount' => $row['exempted_tax_amount'],
                    'additional_tax_amount' => $row['additional_tax_amount'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        DB::table('taxes')->whereIn('tax_year', [2025, 2026])->delete();
    }
};
