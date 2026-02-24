<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payroll_settings', function (Blueprint $table) {
            $table->boolean('absent_deduction_use_formula')->default(true)->after('per_day_absent_deduction');
            $table->decimal('hours_per_day', 5, 2)->default(9)->after('short_hours_threshold');
        });
    }

    public function down(): void
    {
        Schema::table('payroll_settings', function (Blueprint $table) {
            $table->dropColumn(['absent_deduction_use_formula', 'hours_per_day']);
        });
    }
};
