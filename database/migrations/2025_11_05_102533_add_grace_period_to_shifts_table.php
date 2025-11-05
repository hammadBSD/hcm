<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('shifts', function (Blueprint $table) {
            $table->integer('grace_period_late_in')->nullable()->after('time_to')->comment('Grace period in minutes for late check-in. Null uses global setting.');
            $table->integer('grace_period_early_out')->nullable()->after('grace_period_late_in')->comment('Grace period in minutes for early check-out. Null uses global setting.');
            $table->boolean('disable_grace_period')->default(false)->after('grace_period_early_out')->comment('If true, completely disables grace period for this shift (ignores both shift-specific and global settings)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shifts', function (Blueprint $table) {
            $table->dropColumn([
                'grace_period_late_in',
                'grace_period_early_out',
                'disable_grace_period',
            ]);
        });
    }
};
