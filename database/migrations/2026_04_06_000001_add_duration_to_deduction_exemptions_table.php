<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('deduction_exemptions', function (Blueprint $table) {
            $table->string('duration', 16)->default('monthly')->after('year_month');
        });
    }

    public function down(): void
    {
        Schema::table('deduction_exemptions', function (Blueprint $table) {
            $table->dropColumn('duration');
        });
    }
};
