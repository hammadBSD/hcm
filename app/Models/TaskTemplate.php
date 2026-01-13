<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Spatie\Permission\Models\Role;

class TaskTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'fields',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'fields' => 'array',
        'is_active' => 'boolean',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(TaskTemplateAssignment::class);
    }

    public function taskLogs(): HasMany
    {
        return $this->hasMany(TaskLog::class);
    }

    /**
     * Get template fields for a specific employee
     * This will resolve the template based on priority: Individual > Department > Group > Role
     */
    public static function getTemplateForEmployee(Employee $employee): ?self
    {
        // Priority 1: Individual employee assignment
        $template = static::whereHas('assignments', function ($query) use ($employee) {
            $query->where('assignable_type', Employee::class)
                  ->where('assignable_id', $employee->id);
        })->where('is_active', true)->first();

        if ($template) {
            return $template;
        }

        // Priority 2: Department assignment
        if ($employee->department_id) {
            $template = static::whereHas('assignments', function ($query) use ($employee) {
                $query->where('assignable_type', Department::class)
                      ->where('assignable_id', $employee->department_id);
            })->where('is_active', true)->first();

            if ($template) {
                return $template;
            }
        }

        // Priority 3: Group assignment
        if ($employee->group_id) {
            $template = static::whereHas('assignments', function ($query) use ($employee) {
                $query->where('assignable_type', Group::class)
                      ->where('assignable_id', $employee->group_id);
            })->where('is_active', true)->first();

            if ($template) {
                return $template;
            }
        }

        // Priority 4: Role assignment
        $user = $employee->user;
        if ($user) {
            $roles = $user->roles->pluck('id');
            if ($roles->isNotEmpty()) {
                $template = static::whereHas('assignments', function ($query) use ($roles) {
                    $query->where('assignable_type', Role::class)
                          ->whereIn('assignable_id', $roles);
                })->where('is_active', true)->first();

                if ($template) {
                    return $template;
                }
            }
        }

        return null;
    }

    /**
     * Check if template is assigned to a specific entity
     */
    public function isAssignedTo($assignable): bool
    {
        return $this->assignments()
            ->where('assignable_type', get_class($assignable))
            ->where('assignable_id', $assignable->id)
            ->exists();
    }
}
