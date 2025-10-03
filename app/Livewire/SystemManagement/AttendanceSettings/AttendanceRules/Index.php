<?php

namespace App\Livewire\SystemManagement\AttendanceSettings\AttendanceRules;

use Livewire\Component;

class Index extends Component
{
    public function render()
    {
        return view('livewire.system-management.attendance-settings.attendance-rules.index')
            ->layout('components.layouts.app');
    }
}

