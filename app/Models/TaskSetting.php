<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'enabled',
        'lock_after_shift',
        'mandatory',
        'split_periods',
        'lock_grace_period_minutes',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'lock_after_shift' => 'boolean',
        'mandatory' => 'boolean',
        'split_periods' => 'boolean',
        'lock_grace_period_minutes' => 'integer',
    ];

    /**
     * Get the singleton task setting instance
     */
    public static function getInstance(): self
    {
        return static::firstOrCreate([], [
            'enabled' => true,
            'lock_after_shift' => false,
            'mandatory' => false,
            'split_periods' => false,
            'lock_grace_period_minutes' => 0,
        ]);
    }
}
