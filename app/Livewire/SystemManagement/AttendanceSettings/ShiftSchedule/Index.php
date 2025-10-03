<?php

namespace App\Livewire\SystemManagement\AttendanceSettings\ShiftSchedule;

use Livewire\Component;

class Index extends Component
{
    public function render()
    {
        return view('livewire.system-management.attendance-settings.shift-schedule.index')
            ->layout('components.layouts.app');
    }
}

