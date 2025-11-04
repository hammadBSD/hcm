<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Holiday extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'date',
        'type',
        'country_id',
        'province_id',
        'is_recurring',
        'status',
    ];

    protected $casts = [
        'date' => 'date',
        'is_recurring' => 'boolean',
        'status' => 'string',
    ];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }
}
