<?php

namespace App\Livewire\SystemManagement;

use Livewire\Component;

class Index extends Component
{
    public function render()
    {
        return view('livewire.system-management.index')
            ->layout('components.layouts.app');
    }
}
