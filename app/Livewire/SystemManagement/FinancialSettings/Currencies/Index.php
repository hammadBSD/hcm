<?php

namespace App\Livewire\SystemManagement\FinancialSettings\Currencies;

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
        return view('livewire.system-management.financial-settings.currencies.index')
            ->layout('components.layouts.app');
    }
}
