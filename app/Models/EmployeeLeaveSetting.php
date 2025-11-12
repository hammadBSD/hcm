<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeLeaveSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'leave_type_id',
        'auto_accrual_enabled',
        'manual_quota',
        'manual_increment',
        'carry_forward_cap',
        'encashment_enabled',
        'additional_rules',
    ];

    protected $casts = [
        'auto_accrual_enabled' => 'boolean',
        'manual_quota' => 'float',
        'manual_increment' => 'float',
        'carry_forward_cap' => 'float',
        'encashment_enabled' => 'boolean',
        'additional_rules' => 'array',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class);
    }
}
