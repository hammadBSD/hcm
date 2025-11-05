<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeDepartmentChange extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'old_department_id',
        'new_department_id',
        'changed_by',
        'changed_at',
        'notes',
        'reason',
    ];

    protected $casts = [
        'changed_at' => 'datetime',
    ];

    /**
     * Get the employee whose department was changed
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the old department
     */
    public function oldDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'old_department_id');
    }

    /**
     * Get the new department
     */
    public function newDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'new_department_id');
    }

    /**
     * Get the user who made the change
     */
    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
