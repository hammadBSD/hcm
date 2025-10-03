<?php

namespace App\Livewire\SystemManagement\PayrollSettings\SalaryComponents;

use Livewire\Component;

class Index extends Component
{
    public function render()
    {
        return view('livewire.system-management.payroll-settings.salary-components.index')
            ->layout('components.layouts.app');
    }
}

