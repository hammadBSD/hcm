<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Province extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'country_id',
        'status',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function holidays(): HasMany
    {
        return $this->hasMany(Holiday::class);
    }

    public function vendors(): HasMany
    {
        return $this->hasMany(Vendor::class);
    }
}
