<?php

namespace App\Livewire\SystemManagement\SystemConfiguration\Polls;

use Livewire\Component;

class Index extends Component
{
    public $sortBy = '';
    public $sortDirection = 'asc';

    public function sort($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function render()
    {
        return view('livewire.system-management.system-configuration.polls.index')
            ->layout('components.layouts.app');
    }
}
