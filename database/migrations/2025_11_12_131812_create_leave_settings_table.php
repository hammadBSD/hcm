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
        Schema::create('leave_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('auto_assign_enabled')->default(true);
            $table->boolean('allow_manual_overrides')->default(true);
            $table->enum('default_accrual_frequency', ['monthly', 'quarterly', 'semi-annual', 'annual'])->default('annual');
            $table->unsignedInteger('default_probation_wait_days')->default(0);
            $table->boolean('default_prorate_on_joining')->default(true);
            $table->boolean('carry_forward_enabled')->default(false);
            $table->decimal('carry_forward_cap', 8, 2)->nullable();
            $table->unsignedInteger('carry_forward_expiry_days')->nullable();
            $table->boolean('encashment_enabled')->default(false);
            $table->decimal('encashment_cap', 8, 2)->nullable();
            $table->json('working_day_rules')->nullable();
            $table->json('notification_preferences')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_settings');
    }
};
