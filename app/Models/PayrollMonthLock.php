<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PayrollMonthLock extends Model
{
    protected $fillable = [
        'year_month',
        'locked_by',
        'locked_at',
    ];

    protected $casts = [
        'locked_at' => 'datetime',
    ];

    public function locker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'locked_by');
    }

    public function snapshots(): HasMany
    {
        return $this->hasMany(PayrollMonthSnapshot::class, 'year_month', 'year_month');
    }

    public static function isLocked(string $yearMonth): bool
    {
        return self::query()->where('year_month', $yearMonth)->exists();
    }
}
