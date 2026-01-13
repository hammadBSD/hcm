<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop the unique constraint first
        Schema::table('task_logs', function (Blueprint $table) {
            $table->dropUnique('unique_task_log');
        });
        
        // Drop the foreign key constraint
        Schema::table('task_logs', function (Blueprint $table) {
            $table->dropForeign(['task_template_id']);
        });
        
        // Make task_template_id nullable
        Schema::table('task_logs', function (Blueprint $table) {
            $table->foreignId('task_template_id')->nullable()->change();
        });
        
        // Re-add foreign key constraint (nullable)
        Schema::table('task_logs', function (Blueprint $table) {
            $table->foreign('task_template_id')->references('id')->on('task_templates')->onDelete('set null');
        });
        
        // Re-add unique constraint without task_template_id (since it can be null)
        Schema::table('task_logs', function (Blueprint $table) {
            $table->unique(['employee_id', 'log_date', 'period'], 'unique_task_log');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the unique constraint
        Schema::table('task_logs', function (Blueprint $table) {
            $table->dropUnique('unique_task_log');
        });
        
        // Drop the foreign key constraint
        Schema::table('task_logs', function (Blueprint $table) {
            $table->dropForeign(['task_template_id']);
        });
        
        // Make task_template_id required again
        Schema::table('task_logs', function (Blueprint $table) {
            $table->foreignId('task_template_id')->nullable(false)->change();
        });
        
        // Re-add foreign key constraint
        Schema::table('task_logs', function (Blueprint $table) {
            $table->foreign('task_template_id')->references('id')->on('task_templates')->onDelete('cascade');
        });
        
        // Re-add unique constraint with task_template_id
        Schema::table('task_logs', function (Blueprint $table) {
            $table->unique(['employee_id', 'task_template_id', 'log_date', 'period'], 'unique_task_log');
        });
    }
};
