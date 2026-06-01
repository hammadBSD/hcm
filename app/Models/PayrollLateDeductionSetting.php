<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollLateDeductionSetting extends Model
{
    protected $fillable = [
        'lates_per_day_deduction',
        'effective_from',
        'created_by',
    ];

    protected $casts = [
        'lates_per_day_deduction' => 'integer',
        'effective_from' => 'date',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function latestRule(): ?self
    {
        return self::query()
            ->orderByDesc('effective_from')
            ->orderByDesc('id')
            ->first();
    }

    /**
     * Rule in effect for a payroll month (Y-m): latest row with effective_from on or before month end.
     */
    public static function forPayrollMonth(string $yearMonth): ?self
    {
        if (! preg_match('/^\d{4}-\d{2}$/', $yearMonth)) {
            return null;
        }

        $monthEnd = Carbon::createFromFormat('Y-m', $yearMonth)->endOfMonth()->format('Y-m-d');

        return self::query()
            ->whereDate('effective_from', '<=', $monthEnd)
            ->orderByDesc('effective_from')
            ->orderByDesc('id')
            ->first();
    }
}
