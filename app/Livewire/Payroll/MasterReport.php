<?php

namespace App\Livewire\Payroll;

use App\Models\Employee;
use App\Services\AttendanceStatsForPayrollService;
use App\Services\PayrollCalculationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MasterReport extends Component
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
            'increments',
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
            $workingDays = (int) ($att['working_days'] ?? 0);
            $daysPresent = (float) ($att['attended_days'] ?? 0);
            $onLeaveDays = (float) ($att['on_leave_days'] ?? 0);
            $absent = (int) ($att['absent_days'] ?? 0);
            $holiday = (int) ($att['holiday_days'] ?? 0);
            $lateDays = (int) ($att['late_days'] ?? 0);
            $totalBreakTime = (string) ($att['total_break_time'] ?? '0:00');
            $totalHoursWorked = (string) ($att['total_hours'] ?? '0:00');
            // Match Attendance Report display: use adjusted expected hours when employee has leaves/holidays/absent
            $hasLeavesOrHolidaysOrAbsent = ($att['on_leave_days'] ?? 0) > 0 || ($att['holiday_days'] ?? 0) > 0 || ($att['absent_days'] ?? 0) > 0;
            $monthlyExpectedHours = (string) ($hasLeavesOrHolidaysOrAbsent ? ($att['expected_hours_adjusted'] ?? '0:00') : ($att['expected_hours'] ?? '0:00'));
            $shortExcessHours = (string) ($att['short_excess_hours'] ?? '0:00');
            $salary = $employee->salaryLegalCompliance;
            // Effective salary as of report month: use latest non-history increment effective by end of report month, else compliance
            $reportMonthEnd = Carbon::createFromFormat('Y-m', $month)->endOfMonth();
            $effectiveIncrement = $employee->increments
                ->where('for_history', false)
                ->whereNotNull('last_increment_date')
                ->filter(fn ($i) => $i->last_increment_date->lte($reportMonthEnd))
                ->sortByDesc(fn ($i) => $i->last_increment_date->format('Y-m-d'))
                ->first();
            if ($effectiveIncrement && $effectiveIncrement->basic_salary_after !== null && $effectiveIncrement->gross_salary_after !== null) {
                $basic = (float) $effectiveIncrement->basic_salary_after;
                $grossFromIncrement = (float) $effectiveIncrement->gross_salary_after;
                $allowances = $grossFromIncrement - $basic;
            } else {
                $basic = $salary ? (float) $salary->basic_salary : 0;
                $allowances = $salary ? (float) ($salary->allowances ?? 0) : 0;
            }
            $bonus = $salary ? (float) ($salary->bonus ?? 0) : 0;
            $gross = $basic + $allowances;
            $otHrs = 0;
            $otAmt = 0;
            $grossWithOt = $gross + $otAmt;
            $tax = PayrollCalculationService::getTaxAmount($grossWithOt, $taxYear);
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
            $designationName = $this->getEmployeeDesignationName($employee);
            $reportingManager = $this->getReportingManagerName($employee);
            $doj = $employee->organizationalInfo && $employee->organizationalInfo->joining_date
                ? $employee->organizationalInfo->joining_date->format('Y-m-d')
                : '—';
            $salaryType = $salary && trim((string) ($salary->payment_frequency ?? '')) !== ''
                ? ucfirst(strtolower($salary->payment_frequency))
                : '—';
            $bankName = $salary ? (trim((string) ($salary->bank ?? '')) ?: '—') : '—';
            $bankAccount = $salary ? (trim((string) ($salary->bank_account ?? '')) ?: '—') : '—';
            $accountTitle = $salary ? (trim((string) ($salary->account_title ?? '')) ?: '—') : '—';

            $latestIncrement = $employee->increments
                ->whereNotNull('last_increment_date')
                ->sortByDesc(fn ($i) => $i->last_increment_date->format('Y-m-d'))
                ->first();
            $lastIncrementDate = $latestIncrement && $latestIncrement->last_increment_date
                ? $latestIncrement->last_increment_date->format('Y-m-d')
                : '—';
            $lastIncrementAmount = $latestIncrement ? (float) $latestIncrement->increment_amount : 0;
            $monthsSinceIncrement = 0;
            if ($latestIncrement && $latestIncrement->last_increment_date) {
                $reportStart = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
                $monthsSinceIncrement = (int) max(0, $reportStart->diffInMonths($latestIncrement->last_increment_date, false));
            }
            $leavePaid = $onLeaveDays;
            $leaveUnpaid = 0;
            $leaveLwp = 0;

            return [
                'employee' => $employee,
                'department' => $departmentName,
                'designation' => $designationName,
                'doj' => $doj,
                'current_status' => $employee->status ?? 'active',
                'reporting_manager' => $reportingManager,
                'last_increment_date' => $lastIncrementDate,
                'last_increment_amount' => $lastIncrementAmount,
                'months_since_increment' => $monthsSinceIncrement,
                'working_days' => $workingDays,
                'days_present' => $daysPresent,
                'leave_paid' => $leavePaid,
                'leave_unpaid' => $leaveUnpaid,
                'leave_lwp' => $leaveLwp,
                'absent' => $absent,
                'holiday' => $holiday,
                'late_days' => $lateDays,
                'total_break_time' => $totalBreakTime,
                'total_hours_worked' => $totalHoursWorked,
                'monthly_expected_hours' => $monthlyExpectedHours,
                'short_excess_hours' => $shortExcessHours,
                'salary_type' => $salaryType,
                'basic_salary' => $basic,
                'allowances' => $allowances,
                'ot_hrs' => $otHrs,
                'ot_amt' => $otAmt,
                'gross_salary' => $grossWithOt,
                'bonus' => $bonus,
                'epf_ee' => $epfEe,
                'epf_er' => $epfEr,
                'esic_ee' => $esicEe,
                'esic_er' => $esicEr,
                'tax' => $tax,
                'prof_tax' => $profTax,
                'eobi' => $eobi,
                'advance' => $advance,
                'loan' => $loan,
                'other_deductions' => $otherDeductions,
                'total_deductions' => $totalDeductions,
                'net_salary' => $netSalary,
                'bank_name' => $bankName,
                'account_title' => $accountTitle,
                'bank_account' => $bankAccount,
            ];
        })->toArray();
    }

    protected function getReportingManagerName(Employee $employee): string
    {
        if ($employee->reports_to_id && $employee->relationLoaded('reportsTo') && $employee->reportsTo) {
            $m = $employee->reportsTo;
            return trim(($m->first_name ?? '') . ' ' . ($m->last_name ?? ''));
        }
        if (trim((string) ($employee->reports_to ?? '')) !== '') {
            return (string) $employee->reports_to;
        }
        return '—';
    }

    public function exportToCsv(): StreamedResponse
    {
        $monthLabel = $this->selectedMonth
            ? Carbon::createFromFormat('Y-m', $this->selectedMonth)->format('F Y')
            : Carbon::now()->format('F Y');

        $data = $this->getFilteredReportData();

        $filename = "master-report-{$this->selectedMonth}.csv";

        $headers = [
            'Sr No', 'Emp Code', 'Employee Name', 'DEPT', 'DSG', 'DOJ', 'Current Status', 'Reporting Manager',
            'Date of Last Increment', 'Increment Amount', '# Months Since Last Increment',
            'Working Days', 'Present Days', 'Leave Paid', 'Leave Unpaid', 'Leave LWP', 'Absent Days', 'Late Days', 'Total Break Time', 'Holidays', 'Total Hours Worked', 'Monthly Expected Hours', 'Short/Excess Hours',
            'Salary Type', 'Basic Salary', 'Allowances', 'OT Hrs', 'OT Amt', 'Gross Salary', 'Bonus',
            'EPF EE', 'EPF ER', 'ESIC EE', 'ESIC ER', 'Tax', 'Prof Tax', 'EOBI', 'Advance', 'Loan',
            'Other Deductions', 'Total Deductions', 'Net Salary',
            'Bank Name', 'Account Title', 'Bank Account',
        ];
        return response()->streamDownload(function () use ($data, $headers) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $headers);
            $sr = 0;
            foreach ($data as $row) {
                $sr++;
                $emp = $row['employee'];
                $name = trim(($emp->first_name ?? '') . ' ' . ($emp->last_name ?? ''));
                fputcsv($out, [
                    $sr,
                    $emp->employee_code ?? 'N/A',
                    $name,
                    $row['department'],
                    $row['designation'],
                    $row['doj'] ?? '—',
                    $row['current_status'] ?? '—',
                    $row['reporting_manager'] ?? '—',
                    $row['last_increment_date'] ?? '—',
                    number_format($row['last_increment_amount'] ?? 0, 2),
                    $row['months_since_increment'] ?? 0,
                    $row['working_days'] ?? 0,
                    $row['days_present'] ?? 0,
                    $row['leave_paid'] ?? 0,
                    $row['leave_unpaid'] ?? 0,
                    $row['leave_lwp'] ?? 0,
                    $row['absent'] ?? 0,
                    $row['late_days'] ?? 0,
                    $row['total_break_time'] ?? '0:00',
                    $row['holiday'] ?? 0,
                    $row['total_hours_worked'] ?? '0:00',
                    $row['monthly_expected_hours'] ?? '0:00',
                    $row['short_excess_hours'] ?? '0:00',
                    $row['salary_type'] ?? '—',
                    number_format($row['basic_salary'], 2),
                    number_format($row['allowances'] ?? 0, 2),
                    $row['ot_hrs'] ?? 0,
                    number_format($row['ot_amt'] ?? 0, 2),
                    number_format($row['gross_salary'], 2),
                    number_format($row['bonus'] ?? 0, 2),
                    number_format($row['epf_ee'] ?? 0, 2),
                    number_format($row['epf_er'] ?? 0, 2),
                    number_format($row['esic_ee'] ?? 0, 2),
                    number_format($row['esic_er'] ?? 0, 2),
                    number_format($row['tax'] ?? 0, 2),
                    number_format($row['prof_tax'] ?? 0, 2),
                    number_format($row['eobi'] ?? 0, 2),
                    number_format($row['advance'] ?? 0, 2),
                    number_format($row['loan'] ?? 0, 2),
                    number_format($row['other_deductions'] ?? 0, 2),
                    number_format($row['total_deductions'] ?? 0, 2),
                    number_format($row['net_salary'] ?? 0, 2),
                    $row['bank_name'] ?? '—',
                    $row['account_title'] ?? '—',
                    $row['bank_account'] ?? '—',
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
        $filename = "master-report-{$this->selectedMonth}.xlsx";
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\MasterReportExport($groupedData, $monthLabel),
            $filename,
            \Maatwebsite\Excel\Excel::XLSX
        );
    }

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

    protected function getEmployeeDesignationName(Employee $employee): string
    {
        if ($employee->designation_id) {
            $des = $employee->relationLoaded('designation')
                ? $employee->getRelation('designation')
                : $employee->designation()->first();
            if ($des && is_object($des)) {
                return $des->name ?? 'N/A';
            }
        }
        $legacy = $employee->getRawOriginal('designation');
        return trim((string) $legacy) !== '' ? (string) $legacy : 'N/A';
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
                $des = strtolower($row['designation']);
                return str_contains($name, $term) || str_contains($code, $term) || str_contains($dept, $term) || str_contains($des, $term);
            });
        }
        return $data->values()->toArray();
    }

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
                    'total_gross' => 0,
                    'total_deductions' => 0,
                    'total_net_salary' => 0,
                    'count' => 0,
                    'employees' => [],
                ];
            }
            $groups[$dept]['total_basic'] += $row['basic_salary'];
            $groups[$dept]['total_gross'] += $row['gross_salary'];
            $groups[$dept]['total_deductions'] += $row['total_deductions'] ?? 0;
            $groups[$dept]['total_net_salary'] += $row['net_salary'] ?? 0;
            $groups[$dept]['count']++;
            $row['sr_no'] = $groups[$dept]['count'];
            $groups[$dept]['employees'][] = $row;
        }
        return array_values($groups);
    }

    public function render()
    {
        $groupedData = $this->getGroupedByDepartment();
        $hasData = !empty($groupedData);
        $subheading = $this->selectedMonth
            ? __('Master report by department for :month', ['month' => Carbon::createFromFormat('Y-m', $this->selectedMonth)->format('F Y')])
            : __('Master report by department');

        $grandTotals = [
            'total_gross' => 0,
            'total_deductions' => 0,
            'total_net_salary' => 0,
            'count' => 0,
        ];
        foreach ($groupedData as $group) {
            $grandTotals['total_gross'] += $group['total_gross'] ?? 0;
            $grandTotals['total_deductions'] += $group['total_deductions'] ?? 0;
            $grandTotals['total_net_salary'] += $group['total_net_salary'] ?? 0;
            $grandTotals['count'] += $group['count'] ?? 0;
        }

        return view('livewire.payroll.master-report', [
            'groupedData' => $groupedData,
            'hasData' => $hasData,
            'subheading' => $subheading,
            'grandTotals' => $grandTotals,
        ])->layout('components.layouts.app');
    }
}
