<?php

namespace App\Livewire\Payroll;

use App\Models\Employee;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Index extends Component
{
    public $employee;
    public $payslips = [];
    public $currentMonth;
    public $selectedYear;
    public $selectedMonth;

    public function mount()
    {
        // Get the current logged-in user
        $user = Auth::user();
        
        // Find the employee record for the current user
        $this->employee = Employee::where('user_id', $user->id)->first();
        
        // Set current month and year
        $this->currentMonth = now()->format('Y-m');
        $this->selectedYear = now()->year;
        $this->selectedMonth = now()->month;
        
        $this->loadPayslips();
    }

    public function loadPayslips()
    {
        // For now, we'll create some sample data
        // In a real application, this would come from a Payslip model
        $this->payslips = [
            [
                'id' => 1,
                'month' => '2024-01',
                'basic_salary' => 5000,
                'allowances' => 1000,
                'deductions' => 500,
                'net_salary' => 5500,
                'status' => 'paid',
                'paid_date' => '2024-01-31'
            ],
            [
                'id' => 2,
                'month' => '2024-02',
                'basic_salary' => 5000,
                'allowances' => 1000,
                'deductions' => 500,
                'net_salary' => 5500,
                'status' => 'paid',
                'paid_date' => '2024-02-29'
            ]
        ];
    }

    public function updatedSelectedYear()
    {
        $this->loadPayslips();
    }

    public function updatedSelectedMonth()
    {
        $this->loadPayslips();
    }

    public function render()
    {
        return view('livewire.payroll.index')
            ->layout('components.layouts.app');
    }
}
