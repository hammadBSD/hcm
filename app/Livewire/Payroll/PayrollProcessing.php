<?php

namespace App\Livewire\Payroll;

use App\Models\Employee;
use App\Models\PayrollRun;
use App\Services\PayrollRunService;
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
    public $selectedRunId = null; // when set, show run detail view
    public $lineEdits = [];

    public function mount($run = null)
    {
        $user = Auth::user();

        if (!$user || !$user->can('payroll.process')) {
            abort(403);
        }

        $this->selectedYear = now()->year;
        $this->processMonth = now()->month;
        $this->processYear = now()->year;

        if ($run !== null && $run !== '') {
            $this->selectedRunId = (int) $run;
        }
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
            session()->flash('error', __('Please select a processing type.'));
            return;
        }

        try {
            $service = app(PayrollRunService::class);
            $run = $service->createDraftRun(
                (int) $this->processMonth,
                (int) $this->processYear,
                $this->selectedProcessingType
            );
            $this->closeProcessPayrollModal();
            session()->flash('message', __('Payroll run created for :period. Review and approve when ready.', ['period' => $run->period_label]));
            $this->selectedRunId = $run->id;
            $this->hydrateLineEdits($run);
        } catch (\Throwable $e) {
            session()->flash('error', __('Failed to create payroll run: :message', ['message' => $e->getMessage()]));
        }
    }

    public function closeRunDetail()
    {
        $this->selectedRunId = null;
        $this->lineEdits = [];
        return $this->redirect(route('payroll.payroll-processing'), navigate: true);
    }

    /**
     * Build flat lineEdits from run lines for draft editing.
     */
    protected function hydrateLineEdits(PayrollRun $run): void
    {
        $this->lineEdits = [];
        $fields = ['working_days', 'absent', 'gross_salary', 'total_deductions', 'net_salary'];
        foreach ($run->lines as $line) {
            $id = (int) $line->id;
            foreach ($fields as $field) {
                $v = $line->getAttribute($field);
                $this->lineEdits[$id . '_' . $field] = is_array($v) ? '0' : (string) $v;
            }
        }
    }

    protected function sanitizeLineEdits(array $lineEdits): array
    {
        $out = [];
        foreach ($lineEdits as $key => $value) {
            $out[(string) $key] = is_array($value) ? '' : (string) $value;
        }
        return $out;
    }

    public function saveLineEdits()
    {
        if (!$this->selectedRunId) {
            return;
        }
        $run = PayrollRun::with('lines')->find($this->selectedRunId);
        if (!$run || !$run->isDraft()) {
            session()->flash('error', __('Only draft runs can be edited.'));
            return;
        }
        $validNum = fn ($v) => is_numeric($v) ? (float) $v : 0;
        foreach ($run->lines as $line) {
            $p = $line->id . '_';
            $line->update([
                'working_days' => (int) ($this->lineEdits[$p . 'working_days'] ?? 0),
                'absent' => (int) ($this->lineEdits[$p . 'absent'] ?? 0),
                'gross_salary' => $validNum($this->lineEdits[$p . 'gross_salary'] ?? 0),
                'total_deductions' => $validNum($this->lineEdits[$p . 'total_deductions'] ?? 0),
                'net_salary' => $validNum($this->lineEdits[$p . 'net_salary'] ?? 0),
            ]);
        }
        session()->flash('message', __('Changes saved.'));
    }

    public function approveRun()
    {
        if (!$this->selectedRunId) {
            return;
        }
        $run = PayrollRun::find($this->selectedRunId);
        if (!$run) {
            session()->flash('error', __('Payroll run not found.'));
            return;
        }
        try {
            app(PayrollRunService::class)->approveRun($run);
            session()->flash('message', __('Payroll run for :period has been approved.', ['period' => $run->period_label]));
            $this->selectedRunId = $run->id; // stay on detail with updated status
        } catch (\Throwable $e) {
            session()->flash('error', $e->getMessage());
        }
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

        $payrollRuns = PayrollRun::withCount('lines')
            ->latest()
            ->take(20)
            ->get();

        $selectedRun = $this->selectedRunId
            ? PayrollRun::with(['lines.employee'])->find($this->selectedRunId)
            : null;

        if ($selectedRun && $selectedRun->lines->isNotEmpty() && empty($this->lineEdits)) {
            $this->hydrateLineEdits($selectedRun);
        }
        $this->lineEdits = $this->sanitizeLineEdits($this->lineEdits);

        return view('livewire.payroll.payroll-processing', [
            'months' => $months,
            'departments' => $departments,
            'payrollRuns' => $payrollRuns,
            'selectedRun' => $selectedRun,
        ])->layout('components.layouts.app');
    }
}
