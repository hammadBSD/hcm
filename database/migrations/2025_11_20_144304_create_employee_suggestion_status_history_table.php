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
        Schema::create('employee_suggestion_status_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_suggestion_id');
            $table->foreign('employee_suggestion_id', 'essh_suggestion_id_foreign')->references('id')->on('employee_suggestions')->onDelete('cascade');
            $table->enum('status', ['pending', 'in_progress', 'resolved', 'dismissed']);
            $table->text('notes')->nullable();
            $table->foreignId('changed_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_suggestion_status_history');
    }
};
