<?php

namespace App\Livewire\SystemManagement\AttendanceSettings;

use Livewire\Component;
use App\Models\Employee;
use Spatie\Permission\Models\Role;

class BreakSettings extends Component
{
    public $settings = [
        'show_in_attendance_grid' => true,
        'use_breaks_in_payroll' => false,
        'use_in_salary_deductions' => false,
        'enable_break_tracking' => true,
        'mandatory_break_duration' => false,
        'auto_deduct_breaks' => false,
        'break_overtime_calculation' => false,
        'break_notifications' => true,
    ];

    // Flyout properties
    public $showExclusionFlyout = false;
    public $exclusionType = 'users'; // 'users' or 'roles'
    public $selectedUsers = [];
    public $selectedRoles = [];
    
    // Data properties
    public $employees = [];
    public $roles = [];
    
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
        // Load existing settings from database or use default values
        // For now, we'll use the default values defined above
        
        // Load data for exclusion selection
        $this->loadEmployees();
        $this->loadRoles();
    }

    public function loadEmployees()
    {
        // Fetch all employees for the multi-select dropdown
        $this->employees = Employee::select('id', 'first_name', 'last_name', 'employee_code')
            ->where('status', 'active')
            ->orderBy('first_name')
            ->get()
            ->map(function ($employee) {
                return [
                    'value' => $employee->id,
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
        
        // Clear search terms when closing flyout
        $this->userSearchTerm = '';
        $this->roleSearchTerm = '';
    }

    public function updatedExclusionType()
    {
        // Clear selections when switching between users and roles
        $this->selectedUsers = [];
        $this->selectedRoles = [];
        
        // Clear search terms when switching
        $this->userSearchTerm = '';
        $this->roleSearchTerm = '';
    }

    public function removeUser($userId)
    {
        $this->selectedUsers = array_filter($this->selectedUsers, function($id) use ($userId) {
            return $id != $userId;
        });
    }

    public function removeRole($roleId)
    {
        $this->selectedRoles = array_filter($this->selectedRoles, function($id) use ($roleId) {
            return $id != $roleId;
        });
    }

    public function saveExclusions()
    {
        // Here you would save the exclusions to database
        // For now, we'll just show a success message
        
        $exclusionCount = count($this->selectedUsers) + count($this->selectedRoles);
        
        session()->flash('message', "Exclusions saved successfully! {$exclusionCount} items added.");
        
        $this->dispatch('show-toast', [
            'type' => 'success',
            'message' => "Exclusions saved successfully! {$exclusionCount} items added."
        ]);
        
        $this->closeExclusionFlyout();
    }

    public function saveAllSettings()
    {
        // Here you would save settings to database
        // For now, we'll just show a success message
        
        session()->flash('message', 'Break settings saved successfully!');
        
        $this->dispatch('show-toast', [
            'type' => 'success',
            'message' => 'Break settings have been updated successfully!'
        ]);
    }

    public function resetToDefaults()
    {
        $this->settings = [
            'show_in_attendance_grid' => true,
            'use_breaks_in_payroll' => false,
            'use_in_salary_deductions' => false,
            'enable_break_tracking' => true,
            'mandatory_break_duration' => false,
            'auto_deduct_breaks' => false,
            'break_overtime_calculation' => false,
            'break_notifications' => true,
        ];
        
        // Reset exclusions
        $this->selectedUsers = [];
        $this->selectedRoles = [];
        
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
        ])->layout('components.layouts.app');
    }
}
