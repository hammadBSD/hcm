<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeLeaveBalance extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'leave_type_id',
        'leave_policy_id',
        'entitled',
        'carried_forward',
        'manual_adjustment',
        'used',
        'pending',
        'balance',
        'metadata',
    ];

    protected $casts = [
        'entitled' => 'float',
        'carried_forward' => 'float',
        'manual_adjustment' => 'float',
        'used' => 'float',
        'pending' => 'float',
        'balance' => 'float',
        'metadata' => 'array',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function policy()
    {
        return $this->belongsTo(LeavePolicy::class, 'leave_policy_id');
    }
}

