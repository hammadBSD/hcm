<?php

namespace App\Livewire\Attendance;

use Livewire\Component;

class ExemptionRequest extends Component
{
    public function render()
    {
        return view('livewire.attendance.exemption-request')
            ->layout('components.layouts.app');
    }
}
