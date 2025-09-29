<?php

namespace App\Livewire\Attendance;

use Livewire\Component;

class Schedule extends Component
{
    public function render()
    {
        return view('livewire.attendance.schedule')
            ->layout('components.layouts.app');
    }
}
