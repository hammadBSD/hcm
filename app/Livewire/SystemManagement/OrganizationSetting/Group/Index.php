<?php

namespace App\Livewire\SystemManagement\OrganizationSetting\Group;

use Livewire\Component;

class Index extends Component
{
    public $sortBy = '';
    public $sortDirection = 'asc';
    
    // Add Group Flyout Properties
    public $showAddGroupFlyout = false;
    public $groupName = '';
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

    public function createGroup()
    {
        $this->resetForm();
        $this->showAddGroupFlyout = true;
    }
    
    public function closeAddGroupFlyout()
    {
        $this->showAddGroupFlyout = false;
        $this->resetForm();
    }
    
    public function submitGroup()
    {
        $this->validate([
            'groupName' => 'required|string|max:255',
            'description' => 'nullable|string',
            'isActive' => 'boolean',
        ]);
        
        // Here you would save the group to the database
        // For now, we'll just close the flyout and show a success message
        session()->flash('message', 'Group created successfully!');
        $this->closeAddGroupFlyout();
    }
    
    private function resetForm()
    {
        $this->groupName = '';
        $this->description = '';
        $this->isActive = true;
    }

    public function editGroup($id) { /* ... */ }
    public function viewGroup($id) { /* ... */ }
    public function deleteGroup($id) { /* ... */ }

    public function render()
    {
        return view('livewire.system-management.organization-setting.group.index')
            ->layout('components.layouts.app');
    }
}