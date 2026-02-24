<?php

namespace App\Livewire\Payroll;

use App\Exports\GrossSalaryReportExport;
use App\Models\Employee;
use App\Services\PayrollCalculationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GrossSalaryReport extends Component
{
    public $selectedMonth = '';

    public $employeeSearchTerm = '';

    public $currentMonth = '';

    public $availableMonths = [];

    public $reportData = [];

    public function mount()
    {
        $user = Auth::user();

        if (!$user || !$user->can('payroll.view')) {
            abort(403);
        }

        $this->currentMonth = now()->format('Y-m');
        $this->selectedMonth = $this->currentMonth;
        $this->buildAvailableMonths();
        $this->loadReportData();
    }

    public function updatedSelectedMonth()
    {
        $this->loadReportData();
    }

    public function buildAvailableMonths(): void
    {
        $months = [];
        for ($i = 0; $i < 12; $i++) {
            $date = now()->subMonths($i);
            $months[] = [
                'value' => $date->format('Y-m'),
                'label' => $date->format('F Y'),
            ];
        }
        $this->availableMonths = $months;
    }

    public function loadReportData(): void
    {
        $employees = Employee::with(['department', 'salaryLegalCompliance'])
            ->where('status', 'active')
            ->orderBy('department_id')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        $taxYear = $this->selectedMonth ? (int) substr($this->selectedMonth, 0, 4) : (int) date('Y');

        $this->reportData = $employees->map(function (Employee $employee) use ($taxYear) {
            $salary = $employee->salaryLegalCompliance;
            $basic = $salary ? (float) $salary->basic_salary : 0;
            $allowances = $salary ? (float) ($salary->allowances ?? 0) : 0;
            $gross = $basic + $allowances;
            $tax = PayrollCalculationService::getTaxAmount($gross, $taxYear);
            $departmentName = $this->getEmployeeDepartmentName($employee);

            return [
                'employee' => $employee,
                'department' => $departmentName,
                'basic_salary' => $basic,
                'allowances' => $allowances,
                'gross_salary' => $gross,
                'tax' => $tax,
            ];
        })->toArray();
    }

    public function exportToCsv(): StreamedResponse
    {
        $monthLabel = $this->selectedMonth
            ? Carbon::createFromFormat('Y-m', $this->selectedMonth)->format('F Y')
            : Carbon::now()->format('F Y');

        $data = $this->getFilteredReportData();

        $filename = "gross-salary-report-{$this->selectedMonth}.csv";

        return response()->streamDownload(function () use ($data) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Department', 'Employee', 'Employee ID', 'Basic Salary', 'Allowances', 'Gross Salary', 'Tax']);
            foreach ($data as $row) {
                $emp = $row['employee'];
                $name = trim(($emp->first_name ?? '') . ' ' . ($emp->last_name ?? ''));
                fputcsv($out, [
                    $row['department'],
                    $name,
                    $emp->employee_code ?? 'N/A',
                    number_format($row['basic_salary'], 2),
                    number_format($row['allowances'], 2),
                    number_format($row['gross_salary'], 2),
                    number_format($row['tax'] ?? 0, 2),
                ]);
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function exportToExcel()
    {
        $monthLabel = $this->selectedMonth
            ? Carbon::createFromFormat('Y-m', $this->selectedMonth)->format('F Y')
            : Carbon::now()->format('F Y');
        $groupedData = $this->getGroupedByDepartment();
        if (empty($groupedData)) {
            session()->flash('error', __('No data available to export.'));
            return null;
        }
        $filename = "gross-salary-report-{$this->selectedMonth}.xlsx";
        return Excel::download(
            new GrossSalaryReportExport($groupedData, $monthLabel),
            $filename,
            \Maatwebsite\Excel\Excel::XLSX
        );
    }

    /**
     * Get department name for an employee. Uses only department_id and the departments
     * table (canonical list). Does not use the legacy varchar "department" column.
     */
    protected function getEmployeeDepartmentName(Employee $employee): string
    {
        if (!$employee->department_id) {
            return 'N/A';
        }
        $dept = $employee->relationLoaded('department')
            ? $employee->getRelation('department')
            : $employee->department()->first();
        if ($dept && is_object($dept)) {
            return $dept->title ?? 'N/A';
        }
        return 'N/A';
    }

    protected function getFilteredReportData(): array
    {
        $data = collect($this->reportData);
        if (!empty($this->employeeSearchTerm)) {
            $term = strtolower($this->employeeSearchTerm);
            $data = $data->filter(function ($row) use ($term) {
                $emp = $row['employee'];
                $name = strtolower(trim(($emp->first_name ?? '') . ' ' . ($emp->last_name ?? '')));
                $code = strtolower($emp->employee_code ?? '');
                $dept = strtolower($row['department']);
                return str_contains($name, $term) || str_contains($code, $term) || str_contains($dept, $term);
            });
        }
        return $data->values()->toArray();
    }

    /**
     * Group filtered data by department with totals (total gross, total salary without tax, count).
     */
    protected function getGroupedByDepartment(): array
    {
        $flat = $this->getFilteredReportData();
        $groups = [];
        foreach ($flat as $row) {
            $dept = $row['department'];
            if (!isset($groups[$dept])) {
                $groups[$dept] = [
                    'department' => $dept,
                    'total_basic' => 0,
                    'total_allowances' => 0,
                    'total_gross' => 0,
                    'total_tax' => 0,
                    'count' => 0,
                    'employees' => [],
                ];
            }
            $groups[$dept]['total_basic'] += $row['basic_salary'];
            $groups[$dept]['total_allowances'] += $row['allowances'];
            $groups[$dept]['total_gross'] += $row['gross_salary'];
            $groups[$dept]['total_tax'] += $row['tax'] ?? 0;
            $groups[$dept]['count']++;
            $groups[$dept]['employees'][] = $row;
        }
        return array_values($groups);
    }

    public function render()
    {
        $groupedData = $this->getGroupedByDepartment();
        $hasData = !empty($groupedData);
        $subheading = $this->selectedMonth
            ? __('Gross salary by department for :month', ['month' => Carbon::createFromFormat('Y-m', $this->selectedMonth)->format('F Y')])
            : __('Gross salary by department');

        return view('livewire.payroll.gross-salary-report', [
            'groupedData' => $groupedData,
            'hasData' => $hasData,
            'subheading' => $subheading,
        ])->layout('components.layouts.app');
    }
}
