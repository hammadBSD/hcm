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
        Schema::create('recruitment_candidates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_post_id')->constrained('recruitment_job_posts')->onDelete('cascade');
            $table->foreignId('pipeline_stage_id')->constrained('recruitment_pipeline_stages')->onDelete('restrict');
            $table->integer('applicant_number')->nullable(); // Auto-increment per job post
            
            // Basic Information
            $table->string('first_name');
            $table->string('last_name');
            $table->date('date_of_birth')->nullable();
            $table->text('description')->nullable();
            
            // Contact Information
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('linkedin_url')->nullable();
            
            // Professional Details
            $table->enum('position', ['full-time', 'part-time', 'half-day', 'contract', 'freelance'])->nullable();
            $table->foreignId('designation_id')->nullable()->constrained('designations')->onDelete('set null');
            $table->decimal('experience', 4, 1)->nullable(); // Years of experience
            $table->enum('source', ['linkedin', 'glassdoor', 'indeed', 'company-website', 'referral', 'job-board', 'self', 'recruitment-agency', 'other'])->nullable();
            
            // Location & Address
            $table->text('current_address')->nullable();
            $table->string('city')->nullable();
            $table->foreignId('country_id')->nullable()->constrained('countries')->onDelete('set null');
            $table->foreignId('province_id')->nullable()->constrained('provinces')->onDelete('set null');
            
            // Current Employment
            $table->string('current_company')->nullable();
            
            // Additional Information
            $table->string('notice_period')->nullable();
            $table->decimal('expected_salary', 15, 2)->nullable();
            $table->date('availability_date')->nullable();
            
            // Rating
            $table->decimal('rating', 2, 1)->nullable()->default(0); // 0.0 to 5.0
            
            // Status
            $table->enum('status', ['active', 'hired', 'rejected', 'withdrawn'])->default('active');
            $table->boolean('is_hired')->default(false);
            $table->foreignId('hired_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('hired_at')->nullable();
            
            // Metadata
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('job_post_id');
            $table->index('pipeline_stage_id');
            $table->index('status');
            $table->index('is_hired');
            $table->index(['job_post_id', 'applicant_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recruitment_candidates');
    }
};
