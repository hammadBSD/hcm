<?php

namespace App\Livewire\Employees;

use Livewire\Component;

class EmployeeList extends Component
{
    public function render()
    {
        return view('livewire.employees.list')
            ->layout('components.layouts.app');
    }
}
