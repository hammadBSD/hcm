<?php

namespace App\Livewire\SystemManagement\PayrollSettings\PayrollPeriods;

use Livewire\Component;

class Index extends Component
{
    public function render()
    {
        return view('livewire.system-management.payroll-settings.payroll-periods.index')
            ->layout('components.layouts.app');
    }
}

