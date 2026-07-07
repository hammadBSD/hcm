<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_month_snapshots', function (Blueprint $table) {
            $table->id();
            $table->char('year_month', 7);
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();

            $table->string('department')->nullable();
            $table->string('designation')->nullable();
            $table->string('region')->nullable();
            $table->string('shift')->nullable();
            $table->string('doj', 32)->nullable();
            $table->string('current_status', 32)->nullable();
            $table->string('reporting_manager')->nullable();
            $table->string('mcs')->nullable();
            $table->string('brands')->nullable();
            $table->string('employment_status')->nullable();
            $table->string('cnic')->nullable();
            $table->string('last_increment_date', 32)->nullable();
            $table->decimal('last_increment_amount', 12, 2)->default(0);
            $table->unsignedSmallInteger('months_since_increment')->default(0);
            $table->string('job_duration')->nullable();

            $table->unsignedSmallInteger('working_days')->default(0);
            $table->decimal('days_present', 8, 2)->default(0);
            $table->unsignedSmallInteger('extra_days')->default(0);
            $table->decimal('amount_extra_days', 12, 2)->default(0);
            $table->decimal('hourly_rate', 12, 2)->default(0);
            $table->decimal('daily_rate', 12, 2)->default(0);
            $table->decimal('hourly_deduction_amount', 12, 2)->default(0);
            $table->unsignedSmallInteger('deduction_late_days')->default(0);
            $table->decimal('deduction_late_amount', 12, 2)->default(0);

            $table->decimal('leave_paid', 8, 2)->default(0);
            $table->decimal('leaves_approved', 8, 2)->default(0);
            $table->decimal('leave_unpaid', 8, 2)->default(0);
            $table->decimal('leave_lwp', 8, 2)->default(0);
            $table->unsignedSmallInteger('absent')->default(0);
            $table->unsignedSmallInteger('holiday')->default(0);
            $table->decimal('total_absent_days', 8, 2)->default(0);
            $table->decimal('applied_leaves', 8, 2)->default(0);
            $table->decimal('leaves_unapproved', 8, 2)->default(0);
            $table->unsignedSmallInteger('late_days')->default(0);
            $table->string('total_break_time', 16)->default('0:00');
            $table->string('total_hours_worked', 16)->default('0:00');
            $table->string('monthly_expected_hours', 16)->default('0:00');
            $table->string('short_excess_hours', 16)->default('0:00');
            $table->string('salary_type')->nullable();

            $table->decimal('basic_salary', 12, 2)->default(0);
            $table->decimal('allowances', 12, 2)->default(0);
            $table->decimal('ot_hrs', 8, 2)->default(0);
            $table->decimal('ot_amt', 12, 2)->default(0);
            $table->decimal('gross_salary', 12, 2)->default(0);
            $table->decimal('bonus', 12, 2)->default(0);
            $table->decimal('epf_ee', 12, 2)->default(0);
            $table->decimal('epf_er', 12, 2)->default(0);
            $table->decimal('esic_ee', 12, 2)->default(0);
            $table->decimal('esic_er', 12, 2)->default(0);
            $table->decimal('tax', 12, 2)->default(0);
            $table->decimal('tax_adjustment', 12, 2)->default(0);
            $table->decimal('salary_adjustment', 12, 2)->default(0);
            $table->decimal('prof_tax', 12, 2)->default(0);
            $table->decimal('eobi', 12, 2)->default(0);
            $table->decimal('advance', 12, 2)->default(0);
            $table->decimal('loan', 12, 2)->default(0);
            $table->decimal('deduction_absent_days', 12, 2)->default(0);
            $table->decimal('other_deductions', 12, 2)->default(0);
            $table->decimal('total_deductions', 12, 2)->default(0);
            $table->decimal('net_salary_after_attendance', 12, 2)->default(0);
            $table->decimal('net_salary', 12, 2)->default(0);

            $table->string('bank_name')->nullable();
            $table->string('account_title')->nullable();
            $table->string('bank_account')->nullable();
            $table->string('transaction_type', 32)->nullable();
            $table->decimal('transaction_hold', 12, 2)->nullable();
            $table->decimal('transaction_interbank', 12, 2)->nullable();
            $table->decimal('transaction_ibft', 12, 2)->nullable();
            $table->decimal('transaction_cash', 12, 2)->nullable();
            $table->decimal('transaction_cheque', 12, 2)->nullable();
            $table->string('deductions_exempted', 64)->default('No');

            $table->timestamps();

            $table->unique(['year_month', 'employee_id']);
            $table->index('year_month');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_month_snapshots');
    }
};
