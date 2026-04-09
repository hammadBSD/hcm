<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->date('repayment_start_month')->nullable()->after('loan_date');
        });

        DB::statement("
            UPDATE loans
            SET repayment_start_month = DATE_FORMAT(COALESCE(loan_date, created_at), '%Y-%m-01')
            WHERE repayment_start_month IS NULL
        ");
    }

    public function down(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->dropColumn('repayment_start_month');
        });
    }
};
