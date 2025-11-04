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
        Schema::table('device_attendances', function (Blueprint $table) {
            // Drop the existing unique constraint on punch_time alone
            // First, we need to drop the index if it exists
            $table->dropUnique(['punch_time']);
        });
        
        // Add composite unique constraint on (punch_code, device_ip, punch_time)
        // This allows multiple employees to have the same punch_time,
        // but prevents the same employee from having duplicate records
        // on the same device at the same time
        Schema::table('device_attendances', function (Blueprint $table) {
            $table->unique(['punch_code', 'device_ip', 'punch_time'], 'device_attendances_unique_punch_record');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('device_attendances', function (Blueprint $table) {
            // Drop the composite unique constraint
            $table->dropUnique('device_attendances_unique_punch_record');
        });
        
        // Restore the original unique constraint on punch_time alone
        Schema::table('device_attendances', function (Blueprint $table) {
            $table->unique('punch_time');
        });
    }
};
