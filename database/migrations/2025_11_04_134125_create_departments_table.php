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
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('code')->unique()->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('departments')->onDelete('set null');
            $table->foreignId('group_id')->nullable()->constrained('groups')->onDelete('set null');
            $table->foreignId('department_head_id')->nullable()->constrained('employees', 'id')->onDelete('set null');
            $table->text('description')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
            
            $table->index('code');
            $table->index('parent_id');
            $table->index('group_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};
