<?php

namespace App\Livewire\Employees;

use Livewire\Component;

class Create extends Component
{
    public function render()
    {
        return view('livewire.employees.create')
            ->layout('components.layouts.app');
    }
}
