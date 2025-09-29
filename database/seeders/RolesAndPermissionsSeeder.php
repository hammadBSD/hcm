<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // Employee Management
            'employee.view',
            'employee.create',
            'employee.edit',
            'employee.delete',
            'employee.export',
            
            // Department Management
            'department.view',
            'department.create',
            'department.edit',
            'department.delete',
            
            // Position Management
            'position.view',
            'position.create',
            'position.edit',
            'position.delete',
            
            // Payroll Management
            'payroll.view',
            'payroll.create',
            'payroll.edit',
            'payroll.delete',
            'payroll.process',
            'payroll.approve',
            'payroll.export',
            
            // Salary Management
            'salary.view',
            'salary.edit',
            'salary.history',
            
            // Time & Attendance
            'timesheet.view',
            'timesheet.create',
            'timesheet.edit',
            'timesheet.approve',
            'timesheet.export',
            
            // Leave Management
            'leave.view',
            'leave.create',
            'leave.edit',
            'leave.approve',
            'leave.export',
            
            // Performance Management
            'performance.view',
            'performance.create',
            'performance.edit',
            'performance.approve',
            'performance.export',
            
            // Reports & Analytics
            'reports.view',
            'reports.generate',
            'reports.export',
            'reports.advanced',
            
            // User Management
            'user.view',
            'user.create',
            'user.edit',
            'user.delete',
            'user.activate',
            'user.deactivate',
            
            // Role & Permission Management
            'role.view',
            'role.create',
            'role.edit',
            'role.delete',
            'role.assign',
            
            // System Settings
            'settings.view',
            'settings.edit',
            'settings.advanced',
            
            // Company Management
            'company.view',
            'company.edit',
            
            // Audit & Logs
            'audit.view',
            'audit.export',
            
            // Notifications
            'notification.view',
            'notification.send',
            'notification.manage',
            
            // Documents
            'document.view',
            'document.upload',
            'document.download',
            'document.delete',
            
            // Dashboard
            'dashboard.view',
            'dashboard.admin',
            'dashboard.manager',
            'dashboard.employee',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles and assign permissions
        $this->createRoles();
    }

    private function createRoles(): void
    {
        // Super Admin - Full access to everything
        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin']);
        $superAdmin->givePermissionTo(Permission::all());

        // HR Director - High-level HR management
        $hrDirector = Role::firstOrCreate(['name' => 'HR Director']);
        $hrDirector->syncPermissions([
            'employee.view', 'employee.create', 'employee.edit', 'employee.delete', 'employee.export',
            'department.view', 'department.create', 'department.edit', 'department.delete',
            'position.view', 'position.create', 'position.edit', 'position.delete',
            'payroll.view', 'payroll.create', 'payroll.edit', 'payroll.process', 'payroll.approve', 'payroll.export',
            'salary.view', 'salary.edit', 'salary.history',
            'timesheet.view', 'timesheet.approve', 'timesheet.export',
            'leave.view', 'leave.approve', 'leave.export',
            'performance.view', 'performance.create', 'performance.edit', 'performance.approve', 'performance.export',
            'reports.view', 'reports.generate', 'reports.export', 'reports.advanced',
            'user.view', 'user.create', 'user.edit', 'user.activate', 'user.deactivate',
            'role.view', 'role.assign',
            'settings.view', 'settings.edit',
            'company.view', 'company.edit',
            'audit.view', 'audit.export',
            'notification.view', 'notification.send', 'notification.manage',
            'document.view', 'document.upload', 'document.download', 'document.delete',
            'dashboard.view', 'dashboard.admin',
        ]);

        // HR Manager - Mid-level HR management
        $hrManager = Role::firstOrCreate(['name' => 'HR Manager']);
        $hrManager->syncPermissions([
            'employee.view', 'employee.create', 'employee.edit', 'employee.export',
            'department.view', 'department.create', 'department.edit',
            'position.view', 'position.create', 'position.edit',
            'payroll.view', 'payroll.create', 'payroll.edit', 'payroll.process', 'payroll.export',
            'salary.view', 'salary.edit',
            'timesheet.view', 'timesheet.approve', 'timesheet.export',
            'leave.view', 'leave.approve', 'leave.export',
            'performance.view', 'performance.create', 'performance.edit', 'performance.approve', 'performance.export',
            'reports.view', 'reports.generate', 'reports.export',
            'user.view', 'user.create', 'user.edit',
            'notification.view', 'notification.send',
            'document.view', 'document.upload', 'document.download',
            'dashboard.view', 'dashboard.admin',
        ]);

        // HR Staff - Basic HR operations
        $hrStaff = Role::firstOrCreate(['name' => 'HR Staff']);
        $hrStaff->syncPermissions([
            'employee.view', 'employee.create', 'employee.edit',
            'department.view', 'department.create', 'department.edit',
            'position.view', 'position.create', 'position.edit',
            'payroll.view', 'payroll.create', 'payroll.edit',
            'salary.view',
            'timesheet.view', 'timesheet.export',
            'leave.view', 'leave.approve',
            'performance.view', 'performance.create', 'performance.edit',
            'reports.view', 'reports.generate',
            'user.view', 'user.create',
            'notification.view',
            'document.view', 'document.upload',
            'dashboard.view', 'dashboard.admin',
        ]);

        // Payroll Admin - Specialized payroll management
        $payrollAdmin = Role::firstOrCreate(['name' => 'Payroll Admin']);
        $payrollAdmin->syncPermissions([
            'employee.view', 'employee.export',
            'payroll.view', 'payroll.create', 'payroll.edit', 'payroll.process', 'payroll.export',
            'salary.view', 'salary.edit', 'salary.history',
            'timesheet.view', 'timesheet.export',
            'reports.view', 'reports.generate', 'reports.export',
            'audit.view',
            'dashboard.view', 'dashboard.admin',
        ]);

        // Department Manager - Team management
        $departmentManager = Role::firstOrCreate(['name' => 'Department Manager']);
        $departmentManager->syncPermissions([
            'employee.view',
            'department.view',
            'timesheet.view', 'timesheet.approve',
            'leave.view', 'leave.approve',
            'performance.view', 'performance.create', 'performance.edit', 'performance.approve',
            'reports.view', 'reports.generate',
            'notification.view', 'notification.send',
            'document.view', 'document.upload',
            'dashboard.view', 'dashboard.manager',
        ]);

        // Team Lead - Direct report management
        $teamLead = Role::firstOrCreate(['name' => 'Team Lead']);
        $teamLead->syncPermissions([
            'employee.view',
            'timesheet.view', 'timesheet.approve',
            'leave.view', 'leave.approve',
            'performance.view', 'performance.create', 'performance.edit',
            'notification.view', 'notification.send',
            'document.view',
            'dashboard.view', 'dashboard.manager',
        ]);

        // Employee - Self-service only
        $employee = Role::firstOrCreate(['name' => 'Employee']);
        $employee->syncPermissions([
            'employee.view', // Own profile only
            'timesheet.view', 'timesheet.create', 'timesheet.edit',
            'leave.view', 'leave.create', 'leave.edit',
            'performance.view', // Own performance only
            'notification.view',
            'document.view', 'document.download',
            'dashboard.view', 'dashboard.employee',
        ]);

        // Contractor - Limited access
        $contractor = Role::firstOrCreate(['name' => 'Contractor']);
        $contractor->syncPermissions([
            'employee.view', // Own profile only
            'timesheet.view', 'timesheet.create', 'timesheet.edit',
            'leave.view', 'leave.create',
            'notification.view',
            'document.view', 'document.download',
            'dashboard.view', 'dashboard.employee',
        ]);

        // Intern - Minimal access
        $intern = Role::firstOrCreate(['name' => 'Intern']);
        $intern->syncPermissions([
            'employee.view', // Own profile only
            'timesheet.view', 'timesheet.create',
            'leave.view', 'leave.create',
            'notification.view',
            'document.view',
            'dashboard.view', 'dashboard.employee',
        ]);
    }
}