<?php

namespace App\Livewire\SystemManagement\OrganizationSetting\EmploymentStatus;

use Livewire\Component;

class Index extends Component
{
    public $sortBy = '';
    public $sortDirection = 'asc';
    
    // Add Employment Status Flyout Properties
    public $showAddEmploymentStatusFlyout = false;
    public $statusName = '';
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

    public function createEmploymentStatus()
    {
        $this->resetForm();
        $this->showAddEmploymentStatusFlyout = true;
    }
    
    public function closeAddEmploymentStatusFlyout()
    {
        $this->showAddEmploymentStatusFlyout = false;
        $this->resetForm();
    }
    
    public function submitEmploymentStatus()
    {
        $this->validate([
            'statusName' => 'required|string|max:255',
            'description' => 'nullable|string',
            'isActive' => 'boolean',
        ]);
        
        // Here you would save the employment status to the database
        // For now, we'll just close the flyout and show a success message
        session()->flash('message', 'Employment Status created successfully!');
        $this->closeAddEmploymentStatusFlyout();
    }
    
    private function resetForm()
    {
        $this->statusName = '';
        $this->description = '';
        $this->isActive = true;
    }

    public function editEmploymentStatus($id) { /* ... */ }
    public function viewEmploymentStatus($id) { /* ... */ }
    public function deleteEmploymentStatus($id) { /* ... */ }

    public function render()
    {
        return view('livewire.system-management.organization-setting.employment-status.index')
            ->layout('components.layouts.app');
    }
}
