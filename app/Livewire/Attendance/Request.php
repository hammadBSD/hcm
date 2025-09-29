<?php

namespace App\Livewire\Attendance;

use Livewire\Component;

class Request extends Component
{
    public function render()
    {
        return view('livewire.attendance.request')
            ->layout('components.layouts.app');
    }
}
