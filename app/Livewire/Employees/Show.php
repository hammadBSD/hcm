<?php

namespace App\Livewire\Employees;

use App\Models\Employee;
use App\Models\EmployeeIncrement;
use Livewire\Component;

class Show extends Component
{
    public $employeeId;
    public $employee;
    public $additionalInfo;
    public $organizationalInfo;
    public $salaryLegalCompliance;
    public $user;

    public function mount($id)
    {
        $this->employeeId = $id;
        
        // Find the employee record by user_id (since the URL uses user_id)
        $this->employee = Employee::where('user_id', $this->employeeId)
            ->with(['user', 'group', 'additionalInfo', 'organizationalInfo', 'salaryLegalCompliance'])
            ->first();
            
        if ($this->employee) {
            $this->user = $this->employee->user;
            $this->additionalInfo = $this->employee->additionalInfo;
            $this->organizationalInfo = $this->employee->organizationalInfo;
            $this->salaryLegalCompliance = $this->employee->salaryLegalCompliance;
        }
    }

    public function render()
    {
        $increments = $this->employee
            ? EmployeeIncrement::where('employee_id', $this->employee->id)
                ->orderByDesc('last_increment_date')
                ->orderByDesc('created_at')
                ->get()
            : collect();

        return view('livewire.employees.show', [
            'increments' => $increments,
        ])->layout('components.layouts.app');
    }
}
