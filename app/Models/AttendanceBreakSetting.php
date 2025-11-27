<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceBreakSetting extends Model
{
    protected $fillable = [
        'enable_break_tracking',
        'show_in_attendance_grid',
        'break_notifications',
        'allowed_break_time',
        'use_breaks_in_payroll',
        'use_in_salary_deductions',
        'auto_deduct_breaks',
        'break_overtime_calculation',
        'mandatory_break_duration_enabled',
        'mandatory_break_duration_minutes',
        'metadata',
    ];

    protected $casts = [
        'enable_break_tracking' => 'bool',
        'show_in_attendance_grid' => 'bool',
        'break_notifications' => 'bool',
        'allowed_break_time' => 'integer',
        'use_breaks_in_payroll' => 'bool',
        'use_in_salary_deductions' => 'bool',
        'auto_deduct_breaks' => 'bool',
        'break_overtime_calculation' => 'bool',
        'mandatory_break_duration_enabled' => 'bool',
        'mandatory_break_duration_minutes' => 'integer',
        'metadata' => 'array',
    ];

    public static function current(): self
    {
        return static::query()->firstOrCreate([]);
    }
}
