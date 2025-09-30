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
        Schema::create('employee_salary_legal_compliance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            
            // Salary Information Section
            $table->decimal('basic_salary', 10, 2)->nullable();
            $table->decimal('allowances', 10, 2)->nullable();
            $table->decimal('bonus', 10, 2)->nullable();
            $table->string('currency')->default('PKR')->nullable();
            $table->string('payment_frequency')->default('monthly')->nullable();
            $table->string('bank_account')->nullable();
            $table->string('account_title')->nullable();
            $table->string('bank')->nullable();
            $table->string('branch_code')->nullable();
            $table->string('tax_id')->nullable();
            $table->text('salary_notes')->nullable();
            
            // Legal/Compliance Section
            $table->string('eobi_registration_no')->nullable();
            $table->date('eobi_entry_date')->nullable();
            $table->string('social_security_no')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_salary_legal_compliance');
    }
};