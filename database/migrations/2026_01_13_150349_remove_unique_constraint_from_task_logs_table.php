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
        Schema::table('task_logs', function (Blueprint $table) {
            // Drop the unique constraint to allow multiple logs per day
            $table->dropUnique('unique_task_log');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('task_logs', function (Blueprint $table) {
            // Re-add the unique constraint
            $table->unique(['employee_id', 'log_date', 'period'], 'unique_task_log');
        });
    }
};
