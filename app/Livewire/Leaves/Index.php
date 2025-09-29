<?php

namespace App\Livewire\Leaves;

use Livewire\Component;

class Index extends Component
{
    public function render()
    {
        return view('livewire.leaves.index')
            ->layout('components.layouts.app');
    }
}
