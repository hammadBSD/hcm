<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tax year as range: start year/month, end year/month. Display as "Tax year 2025-26".
     */
    public function up(): void
    {
        Schema::table('taxes', function (Blueprint $table) {
            $table->unsignedSmallInteger('start_year')->nullable()->after('tax_year');
            $table->unsignedTinyInteger('start_month')->nullable()->after('start_year');
            $table->unsignedSmallInteger('end_year')->nullable()->after('start_month');
            $table->unsignedTinyInteger('end_month')->nullable()->after('end_year');
        });

        // Backfill: Pakistan FY July–June. tax_year 2026 → July 2025 - June 2026 (2025-26)
        \DB::table('taxes')->whereNotNull('tax_year')->update([
            'start_year' => \DB::raw('tax_year - 1'),
            'start_month' => 7,
            'end_year' => \DB::raw('tax_year'),
            'end_month' => 6,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('taxes', function (Blueprint $table) {
            $table->dropColumn(['start_year', 'start_month', 'end_year', 'end_month']);
        });
    }
};
