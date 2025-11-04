<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class Poll extends Model
{
    use HasFactory;

    protected $fillable = [
        'question',
        'options',
        'start_date',
        'end_date',
        'is_anonymous',
        'allow_multiple_choices',
        'created_by',
        'status',
    ];

    protected $casts = [
        'options' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
        'is_anonymous' => 'boolean',
        'allow_multiple_choices' => 'boolean',
        'status' => 'string',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
