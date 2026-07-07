<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollLateDeductionAdjustment extends Model
{
    protected $fillable = [
        'year_month',
        'employee_id',
        'waived_deduction_late_days',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'waived_deduction_late_days' => 'integer',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
