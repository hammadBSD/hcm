<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Loan extends Model
{
    use HasFactory;

    protected $table = 'loans';

    protected $fillable = [
        'employee_id',
        'loan_type',
        'loan_amount',
        'installment_amount',
        'total_installments',
        'remaining_installments',
        'loan_date',
        'repayment_start_month',
        'description',
        'status',
        'decision_comments',
        'requested_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'loan_amount' => 'decimal:2',
        'installment_amount' => 'decimal:2',
        'loan_date' => 'date',
        'repayment_start_month' => 'date',
        'approved_at' => 'datetime',
    ];

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_COMPLETED = 'completed';

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

    public function scenarioActions(): HasMany
    {
        return $this->hasMany(LoanScenarioAction::class)->orderBy('effective_month')->orderBy('id');
    }
}
