<?php

namespace App\Livewire\Employees;

use Livewire\Component;

class Edit extends Component
{
    public function render()
    {
        return view('livewire.employees.edit')
            ->layout('components.layouts.app');
    }
}
