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
        Schema::create('employee_additional_info', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            
            // Additional Info Tab Fields
            $table->date('date_of_birth')->nullable();
            $table->string('gender')->nullable();
            $table->string('marital_status')->nullable();
            $table->string('nationality')->nullable();
            $table->string('blood_group')->nullable();
            
            // Address Details
            $table->string('place_of_birth')->nullable();
            $table->string('religion')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->string('province')->nullable();
            $table->string('city')->nullable();
            $table->string('area')->nullable();
            $table->string('zip_code')->nullable();
            $table->string('family_code')->nullable();
            $table->text('address')->nullable();
            
            // Qualifications
            $table->string('degree')->nullable();
            $table->string('institute')->nullable();
            $table->string('passing_year')->nullable();
            $table->string('grade')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_additional_info');
    }
};