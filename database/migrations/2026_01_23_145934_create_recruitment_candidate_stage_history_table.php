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
        Schema::create('recruitment_candidate_stage_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')->constrained('recruitment_candidates')->onDelete('cascade');
            $table->foreignId('from_stage_id')->nullable()->constrained('recruitment_pipeline_stages')->onDelete('set null');
            $table->foreignId('to_stage_id')->constrained('recruitment_pipeline_stages')->onDelete('restrict');
            $table->text('notes')->nullable();
            $table->foreignId('moved_by')->constrained('users')->onDelete('cascade');
            $table->timestamp('moved_at');
            $table->timestamps();
            
            $table->index('candidate_id');
            $table->index('moved_by');
            $table->index('moved_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recruitment_candidate_stage_history');
    }
};
