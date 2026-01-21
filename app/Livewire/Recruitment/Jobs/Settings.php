<?php

namespace App\Livewire\Recruitment\Jobs;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Settings extends Component
{
    public function mount()
    {
        $user = Auth::user();
        
        // Check if user is Super Admin
        if (!$user || !$user->hasRole('Super Admin')) {
            abort(403, 'Unauthorized access. Only Super Admin can access this module.');
        }
    }

    public function render()
    {
        return view('livewire.recruitment.jobs.settings')
            ->layout('components.layouts.app');
    }
}
