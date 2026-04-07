<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdvanceSalaryRequest extends Model
{
    use HasFactory;

    protected $table = 'advance_salary_requests';

    protected $fillable = [
        'employee_id',
        'amount',
        'reason',
        'expected_payback_date',
        'payback_transaction_type',
        'payback_months',
        'payback_mode',
        'expected_receiving_date',
        'received_amount',
        'received_at',
        'status',
        'requested_by',
        'approved_by',
        'approved_at',
        'confirmed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'expected_payback_date' => 'date',
        'payback_months' => 'integer',
        'expected_receiving_date' => 'date',
        'received_amount' => 'decimal:2',
        'received_at' => 'datetime',
        'approved_at' => 'datetime',
        'confirmed_at' => 'datetime',
    ];

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function requestedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }
}
