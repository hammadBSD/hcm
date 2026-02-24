<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payroll_settings', function (Blueprint $table) {
            $table->id();
            $table->string('payroll_frequency', 32)->default('monthly');
            $table->unsignedTinyInteger('payroll_day')->default(1);
            $table->decimal('overtime_rate', 5, 2)->default(1.5);
            $table->decimal('allowance_percentage', 5, 2)->default(10);
            $table->decimal('tax_percentage', 5, 2)->default(15);
            $table->decimal('provident_fund_percentage', 5, 2)->default(5);
            $table->string('tax_calculation_method', 32)->default('percentage'); // 'percentage' or 'tax_slabs'
            $table->decimal('short_hours_threshold', 5, 2)->default(9); // hours; deduction starts when short hours exceed this
            $table->decimal('per_day_absent_deduction', 14, 2)->default(0);
            $table->decimal('short_hours_deduction_per_hour', 14, 2)->nullable(); // optional: deduction per hour when short above threshold
            $table->boolean('auto_process')->default(false);
            $table->boolean('email_payslips')->default(true);
            $table->boolean('backup_payroll')->default(true);
            $table->timestamps();
        });

        // Insert default row
        DB::table('payroll_settings')->insert([
            'payroll_frequency' => 'monthly',
            'payroll_day' => 1,
            'overtime_rate' => 1.5,
            'allowance_percentage' => 10,
            'tax_percentage' => 15,
            'provident_fund_percentage' => 5,
            'tax_calculation_method' => 'percentage',
            'short_hours_threshold' => 9,
            'per_day_absent_deduction' => 0,
            'short_hours_deduction_per_hour' => null,
            'auto_process' => false,
            'email_payslips' => true,
            'backup_payroll' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_settings');
    }
};
