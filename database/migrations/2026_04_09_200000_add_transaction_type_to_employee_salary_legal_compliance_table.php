<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_salary_legal_compliance', function (Blueprint $table) {
            $table->string('transaction_type', 32)->nullable()->after('branch_code');
        });
    }

    public function down(): void
    {
        Schema::table('employee_salary_legal_compliance', function (Blueprint $table) {
            $table->dropColumn('transaction_type');
        });
    }
};
