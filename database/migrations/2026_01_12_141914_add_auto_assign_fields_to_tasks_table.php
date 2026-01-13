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
        Schema::table('tasks', function (Blueprint $table) {
            $table->string('name')->nullable()->after('id');
            $table->boolean('auto_assign')->default(false)->after('frequency');
            $table->foreignId('parent_task_id')->nullable()->constrained('tasks')->onDelete('cascade')->after('auto_assign');
            $table->json('template_employee_ids')->nullable()->after('parent_task_id');
            $table->date('next_assign_date')->nullable()->after('template_employee_ids');
            
            $table->index('parent_task_id');
            $table->index('auto_assign');
            $table->index('next_assign_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['parent_task_id']);
            $table->dropIndex(['parent_task_id']);
            $table->dropIndex(['auto_assign']);
            $table->dropIndex(['next_assign_date']);
            $table->dropColumn(['name', 'auto_assign', 'parent_task_id', 'template_employee_ids', 'next_assign_date']);
        });
    }
};
