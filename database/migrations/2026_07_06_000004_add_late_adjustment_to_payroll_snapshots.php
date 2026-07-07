<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('payroll_late_deduction_adjustments', 'waived_deduction_late_days')) {
            Schema::table('payroll_late_deduction_adjustments', function (Blueprint $table) {
                $table->unsignedSmallInteger('waived_deduction_late_days')->default(0)->after('employee_id');
            });
        }

        if (Schema::hasColumn('payroll_late_deduction_adjustments', 'adjusted_deduction_late_days')) {
            DB::table('payroll_late_deduction_adjustments')->update([
                'waived_deduction_late_days' => DB::raw('adjusted_deduction_late_days'),
            ]);

            Schema::table('payroll_late_deduction_adjustments', function (Blueprint $table) {
                $table->dropColumn('adjusted_deduction_late_days');
            });
        }

        Schema::table('payroll_month_snapshots', function (Blueprint $table) {
            if (!Schema::hasColumn('payroll_month_snapshots', 'late_adjustment_days')) {
                $table->unsignedSmallInteger('late_adjustment_days')->default(0)->after('deduction_late_days');
            }
            if (!Schema::hasColumn('payroll_month_snapshots', 'calculated_deduction_late_days')) {
                $table->unsignedSmallInteger('calculated_deduction_late_days')->default(0)->after('late_adjustment_days');
            }
        });
    }

    public function down(): void
    {
        Schema::table('payroll_month_snapshots', function (Blueprint $table) {
            if (Schema::hasColumn('payroll_month_snapshots', 'calculated_deduction_late_days')) {
                $table->dropColumn('calculated_deduction_late_days');
            }
            if (Schema::hasColumn('payroll_month_snapshots', 'late_adjustment_days')) {
                $table->dropColumn('late_adjustment_days');
            }
        });

        if (!Schema::hasColumn('payroll_late_deduction_adjustments', 'adjusted_deduction_late_days')) {
            Schema::table('payroll_late_deduction_adjustments', function (Blueprint $table) {
                $table->unsignedSmallInteger('adjusted_deduction_late_days')->default(0)->after('employee_id');
            });

            DB::table('payroll_late_deduction_adjustments')->update([
                'adjusted_deduction_late_days' => DB::raw('waived_deduction_late_days'),
            ]);
        }

        if (Schema::hasColumn('payroll_late_deduction_adjustments', 'waived_deduction_late_days')) {
            Schema::table('payroll_late_deduction_adjustments', function (Blueprint $table) {
                $table->dropColumn('waived_deduction_late_days');
            });
        }
    }
};
