<?php

namespace App\Livewire\Employees;

use Livewire\Component;

class Suggestions extends Component
{
    public function render()
    {
        return view('livewire.employees.suggestions')
            ->layout('components.layouts.app');
    }
}
