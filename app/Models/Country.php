<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Country extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'phone_code',
        'status',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    public function provinces(): HasMany
    {
        return $this->hasMany(Province::class);
    }

    public function holidays(): HasMany
    {
        return $this->hasMany(Holiday::class);
    }

    public function banks(): HasMany
    {
        return $this->hasMany(Bank::class);
    }

    public function vendors(): HasMany
    {
        return $this->hasMany(Vendor::class);
    }
}
