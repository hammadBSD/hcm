<?php

namespace App\Livewire\SystemManagement\LeavesManagement\LeaveBalances;

use Livewire\Component;

class Index extends Component
{
    public function render()
    {
        return view('livewire.system-management.leaves-management.leave-balances.index')
            ->layout('components.layouts.app');
    }
}

