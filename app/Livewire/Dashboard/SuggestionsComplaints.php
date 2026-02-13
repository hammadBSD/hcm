<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeSuggestion;

class SuggestionsComplaints extends Component
{
    public $showFlyout = false;
    public $type = 'complaint';
    public $complaintType = '';
    public $departmentId = '';
    public $priority = 'medium';
    public $message = '';

    protected $rules = [
        'type' => 'required|in:complaint',
        'complaintType' => 'required_if:type,complaint',
        'departmentId' => 'required|exists:departments,id',
        'priority' => 'required|in:low,medium,high,urgent',
        'message' => 'required|string|min:10|max:2000',
    ];

    protected $messages = [
        'type.required' => 'Please select a type.',
        'type.in' => 'Invalid type selected.',
        'complaintType.required_if' => 'Please select a complaint type.',
        'departmentId.required' => 'Please select a department.',
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
        $this->type = 'complaint';
        $this->complaintType = '';
        $this->departmentId = '';
        $this->priority = 'medium';
        $this->message = '';
        $this->resetErrorBag();
    }

    public function updatedType()
    {
        if ($this->type === 'complaint') {
            // keep complaintType
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
            'department_id' => $this->departmentId ?: null,
            'priority' => $this->priority,
            'message' => $this->message,
            'status' => 'pending',
        ]);

        session()->flash('success', __('Your complaint has been submitted successfully!'));
        $this->dispatch('notify', type: 'success', message: __('Your complaint has been submitted successfully!'));

        $this->closeFlyout();
    }

    protected $listeners = ['open-suggestions-flyout' => 'openFlyout'];

    public function render()
    {
        $departments = Department::where('status', 'active')
            ->orderBy('title')
            ->get()
            ->map(fn ($d) => ['id' => $d->id, 'label' => $d->title . ($d->code ? ' (' . $d->code . ')' : '')])
            ->toArray();

        return view('livewire.dashboard.suggestions-complaints', [
            'departments' => $departments,
        ]);
    }
}
