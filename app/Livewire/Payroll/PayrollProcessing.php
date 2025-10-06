<?php

namespace App\Livewire\Payroll;

use App\Models\Employee;
use Livewire\Component;
use Livewire\WithPagination;

class PayrollProcessing extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedDepartment = '';
    public $selectedMonth;
    public $selectedYear;
    public $processingStatus = 'pending';
    public $sortBy = '';
    public $sortDirection = 'asc';

    public function mount()
    {
        $this->selectedMonth = now()->month;
        $this->selectedYear = now()->year;
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedSelectedDepartment()
    {
        $this->resetPage();
    }

    public function updatedSelectedMonth()
    {
        $this->resetPage();
    }

    public function updatedSelectedYear()
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

    public function processPayroll()
    {
        // This would contain the actual payroll processing logic
        session()->flash('message', 'Payroll processing initiated successfully!');
    }

    public function render()
    {
        $employees = Employee::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('first_name', 'like', '%' . $this->search . '%')
                      ->orWhere('last_name', 'like', '%' . $this->search . '%')
                      ->orWhere('employee_code', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->selectedDepartment, function ($query) {
                $query->where('department', $this->selectedDepartment);
            })
            ->where('status', 'active')
            ->paginate(10);

        $departments = Employee::distinct()->pluck('department')->filter();

        return view('livewire.payroll.payroll-processing', [
            'employees' => $employees,
            'departments' => $departments
        ])->layout('components.layouts.app');
    }
}
