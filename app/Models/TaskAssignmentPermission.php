<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Permission\Models\Role;

class TaskAssignmentPermission extends Model
{
    use HasFactory;

    protected $fillable = [
        'role_id',
        'can_assign_to',
    ];

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Get or create permission for a role
     */
    public static function getForRole(Role $role): self
    {
        return static::firstOrCreate(
            ['role_id' => $role->id],
            ['can_assign_to' => 'own_group']
        );
    }

    /**
     * Check if a user can assign tasks to a specific employee
     */
    public static function canAssignTo(User $user, Employee $targetEmployee): bool
    {
        // Super Admin can assign to anyone
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        // Users with tasks.view.all permission can assign to anyone
        if ($user->can('tasks.view.all')) {
            return true;
        }

        $userEmployee = $user->employee;
        if (!$userEmployee) {
            return false;
        }

        // Check permission-based assignment (new granular permissions)
        if ($user->can('tasks.create.role')) {
            // Check if target employee has the same role as user
            $targetUser = $targetEmployee->user;
            if ($targetUser) {
                $userRoleNames = $user->roles->pluck('name')->toArray();
                $targetRoleNames = $targetUser->roles->pluck('name')->toArray();
                if (!empty(array_intersect($userRoleNames, $targetRoleNames))) {
                    return true;
                }
            }
        }

        if ($user->can('tasks.create.department')) {
            // Check if target employee is in the same department
            if ($userEmployee->department_id && $targetEmployee->department_id === $userEmployee->department_id) {
                return true;
            }
        }

        if ($user->can('tasks.create.group')) {
            // Check if target employee is in the same group
            if ($userEmployee->group_id && $targetEmployee->group_id === $userEmployee->group_id) {
                return true;
            }
        }

        // Fallback to legacy role-based permission system
        $userRoles = $user->roles;
        if ($userRoles->isEmpty()) {
            return false;
        }

        // Check each role's permission
        foreach ($userRoles as $role) {
            $permission = static::getForRole($role);
            
            switch ($permission->can_assign_to) {
                case 'anyone':
                    return true;
                    
                case 'own_role':
                    // Check if target employee has the same role
                    $targetUser = $targetEmployee->user;
                    if ($targetUser && $targetUser->hasRole($role->name)) {
                        return true;
                    }
                    break;
                    
                case 'own_group':
                    // Check if target employee is in the same group
                    if ($userEmployee->group_id && $targetEmployee->group_id === $userEmployee->group_id) {
                        return true;
                    }
                    break;
            }
        }

        return false;
    }
}
