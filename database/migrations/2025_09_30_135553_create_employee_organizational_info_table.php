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
        Schema::create('employee_organizational_info', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            
            // Company Information Section
            $table->string('previous_company_name')->nullable();
            $table->string('previous_designation')->nullable();
            $table->date('from_date')->nullable();
            $table->date('to_date')->nullable();
            $table->text('reason_for_leaving')->nullable();
            
            // Employment Details Section
            $table->date('joining_date')->nullable();
            $table->date('confirmation_date')->nullable();
            $table->integer('expected_confirmation_days')->nullable();
            $table->date('contract_start_date')->nullable();
            $table->date('contract_end_date')->nullable();
            $table->date('resign_date')->nullable();
            $table->date('leaving_date')->nullable();
            $table->text('leaving_reason')->nullable();
            
            // Organizational Section
            $table->string('vendor')->nullable();
            $table->string('division')->nullable();
            $table->string('grade')->nullable();
            $table->string('employee_status')->nullable();
            $table->string('employee_group')->nullable();
            $table->string('cost_center')->nullable();
            $table->string('region')->nullable();
            $table->string('gl_class')->nullable();
            $table->string('position_type')->nullable();
            $table->string('position')->nullable();
            $table->string('station')->nullable();
            $table->string('sub_department')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_organizational_info');
    }
};