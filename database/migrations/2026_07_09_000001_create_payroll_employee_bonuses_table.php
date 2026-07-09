<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_employee_bonuses', function (Blueprint $table) {
            $table->id();
            $table->string('year_month', 7)->index();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('bonus_type', 50);
            $table->decimal('amount', 14, 2);
            $table->text('description')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index(['year_month', 'employee_id']);
            $table->index(['year_month', 'bonus_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_employee_bonuses');
    }
};
