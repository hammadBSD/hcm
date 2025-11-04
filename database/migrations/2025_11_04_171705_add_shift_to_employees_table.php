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
            // Drop the old varchar 'shift' column if it exists
            if (Schema::hasColumn('employees', 'shift')) {
                $table->dropColumn('shift');
            }
            
            // Add the new foreign key 'shift_id' column if it doesn't exist
            if (!Schema::hasColumn('employees', 'shift_id')) {
                $table->foreignId('shift_id')->nullable()->after('employment_status_id')->constrained('shifts')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            // Drop the foreign key and column
            if (Schema::hasColumn('employees', 'shift_id')) {
                $table->dropConstrainedForeignId('shift_id');
            }
            
            // Re-add the old varchar column if needed (for rollback)
            if (!Schema::hasColumn('employees', 'shift')) {
                $table->string('shift')->nullable()->after('employment_status_id');
            }
        });
    }
};
