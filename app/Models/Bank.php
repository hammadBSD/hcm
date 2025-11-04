<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Bank extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'swift_code',
        'country_id',
        'contact_person',
        'contact_email',
        'contact_phone',
        'address',
        'status',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }
}
