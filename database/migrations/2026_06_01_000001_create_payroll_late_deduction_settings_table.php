<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_late_deduction_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('lates_per_day_deduction');
            $table->date('effective_from');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['effective_from', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_late_deduction_settings');
    }
};
