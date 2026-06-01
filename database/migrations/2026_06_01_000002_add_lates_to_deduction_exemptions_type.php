<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE deduction_exemptions MODIFY COLUMN exemption_type VARCHAR(50) NOT NULL DEFAULT 'all'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE deduction_exemptions MODIFY COLUMN exemption_type ENUM('absent_days', 'hourly_deduction_short_hours', 'all') NOT NULL DEFAULT 'all'");
    }
};
