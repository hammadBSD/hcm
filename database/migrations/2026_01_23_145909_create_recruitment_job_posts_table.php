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
        Schema::create('recruitment_job_posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('department_id')->nullable()->constrained('departments')->onDelete('set null');
            $table->foreignId('designation_id')->nullable()->constrained('designations')->onDelete('set null');
            $table->enum('entry_level', ['intern', 'junior', 'mid-junior', 'mid-level', 'mid-senior', 'senior', 'team-lead', 'above'])->nullable();
            $table->enum('position_type', ['full-time', 'part-time', 'half-day', 'contract', 'freelance'])->default('full-time');
            $table->enum('work_type', ['remote', 'on-site', 'hybrid'])->default('remote');
            $table->enum('hiring_priority', ['low', 'medium', 'urgent', 'very-urgent'])->default('medium');
            $table->integer('number_of_positions')->default(1);
            $table->enum('status', ['draft', 'active', 'paused', 'closed', 'cancelled'])->default('draft');
            $table->string('location')->nullable();
            $table->decimal('budget', 15, 2)->nullable();
            $table->date('application_deadline')->nullable();
            $table->date('start_date')->nullable();
            $table->text('required_skills')->nullable();
            $table->text('benefits')->nullable();
            $table->foreignId('reporting_to_id')->nullable()->constrained('employees')->onDelete('set null');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->unsignedBigInteger('default_pipeline_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('status');
            $table->index('department_id');
            $table->index('created_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recruitment_job_posts');
    }
};
