<?php

namespace App\Livewire\Payroll;

use App\Models\Employee;
use Livewire\Component;
use Livewire\WithPagination;

class AdvanceSalary extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedDepartment = '';
    public $status = '';
    public $showAddAdvanceModal = false;
    public $sortBy = '';
    public $sortDirection = 'asc';

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedSelectedDepartment()
    {
        $this->resetPage();
    }

    public function updatedStatus()
    {
        $this->resetPage();
    }

    public function sort($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function openAddAdvanceModal()
    {
        $this->showAddAdvanceModal = true;
    }

    public function closeAddAdvanceModal()
    {
        $this->showAddAdvanceModal = false;
    }

    public function addAdvanceSalary()
    {
        // This would handle adding advance salary request
        $this->closeAddAdvanceModal();
        session()->flash('message', 'Advance salary request submitted successfully!');
    }

    public function approveAdvance($id)
    {
        // This would handle approving advance salary
        session()->flash('message', 'Advance salary approved successfully!');
    }

    public function rejectAdvance($id)
    {
        // This would handle rejecting advance salary
        session()->flash('message', 'Advance salary rejected successfully!');
    }

    public function render()
    {
        // For now, we'll return sample data
        // In a real application, this would query actual advance salary data
        $advanceRequests = collect([
            [
                'id' => 1,
                'employee_name' => 'John Doe',
                'employee_code' => 'EMP001',
                'department' => 'IT',
                'amount' => 2000,
                'reason' => 'Medical emergency',
                'request_date' => '2024-01-15',
                'status' => 'pending',
                'approved_by' => null
            ],
            [
                'id' => 2,
                'employee_name' => 'Jane Smith',
                'employee_code' => 'EMP002',
                'department' => 'HR',
                'amount' => 1500,
                'reason' => 'Family emergency',
                'request_date' => '2024-01-20',
                'status' => 'approved',
                'approved_by' => 'HR Manager'
            ]
        ]);

        $departments = ['IT', 'HR', 'Finance', 'Marketing', 'Operations'];
        $statuses = ['pending', 'approved', 'rejected'];

        return view('livewire.payroll.advance-salary', [
            'advanceRequests' => $advanceRequests,
            'departments' => $departments,
            'statuses' => $statuses
        ])->layout('components.layouts.app');
    }
}
