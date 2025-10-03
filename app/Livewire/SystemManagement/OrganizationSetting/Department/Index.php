<?php

namespace App\Livewire\SystemManagement\OrganizationSetting\Department;

use Livewire\Component;

class Index extends Component
{
    public $sortBy = '';
    public $sortDirection = 'asc';
    
    // Add Department Flyout Properties
    public $showAddDepartmentFlyout = false;
    public $departmentTitle = '';
    public $departmentHead = '';
    public $departmentCode = '';
    public $description = '';
    public $isActive = true;

    public function sort($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function createDepartment()
    {
        $this->resetForm();
        $this->showAddDepartmentFlyout = true;
    }
    
    public function closeAddDepartmentFlyout()
    {
        $this->showAddDepartmentFlyout = false;
        $this->resetForm();
    }
    
    public function submitDepartment()
    {
        $this->validate([
            'departmentTitle' => 'required|string|max:255',
            'departmentCode' => 'required|string|max:50',
            'departmentHead' => 'nullable|string',
            'description' => 'nullable|string',
            'isActive' => 'boolean',
        ]);
        
        // Here you would save the department to the database
        // For now, we'll just close the flyout and show a success message
        session()->flash('message', 'Department created successfully!');
        $this->closeAddDepartmentFlyout();
    }
    
    private function resetForm()
    {
        $this->departmentTitle = '';
        $this->departmentHead = '';
        $this->departmentCode = '';
        $this->description = '';
        $this->isActive = true;
    }

    public function editDepartment($id) { /* ... */ }
    public function viewDepartment($id) { /* ... */ }
    public function deleteDepartment($id) { /* ... */ }

    public function render()
    {
        return view('livewire.system-management.organization-setting.department.index')
            ->layout('components.layouts.app');
    }
}
