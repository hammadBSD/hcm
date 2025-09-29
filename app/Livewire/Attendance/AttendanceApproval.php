<?php

namespace App\Livewire\Attendance;

use Livewire\Component;

class AttendanceApproval extends Component
{
    public function render()
    {
        return view('livewire.attendance.attendance-approval')
            ->layout('components.layouts.app');
    }
}
