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
        Schema::create('recruitment_job_post_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_post_id')->constrained('recruitment_job_posts')->onDelete('cascade');
            $table->string('field_name')->nullable(); // null means general change/action
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->string('action_type')->nullable(); // 'created', 'updated', 'status_changed', 'deleted', etc.
            $table->text('notes')->nullable();
            $table->foreignId('changed_by')->constrained('users')->onDelete('cascade');
            $table->timestamp('changed_at');
            $table->timestamps();
            
            $table->index('job_post_id');
            $table->index('changed_by');
            $table->index('changed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recruitment_job_post_history');
    }
};
