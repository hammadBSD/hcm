<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Holiday;

class HolidayEligibilityService
{
    public function appliesToEmployee(Holiday $holiday, Employee $employee): bool
    {
        if ($holiday->excludedEmployees->contains('id', $employee->id)) {
            return false;
        }

        if ($holiday->scope_type === 'all_employees') {
            return true;
        }

        if ($holiday->scope_type === 'department') {
            if ($employee->department_id && $holiday->departments->contains('id', $employee->department_id)) {
                return true;
            }

            return $holiday->employees->contains('id', $employee->id);
        }

        if ($holiday->scope_type === 'role') {
            $employee->loadMissing('user.roles');
            $userRoles = $employee->relationLoaded('user') && $employee->user
                ? $employee->user->roles->pluck('id')->toArray()
                : [];

            if ($userRoles !== [] && ! empty(array_intersect($userRoles, $holiday->roles->pluck('id')->toArray()))) {
                return true;
            }

            return $holiday->employees->contains('id', $employee->id);
        }

        if ($holiday->scope_type === 'group') {
            if ($employee->group_id && $holiday->groups->contains('id', $employee->group_id)) {
                return true;
            }

            return $holiday->employees->contains('id', $employee->id);
        }

        if ($holiday->scope_type === 'employee') {
            return $holiday->employees->contains('id', $employee->id);
        }

        return false;
    }
}
