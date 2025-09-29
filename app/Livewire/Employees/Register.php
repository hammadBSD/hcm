<?php

namespace App\Livewire\Employees;

use Livewire\Component;

class Register extends Component
{
    public function render()
    {
        return view('livewire.employees.register')
            ->layout('components.layouts.app');
    }
}
