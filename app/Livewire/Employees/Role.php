<?php

namespace App\Livewire\Employees;

use Livewire\Component;

class Role extends Component
{
    public function render()
    {
        return view('livewire.employees.role')
            ->layout('components.layouts.app');
    }
}
