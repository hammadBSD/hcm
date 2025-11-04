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
        Schema::table('employees', function (Blueprint $table) {
            // Add nullable foreign keys for organization structure
            $table->foreignId('department_id')->nullable()->after('department')->constrained('departments')->onDelete('set null');
            $table->foreignId('designation_id')->nullable()->after('designation')->constrained('designations')->onDelete('set null');
            $table->foreignId('group_id')->nullable()->after('status')->constrained('groups')->onDelete('set null');
            $table->foreignId('employment_type_id')->nullable()->after('role')->constrained('employment_types')->onDelete('set null');
            $table->foreignId('employment_status_id')->nullable()->after('status')->constrained('employment_statuses')->onDelete('set null');
            
            // Add nullable foreign keys for location (from employee_additional_info or can be added here)
            $table->foreignId('country_id')->nullable()->after('punch_code')->constrained('countries')->onDelete('set null');
            $table->foreignId('province_id')->nullable()->after('country_id')->constrained('provinces')->onDelete('set null');
            
            // Add indexes for performance
            $table->index('department_id');
            $table->index('designation_id');
            $table->index('group_id');
            $table->index('employment_type_id');
            $table->index('employment_status_id');
            $table->index('country_id');
            $table->index('province_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropForeign(['designation_id']);
            $table->dropForeign(['group_id']);
            $table->dropForeign(['employment_type_id']);
            $table->dropForeign(['employment_status_id']);
            $table->dropForeign(['country_id']);
            $table->dropForeign(['province_id']);
            
            $table->dropColumn([
                'department_id',
                'designation_id',
                'group_id',
                'employment_type_id',
                'employment_status_id',
                'country_id',
                'province_id',
            ]);
        });
    }
};

