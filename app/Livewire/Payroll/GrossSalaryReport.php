<?php

namespace App\Livewire\Payroll;

use App\Exports\GrossSalaryReportExport;
use App\Models\Employee;
use App\Services\AttendanceStatsForPayrollService;
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
        $employees = Employee::with([
            'department',
            'designation',
            'salaryLegalCompliance',
            'organizationalInfo',
            'reportsTo',
            'shift',
            'user.roles',
        ])
            ->where('status', 'active')
            ->orderBy('department_id')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        $month = $this->selectedMonth ?: now()->format('Y-m');
        $taxYear = $month ? (int) substr($month, 0, 4) : (int) date('Y');
        $periodMonth = $month ? (int) substr($month, 5, 2) : (int) date('n');
        $attendanceStatsService = app(AttendanceStatsForPayrollService::class);
        $attendanceStatsByEmployee = $attendanceStatsService->getStatsForEmployeesAndMonth($employees, $month);

        $this->reportData = $employees->map(function (Employee $employee) use ($month, $taxYear, $periodMonth, $attendanceStatsByEmployee) {
            $att = $attendanceStatsByEmployee[$employee->id] ?? [];
            $absent = (int) ($att['absent_days'] ?? 0);
            $workingDays = (int) ($att['working_days'] ?? 0);
            $shortExcessHours = (string) ($att['short_excess_hours'] ?? '0:00');
            $salary = $employee->salaryLegalCompliance;
            $basic = $salary ? (float) $salary->basic_salary : 0;
            $allowances = $salary ? (float) ($salary->allowances ?? 0) : 0;
            $bonus = $salary ? (float) ($salary->bonus ?? 0) : 0;
            $gross = $basic + $allowances;
            $otAmt = 0;
            $grossWithOt = $gross + $otAmt;
            $tax = PayrollCalculationService::getTaxAmount($grossWithOt, $taxYear, $month);
            $shortDeduction = PayrollCalculationService::getShortHoursDeduction($shortExcessHours, $grossWithOt, $workingDays);
            $absentDeduction = PayrollCalculationService::getAbsentDeduction($absent, $grossWithOt, $workingDays);
            $otherDeductions = round($shortDeduction + $absentDeduction, 2);
            $epfEe = 0;
            $epfEr = 0;
            $esicEe = 0;
            $esicEr = 0;
            $profTax = 0;
            $eobi = 0;
            $advance = PayrollCalculationService::getAdvanceDeduction($employee->id, $periodMonth, $taxYear);
            $loan = PayrollCalculationService::getLoanDeduction($employee->id);
            $totalDeductions = $tax + $epfEe + $epfEr + $esicEe + $esicEr + $profTax + $eobi + $advance + $loan + $otherDeductions;
            $netSalary = $grossWithOt + $bonus - $totalDeductions;
            $departmentName = $this->getEmployeeDepartmentName($employee);

            return [
                'employee' => $employee,
                'department' => $departmentName,
                'absent' => $absent,
                'basic_salary' => $basic,
                'allowances' => $allowances,
                'gross_salary' => $grossWithOt,
                'tax' => $tax,
                'eobi' => $eobi,
                'advance' => $advance,
                'loan' => $loan,
                'total_deductions' => $totalDeductions,
                'net_salary' => $netSalary,
            ];
        })->toArray();
    }

    /**
     * Same aggregation as Dept-wise Summary, scoped to the current search filter.
     *
     * @return array{rows: array, grand_total: array}
     */
    protected function getDeptSummaryData(): array
    {
        $flat = $this->getFilteredReportData();
        $groups = [];
        foreach ($flat as $row) {
            $dept = $row['department'];
            if (!isset($groups[$dept])) {
                $groups[$dept] = [
                    'department' => $dept,
                    'no_of_emp' => 0,
                    'mcs' => '—',
                    'brand' => '—',
                    'total_basic' => 0,
                    'total_gross' => 0,
                    'total_absent' => 0,
                    'ded_amt_hrs' => 0,
                    'net_gross' => 0,
                    'total_tax' => 0,
                    'total_eobi' => 0,
                    'total_advance' => 0,
                    'total_loan' => 0,
                    'total_deductions' => 0,
                    'total_net_salary' => 0,
                    'hbl_to_hbl' => 0,
                    'cheque' => 0,
                    'ibft' => 0,
                    'cash' => 0,
                    'to_be_disbursed' => 0,
                    'hold' => 0,
                    'total' => 0,
                    'already_paid' => 0,
                    'balance' => 0,
                    'eobi_employer' => 0,
                ];
            }
            $groups[$dept]['no_of_emp']++;
            $groups[$dept]['total_basic'] += $row['basic_salary'];
            $groups[$dept]['total_gross'] += $row['gross_salary'];
            $groups[$dept]['total_absent'] += $row['absent'];
            $groups[$dept]['net_gross'] += $row['gross_salary'];
            $groups[$dept]['total_tax'] += $row['tax'];
            $groups[$dept]['total_advance'] += $row['advance'];
            $groups[$dept]['total_loan'] += $row['loan'];
            $groups[$dept]['total_eobi'] += $row['eobi'];
            $groups[$dept]['total_deductions'] += $row['total_deductions'];
            $groups[$dept]['total_net_salary'] += $row['net_salary'];
            $groups[$dept]['to_be_disbursed'] += $row['net_salary'];
            $groups[$dept]['total'] += $row['net_salary'];
            $groups[$dept]['balance'] += $row['net_salary'];
        }

        $rows = array_values($groups);

        $grandTotal = [
            'no_of_emp' => 0,
            'total_basic' => 0,
            'total_gross' => 0,
            'total_absent' => 0,
            'ded_amt_hrs' => 0,
            'net_gross' => 0,
            'total_tax' => 0,
            'total_eobi' => 0,
            'total_advance' => 0,
            'total_loan' => 0,
            'total_deductions' => 0,
            'total_net_salary' => 0,
            'hbl_to_hbl' => 0,
            'cheque' => 0,
            'ibft' => 0,
            'cash' => 0,
            'to_be_disbursed' => 0,
            'hold' => 0,
            'total' => 0,
            'already_paid' => 0,
            'balance' => 0,
            'eobi_employer' => 0,
        ];
        foreach ($rows as $r) {
            $grandTotal['no_of_emp'] += $r['no_of_emp'];
            $grandTotal['total_basic'] += $r['total_basic'];
            $grandTotal['total_gross'] += $r['total_gross'];
            $grandTotal['total_absent'] += $r['total_absent'];
            $grandTotal['net_gross'] += $r['net_gross'];
            $grandTotal['total_tax'] += $r['total_tax'];
            $grandTotal['total_advance'] += $r['total_advance'];
            $grandTotal['total_loan'] += $r['total_loan'];
            $grandTotal['total_eobi'] += $r['total_eobi'];
            $grandTotal['total_deductions'] += $r['total_deductions'];
            $grandTotal['total_net_salary'] += $r['total_net_salary'];
            $grandTotal['to_be_disbursed'] += $r['to_be_disbursed'];
            $grandTotal['total'] += $r['total'];
            $grandTotal['balance'] += $r['balance'];
        }

        return ['rows' => $rows, 'grand_total' => $grandTotal];
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
        $summary = $this->getDeptSummaryData();
        $hasAnyEmployees = !empty($this->reportData);
        $hasFilteredData = !empty($groupedData);
        $subheading = $this->selectedMonth
            ? __('Gross salary by department for :month', ['month' => Carbon::createFromFormat('Y-m', $this->selectedMonth)->format('F Y')])
            : __('Gross salary by department');

        return view('livewire.payroll.gross-salary-report', [
            'groupedData' => $groupedData,
            'summaryRows' => $summary['rows'],
            'grandTotal' => $summary['grand_total'],
            'hasAnyEmployees' => $hasAnyEmployees,
            'hasFilteredData' => $hasFilteredData,
            'subheading' => $subheading,
        ])->layout('components.layouts.app');
    }
}
