<?php

use App\Livewire\Settings\ApiMonitor;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use App\Livewire\Settings\TwoFactor;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', Profile::class)->name('settings.profile');
    Route::get('settings/password', Password::class)->name('settings.password');
    Route::get('settings/appearance', Appearance::class)->name('settings.appearance');
    Route::get('settings/api-monitor', ApiMonitor::class)->name('settings.api-monitor');

    Route::get('settings/two-factor', TwoFactor::class)
        ->middleware(
            when(
                Features::canManageTwoFactorAuthentication()
                    && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                ['password.confirm'],
                [],
            ),
        )
        ->name('two-factor.show');

    // Employees Routes
    Route::redirect('employees', 'employees/index');
    
    // Employee Module Routes
    Route::get('employees/index', \App\Livewire\Employees\Index::class)->name('employees.index');
    Route::get('employees/register', \App\Livewire\Employees\Register::class)->name('employees.register');
    Route::get('employees/list', \App\Livewire\Employees\EmployeeList::class)->name('employees.list');
    Route::get('employees/import', \App\Livewire\Employees\Import::class)->name('employees.import');
    Route::get('employees/role', \App\Livewire\Employees\Role::class)->name('employees.role');
    Route::get('employees/transfer', \App\Livewire\Employees\Transfer::class)->name('employees.transfer');
    Route::get('employees/delegation-request', \App\Livewire\Employees\DelegationRequest::class)->name('employees.delegation-request');
    Route::get('employees/amend-dept', \App\Livewire\Employees\AmendDept::class)->name('employees.amend-dept');
    Route::get('employees/suggestions', \App\Livewire\Employees\Suggestions::class)->name('employees.suggestions');
    
    // Employee CRUD Routes
    Route::get('employees/create', \App\Livewire\Employees\Create::class)->name('employees.create');
    Route::get('employees/{id}', \App\Livewire\Employees\Show::class)->name('employees.show');
    Route::get('employees/{id}/edit', \App\Livewire\Employees\Edit::class)->name('employees.edit');

    // Attendance Routes
    Route::redirect('attendance', 'attendance/index');
    
    // Attendance Module Routes
    Route::get('attendance/index', \App\Livewire\Attendance\Index::class)->name('attendance.index');
    Route::get('attendance/request', \App\Livewire\Attendance\Request::class)->name('attendance.request');
    Route::get('attendance/exemption-request', \App\Livewire\Attendance\ExemptionRequest::class)->name('attendance.exemption-request');
    Route::get('attendance/attendance-approval', \App\Livewire\Attendance\AttendanceApproval::class)->name('attendance.attendance-approval');
    Route::get('attendance/schedule', \App\Livewire\Attendance\Schedule::class)->name('attendance.schedule');

    // Leaves Routes
    Route::redirect('leaves', 'leaves/index');
    
    // Leaves Module Routes
    Route::get('leaves/index', \App\Livewire\Leaves\Index::class)->name('leaves.index');
    Route::get('leaves/employees-leaves', \App\Livewire\Leaves\EmployeesLeaves\Index::class)->name('leaves.employees-leaves');
    Route::get('leaves/leave-request', \App\Livewire\Leaves\LeaveRequest::class)->name('leaves.leave-request');

    // System Management Routes
    Route::redirect('system-management', 'system-management/index');
    
    // System Management Module Routes
    Route::get('system-management/index', \App\Livewire\SystemManagement\Index::class)->name('system-management.index');
    
// Organization Settings
Route::get('system-management/organization-setting/department', \App\Livewire\SystemManagement\OrganizationSetting\Department\Index::class)->name('system-management.organization-setting.department');
Route::get('system-management/organization-setting/designation', \App\Livewire\SystemManagement\OrganizationSetting\Designation\Index::class)->name('system-management.organization-setting.designation');
    Route::get('system-management/organization-setting/employment-status', \App\Livewire\SystemManagement\OrganizationSetting\EmploymentStatus\Index::class)->name('system-management.organization-setting.employment-status');
    Route::get('system-management/organization-setting/employment-type', \App\Livewire\SystemManagement\OrganizationSetting\EmploymentType\Index::class)->name('system-management.organization-setting.employment-type');
Route::get('system-management/organization-setting/group', \App\Livewire\SystemManagement\OrganizationSetting\Group\Index::class)->name('system-management.organization-setting.group');
Route::get('system-management/organization-setting/country', \App\Livewire\SystemManagement\OrganizationSetting\Country\Index::class)->name('system-management.organization-setting.country');
Route::get('system-management/organization-setting/province', \App\Livewire\SystemManagement\OrganizationSetting\Province\Index::class)->name('system-management.organization-setting.province');
Route::get('system-management/organization-setting/organization-settings', \App\Livewire\SystemManagement\OrganizationSetting\OrganizationSettings\Index::class)->name('system-management.organization-setting.organization-settings');
    
    // User Management
    Route::get('system-management/user-management/user-roles', \App\Livewire\SystemManagement\UserManagement\UserRoles\Index::class)->name('system-management.user-management.user-roles');
    Route::get('system-management/user-management/users', \App\Livewire\SystemManagement\UserManagement\Users\Index::class)->name('system-management.user-management.users');
    
    // Financial Settings
    Route::get('system-management/financial-settings/bank-info', \App\Livewire\SystemManagement\FinancialSettings\BankInfo\Index::class)->name('system-management.financial-settings.bank-info');
    Route::get('system-management/financial-settings/currencies', \App\Livewire\SystemManagement\FinancialSettings\Currencies\Index::class)->name('system-management.financial-settings.currencies');
    Route::get('system-management/financial-settings/vendors', \App\Livewire\SystemManagement\FinancialSettings\Vendors\Index::class)->name('system-management.financial-settings.vendors');
    
    // System Configuration
    Route::get('system-management/system-configuration/system-logs', \App\Livewire\SystemManagement\SystemConfiguration\SystemLogs\Index::class)->name('system-management.system-configuration.system-logs');
    Route::get('system-management/system-configuration/announcements', \App\Livewire\SystemManagement\SystemConfiguration\Announcements\Index::class)->name('system-management.system-configuration.announcements');
    Route::get('system-management/system-configuration/polls', \App\Livewire\SystemManagement\SystemConfiguration\Polls\Index::class)->name('system-management.system-configuration.polls');
    Route::get('system-management/system-configuration/holidays', \App\Livewire\SystemManagement\SystemConfiguration\Holidays\Index::class)->name('system-management.system-configuration.holidays');
    
    // Security & Access
    Route::get('system-management/security-access/geo-restrictions', \App\Livewire\SystemManagement\SecurityAccess\GeoRestrictions\Index::class)->name('system-management.security-access.geo-restrictions');
    Route::get('system-management/security-access/security', \App\Livewire\SystemManagement\SecurityAccess\Security\Index::class)->name('system-management.security-access.security');
    
    // Operations
    Route::get('system-management/operations/projects', \App\Livewire\SystemManagement\Operations\Projects\Index::class)->name('system-management.operations.projects');
    Route::get('system-management/operations/tasks', \App\Livewire\SystemManagement\Operations\Tasks\Index::class)->name('system-management.operations.tasks');
    Route::get('system-management/operations/month-close', \App\Livewire\SystemManagement\Operations\MonthClose\Index::class)->name('system-management.operations.month-close');
    Route::get('system-management/operations/day-close', \App\Livewire\SystemManagement\Operations\DayClose\Index::class)->name('system-management.operations.day-close');
    Route::get('system-management/operations/constants', \App\Livewire\SystemManagement\Operations\Constants\Index::class)->name('system-management.operations.constants');
    
    // Attendance Settings
    Route::get('system-management/attendance-settings/shift-schedule', \App\Livewire\SystemManagement\AttendanceSettings\ShiftSchedule\Index::class)->name('system-management.attendance-settings.shift-schedule');
    Route::get('system-management/attendance-settings/work-schedule', \App\Livewire\SystemManagement\AttendanceSettings\WorkSchedule\Index::class)->name('system-management.attendance-settings.work-schedule');
    Route::get('system-management/attendance-settings/attendance-rules', \App\Livewire\SystemManagement\AttendanceSettings\AttendanceRules\Index::class)->name('system-management.attendance-settings.attendance-rules');
    
    // Leaves Management
    Route::get('system-management/leaves-management/leave-types', \App\Livewire\SystemManagement\LeavesManagement\LeaveTypes\Index::class)->name('system-management.leaves-management.leave-types');
    Route::get('system-management/leaves-management/leave-policies', \App\Livewire\SystemManagement\LeavesManagement\LeavePolicies\Index::class)->name('system-management.leaves-management.leave-policies');
    Route::get('system-management/leaves-management/leave-balances', \App\Livewire\SystemManagement\LeavesManagement\LeaveBalances\Index::class)->name('system-management.leaves-management.leave-balances');
    
    // Payroll Settings
    Route::get('system-management/payroll-settings/salary-components', \App\Livewire\SystemManagement\PayrollSettings\SalaryComponents\Index::class)->name('system-management.payroll-settings.salary-components');
    Route::get('system-management/payroll-settings/payroll-periods', \App\Livewire\SystemManagement\PayrollSettings\PayrollPeriods\Index::class)->name('system-management.payroll-settings.payroll-periods');
    Route::get('system-management/payroll-settings/tax-settings', \App\Livewire\SystemManagement\PayrollSettings\TaxSettings\Index::class)->name('system-management.payroll-settings.tax-settings');
});

require __DIR__.'/auth.php';
