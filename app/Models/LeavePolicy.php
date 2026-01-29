<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeavePolicy extends Model
{
    use HasFactory;

    protected $fillable = [
        'leave_type_id',
        'effective_from',
        'effective_to',
        'accrual_frequency',
        'base_quota',
        'quota_unit',
        'auto_assign',
        'probation_wait_days',
        'prorate_on_joining',
        'carry_forward_enabled',
        'carry_forward_cap',
        'carry_forward_expiry_days',
        'encashment_enabled',
        'encashment_cap',
        'allow_negative_balance',
        'is_active',
        'assign_only_to_permanent',
        'eligibility_rules',
        'additional_settings',
    ];

    protected $casts = [
        'effective_from' => 'date',
        'effective_to' => 'date',
        'base_quota' => 'float',
        'auto_assign' => 'boolean',
        'prorate_on_joining' => 'boolean',
        'carry_forward_enabled' => 'boolean',
        'carry_forward_cap' => 'float',
        'encashment_enabled' => 'boolean',
        'encashment_cap' => 'float',
        'allow_negative_balance' => 'boolean',
        'is_active' => 'boolean',
        'assign_only_to_permanent' => 'boolean',
        'eligibility_rules' => 'array',
        'additional_settings' => 'array',
    ];

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function tiers()
    {
        return $this->hasMany(LeavePolicyTier::class);
    }

    public function balances()
    {
        return $this->hasMany(EmployeeLeaveBalance::class);
    }

    public function transactions()
    {
        return $this->hasMany(LeaveBalanceTransaction::class);
    }
}
