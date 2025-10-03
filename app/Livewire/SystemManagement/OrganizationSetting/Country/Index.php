<?php

namespace App\Livewire\SystemManagement\OrganizationSetting\Country;

use Livewire\Component;

class Index extends Component
{
    public $sortBy = '';
    public $sortDirection = 'asc';
    
    // Add Country Flyout Properties
    public $showAddCountryFlyout = false;
    public $countryName = '';
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

    public function createCountry()
    {
        $this->resetForm();
        $this->showAddCountryFlyout = true;
    }
    
    public function closeAddCountryFlyout()
    {
        $this->showAddCountryFlyout = false;
        $this->resetForm();
    }
    
    public function submitCountry()
    {
        $this->validate([
            'countryName' => 'required|string|max:255',
            'description' => 'nullable|string',
            'isActive' => 'boolean',
        ]);
        
        // Here you would save the country to the database
        // For now, we'll just close the flyout and show a success message
        session()->flash('message', 'Country created successfully!');
        $this->closeAddCountryFlyout();
    }
    
    private function resetForm()
    {
        $this->countryName = '';
        $this->description = '';
        $this->isActive = true;
    }

    public function editCountry($id) { /* ... */ }
    public function viewCountry($id) { /* ... */ }
    public function deleteCountry($id) { /* ... */ }

    public function render()
    {
        return view('livewire.system-management.organization-setting.country.index')
            ->layout('components.layouts.app');
    }
}