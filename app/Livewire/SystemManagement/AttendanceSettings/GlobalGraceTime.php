<?php

namespace App\Livewire\SystemManagement\AttendanceSettings;

use Livewire\Component;
use App\Models\Constant;

class GlobalGraceTime extends Component
{
    public $gracePeriodLateIn = 30;
    public $gracePeriodEarlyOut = 30;

    public function mount()
    {
        $this->loadGracePeriods();
    }

    public function loadGracePeriods()
    {
        $lateInConstant = Constant::where('key', 'attendance_grace_period_late_in')->first();
        $earlyOutConstant = Constant::where('key', 'attendance_grace_period_early_out')->first();

        $this->gracePeriodLateIn = $lateInConstant ? (int)$lateInConstant->value : 30;
        $this->gracePeriodEarlyOut = $earlyOutConstant ? (int)$earlyOutConstant->value : 30;
    }

    public function saveGracePeriods()
    {
        $this->validate([
            'gracePeriodLateIn' => 'required|integer|min:0|max:1440', // Max 24 hours in minutes
            'gracePeriodEarlyOut' => 'required|integer|min:0|max:1440',
        ]);

        // Save or update late in grace period
        Constant::updateOrCreate(
            ['key' => 'attendance_grace_period_late_in'],
            [
                'value' => (string)$this->gracePeriodLateIn,
                'type' => 'integer',
                'category' => 'attendance',
                'description' => 'Global grace period for late check-in (in minutes)',
                'status' => 'active',
            ]
        );

        // Save or update early out grace period
        Constant::updateOrCreate(
            ['key' => 'attendance_grace_period_early_out'],
            [
                'value' => (string)$this->gracePeriodEarlyOut,
                'type' => 'integer',
                'category' => 'attendance',
                'description' => 'Global grace period for early check-out (in minutes)',
                'status' => 'active',
            ]
        );

        session()->flash('message', 'Global grace periods saved successfully!');
        
        $this->dispatch('show-toast', [
            'type' => 'success',
            'message' => 'Global grace periods have been updated successfully!'
        ]);
    }

    public function resetToDefaults()
    {
        $this->gracePeriodLateIn = 30;
        $this->gracePeriodEarlyOut = 30;
        
        session()->flash('message', 'Grace periods reset to defaults!');
        
        $this->dispatch('show-toast', [
            'type' => 'info',
            'message' => 'Grace periods have been reset to default values (30 minutes each)!'
        ]);
    }

    public function render()
    {
        return view('livewire.system-management.attendance-settings.global-grace-time')
            ->layout('components.layouts.app');
    }
}
