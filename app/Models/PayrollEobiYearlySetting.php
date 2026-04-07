<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollEobiYearlySetting extends Model
{
    protected $table = 'payroll_eobi_yearly_settings';

    protected $fillable = [
        'year',
        'date_from',
        'date_to',
        'monthly_amount',
        'created_by',
    ];

    protected $casts = [
        'year' => 'integer',
        'date_from' => 'date',
        'date_to' => 'date',
        'monthly_amount' => 'decimal:2',
    ];

    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function monthlyAmountForMonth(string $yearMonth): float
    {
        $monthStart = Carbon::createFromFormat('Y-m', $yearMonth)->startOfMonth();
        $monthEnd = Carbon::createFromFormat('Y-m', $yearMonth)->endOfMonth();

        $record = static::query()
            ->whereDate('date_from', '<=', $monthEnd)
            ->where(function ($q) use ($monthStart) {
                $q->whereNull('date_to')
                    ->orWhereDate('date_to', '>=', $monthStart);
            })
            ->orderBy('date_from', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        return $record ? round((float) $record->monthly_amount, 2) : 0.0;
    }
}
