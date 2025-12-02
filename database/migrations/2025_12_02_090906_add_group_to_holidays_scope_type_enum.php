<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modify the ENUM to include 'group'
        // MySQL doesn't support direct ENUM modification, so we use raw SQL
        DB::statement("ALTER TABLE holidays MODIFY COLUMN scope_type ENUM('all_employees', 'department', 'role', 'group', 'employee') DEFAULT 'all_employees'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original ENUM values (without 'group')
        DB::statement("ALTER TABLE holidays MODIFY COLUMN scope_type ENUM('all_employees', 'department', 'role', 'employee') DEFAULT 'all_employees'");
    }
};
