<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class EmployeeGroupHistory extends Model
{
    use HasFactory;

    protected $table = 'employee_group_history';

    protected $fillable = [
        'employee_id',
        'group_id',
        'previous_group_id',
        'assigned_by',
        'assigned_date',
        'end_date',
        'notes',
    ];

    protected $casts = [
        'assigned_date' => 'date',
        'end_date' => 'date',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function previousGroup(): BelongsTo
    {
        return $this->belongsTo(Group::class, 'previous_group_id');
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
