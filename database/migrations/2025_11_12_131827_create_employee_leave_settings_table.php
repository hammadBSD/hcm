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
        Schema::create('employee_leave_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('leave_type_id')->constrained('leave_types')->cascadeOnDelete();
            $table->boolean('auto_accrual_enabled')->default(true);
            $table->decimal('manual_quota', 8, 2)->nullable();
            $table->decimal('manual_increment', 8, 2)->nullable();
            $table->decimal('carry_forward_cap', 8, 2)->nullable();
            $table->boolean('encashment_enabled')->nullable();
            $table->json('additional_rules')->nullable();
            $table->timestamps();

            $table->unique(['employee_id', 'leave_type_id'], 'employee_leave_settings_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_leave_settings');
    }
};
