<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_increments', function (Blueprint $table) {
            $table->boolean('for_history')->default(false)->after('basic_salary_after');
        });
    }

    public function down(): void
    {
        Schema::table('employee_increments', function (Blueprint $table) {
            $table->dropColumn('for_history');
        });
    }
};
