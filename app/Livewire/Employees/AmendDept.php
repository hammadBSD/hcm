<?php

namespace App\Livewire\Employees;

use Livewire\Component;

class AmendDept extends Component
{
    public function render()
    {
        return view('livewire.employees.amend-dept')
            ->layout('components.layouts.app');
    }
}
