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
        Schema::create('employee_increments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->unsignedSmallInteger('number_of_increments')->default(0);
            $table->date('increment_due_date')->nullable();
            $table->date('last_increment_date')->nullable();
            $table->decimal('increment_amount', 14, 2)->default(0);
            $table->decimal('gross_salary_after', 14, 2)->nullable();
            $table->decimal('basic_salary_after', 14, 2)->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('employee_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_increments');
    }
};
