<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveBalanceTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'leave_type_id',
        'leave_policy_id',
        'related_request_id',
        'reference',
        'transaction_type',
        'amount',
        'balance_after',
        'notes',
        'meta',
        'performed_by',
        'transaction_date',
    ];

    protected $casts = [
        'amount' => 'float',
        'balance_after' => 'float',
        'meta' => 'array',
        'transaction_date' => 'datetime',
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

    public function performer()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
