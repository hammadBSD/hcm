<?php

namespace App\Livewire\Recruitment\Jobs;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ShowSettings extends Component
{
    public $jobId;
    public $job = null;

    public function mount($id)
    {
        $user = Auth::user();
        
        // Check if user is Super Admin or HR Manager
        if (!$user || (!$user->hasRole('Super Admin') && !$user->hasRole('HR Manager'))) {
            abort(403, 'Unauthorized access. Only Super Admin and HR Manager can access this module.');
        }

        $this->jobId = $id;
        
        // Mock job data - will be replaced with actual database query later
        $this->job = [
            'id' => $id,
            'title' => 'Senior Software Developer',
            'department' => 'IT',
            'entry_level' => 'Senior',
            'position_type' => 'Full Time',
            'work_type' => 'Remote',
            'hiring_priority' => 'Urgent',
            'number_of_positions' => 2,
            'status' => 'active',
        ];
    }

    public function render()
    {
        return view('livewire.recruitment.jobs.show-settings')
            ->layout('components.layouts.app');
    }
}
