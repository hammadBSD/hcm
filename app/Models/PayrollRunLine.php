<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollRunLine extends Model
{
    protected $table = 'payroll_run_lines';

    protected $fillable = [
        'payroll_run_id',
        'employee_id',
        'department',
        'designation',
        'working_days',
        'days_present',
        'leave_paid',
        'leave_unpaid',
        'leave_lwp',
        'absent',
        'holiday',
        'late_days',
        'total_break_time',
        'total_hours_worked',
        'monthly_expected_hours',
        'short_excess_hours',
        'basic_salary',
        'allowances',
        'bonus',
        'gross_salary',
        'tax',
        'eobi',
        'advance',
        'loan',
        'other_deductions',
        'total_deductions',
        'net_salary',
    ];

    protected $casts = [
        'basic_salary' => 'decimal:2',
        'allowances' => 'decimal:2',
        'bonus' => 'decimal:2',
        'gross_salary' => 'decimal:2',
        'tax' => 'decimal:2',
        'eobi' => 'decimal:2',
        'advance' => 'decimal:2',
        'loan' => 'decimal:2',
        'other_deductions' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'net_salary' => 'decimal:2',
        'days_present' => 'decimal:2',
        'leave_paid' => 'decimal:2',
        'leave_unpaid' => 'decimal:2',
        'leave_lwp' => 'decimal:2',
    ];

    public function payrollRun(): BelongsTo
    {
        return $this->belongsTo(PayrollRun::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
