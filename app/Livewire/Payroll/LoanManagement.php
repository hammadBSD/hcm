<?php

namespace App\Livewire\Payroll;

use App\Models\Employee;
use Livewire\Component;
use Livewire\WithPagination;

class LoanManagement extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedDepartment = '';
    public $loanStatus = '';
    public $showAddLoanModal = false;
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

    public function updatedLoanStatus()
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

    public function openAddLoanModal()
    {
        $this->showAddLoanModal = true;
    }

    public function closeAddLoanModal()
    {
        $this->showAddLoanModal = false;
    }

    public function addLoan()
    {
        // This would handle adding loan request
        $this->closeAddLoanModal();
        session()->flash('message', 'Loan request submitted successfully!');
    }

    public function approveLoan($id)
    {
        // This would handle approving loan
        session()->flash('message', 'Loan approved successfully!');
    }

    public function rejectLoan($id)
    {
        // This would handle rejecting loan
        session()->flash('message', 'Loan rejected successfully!');
    }

    public function render()
    {
        // For now, we'll return sample data
        // In a real application, this would query actual loan data
        $loans = collect([
            [
                'id' => 1,
                'employee_name' => 'John Doe',
                'employee_code' => 'EMP001',
                'department' => 'IT',
                'loan_amount' => 10000,
                'loan_type' => 'Personal',
                'installment_amount' => 1000,
                'total_installments' => 10,
                'remaining_installments' => 8,
                'request_date' => '2024-01-01',
                'status' => 'approved'
            ],
            [
                'id' => 2,
                'employee_name' => 'Jane Smith',
                'employee_code' => 'EMP002',
                'department' => 'HR',
                'loan_amount' => 15000,
                'loan_type' => 'Housing',
                'installment_amount' => 1500,
                'total_installments' => 12,
                'remaining_installments' => 12,
                'request_date' => '2024-01-15',
                'status' => 'pending'
            ]
        ]);

        $departments = ['IT', 'HR', 'Finance', 'Marketing', 'Operations'];
        $loanTypes = ['Personal', 'Housing', 'Vehicle', 'Education', 'Medical'];
        $statuses = ['pending', 'approved', 'rejected', 'completed'];

        return view('livewire.payroll.loan-management', [
            'loans' => $loans,
            'departments' => $departments,
            'loanTypes' => $loanTypes,
            'statuses' => $statuses
        ])->layout('components.layouts.app');
    }
}
