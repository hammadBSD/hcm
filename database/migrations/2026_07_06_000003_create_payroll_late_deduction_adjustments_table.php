<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_late_deduction_adjustments', function (Blueprint $table) {
            $table->id();
            $table->string('year_month', 7)->index();
            $table->unsignedBigInteger('employee_id')->index();
            $table->unsignedSmallInteger('waived_deduction_late_days');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->unique(['year_month', 'employee_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_late_deduction_adjustments');
    }
};
