<?php

namespace App\Livewire\SystemManagement\OrganizationSetting\Department;

use Livewire\Component;

class Index extends Component
{
    public $sortBy = '';
    public $sortDirection = 'asc';

    public function sort($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function createDepartment() { /* ... */ }
    public function editDepartment($id) { /* ... */ }
    public function viewDepartment($id) { /* ... */ }
    public function deleteDepartment($id) { /* ... */ }

    public function render()
    {
        return view('livewire.system-management.organization-setting.department.index')
            ->layout('components.layouts.app');
    }
}
