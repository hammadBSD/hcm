<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payroll_run_lines', function (Blueprint $table) {
            $table->decimal('leave_paid', 8, 2)->default(0)->after('days_present');
            $table->decimal('leave_unpaid', 8, 2)->default(0)->after('leave_paid');
            $table->decimal('leave_lwp', 8, 2)->default(0)->after('leave_unpaid');
            $table->string('total_break_time', 20)->nullable()->after('late_days');
            $table->string('total_hours_worked', 20)->nullable()->after('total_break_time');
            $table->string('monthly_expected_hours', 20)->nullable()->after('total_hours_worked');
            $table->string('short_excess_hours', 20)->nullable()->after('monthly_expected_hours');
        });
    }

    public function down(): void
    {
        Schema::table('payroll_run_lines', function (Blueprint $table) {
            $table->dropColumn([
                'leave_paid', 'leave_unpaid', 'leave_lwp',
                'total_break_time', 'total_hours_worked', 'monthly_expected_hours', 'short_excess_hours',
            ]);
        });
    }
};
