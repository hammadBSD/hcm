<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payroll_eobi_yearly_settings', function (Blueprint $table) {
            $table->date('date_from')->nullable()->after('year');
            $table->date('date_to')->nullable()->after('date_from');
        });

        DB::table('payroll_eobi_yearly_settings')
            ->whereNull('date_from')
            ->orderBy('id')
            ->get(['id', 'year'])
            ->each(function ($row) {
                $year = (int) ($row->year ?: date('Y'));
                DB::table('payroll_eobi_yearly_settings')
                    ->where('id', $row->id)
                    ->update(['date_from' => sprintf('%04d-01-01', $year)]);
            });

        Schema::table('payroll_eobi_yearly_settings', function (Blueprint $table) {
            $table->dropUnique('payroll_eobi_yearly_settings_year_unique');
        });
    }

    public function down(): void
    {
        Schema::table('payroll_eobi_yearly_settings', function (Blueprint $table) {
            $table->unique('year');
            $table->dropColumn(['date_from', 'date_to']);
        });
    }
};
