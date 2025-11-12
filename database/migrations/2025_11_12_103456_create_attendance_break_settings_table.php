<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('attendance_break_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('enable_break_tracking')->default(true);
            $table->boolean('show_in_attendance_grid')->default(true);
            $table->boolean('break_notifications')->default(true);
            $table->boolean('use_breaks_in_payroll')->default(false);
            $table->boolean('use_in_salary_deductions')->default(false);
            $table->boolean('auto_deduct_breaks')->default(false);
            $table->boolean('break_overtime_calculation')->default(false);
            $table->boolean('mandatory_break_duration_enabled')->default(false);
            $table->unsignedInteger('mandatory_break_duration_minutes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_break_settings');
    }
};
