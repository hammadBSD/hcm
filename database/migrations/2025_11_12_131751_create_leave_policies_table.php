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
        Schema::create('leave_policies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('leave_type_id')->constrained('leave_types')->cascadeOnDelete();
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->enum('accrual_frequency', ['none', 'monthly', 'quarterly', 'semi-annual', 'annual'])->default('annual');
            $table->decimal('base_quota', 8, 2)->default(0);
            $table->enum('quota_unit', ['days', 'hours'])->default('days');
            $table->boolean('auto_assign')->default(true);
            $table->unsignedInteger('probation_wait_days')->default(0);
            $table->boolean('prorate_on_joining')->default(true);
            $table->boolean('carry_forward_enabled')->default(false);
            $table->decimal('carry_forward_cap', 8, 2)->nullable();
            $table->unsignedInteger('carry_forward_expiry_days')->nullable();
            $table->boolean('encashment_enabled')->default(false);
            $table->decimal('encashment_cap', 8, 2)->nullable();
            $table->boolean('allow_negative_balance')->default(false);
            $table->json('eligibility_rules')->nullable();
            $table->json('additional_settings')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_policies');
    }
};
