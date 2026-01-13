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
        Schema::create('task_template_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_template_id')->constrained('task_templates')->onDelete('cascade');
            $table->string('assignable_type'); // Group, Department, Role (Spatie), Employee
            $table->unsignedBigInteger('assignable_id');
            $table->timestamps();
            
            $table->index(['assignable_type', 'assignable_id']);
            $table->index('task_template_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_template_assignments');
    }
};
