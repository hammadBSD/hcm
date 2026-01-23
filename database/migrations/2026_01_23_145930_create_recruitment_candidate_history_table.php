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
        Schema::create('recruitment_candidate_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')->constrained('recruitment_candidates')->onDelete('cascade');
            $table->string('field_name')->nullable(); // null means general change/action
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->string('action_type')->nullable(); // 'created', 'updated', 'rating_changed', 'hired', 'rejected', etc.
            $table->text('notes')->nullable();
            $table->foreignId('changed_by')->constrained('users')->onDelete('cascade');
            $table->timestamp('changed_at');
            $table->timestamps();
            
            $table->index('candidate_id');
            $table->index('changed_by');
            $table->index('changed_at');
            $table->index('action_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recruitment_candidate_history');
    }
};
