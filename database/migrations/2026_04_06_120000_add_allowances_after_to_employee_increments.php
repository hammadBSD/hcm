<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Snapshot of allowances after each applied increment (with 60/40 split on new records).
     * Null = legacy row (entire increment_amount was applied to basic only).
     */
    public function up(): void
    {
        Schema::table('employee_increments', function (Blueprint $table) {
            $table->decimal('allowances_after', 14, 2)->nullable()->after('basic_salary_after');
        });
    }

    public function down(): void
    {
        Schema::table('employee_increments', function (Blueprint $table) {
            $table->dropColumn('allowances_after');
        });
    }
};
