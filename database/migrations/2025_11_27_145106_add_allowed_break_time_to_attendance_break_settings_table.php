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
        Schema::table('attendance_break_settings', function (Blueprint $table) {
            $table->unsignedInteger('allowed_break_time')->nullable()->after('break_notifications');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_break_settings', function (Blueprint $table) {
            $table->dropColumn('allowed_break_time');
        });
    }
};
