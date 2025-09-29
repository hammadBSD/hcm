<?php

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
    Route::redirect('employees', 'employees/list');
    
    // Employee Module Routes
    Route::get('employees/register', \App\Livewire\Employees\Register::class)->name('employees.register');
    Route::get('employees/list', \App\Livewire\Employees\EmployeeList::class)->name('employees.list');
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
    Route::get('leaves/leave-approvals', \App\Livewire\Leaves\LeaveApprovals::class)->name('leaves.leave-approvals');
});

require __DIR__.'/auth.php';
