<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_salary_legal_compliance', function (Blueprint $table) {
            $table->boolean('eobi_enabled')->default(false)->after('social_security_no');
        });
    }

    public function down(): void
    {
        Schema::table('employee_salary_legal_compliance', function (Blueprint $table) {
            $table->dropColumn('eobi_enabled');
        });
    }
};
