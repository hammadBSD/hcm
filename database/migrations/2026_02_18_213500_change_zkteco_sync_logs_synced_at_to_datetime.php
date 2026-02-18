<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Use DATETIME so the stored value is exactly what we write (app timezone), not UTC.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE zkteco_sync_logs MODIFY synced_at DATETIME NOT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE zkteco_sync_logs MODIFY synced_at TIMESTAMP NOT NULL');
    }
};
