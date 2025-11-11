<?php

namespace App\Livewire\Payroll;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class SalaryReports extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedDepartment = '';
    public $selectedYear;
    public $reportType = 'monthly';
    public $sortBy = '';
    public $sortDirection = 'asc';

    public function mount()
    {
        $user = Auth::user();

        if (!$user || !$user->can('payroll.view')) {
            abort(403);
        }

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

    public function updatedReportType()
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

    public function generateReport()
    {
        // This would generate the actual salary report
        session()->flash('message', 'Salary report generated successfully!');
    }

    public function exportReport()
    {
        // This would export the report to Excel/PDF
        session()->flash('message', 'Report exported successfully!');
    }

    public function render()
    {
        // For now, we'll return sample data
        // In a real application, this would query actual salary data
        $salaryData = collect([
            [
                'id' => 1,
                'employee_name' => 'John Doe',
                'employee_code' => 'EMP001',
                'department' => 'IT',
                'basic_salary' => 5000,
                'total_allowances' => 1000,
                'total_deductions' => 500,
                'net_salary' => 5500,
                'month' => '2024-01'
            ],
            [
                'id' => 2,
                'employee_name' => 'Jane Smith',
                'employee_code' => 'EMP002',
                'department' => 'HR',
                'basic_salary' => 4500,
                'total_allowances' => 800,
                'total_deductions' => 400,
                'net_salary' => 4900,
                'month' => '2024-01'
            ]
        ]);

        $departments = ['IT', 'HR', 'Finance', 'Marketing', 'Operations'];

        return view('livewire.payroll.salary-reports', [
            'salaryData' => $salaryData,
            'departments' => $departments
        ])->layout('components.layouts.app');
    }
}
