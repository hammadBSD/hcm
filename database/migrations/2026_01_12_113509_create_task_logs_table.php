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
        Schema::create('task_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->foreignId('task_template_id')->constrained('task_templates')->onDelete('cascade');
            $table->date('log_date');
            $table->enum('period', ['first_half', 'second_half', 'full_day'])->default('full_day');
            $table->json('data'); // Field values stored as JSON
            $table->boolean('is_locked')->default(false);
            $table->timestamp('locked_at')->nullable();
            $table->timestamps();
            
            $table->index(['employee_id', 'log_date', 'period']);
            $table->index('task_template_id');
            $table->index('log_date');
            $table->unique(['employee_id', 'task_template_id', 'log_date', 'period'], 'unique_task_log');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_logs');
    }
};
