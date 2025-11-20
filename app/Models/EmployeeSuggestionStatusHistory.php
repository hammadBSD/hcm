<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeSuggestionStatusHistory extends Model
{
    use HasFactory;

    protected $table = 'employee_suggestion_status_history';

    protected $fillable = [
        'employee_suggestion_id',
        'status',
        'notes',
        'changed_by',
    ];

    public function employeeSuggestion(): BelongsTo
    {
        return $this->belongsTo(EmployeeSuggestion::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
