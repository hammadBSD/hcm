<?php

namespace App\Livewire\SystemManagement\LeavesManagement\LeavePolicies;

use Livewire\Component;

class Index extends Component
{
    public function render()
    {
        return view('livewire.system-management.leaves-management.leave-policies.index')
            ->layout('components.layouts.app');
    }
}

