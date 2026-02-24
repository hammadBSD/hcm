<?php

namespace App\Livewire\Payroll;

use App\Models\PayrollSetting;
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
        'tax_calculation_method' => 'percentage',
        'short_hours_threshold' => 9,
        'hours_per_day' => 9,
        'absent_deduction_use_formula' => true,
        'per_day_absent_deduction' => 0,
        'short_hours_deduction_per_hour' => null,
        'auto_process' => false,
        'email_payslips' => true,
        'backup_payroll' => true,
    ];

    public function mount()
    {
        $user = Auth::user();

        if (!$user || !$user->can('payroll.sidebar.settings')) {
            abort(403);
        }

        $row = PayrollSetting::first();
        if ($row) {
            $this->settings = [
                'payroll_frequency' => $row->payroll_frequency,
                'payroll_day' => (int) $row->payroll_day,
                'overtime_rate' => (float) $row->overtime_rate,
                'allowance_percentage' => (float) $row->allowance_percentage,
                'tax_percentage' => (float) $row->tax_percentage,
                'provident_fund_percentage' => (float) $row->provident_fund_percentage,
                'tax_calculation_method' => $row->tax_calculation_method ?? 'percentage',
                'short_hours_threshold' => (float) $row->short_hours_threshold,
                'hours_per_day' => (float) ($row->hours_per_day ?? 9),
                'absent_deduction_use_formula' => (bool) ($row->absent_deduction_use_formula ?? true),
                'per_day_absent_deduction' => (float) $row->per_day_absent_deduction,
                'short_hours_deduction_per_hour' => $row->short_hours_deduction_per_hour !== null ? (float) $row->short_hours_deduction_per_hour : null,
                'auto_process' => (bool) $row->auto_process,
                'email_payslips' => (bool) $row->email_payslips,
                'backup_payroll' => (bool) $row->backup_payroll,
            ];
        }
    }

    public function updateSetting($key, $value)
    {
        $this->settings[$key] = $value;
        $this->saveToDb();
        session()->flash('message', __('Setting updated successfully!'));
    }

    public function saveAllSettings()
    {
        $this->saveToDb();
        PayrollSetting::clearCached();
        session()->flash('message', __('All settings saved successfully!'));
    }

    protected function saveToDb(): void
    {
        $data = [
            'payroll_frequency' => $this->settings['payroll_frequency'] ?? 'monthly',
            'payroll_day' => (int) ($this->settings['payroll_day'] ?? 1),
            'overtime_rate' => (float) ($this->settings['overtime_rate'] ?? 1.5),
            'allowance_percentage' => (float) ($this->settings['allowance_percentage'] ?? 10),
            'tax_percentage' => (float) ($this->settings['tax_percentage'] ?? 15),
            'provident_fund_percentage' => (float) ($this->settings['provident_fund_percentage'] ?? 5),
            'tax_calculation_method' => $this->settings['tax_calculation_method'] ?? 'percentage',
            'short_hours_threshold' => (float) ($this->settings['short_hours_threshold'] ?? 9),
            'hours_per_day' => (float) ($this->settings['hours_per_day'] ?? 9),
            'absent_deduction_use_formula' => (bool) ($this->settings['absent_deduction_use_formula'] ?? true),
            'per_day_absent_deduction' => (float) ($this->settings['per_day_absent_deduction'] ?? 0),
            'short_hours_deduction_per_hour' => isset($this->settings['short_hours_deduction_per_hour']) && $this->settings['short_hours_deduction_per_hour'] !== '' && $this->settings['short_hours_deduction_per_hour'] !== null
                ? (float) $this->settings['short_hours_deduction_per_hour'] : null,
            'auto_process' => (bool) ($this->settings['auto_process'] ?? false),
            'email_payslips' => (bool) ($this->settings['email_payslips'] ?? true),
            'backup_payroll' => (bool) ($this->settings['backup_payroll'] ?? true),
        ];
        $row = PayrollSetting::first();
        if ($row) {
            $row->update($data);
        } else {
            PayrollSetting::create($data);
        }
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
            'tax_calculation_method' => PayrollSetting::TAX_METHOD_PERCENTAGE,
            'short_hours_threshold' => 9,
            'hours_per_day' => 9,
            'absent_deduction_use_formula' => true,
            'per_day_absent_deduction' => 0,
            'short_hours_deduction_per_hour' => null,
            'auto_process' => false,
            'email_payslips' => true,
            'backup_payroll' => true,
        ];
        $this->saveToDb();
        PayrollSetting::clearCached();
        session()->flash('message', __('Settings reset to defaults!'));
    }

    public function render()
    {
        return view('livewire.payroll.payroll-settings')
            ->layout('components.layouts.app');
    }
}
