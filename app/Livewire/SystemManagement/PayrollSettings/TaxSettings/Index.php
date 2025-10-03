<?php

namespace App\Livewire\SystemManagement\PayrollSettings\TaxSettings;

use Livewire\Component;

class Index extends Component
{
    public function render()
    {
        return view('livewire.system-management.payroll-settings.tax-settings.index')
            ->layout('components.layouts.app');
    }
}

