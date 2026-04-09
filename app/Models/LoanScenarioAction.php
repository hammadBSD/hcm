<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanScenarioAction extends Model
{
    protected $table = 'loan_scenario_actions';

    protected $fillable = [
        'loan_id',
        'scenario',
        'effective_month',
        'payload',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'effective_month' => 'date',
        'payload' => 'array',
    ];

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
