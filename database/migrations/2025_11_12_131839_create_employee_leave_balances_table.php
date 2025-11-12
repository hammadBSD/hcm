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
        Schema::create('employee_leave_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('leave_type_id')->constrained('leave_types')->cascadeOnDelete();
            $table->foreignId('leave_policy_id')->nullable()->constrained('leave_policies')->nullOnDelete();
            $table->decimal('entitled', 8, 2)->default(0);
            $table->decimal('carried_forward', 8, 2)->default(0);
            $table->decimal('manual_adjustment', 8, 2)->default(0);
            $table->decimal('used', 8, 2)->default(0);
            $table->decimal('pending', 8, 2)->default(0);
            $table->decimal('balance', 8, 2)->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['employee_id', 'leave_type_id'], 'employee_leave_balances_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_leave_balances');
    }
};
