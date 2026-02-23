<?php

namespace App\Livewire\Payroll;

use App\Models\Employee;
use App\Services\AttendanceStatsForPayrollService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Dept-wise Summary: same data as Master Report, aggregated by department.
 * One row per department with totals + grand total row.
 */
class DeptWiseSummary extends Component
{
    public $selectedMonth = '';

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
        $attendanceStatsService = app(AttendanceStatsForPayrollService::class);
        $attendanceStatsByEmployee = $attendanceStatsService->getStatsForEmployeesAndMonth($employees, $month);

        $taxPercentage = $this->getTaxPercentage();

        $this->reportData = $employees->map(function (Employee $employee) use ($taxPercentage, $attendanceStatsByEmployee) {
            $att = $attendanceStatsByEmployee[$employee->id] ?? [];
            $absent = (int) ($att['absent_days'] ?? 0);
            $salary = $employee->salaryLegalCompliance;
            $basic = $salary ? (float) $salary->basic_salary : 0;
            $allowances = $salary ? (float) ($salary->allowances ?? 0) : 0;
            $bonus = $salary ? (float) ($salary->bonus ?? 0) : 0;
            $gross = $basic + $allowances;
            $otAmt = 0;
            $grossWithOt = $gross + $otAmt;
            $tax = round($gross * ($taxPercentage / 100), 2);
            $epfEe = 0;
            $epfEr = 0;
            $esicEe = 0;
            $esicEr = 0;
            $profTax = 0;
            $eobi = 0;
            $advance = 0;
            $loan = 0;
            $otherDeductions = 0;
            $totalDeductions = $tax + $epfEe + $epfEr + $esicEe + $esicEr + $profTax + $eobi + $advance + $loan + $otherDeductions;
            $netSalary = $grossWithOt + $bonus - $totalDeductions;
            $departmentName = $this->getEmployeeDepartmentName($employee);

            return [
                'employee' => $employee,
                'department' => $departmentName,
                'absent' => $absent,
                'basic_salary' => $basic,
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
     * Summary rows: one per department with aggregated totals.
     *
     * @return array{rows: array, grand_total: array}
     */
    protected function getSummaryData(): array
    {
        $flat = $this->reportData;
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

    public function exportToExcel()
    {
        $data = $this->getSummaryData();
        if (empty($data['rows'])) {
            session()->flash('error', __('No data available to export.'));
            return null;
        }
        $monthLabel = $this->selectedMonth
            ? Carbon::createFromFormat('Y-m', $this->selectedMonth)->format('F Y')
            : Carbon::now()->format('F Y');
        $filename = "dept-wise-summary-{$this->selectedMonth}.xlsx";
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\DeptWiseSummaryExport($data['rows'], $data['grand_total'], $monthLabel),
            $filename,
            \Maatwebsite\Excel\Excel::XLSX
        );
    }

    public function exportToCsv(): StreamedResponse
    {
        $data = $this->getSummaryData();
        $monthLabel = $this->selectedMonth
            ? Carbon::createFromFormat('Y-m', $this->selectedMonth)->format('F Y')
            : Carbon::now()->format('F Y');
        $filename = "dept-wise-summary-{$this->selectedMonth}.csv";
        $headers = [
            'Department', 'No. of Emp.', 'MCS', 'Brand',
            'Gross Salary Before Vehicle Allowance', 'Gross Salary', 'Deduction Absent Days', 'Ded Amt Hrs', 'Net Gross',
            'Tax', 'Eobi', 'Advance / Rentals', 'Loan',
            'Net Pay', 'HBL to HBL', 'Cheque', 'IBFT', 'Cash', 'To be Disbursed', 'Hold', 'Total',
            'Already Paid', 'Balance', 'EOBI Contribution (Employer)',
        ];
        return response()->streamDownload(function () use ($data, $headers) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $headers);
            foreach ($data['rows'] as $r) {
                fputcsv($out, [
                    $r['department'],
                    $r['no_of_emp'],
                    $r['mcs'] ?? '—',
                    $r['brand'] ?? '—',
                    number_format($r['total_basic'], 2),
                    number_format($r['total_gross'], 2),
                    $r['total_absent'],
                    number_format($r['ded_amt_hrs'], 2),
                    number_format($r['net_gross'], 2),
                    number_format($r['total_tax'], 2),
                    number_format($r['total_eobi'], 2),
                    number_format($r['total_advance'], 2),
                    number_format($r['total_loan'], 2),
                    number_format($r['total_net_salary'], 2),
                    number_format($r['hbl_to_hbl'], 2),
                    number_format($r['cheque'], 2),
                    number_format($r['ibft'], 2),
                    number_format($r['cash'], 2),
                    number_format($r['to_be_disbursed'], 2),
                    number_format($r['hold'], 2),
                    number_format($r['total'], 2),
                    number_format($r['already_paid'], 2),
                    number_format($r['balance'], 2),
                    number_format($r['eobi_employer'], 2),
                ]);
            }
            $g = $data['grand_total'];
            fputcsv($out, [
                'GRAND TOTAL',
                $g['no_of_emp'], '', '',
                number_format($g['total_basic'], 2),
                number_format($g['total_gross'], 2),
                $g['total_absent'],
                number_format($g['ded_amt_hrs'], 2),
                number_format($g['net_gross'], 2),
                number_format($g['total_tax'], 2),
                number_format($g['total_eobi'], 2),
                number_format($g['total_advance'], 2),
                number_format($g['total_loan'], 2),
                number_format($g['total_net_salary'], 2),
                number_format($g['hbl_to_hbl'], 2),
                number_format($g['cheque'], 2),
                number_format($g['ibft'], 2),
                number_format($g['cash'], 2),
                number_format($g['to_be_disbursed'], 2),
                number_format($g['hold'], 2),
                number_format($g['total'], 2),
                number_format($g['already_paid'], 2),
                number_format($g['balance'], 2),
                number_format($g['eobi_employer'], 2),
            ]);
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    protected function getTaxPercentage(): float
    {
        return 15.0;
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

    public function render()
    {
        $data = $this->getSummaryData();
        $hasData = !empty($data['rows']);
        $subheading = $this->selectedMonth
            ? __('Dept-wise summary for :month', ['month' => Carbon::createFromFormat('Y-m', $this->selectedMonth)->format('F Y')])
            : __('Dept-wise summary');

        return view('livewire.payroll.dept-wise-summary', [
            'summaryRows' => $data['rows'],
            'grandTotal' => $data['grand_total'],
            'hasData' => $hasData,
            'subheading' => $subheading,
        ])->layout('components.layouts.app');
    }
}
