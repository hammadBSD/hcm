<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'auto_assign_enabled',
        'allow_manual_overrides',
        'auto_approve_requests',
        'default_accrual_frequency',
        'default_probation_wait_days',
        'default_prorate_on_joining',
        'carry_forward_enabled',
        'carry_forward_cap',
        'carry_forward_expiry_days',
        'encashment_enabled',
        'encashment_cap',
        'working_day_rules',
        'notification_preferences',
    ];

    protected $casts = [
        'auto_assign_enabled' => 'boolean',
        'allow_manual_overrides' => 'boolean',
        'auto_approve_requests' => 'boolean',
        'default_probation_wait_days' => 'integer',
        'default_prorate_on_joining' => 'boolean',
        'carry_forward_enabled' => 'boolean',
        'carry_forward_cap' => 'float',
        'carry_forward_expiry_days' => 'integer',
        'encashment_enabled' => 'boolean',
        'encashment_cap' => 'float',
        'working_day_rules' => 'array',
        'notification_preferences' => 'array',
    ];
}
