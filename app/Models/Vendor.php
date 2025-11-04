<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vendor extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'contact_person',
        'contact_email',
        'contact_phone',
        'address',
        'country_id',
        'province_id',
        'city',
        'zip_code',
        'tax_id',
        'notes',
        'status',
    ];

    protected $casts = [
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
