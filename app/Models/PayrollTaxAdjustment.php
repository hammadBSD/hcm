<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollTaxAdjustment extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'adjusted_tax_amount',
        'effective_from',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'adjusted_tax_amount' => 'decimal:2',
        'effective_from' => 'date',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
