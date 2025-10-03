<?php

namespace App\Livewire\SystemManagement\LeavesManagement\LeaveTypes;

use Livewire\Component;

class Index extends Component
{
    public function render()
    {
        return view('livewire.system-management.leaves-management.leave-types.index')
            ->layout('components.layouts.app');
    }
}

