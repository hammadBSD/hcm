<?php

namespace App\Livewire\SystemManagement\OrganizationSetting\Province;

use Livewire\Component;

class Index extends Component
{
    public $sortBy = '';
    public $sortDirection = 'asc';
    
    // Add Province Flyout Properties
    public $showAddProvinceFlyout = false;
    public $provinceName = '';
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

    public function createProvince()
    {
        $this->resetForm();
        $this->showAddProvinceFlyout = true;
    }
    
    public function closeAddProvinceFlyout()
    {
        $this->showAddProvinceFlyout = false;
        $this->resetForm();
    }
    
    public function submitProvince()
    {
        $this->validate([
            'provinceName' => 'required|string|max:255',
            'description' => 'nullable|string',
            'isActive' => 'boolean',
        ]);
        
        // Here you would save the province to the database
        // For now, we'll just close the flyout and show a success message
        session()->flash('message', 'Province created successfully!');
        $this->closeAddProvinceFlyout();
    }
    
    private function resetForm()
    {
        $this->provinceName = '';
        $this->description = '';
        $this->isActive = true;
    }

    public function editProvince($id) { /* ... */ }
    public function viewProvince($id) { /* ... */ }
    public function deleteProvince($id) { /* ... */ }

    public function render()
    {
        return view('livewire.system-management.organization-setting.province.index')
            ->layout('components.layouts.app');
    }
}