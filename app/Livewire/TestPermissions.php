<?php

namespace App\Livewire;

use Livewire\Component;

class TestPermissions extends Component
{
    public function render()
    {
        $user = auth()->user();
        
        $permissionTests = [
            'employee.view' => $user->hasPermissionTo('employee.view'),
            'employee.create' => $user->hasPermissionTo('employee.create'),
            'employee.edit' => $user->hasPermissionTo('employee.edit'),
            'employee.delete' => $user->hasPermissionTo('employee.delete'),
            'payroll.view' => $user->hasPermissionTo('payroll.view'),
            'payroll.process' => $user->hasPermissionTo('payroll.process'),
            'timesheet.approve' => $user->hasPermissionTo('timesheet.approve'),
            'leave.approve' => $user->hasPermissionTo('leave.approve'),
            'performance.create' => $user->hasPermissionTo('performance.create'),
            'reports.generate' => $user->hasPermissionTo('reports.generate'),
        ];
        
        $roleTests = [
            'Super Admin' => $user->hasRole('Super Admin'),
            'HR Manager' => $user->hasRole('HR Manager'),
            'HR Staff' => $user->hasRole('HR Staff'),
            'Employee' => $user->hasRole('Employee'),
            'Department Manager' => $user->hasRole('Department Manager'),
        ];

        return view('livewire.test-permissions', [
            'user' => $user,
            'permissionTests' => $permissionTests,
            'roleTests' => $roleTests,
            'allRoles' => $user->roles->pluck('name'),
            'allPermissions' => $user->getAllPermissions()->pluck('name'),
        ]);
    }
}