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
        Schema::table('exemption_days', function (Blueprint $table) {
            $table->foreignId('group_id')->nullable()->after('role_id')->constrained('groups')->nullOnDelete();
        });

        // Update the enum to include 'group'
        DB::statement("ALTER TABLE exemption_days MODIFY COLUMN scope_type ENUM('all', 'department', 'role', 'user', 'group') DEFAULT 'all'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exemption_days', function (Blueprint $table) {
            $table->dropForeign(['group_id']);
            $table->dropColumn('group_id');
        });

        // Revert the enum back to original
        DB::statement("ALTER TABLE exemption_days MODIFY COLUMN scope_type ENUM('all', 'department', 'role', 'user') DEFAULT 'all'");
    }
};
