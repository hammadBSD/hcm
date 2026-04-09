<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('employee_salary_legal_compliance')
            ->whereNull('transaction_type')
            ->orWhere('transaction_type', '')
            ->update(['transaction_type' => 'interbank']);

        DB::statement("
            ALTER TABLE employee_salary_legal_compliance
            MODIFY transaction_type VARCHAR(32) NULL DEFAULT 'interbank'
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE employee_salary_legal_compliance
            MODIFY transaction_type VARCHAR(32) NULL DEFAULT NULL
        ");
    }
};
