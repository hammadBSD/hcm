<?php

namespace App\Livewire\Recruitment;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Summary extends Component
{
    public function mount()
    {
        $user = Auth::user();

        if (!$user || !$user->can('recruitment.summary')) {
            abort(403, 'Unauthorized access.');
        }
    }

    public function render()
    {
        return view('livewire.recruitment.summary')
            ->layout('components.layouts.app');
    }
}
