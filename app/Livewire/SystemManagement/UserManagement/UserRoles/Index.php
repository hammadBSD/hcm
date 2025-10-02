<?php

namespace App\Livewire\SystemManagement\UserManagement\UserRoles;

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

    public function createRole() { /* ... */ }
    public function editRole($id) { /* ... */ }
    public function viewRole($id) { /* ... */ }
    public function duplicateRole($id) { /* ... */ }

    public function render()
    {
        return view('livewire.system-management.user-management.user-roles.index')
            ->layout('components.layouts.app');
    }
}
