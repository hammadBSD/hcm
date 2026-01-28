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
        Schema::create('recruitment_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('restrict_applicant_access')->default(false); // Only creator can work on applicant
            $table->boolean('show_hire_button_last_stage_only')->default(true); // Show hire button only in last stage
            $table->boolean('auto_assign_applicant_number')->default(true); // Auto-assign applicant numbers
            $table->boolean('require_rating_before_move')->default(false); // Require rating before moving to next stage
            $table->boolean('notify_on_new_application')->default(true); // Send notifications on new applications
            $table->boolean('notify_on_stage_change')->default(true); // Send notifications on stage changes
            $table->boolean('allow_public_applications')->default(true); // Allow public job applications
            $table->integer('default_pipeline_id')->nullable(); // Default pipeline for new job posts
            $table->integer('application_deadline_reminder_days')->default(7); // Days before deadline to send reminder
            $table->boolean('auto_archive_rejected')->default(false); // Auto-archive rejected candidates
            $table->integer('archive_after_days')->default(90); // Archive candidates after X days
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recruitment_settings');
    }
};
