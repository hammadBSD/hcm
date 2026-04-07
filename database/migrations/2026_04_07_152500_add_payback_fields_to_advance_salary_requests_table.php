<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('advance_salary_requests', function (Blueprint $table) {
            $table->string('payback_transaction_type', 32)->nullable()->after('expected_payback_date');
            $table->unsignedSmallInteger('payback_months')->nullable()->after('payback_transaction_type');
            $table->string('payback_mode', 32)->nullable()->after('payback_months');
        });
    }

    public function down(): void
    {
        Schema::table('advance_salary_requests', function (Blueprint $table) {
            $table->dropColumn(['payback_transaction_type', 'payback_months', 'payback_mode']);
        });
    }
};
