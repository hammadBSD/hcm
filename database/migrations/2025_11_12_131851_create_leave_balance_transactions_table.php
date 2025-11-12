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
        Schema::create('leave_balance_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('leave_type_id')->constrained('leave_types')->cascadeOnDelete();
            $table->foreignId('leave_policy_id')->nullable()->constrained('leave_policies')->nullOnDelete();
            $table->unsignedBigInteger('related_request_id')->nullable();
            $table->string('reference')->nullable();
            $table->enum('transaction_type', [
                'accrual',
                'adjustment',
                'carry_forward',
                'encashment',
                'debit',
                'credit',
                'reset'
            ]);
            $table->decimal('amount', 8, 2);
            $table->decimal('balance_after', 8, 2)->nullable();
            $table->text('notes')->nullable();
            $table->json('meta')->nullable();
            $table->foreignId('performed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('transaction_date')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_balance_transactions');
    }
};
