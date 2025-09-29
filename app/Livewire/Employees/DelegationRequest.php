<?php

namespace App\Livewire\Employees;

use Livewire\Component;

class DelegationRequest extends Component
{
    public function render()
    {
        return view('livewire.employees.delegation-request')
            ->layout('components.layouts.app');
    }
}
