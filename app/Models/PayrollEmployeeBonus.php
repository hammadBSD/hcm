<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollEmployeeBonus extends Model
{
    protected $fillable = [
        'year_month',
        'employee_id',
        'bonus_type',
        'amount',
        'description',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
