<?php

namespace App\Livewire\Leaves;

use Livewire\Component;

class LeaveApprovals extends Component
{
    public function render()
    {
        return view('livewire.leaves.leave-approvals')
            ->layout('components.layouts.app');
    }
}
