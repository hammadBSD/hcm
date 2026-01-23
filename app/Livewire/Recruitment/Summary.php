<?php

namespace App\Livewire\Recruitment;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Summary extends Component
{
    public function mount()
    {
        $user = Auth::user();
        
        // Check if user is Super Admin or HR Manager
        if (!$user || (!$user->hasRole('Super Admin') && !$user->hasRole('HR Manager'))) {
            abort(403, 'Unauthorized access. Only Super Admin and HR Manager can access this module.');
        }
    }

    public function render()
    {
        return view('livewire.recruitment.summary')
            ->layout('components.layouts.app');
    }
}
