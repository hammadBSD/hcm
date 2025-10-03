<?php

namespace App\Livewire\SystemManagement\AttendanceSettings\WorkSchedule;

use Livewire\Component;

class Index extends Component
{
    public function render()
    {
        return view('livewire.system-management.attendance-settings.work-schedule.index')
            ->layout('components.layouts.app');
    }
}

