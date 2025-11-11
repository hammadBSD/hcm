<?php

namespace App\Livewire\Leaves;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class LeaveRequest extends Component
{
    // Form Properties
    public $leaveType = '';
    public $leaveDuration = 'full_day';
    public $leaveDays = '';
    public $leaveFrom = '';
    public $leaveTo = '';
    public $reason = '';

    protected $rules = [
        'leaveType' => 'required|string',
        'leaveDuration' => 'required|string',
        'leaveDays' => 'required|numeric|min:0.1|max:365',
        'leaveFrom' => 'required|date',
        'leaveTo' => 'required|date|after_or_equal:leaveFrom',
        'reason' => 'required|string|min:10',
    ];

    protected $messages = [
        'leaveType.required' => 'Please select a leave type.',
        'leaveDuration.required' => 'Please select leave duration.',
        'leaveDays.required' => 'Please enter the number of leave days.',
        'leaveDays.numeric' => 'Leave days must be a valid number.',
        'leaveDays.min' => 'Leave days must be at least 0.1.',
        'leaveDays.max' => 'Leave days cannot exceed 365.',
        'leaveFrom.required' => 'Please select leave start date.',
        'leaveFrom.date' => 'Please enter a valid start date.',
        'leaveTo.required' => 'Please select leave end date.',
        'leaveTo.date' => 'Please enter a valid end date.',
        'leaveTo.after_or_equal' => 'Leave end date must be on or after start date.',
        'reason.required' => 'Please provide a reason for the leave request.',
        'reason.min' => 'Reason must be at least 10 characters long.',
    ];

    public function mount()
    {
        $user = Auth::user();

        if (!$user || !$user->can('leaves.request.submit')) {
            abort(403);
        }

        // Set default dates to today
        $this->leaveFrom = now()->format('Y-m-d');
        $this->leaveTo = now()->format('Y-m-d');
    }


    public function submit()
    {
        $this->authorizeRequestSubmission();

        $this->validate();

        // Here you would typically save the leave request to database
        // For now, we'll just show a success message
        
        session()->flash('success', 'Leave request submitted successfully!');
        
        // Reset form
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->leaveType = '';
        $this->leaveDuration = 'full_day';
        $this->leaveFrom = now()->format('Y-m-d');
        $this->leaveTo = now()->format('Y-m-d');
        $this->reason = '';
        $this->leaveDays = '';
    }

    protected function authorizeRequestSubmission(): void
    {
        $user = Auth::user();

        if (!$user || !$user->can('leaves.request.submit')) {
            abort(403);
        }
    }

    public function render()
    {
        return view('livewire.leaves.leave-request')
            ->layout('components.layouts.app');
    }
}
