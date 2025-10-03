<?php

namespace App\Livewire\SystemManagement\OrganizationSetting\EmploymentType;

use Livewire\Component;

class Index extends Component
{
    public $sortBy = '';
    public $sortDirection = 'asc';
    
    // Add Employment Type Flyout Properties
    public $showAddEmploymentTypeFlyout = false;
    public $typeName = '';
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

    public function createEmploymentType()
    {
        $this->resetForm();
        $this->showAddEmploymentTypeFlyout = true;
    }
    
    public function closeAddEmploymentTypeFlyout()
    {
        $this->showAddEmploymentTypeFlyout = false;
        $this->resetForm();
    }
    
    public function submitEmploymentType()
    {
        $this->validate([
            'typeName' => 'required|string|max:255',
            'description' => 'nullable|string',
            'isActive' => 'boolean',
        ]);
        
        // Here you would save the employment type to the database
        // For now, we'll just close the flyout and show a success message
        session()->flash('message', 'Employment Type created successfully!');
        $this->closeAddEmploymentTypeFlyout();
    }
    
    private function resetForm()
    {
        $this->typeName = '';
        $this->description = '';
        $this->isActive = true;
    }

    public function editEmploymentType($id) { /* ... */ }
    public function viewEmploymentType($id) { /* ... */ }
    public function deleteEmploymentType($id) { /* ... */ }

    public function render()
    {
        return view('livewire.system-management.organization-setting.employment-type.index')
            ->layout('components.layouts.app');
    }
}
