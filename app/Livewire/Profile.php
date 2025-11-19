<?php

namespace App\Livewire;

use App\Models\Employee;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Profile extends Component
{
    public $employee;
    public $additionalInfo;
    public $organizationalInfo;
    public $salaryLegalCompliance;
    public $user;

    public function mount()
    {
        // Get the current logged-in user
        $this->user = Auth::user();
        
        // Find the employee record for the current user
        $this->employee = Employee::where('user_id', $this->user->id)
            ->with(['additionalInfo', 'organizationalInfo', 'salaryLegalCompliance', 'department', 'designation', 'shift'])
            ->first();
            
        if ($this->employee) {
            $this->additionalInfo = $this->employee->additionalInfo;
            $this->organizationalInfo = $this->employee->organizationalInfo;
            $this->salaryLegalCompliance = $this->employee->salaryLegalCompliance;
        }
    }

    public function render()
    {
        return view('livewire.profile')
            ->layout('components.layouts.app');
    }
}

