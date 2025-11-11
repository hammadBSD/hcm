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
            
            // Dashboard Module
            'dashboard.sidebar.main',
            'dashboard.view.self',
            'dashboard.view.team',
            'dashboard.view.company',

            // Employees Module
            'employees.sidebar.my_profile',
            'employees.sidebar.directory',
            'employees.sidebar.create',
            'employees.sidebar.import',
            'employees.sidebar.roles',
            'employees.sidebar.transfer',
            'employees.sidebar.delegation',
            'employees.sidebar.amend_department',
            'employees.sidebar.suggestions',
            'employees.manage.directory',
            'employees.manage.create',
            'employees.manage.import',
            'employees.manage.roles',
            'employees.manage.transfer',
            'employees.manage.delegation',
            'employees.manage.amend_department',
            'employees.manage.suggestions',

            // Attendance Module
            'attendance.sidebar.my_attendance',
            'attendance.sidebar.requests',
            'attendance.sidebar.exemptions',
            'attendance.sidebar.approvals',
            'attendance.sidebar.schedule',
            'attendance.view.self',
            'attendance.view.team',
            'attendance.view.company',
            'attendance.manage.missing_entries',
            'attendance.manage.manual_entries',
            'attendance.manage.switch_user',
            'attendance.export',

            // Payroll Module
            'payroll.sidebar.main',
            'payroll.sidebar.settings',
            'payroll.view.self',
            'payroll.view.team',
            'payroll.bonus.manage',
            'payroll.advance.manage',
            'payroll.advance.request',
            'payroll.loan.manage',
            'payroll.loan.request',

            // Leaves Module
            'leaves.sidebar.my_leaves',
            'leaves.sidebar.all_leaves',
            'leaves.sidebar.request_form',
            'leaves.view.self',
            'leaves.view.all',
            'leaves.manage.all',
            'leaves.request.submit',
            'leaves.approve.requests',

            // Reports Module
            'reports.sidebar.main',
            'reports.view.basic',
            'reports.view.advanced',
            'reports.export.detailed',

            // System Management Module
            'system.sidebar.roles',
            'system.sidebar.users',
            'system.sidebar.settings',
            'system.manage.roles',
            'system.manage.users',
            'system.manage.settings',

            // Salary Management
            'salary.view',
            'salary.edit',
            'salary.history',
            
            // Time & Attendance
            'attendance.view',
            'attendance.request',
            'attendance.exemption',
            'attendance.approve',
            'attendance.schedule',
            'attendance.export',
            'timesheet.view',
            'timesheet.create',
            'timesheet.edit',
            'timesheet.approve',
            'timesheet.export',
            
            // Leave Management (Legacy)
            'leave.view',
            'leave.create',
            'leave.edit',
            'leave.approve',
            'leave.export',
            
            // Leaves Module (UI scoped)
            'leaves.sidebar.my_leaves',
            'leaves.sidebar.all_leaves',
            'leaves.sidebar.request_form',
            'leaves.view.self',
            'leaves.view.all',
            'leaves.manage.all',
            'leaves.request.submit',
            'leaves.approve.requests',
            
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
            
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        // Create roles and assign permissions
        $this->createRoles();
    }

    private function createRoles(): void
    {
        // Super Admin - Full access to everything
        $superAdmin = Role::firstOrCreate(
            ['name' => 'Super Admin', 'guard_name' => 'web']
        );
        $superAdmin->forceFill([
            'description' => 'Full system access and control',
            'is_active' => true,
        ])->save();
        $superAdmin->givePermissionTo(Permission::all());

        $dashboardSelf = ['dashboard.sidebar.main', 'dashboard.view.self'];
        $dashboardTeam = array_merge($dashboardSelf, ['dashboard.view.team']);
        $dashboardCompany = array_merge($dashboardTeam, ['dashboard.view.company']);

        $employeesSidebarBasic = ['employees.sidebar.my_profile'];
        $employeesSidebarExtended = [
            'employees.sidebar.my_profile',
            'employees.sidebar.directory',
            'employees.sidebar.create',
            'employees.sidebar.import',
            'employees.sidebar.roles',
            'employees.sidebar.transfer',
            'employees.sidebar.delegation',
            'employees.sidebar.amend_department',
            'employees.sidebar.suggestions',
        ];
        $employeesManageFull = [
            'employees.manage.directory',
            'employees.manage.create',
            'employees.manage.import',
            'employees.manage.roles',
            'employees.manage.transfer',
            'employees.manage.delegation',
            'employees.manage.amend_department',
            'employees.manage.suggestions',
        ];

        $attendanceSelf = [
            'attendance.sidebar.my_attendance',
            'attendance.sidebar.requests',
            'attendance.view.self',
        ];
        $attendanceTeam = array_merge($attendanceSelf, [
            'attendance.sidebar.exemptions',
            'attendance.sidebar.schedule',
            'attendance.view.team',
            'attendance.manage.switch_user',
        ]);
        $attendanceApprover = array_merge($attendanceTeam, [
            'attendance.sidebar.approvals',
            'attendance.view.company',
            'attendance.manage.missing_entries',
            'attendance.manage.manual_entries',
            'attendance.export',
        ]);

        $systemSidebarFull = [
            'system.sidebar.roles',
            'system.sidebar.users',
            'system.sidebar.settings',
        ];
        $systemManageFull = [
            'system.manage.roles',
            'system.manage.users',
            'system.manage.settings',
        ];

        $leavesManager = [
            'leaves.sidebar.my_leaves',
            'leaves.sidebar.all_leaves',
            'leaves.sidebar.request_form',
            'leaves.view.self',
            'leaves.view.all',
            'leaves.manage.all',
            'leaves.request.submit',
            'leaves.approve.requests',
        ];
        
        $leavesSelf = [
            'leaves.sidebar.my_leaves',
            'leaves.sidebar.request_form',
            'leaves.view.self',
            'leaves.request.submit',
        ];

        $payrollSidebar = [
            'payroll.sidebar.main',
            'payroll.sidebar.settings',
        ];

        // HR Director - High-level HR management
        $hrDirector = Role::firstOrCreate(
            ['name' => 'HR Director', 'guard_name' => 'web']
        );
        $hrDirector->forceFill([
            'description' => 'Oversees HR policies, approvals, and people operations',
            'is_active' => true,
        ])->save();
        $hrDirectorPermissions = array_unique(array_merge(
            $dashboardCompany,
            $employeesSidebarExtended,
            $employeesManageFull,
            $attendanceApprover,
            $systemSidebarFull,
            $systemManageFull,
            $leavesManager,
            $payrollSidebar,
            [
                'employee.view', 'employee.create', 'employee.edit', 'employee.delete', 'employee.export',
                'department.view', 'department.create', 'department.edit', 'department.delete',
                'position.view', 'position.create', 'position.edit', 'position.delete',
                'payroll.view', 'payroll.create', 'payroll.edit', 'payroll.process', 'payroll.approve', 'payroll.export',
                'payroll.bonus.manage', 'payroll.advance.manage', 'payroll.loan.manage',
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
            ]
        ));
        $hrDirector->syncPermissions($hrDirectorPermissions);

        // HR Manager - Mid-level HR management
        $hrManager = Role::firstOrCreate(
            ['name' => 'HR Manager', 'guard_name' => 'web']
        );
        $hrManager->forceFill([
            'description' => 'Manages HR workflows, timesheets, and leave approvals',
            'is_active' => true,
        ])->save();
        $hrManagerPermissions = array_unique(array_merge(
            $dashboardTeam,
            $employeesSidebarExtended,
            $employeesManageFull,
            $attendanceApprover,
            $systemSidebarFull,
            ['system.manage.roles', 'system.manage.users'],
            $leavesManager,
            $payrollSidebar,
            [
                'employee.view', 'employee.create', 'employee.edit', 'employee.export',
                'department.view', 'department.create', 'department.edit',
                'position.view', 'position.create', 'position.edit',
                'payroll.view', 'payroll.create', 'payroll.edit', 'payroll.process', 'payroll.export',
                'payroll.bonus.manage', 'payroll.advance.manage', 'payroll.loan.manage',
                'salary.view', 'salary.edit',
                'timesheet.view', 'timesheet.approve', 'timesheet.export',
                'leave.view', 'leave.approve', 'leave.export',
                'performance.view', 'performance.create', 'performance.edit', 'performance.approve', 'performance.export',
                'reports.view', 'reports.generate', 'reports.export',
                'user.view', 'user.create', 'user.edit',
                'notification.view', 'notification.send',
                'document.view', 'document.upload', 'document.download',
            ]
        ));
        $hrManager->syncPermissions($hrManagerPermissions);

        // HR Staff - Basic HR operations
        $hrStaff = Role::firstOrCreate(
            ['name' => 'HR Staff', 'guard_name' => 'web']
        );
        $hrStaff->forceFill([
            'description' => 'Handles daily HR operations and employee updates',
            'is_active' => true,
        ])->save();
        $hrStaffPermissions = array_unique(array_merge(
            $dashboardSelf,
            $employeesSidebarExtended,
            $employeesManageFull,
            $attendanceTeam,
            $systemSidebarFull,
            $leavesManager,
            $payrollSidebar,
            [
                'employee.view', 'employee.create', 'employee.edit',
                'department.view', 'department.create', 'department.edit',
                'position.view', 'position.create', 'position.edit',
                'payroll.view', 'payroll.create', 'payroll.edit',
                'payroll.bonus.manage', 'payroll.advance.manage', 'payroll.loan.manage',
                'salary.view',
                'timesheet.view', 'timesheet.export',
                'leave.view', 'leave.approve',
                'performance.view', 'performance.create', 'performance.edit',
                'reports.view', 'reports.generate',
                'user.view', 'user.create',
                'notification.view',
                'document.view', 'document.upload',
            ]
        ));
        $hrStaff->syncPermissions($hrStaffPermissions);

        // Payroll Admin - Specialized payroll management
        $payrollAdmin = Role::firstOrCreate(
            ['name' => 'Payroll Admin', 'guard_name' => 'web']
        );
        $payrollAdmin->forceFill([
            'description' => 'Processes payroll runs and salary adjustments',
            'is_active' => true,
        ])->save();
        $payrollAdminPermissions = array_unique(array_merge(
            $dashboardTeam,
            $employeesSidebarBasic,
            ['employees.sidebar.directory', 'employees.manage.directory'],
            $attendanceTeam,
            $systemSidebarFull,
            ['system.manage.settings'],
            $leavesSelf,
            $payrollSidebar,
            [
                'employee.view', 'employee.export',
                'payroll.view', 'payroll.create', 'payroll.edit', 'payroll.process', 'payroll.export',
                'salary.view', 'salary.edit', 'salary.history',
                'timesheet.view', 'timesheet.export',
                'reports.view', 'reports.generate', 'reports.export',
                'audit.view',
            ]
        ));
        $payrollAdmin->syncPermissions($payrollAdminPermissions);

        // Department Manager - Team management
        $departmentManager = Role::firstOrCreate(
            ['name' => 'Department Manager', 'guard_name' => 'web']
        );
        $departmentManager->forceFill([
            'description' => 'Approves team activity and oversees department staff',
            'is_active' => true,
        ])->save();
        $departmentManagerPermissions = array_unique(array_merge(
            $dashboardTeam,
            $employeesSidebarBasic,
            ['employees.sidebar.directory'],
            ['employees.manage.directory'],
            $attendanceApprover,
            $systemSidebarFull,
            $leavesManager,
            $payrollSidebar,
            [
                'employee.view',
                'department.view',
                'timesheet.view', 'timesheet.approve',
                'leave.view', 'leave.approve',
                'performance.view', 'performance.create', 'performance.edit', 'performance.approve',
                'reports.view', 'reports.generate',
                'notification.view', 'notification.send',
                'document.view', 'document.upload',
            ]
        ));
        $departmentManager->syncPermissions($departmentManagerPermissions);

        // Team Lead - Direct report management
        $teamLead = Role::firstOrCreate(
            ['name' => 'Team Lead', 'guard_name' => 'web']
        );
        $teamLead->forceFill([
            'description' => 'Guides team members and validates attendance records',
            'is_active' => true,
        ])->save();
        $teamLeadPermissions = array_unique(array_merge(
            $dashboardTeam,
            $employeesSidebarBasic,
            ['employees.sidebar.directory', 'employees.sidebar.delegation', 'employees.sidebar.suggestions'],
            ['employees.manage.directory', 'employees.manage.delegation', 'employees.manage.suggestions'],
            $attendanceTeam,
            $leavesManager,
            [
                'employee.view',
                'timesheet.view', 'timesheet.approve',
                'leave.view', 'leave.approve',
                'performance.view', 'performance.create', 'performance.edit',
                'notification.view', 'notification.send',
                'document.view',
            ]
        ));
        $teamLead->syncPermissions($teamLeadPermissions);

        // Employee - Self-service only
        $employee = Role::firstOrCreate(
            ['name' => 'Employee', 'guard_name' => 'web']
        );
        $employee->forceFill([
            'description' => 'Access to self-service tools and personal records',
            'is_active' => true,
        ])->save();
        $employeePermissions = array_unique(array_merge(
            $dashboardSelf,
            $employeesSidebarBasic,
            ['employees.sidebar.delegation', 'employees.sidebar.suggestions'],
            ['employees.manage.delegation', 'employees.manage.suggestions'],
            $attendanceSelf,
            $leavesManager,
            [
                'employee.view', // Own profile only
                'payroll.view.self',
                'payroll.advance.request',
                'payroll.loan.request',
                'timesheet.view', 'timesheet.create', 'timesheet.edit',
                'leave.view', 'leave.create', 'leave.edit',
                'performance.view', // Own performance only
                'notification.view',
                'document.view', 'document.download',
            ]
        ));
        $employee->syncPermissions($employeePermissions);

        // Contractor - Limited access
        $contractor = Role::firstOrCreate(
            ['name' => 'Contractor', 'guard_name' => 'web']
        );
        $contractor->forceFill([
            'description' => 'Limited access for contract-based staff',
            'is_active' => true,
        ])->save();
        $contractorPermissions = array_unique(array_merge(
            $dashboardSelf,
            $employeesSidebarBasic,
            ['employees.sidebar.delegation', 'employees.sidebar.suggestions'],
            ['employees.manage.delegation', 'employees.manage.suggestions'],
            $attendanceSelf,
            $leavesManager,
            [
                'employee.view', // Own profile only
                'payroll.view.self',
                'payroll.advance.request',
                'payroll.loan.request',
                'timesheet.view', 'timesheet.create', 'timesheet.edit',
                'leave.view', 'leave.create',
                'notification.view',
                'document.view', 'document.download',
            ]
        ));
        $contractor->syncPermissions($contractorPermissions);

        // Intern - Minimal access
        $intern = Role::firstOrCreate(
            ['name' => 'Intern', 'guard_name' => 'web']
        );
        $intern->forceFill([
            'description' => 'Basic platform access for trainees and interns',
            'is_active' => true,
        ])->save();
        $internPermissions = array_unique(array_merge(
            $dashboardSelf,
            $employeesSidebarBasic,
            ['employees.sidebar.delegation', 'employees.sidebar.suggestions'],
            ['employees.manage.delegation', 'employees.manage.suggestions'],
            $attendanceSelf,
            $leavesManager,
            [
                'employee.view', // Own profile only
                'payroll.view.self',
                'payroll.advance.request',
                'payroll.loan.request',
                'timesheet.view', 'timesheet.create',
                'leave.view', 'leave.create',
                'notification.view',
                'document.view',
            ]
        ));
        $intern->syncPermissions($internPermissions);
    }
}