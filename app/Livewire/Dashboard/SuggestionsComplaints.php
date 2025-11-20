<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\Employee;
use App\Models\EmployeeSuggestion;

class SuggestionsComplaints extends Component
{
    public $showFlyout = false;
    public $type = 'suggestion'; // 'suggestion' or 'complaint'
    public $complaintType = '';
    public $message = '';

    protected $rules = [
        'type' => 'required|in:suggestion,complaint',
        'complaintType' => 'required_if:type,complaint',
        'message' => 'required|string|min:10|max:2000',
    ];

    protected $messages = [
        'type.required' => 'Please select a type.',
        'type.in' => 'Invalid type selected.',
        'complaintType.required_if' => 'Please select a complaint type.',
        'message.required' => 'Please enter your message.',
        'message.min' => 'Message must be at least 10 characters.',
        'message.max' => 'Message cannot exceed 2000 characters.',
    ];

    public function openFlyout()
    {
        $this->showFlyout = true;
        $this->resetForm();
    }

    public function closeFlyout()
    {
        $this->showFlyout = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->type = 'suggestion';
        $this->complaintType = '';
        $this->message = '';
        $this->resetErrorBag();
    }

    public function updatedType()
    {
        if ($this->type === 'suggestion') {
            $this->complaintType = '';
        }
        $this->resetErrorBag('complaintType');
    }

    public function submit()
    {
        $this->validate();

        $user = Auth::user();
        $employee = Employee::where('user_id', $user->id)->first();

        if (!$employee) {
            session()->flash('error', 'Employee record not found. Please contact administrator.');
            return;
        }

        EmployeeSuggestion::create([
            'employee_id' => $employee->id,
            'type' => $this->type,
            'complaint_type' => $this->type === 'complaint' ? $this->complaintType : null,
            'message' => $this->message,
            'status' => 'pending',
        ]);

        session()->flash('success', $this->type === 'suggestion' 
            ? 'Your suggestion has been submitted successfully!' 
            : 'Your complaint has been submitted successfully!');

        $this->closeFlyout();
    }

    protected $listeners = ['open-suggestions-flyout' => 'openFlyout'];

    public function render()
    {
        return view('livewire.dashboard.suggestions-complaints');
    }
}
