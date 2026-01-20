<?php

namespace App\Livewire\Payroll;

use App\Models\Employee;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class TaxManagement extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedDepartment = '';
    public $taxYear;
    public $showAddTaxModal = false;
    public $sortBy = '';
    public $sortDirection = 'asc';

    public function mount()
    {
        $user = Auth::user();

        if (!$user || !$user->can('payroll.export')) {
            abort(403);
        }

        $this->taxYear = now()->year;
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedSelectedDepartment()
    {
        $this->resetPage();
    }

    public function updatedTaxYear()
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

    public function openAddTaxModal()
    {
        $this->showAddTaxModal = true;
    }

    public function closeAddTaxModal()
    {
        $this->showAddTaxModal = false;
    }

    public function addTaxRecord()
    {
        // This would handle adding tax record
        $this->closeAddTaxModal();
        session()->flash('message', 'Tax record added successfully!');
    }

    public function generateTaxReport()
    {
        // This would generate tax report
        session()->flash('message', 'Tax report generated successfully!');
    }

    public function render()
    {
        // For now, we'll return sample data
        // In a real application, this would query actual tax data
        $taxRecords = collect([
            [
                'id' => 1,
                'employee_name' => 'John Doe',
                'employee_code' => 'EMP001',
                'department' => 'IT',
                'salary_from' => 1,
                'salary_to' => 50000,
                'taxable_income' => 55000,
                'income_tax' => 5500,
                'year' => '2024',
                'status' => 'calculated'
            ],
            [
                'id' => 2,
                'employee_name' => 'Jane Smith',
                'employee_code' => 'EMP002',
                'department' => 'HR',
                'salary_from' => 1,
                'salary_to' => 50000,
                'taxable_income' => 49000,
                'income_tax' => 4900,
                'year' => '2024',
                'status' => 'calculated'
            ]
        ]);

        $departments = ['IT', 'HR', 'Finance', 'Marketing', 'Operations'];

        return view('livewire.payroll.tax-management', [
            'taxRecords' => $taxRecords,
            'departments' => $departments
        ])->layout('components.layouts.app');
    }
}
