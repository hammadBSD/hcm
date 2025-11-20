<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeSuggestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'type',
        'complaint_type',
        'message',
        'status',
        'admin_response',
        'responded_by',
        'responded_at',
    ];

    protected $casts = [
        'responded_at' => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function respondedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responded_by');
    }

    public function statusHistory()
    {
        return $this->hasMany(EmployeeSuggestionStatusHistory::class)->orderBy('created_at', 'desc');
    }
}
