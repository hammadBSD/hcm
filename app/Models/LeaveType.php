<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeaveType extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'icon',
        'description',
        'requires_approval',
        'is_paid',
        'status',
        'metadata',
    ];

    protected $casts = [
        'requires_approval' => 'boolean',
        'is_paid' => 'boolean',
        'metadata' => 'array',
    ];

    public function policies()
    {
        return $this->hasMany(LeavePolicy::class);
    }

    public function employeeSettings()
    {
        return $this->hasMany(EmployeeLeaveSetting::class);
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
