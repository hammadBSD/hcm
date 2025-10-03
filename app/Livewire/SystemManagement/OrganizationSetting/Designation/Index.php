<?php

namespace App\Livewire\SystemManagement\OrganizationSetting\Designation;

use Livewire\Component;

class Index extends Component
{
    public $sortBy = '';
    public $sortDirection = 'asc';
    
    // Add Designation Flyout Properties
    public $showAddDesignationFlyout = false;
    public $designationName = '';
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

    public function createDesignation()
    {
        $this->resetForm();
        $this->showAddDesignationFlyout = true;
    }
    
    public function closeAddDesignationFlyout()
    {
        $this->showAddDesignationFlyout = false;
        $this->resetForm();
    }
    
    public function submitDesignation()
    {
        $this->validate([
            'designationName' => 'required|string|max:255',
            'description' => 'nullable|string',
            'isActive' => 'boolean',
        ]);
        
        // Here you would save the designation to the database
        // For now, we'll just close the flyout and show a success message
        session()->flash('message', 'Designation created successfully!');
        $this->closeAddDesignationFlyout();
    }
    
    private function resetForm()
    {
        $this->designationName = '';
        $this->description = '';
        $this->isActive = true;
    }

    public function editDesignation($id) { /* ... */ }
    public function viewDesignation($id) { /* ... */ }
    public function deleteDesignation($id) { /* ... */ }

    public function render()
    {
        return view('livewire.system-management.organization-setting.designation.index')
            ->layout('components.layouts.app');
    }
}