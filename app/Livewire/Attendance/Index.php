<?php

namespace App\Livewire\Attendance;

use Livewire\Component;

class Index extends Component
{
    public function render()
    {
        return view('livewire.attendance.index')
            ->layout('components.layouts.app');
    }
}
