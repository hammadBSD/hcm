<?php

namespace App\Livewire\Payroll;

use App\Models\Employee;
use Illuminate\Support\Facades\Auth;
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
    public $showMonthEmployeesFlyout = false;
    public $selectedMonthForFlyout = null;
    public $selectedYearForFlyout = null;
    public $monthEmployees = [];
    public $showProcessPayrollModal = false;
    public $processMonth = null;
    public $processYear = null;
    public $selectedProcessingType = null; // 'monthly_attendance' or 'custom'

    public function mount()
    {
        $user = Auth::user();

        if (!$user || !$user->can('payroll.process')) {
            abort(403);
        }

        $this->selectedYear = now()->year;
        $this->processMonth = now()->month;
        $this->processYear = now()->year;
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

    public function openProcessPayrollModal()
    {
        $this->processMonth = now()->month;
        $this->processYear = now()->year;
        $this->selectedProcessingType = null;
        $this->showProcessPayrollModal = true;
    }

    public function closeProcessPayrollModal()
    {
        $this->showProcessPayrollModal = false;
        $this->selectedProcessingType = null;
    }

    public function selectProcessingType($type)
    {
        $this->selectedProcessingType = $type;
    }

    public function createPayroll()
    {
        if (!$this->selectedProcessingType) {
            session()->flash('error', 'Please select a processing type.');
            return;
        }

        if ($this->selectedProcessingType === 'monthly_attendance') {
            // Process monthly attendance payroll
            session()->flash('message', 'Monthly attendance payroll processing initiated for ' . \Carbon\Carbon::create($this->processYear, $this->processMonth, 1)->format('F Y') . '!');
        } elseif ($this->selectedProcessingType === 'custom') {
            // Process custom payroll
            session()->flash('message', 'Custom payroll processing initiated for ' . \Carbon\Carbon::create($this->processYear, $this->processMonth, 1)->format('F Y') . '!');
        }

        $this->closeProcessPayrollModal();
    }

    public function openMonthEmployeesFlyout($month, $year)
    {
        $this->selectedMonthForFlyout = $month;
        $this->selectedYearForFlyout = $year;
        
        // Load employees for this month
        $this->loadMonthEmployees($month, $year);
        
        $this->showMonthEmployeesFlyout = true;
    }

    public function closeMonthEmployeesFlyout()
    {
        $this->showMonthEmployeesFlyout = false;
        $this->selectedMonthForFlyout = null;
        $this->selectedYearForFlyout = null;
        $this->monthEmployees = [];
    }

    public function loadMonthEmployees($month, $year)
    {
        // Get employees for the selected month
        $query = Employee::with(['salaryLegalCompliance', 'department'])
            ->where('status', 'active');

        if ($this->selectedDepartment) {
            $query->whereHas('department', function ($q) {
                $q->where('title', $this->selectedDepartment);
            });
        }

        $employees = $query->get();

        $this->monthEmployees = $employees->map(function ($employee) use ($month, $year) {
            $salary = $employee->salaryLegalCompliance;
            return [
                'id' => $employee->id,
                'name' => $employee->first_name . ' ' . $employee->last_name,
                'employee_code' => $employee->employee_code ?? 'N/A',
                'department' => $employee->department ? $employee->department->title : 'N/A',
                'basic_salary' => $salary ? (float)$salary->basic_salary : 0,
                'allowances' => $salary ? (float)$salary->allowances : 0,
                'deductions' => 0, // This would come from actual payroll calculations
                'net_salary' => ($salary ? ((float)$salary->basic_salary + ((float)($salary->allowances ?? 0))) : 0) - 0,
                'status' => 'pending',
            ];
        })->toArray();
    }

    public function render()
    {
        // Get unique months from available data (for now, generate based on selected year)
        // In a real app, this would come from a payroll table
        $months = [];
        $currentYear = $this->selectedYear ?? now()->year;
        
        // Generate months for the selected year (last 12 months)
        for ($i = 0; $i < 12; $i++) {
            $date = \Carbon\Carbon::create($currentYear, now()->month, 1)->subMonths($i);
            $monthNum = $date->month;
            $yearNum = $date->year;
            $monthName = $date->format('F Y');
            
            // Determine status (mock: current and future months are pending, past are processed)
            $isPending = $date->isFuture() || ($date->year == now()->year && $date->month == now()->month);
            
            $months[] = [
                'month' => $monthNum,
                'year' => $yearNum,
                'month_name' => $monthName,
                'status' => $isPending ? 'pending' : 'processed',
                'employee_count' => 0, // Would be calculated from actual data
                'total_salary' => 0, // Would be calculated from actual data
            ];
        }

        // Filter by selected year
        $months = collect($months)->filter(function ($month) {
            return $month['year'] == ($this->selectedYear ?? now()->year);
        })->values();

        // Get departments for filter
        $departments = \App\Models\Department::where('status', 'active')
            ->orderBy('title')
            ->pluck('title')
            ->toArray();

        return view('livewire.payroll.payroll-processing', [
            'months' => $months,
            'departments' => $departments
        ])->layout('components.layouts.app');
    }
}
