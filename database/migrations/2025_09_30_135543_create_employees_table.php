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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // General Info Tab Fields
            $table->string('prefix')->nullable();
            $table->string('employee_code')->nullable();
            $table->string('punch_code')->nullable();
            $table->string('mobile');
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string('father_name')->nullable();
            $table->string('reports_to')->nullable();
            $table->string('role')->nullable();
            $table->enum('manual_attendance', ['yes', 'no'])->default('no');
            $table->enum('status', ['active', 'inactive', 'on-leave'])->default('active');
            $table->string('department')->nullable();
            $table->string('designation')->nullable();
            $table->string('shift')->nullable();
            
            // Documents Section
            $table->string('document_type')->nullable();
            $table->string('document_number')->nullable();
            $table->date('issue_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('document_file')->nullable();
            $table->string('passport_no')->nullable();
            $table->string('visa_no')->nullable();
            $table->date('visa_expiry')->nullable();
            $table->date('passport_expiry')->nullable();
            
            $table->boolean('allow_employee_login')->default(false);
            $table->string('profile_picture')->nullable();
            
            // Emergency Contact
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_relation')->nullable();
            $table->string('emergency_phone')->nullable();
            $table->text('emergency_address')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};