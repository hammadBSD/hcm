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
        Schema::create('employee_department_changes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->foreignId('old_department_id')->nullable()->constrained('departments')->onDelete('set null');
            $table->foreignId('new_department_id')->constrained('departments')->onDelete('restrict');
            $table->foreignId('changed_by')->constrained('users')->onDelete('restrict');
            $table->timestamp('changed_at');
            $table->text('notes')->nullable();
            $table->enum('reason', ['transfer', 'promotion', 'reorganization', 'other'])->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_department_changes');
    }
};
