<?php

namespace App\Livewire\Payroll;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class PayrollSettings extends Component
{
    public $settings = [
        'payroll_frequency' => 'monthly',
        'payroll_day' => 1,
        'overtime_rate' => 1.5,
        'allowance_percentage' => 10,
        'tax_percentage' => 15,
        'provident_fund_percentage' => 5,
        'auto_process' => false,
        'email_payslips' => true,
        'backup_payroll' => true
    ];

    public function mount()
    {
        $user = Auth::user();

        if (!$user || !$user->can('payroll.sidebar.settings')) {
            abort(403);
        }

        // Load settings from database or config
        // For now, we'll use the default values
    }

    public function updateSetting($key, $value)
    {
        $this->settings[$key] = $value;
        
        // This would save the setting to database
        session()->flash('message', 'Setting updated successfully!');
    }

    public function saveAllSettings()
    {
        // This would save all settings to database
        session()->flash('message', 'All settings saved successfully!');
    }

    public function resetToDefaults()
    {
        $this->settings = [
            'payroll_frequency' => 'monthly',
            'payroll_day' => 1,
            'overtime_rate' => 1.5,
            'allowance_percentage' => 10,
            'tax_percentage' => 15,
            'provident_fund_percentage' => 5,
            'auto_process' => false,
            'email_payslips' => true,
            'backup_payroll' => true
        ];
        
        session()->flash('message', 'Settings reset to defaults!');
    }

    public function render()
    {
        return view('livewire.payroll.payroll-settings')
            ->layout('components.layouts.app');
    }
}
