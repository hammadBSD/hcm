<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'title',
        'description',
        'assigned_to',
        'assigned_by',
        'due_date',
        'status',
        'frequency',
        'auto_assign',
        'parent_task_id',
        'template_employee_ids',
        'next_assign_date',
        'completed_at',
        'rejected_at',
        'rejection_reason',
        'completion_notes',
        'custom_fields',
        'custom_field_values',
    ];

    protected $casts = [
        'due_date' => 'date',
        'next_assign_date' => 'date',
        'completed_at' => 'datetime',
        'rejected_at' => 'datetime',
        'auto_assign' => 'boolean',
        'template_employee_ids' => 'array',
        'custom_fields' => 'array',
        'custom_field_values' => 'array',
    ];

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'assigned_to');
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function parentTask(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'parent_task_id');
    }

    public function childTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'parent_task_id');
    }

    /**
     * Mark task as completed
     */
    public function markAsCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => Carbon::now(),
            'rejected_at' => null,
            'rejection_reason' => null,
        ]);
    }

    /**
     * Mark task as rejected
     */
    public function markAsRejected(?string $reason = null): void
    {
        $this->update([
            'status' => 'rejected',
            'rejected_at' => Carbon::now(),
            'rejection_reason' => $reason,
            'completed_at' => null,
        ]);
    }

    /**
     * Reset task to pending
     */
    public function resetToPending(): void
    {
        $this->update([
            'status' => 'pending',
            'completed_at' => null,
            'rejected_at' => null,
            'rejection_reason' => null,
        ]);
    }

    /**
     * Check if task is overdue
     */
    public function isOverdue(): bool
    {
        if (!$this->due_date || $this->status !== 'pending') {
            return false;
        }

        return Carbon::now()->isAfter($this->due_date);
    }
}
