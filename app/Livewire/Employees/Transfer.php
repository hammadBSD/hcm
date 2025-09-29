<?php

namespace App\Livewire\Employees;

use Livewire\Component;

class Transfer extends Component
{
    public function render()
    {
        return view('livewire.employees.transfer')
            ->layout('components.layouts.app');
    }
}
