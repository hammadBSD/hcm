<?php

namespace App\Livewire\SystemManagement\AttendanceSettings;

use Livewire\Component;
use App\Models\Employee;
use App\Models\AttendanceBreakSetting;
use App\Models\AttendanceBreakExclusion;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class BreakSettings extends Component
{
    public $settings = [
        'enable_break_tracking' => true,
        'show_in_attendance_grid' => true,
        'break_notifications' => true,
        'allowed_break_time' => null,
        'use_breaks_in_payroll' => false,
        'use_in_salary_deductions' => false,
        'auto_deduct_breaks' => false,
        'break_overtime_calculation' => false,
        'mandatory_break_duration_enabled' => false,
        'mandatory_break_duration_minutes' => null,
    ];

    // Flyout properties
    public $showExclusionFlyout = false;
    public $exclusionType = 'users'; // 'users' or 'roles'
    public $selectedUsers = [];
    public $selectedRoles = [];
    
    // Data properties
    public $employees = [];
    public $roles = [];

    // Existing exclusions
    public $existingUserExclusions = [];
    public $existingRoleExclusions = [];
    
    // Search terms for filtering
    public $userSearchTerm = '';
    public $roleSearchTerm = '';

    // Computed properties for filtered data
    public function getFilteredEmployeesProperty()
    {
        if (empty($this->userSearchTerm)) {
            return $this->employees;
        }
        
        return collect($this->employees)->filter(function ($employee) {
            return stripos($employee['label'], $this->userSearchTerm) !== false;
        })->values()->toArray();
    }
    
    public function getFilteredRolesProperty()
    {
        if (empty($this->roleSearchTerm)) {
            return $this->roles;
        }
        
        return collect($this->roles)->filter(function ($role) {
            return stripos($role['label'], $this->roleSearchTerm) !== false;
        })->values()->toArray();
    }

    public function mount()
    {
        $this->loadSettings();
        $this->loadEmployees();
        $this->loadRoles();
        $this->loadExistingExclusions();
    }

    protected function loadSettings(): void
    {
        $settings = AttendanceBreakSetting::query()->first();

        if ($settings) {
            $this->settings = array_merge($this->settings, [
                'enable_break_tracking' => (bool) $settings->enable_break_tracking,
                'show_in_attendance_grid' => (bool) $settings->show_in_attendance_grid,
                'break_notifications' => (bool) $settings->break_notifications,
                'allowed_break_time' => $settings->allowed_break_time,
                'use_breaks_in_payroll' => (bool) $settings->use_breaks_in_payroll,
                'use_in_salary_deductions' => (bool) $settings->use_in_salary_deductions,
                'auto_deduct_breaks' => (bool) $settings->auto_deduct_breaks,
                'break_overtime_calculation' => (bool) $settings->break_overtime_calculation,
                'mandatory_break_duration_enabled' => (bool) $settings->mandatory_break_duration_enabled,
                'mandatory_break_duration_minutes' => $settings->mandatory_break_duration_minutes,
            ]);
        }
    }

    protected function loadExistingExclusions(): void
    {
        $this->existingUserExclusions = AttendanceBreakExclusion::query()
            ->with('user')
            ->where('type', 'user')
            ->get()
            ->map(function (AttendanceBreakExclusion $exclusion) {
                return [
                    'id' => $exclusion->id,
                    'name' => optional($exclusion->user)->name ?? 'Unknown User',
                    'email' => optional($exclusion->user)->email,
                    'notes' => $exclusion->notes,
                ];
            })
            ->toArray();

        $this->existingRoleExclusions = AttendanceBreakExclusion::query()
            ->with('role')
            ->where('type', 'role')
            ->get()
            ->map(function (AttendanceBreakExclusion $exclusion) {
                return [
                    'id' => $exclusion->id,
                    'name' => optional($exclusion->role)->name ?? 'Unknown Role',
                    'notes' => $exclusion->notes,
                ];
            })
            ->toArray();

        $this->hydrateExclusionSelections();
    }

    protected function hydrateExclusionSelections(): void
    {
        $this->selectedUsers = AttendanceBreakExclusion::query()
            ->where('type', 'user')
            ->pluck('user_id')
            ->filter()
            ->map(fn ($id) => (string) $id)
            ->values()
            ->toArray();

        $this->selectedRoles = AttendanceBreakExclusion::query()
            ->where('type', 'role')
            ->pluck('role_id')
            ->filter()
            ->map(fn ($id) => (string) $id)
            ->values()
            ->toArray();
    }

    public function loadEmployees()
    {
        // Fetch all employees for the multi-select dropdown
        $this->employees = Employee::select('id', 'user_id', 'first_name', 'last_name', 'employee_code')
            ->where('status', 'active')
            ->orderBy('first_name')
            ->get()
            ->map(function ($employee) {
                return [
                    'value' => $employee->user_id,
                    'label' => $employee->first_name . ' ' . $employee->last_name . ' (' . ($employee->employee_code ?? 'N/A') . ')',
                    'name' => $employee->first_name . ' ' . $employee->last_name,
                    'code' => $employee->employee_code ?? 'N/A'
                ];
            })
            ->toArray();
    }

    public function loadRoles()
    {
        // Fetch all roles for the multi-select dropdown using Spatie Permission
        $this->roles = Role::select('id', 'name')
            ->orderBy('name')
            ->get()
            ->map(function ($role) {
                return [
                    'value' => $role->id,
                    'label' => $role->name,
                    'name' => $role->name
                ];
            })
            ->toArray();
    }

    public function openExclusionFlyout()
    {
        $this->hydrateExclusionSelections();
        $this->showExclusionFlyout = true;
        
        // Clear search terms when opening flyout
        $this->userSearchTerm = '';
        $this->roleSearchTerm = '';
    }

    public function closeExclusionFlyout()
    {
        $this->showExclusionFlyout = false;
        $this->exclusionType = 'users';
        $this->selectedUsers = [];
        $this->selectedRoles = [];
        $this->userSearchTerm = '';
        $this->roleSearchTerm = '';
    }

    public function updatedExclusionType()
    {
        $this->hydrateExclusionSelections();
        $this->userSearchTerm = '';
        $this->roleSearchTerm = '';
    }

    public function removeUser($userId)
    {
        $this->selectedUsers = array_values(array_filter(
            $this->selectedUsers,
            fn ($id) => (int) $id !== (int) $userId
        ));
    }

    public function removeRole($roleId)
    {
        $this->selectedRoles = array_values(array_filter(
            $this->selectedRoles,
            fn ($id) => (int) $id !== (int) $roleId
        ));
    }

    public function saveExclusions()
    {
        $exclusionCount = 0;

        DB::transaction(function () use (&$exclusionCount) {
            $userIds = collect($this->selectedUsers)
                ->map(fn ($id) => (int) $id)
                ->filter()
                ->unique()
                ->values()
                ->toArray();

            if (! empty($userIds)) {
                $records = collect($userIds)
                    ->map(fn ($id) => [
                        'type' => 'user',
                        'user_id' => $id,
                        'role_id' => null,
                        'notes' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ])
                    ->toArray();

                AttendanceBreakExclusion::upsert(
                    $records,
                    ['type', 'user_id'],
                    ['updated_at']
                );

                AttendanceBreakExclusion::query()
                    ->where('type', 'user')
                    ->whereNotIn('user_id', $userIds)
                    ->delete();

                $exclusionCount += count($records);
            } else {
                AttendanceBreakExclusion::query()
                    ->where('type', 'user')
                    ->delete();
            }

            $roleIds = collect($this->selectedRoles)
                ->map(fn ($id) => (int) $id)
                ->filter()
                ->unique()
                ->values()
                ->toArray();

            if (! empty($roleIds)) {
                $records = collect($roleIds)
                    ->map(fn ($id) => [
                        'type' => 'role',
                        'user_id' => null,
                        'role_id' => $id,
                        'notes' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ])
                    ->toArray();

                AttendanceBreakExclusion::upsert(
                    $records,
                    ['type', 'role_id'],
                    ['updated_at']
                );

                AttendanceBreakExclusion::query()
                    ->where('type', 'role')
                    ->whereNotIn('role_id', $roleIds)
                    ->delete();

                $exclusionCount += count($records);
            } else {
                AttendanceBreakExclusion::query()
                    ->where('type', 'role')
                    ->delete();
            }
        });

        session()->flash('message', "Exclusions saved successfully! {$exclusionCount} items added.");

        $this->dispatch('show-toast', [
            'type' => 'success',
            'message' => "Exclusions saved successfully! {$exclusionCount} items added."
        ]);

        $this->closeExclusionFlyout();
        $this->loadExistingExclusions();
    }

    public function saveAllSettings()
    {
        $data = [
            'enable_break_tracking' => (bool) ($this->settings['enable_break_tracking'] ?? true),
            'show_in_attendance_grid' => (bool) ($this->settings['show_in_attendance_grid'] ?? true),
            'break_notifications' => (bool) ($this->settings['break_notifications'] ?? true),
            'allowed_break_time' => $this->settings['allowed_break_time'] ? (int) $this->settings['allowed_break_time'] : null,
            'use_breaks_in_payroll' => (bool) ($this->settings['use_breaks_in_payroll'] ?? false),
            'use_in_salary_deductions' => (bool) ($this->settings['use_in_salary_deductions'] ?? false),
            'auto_deduct_breaks' => (bool) ($this->settings['auto_deduct_breaks'] ?? false),
            'break_overtime_calculation' => (bool) ($this->settings['break_overtime_calculation'] ?? false),
            'mandatory_break_duration_enabled' => (bool) ($this->settings['mandatory_break_duration_enabled'] ?? false),
            'mandatory_break_duration_minutes' => $this->settings['mandatory_break_duration_enabled']
                ? ($this->settings['mandatory_break_duration_minutes'] ?: null)
                : null,
        ];

        $settings = AttendanceBreakSetting::query()->first();

        if ($settings) {
            $settings->update($data);
        } else {
            AttendanceBreakSetting::query()->create($data);
        }

        $this->loadSettings();

        session()->flash('message', 'Break settings saved successfully!');

        $this->dispatch('show-toast', [
            'type' => 'success',
            'message' => 'Break settings have been updated successfully!'
        ]);
    }

    public function resetToDefaults()
    {
        AttendanceBreakSetting::query()->delete();
        AttendanceBreakExclusion::query()->delete();

        $this->settings = [
            'enable_break_tracking' => true,
            'show_in_attendance_grid' => true,
            'break_notifications' => true,
            'allowed_break_time' => null,
            'use_breaks_in_payroll' => false,
            'use_in_salary_deductions' => false,
            'auto_deduct_breaks' => false,
            'break_overtime_calculation' => false,
            'mandatory_break_duration_enabled' => false,
            'mandatory_break_duration_minutes' => null,
        ];

        $this->selectedUsers = [];
        $this->selectedRoles = [];
        $this->existingUserExclusions = [];
        $this->existingRoleExclusions = [];
        $this->hydrateExclusionSelections();

        $this->loadSettings();
        $this->loadExistingExclusions();

        session()->flash('message', 'Break settings reset to defaults!');

        $this->dispatch('show-toast', [
            'type' => 'info',
            'message' => 'Break settings have been reset to default values!'
        ]);
    }

    public function render()
    {
        return view('livewire.system-management.attendance-settings.break-settings', [
            'filteredEmployees' => $this->filteredEmployees,
            'filteredRoles' => $this->filteredRoles,
            'existingUserExclusions' => $this->existingUserExclusions,
            'existingRoleExclusions' => $this->existingRoleExclusions,
        ])->layout('components.layouts.app');
    }
}
