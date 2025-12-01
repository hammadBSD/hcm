<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HolidayDay extends Model
{
    use HasFactory;

    protected $fillable = [
        'holiday_id',
        'day',
    ];

    protected $casts = [
        'day' => 'date',
    ];

    public function holiday(): BelongsTo
    {
        return $this->belongsTo(Holiday::class);
    }
}
