<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\PayrollRun;
use App\Models\PayrollRunLine;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PayrollRunService
{
    /**
     * Build payroll line data for the given month/year using same logic as Master Report.
     */
    public function buildRunData(int $month, int $year): array
    {
        $monthYmd = sprintf('%04d-%02d', $year, $month);
        $employees = Employee::with([
            'department',
            'designation',
            'salaryLegalCompliance',
            'organizationalInfo',
        ])
            ->where('status', 'active')
            ->orderBy('department_id')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        $attendanceStatsService = app(AttendanceStatsForPayrollService::class);
        $attendanceStatsByEmployee = $attendanceStatsService->getStatsForEmployeesAndMonth($employees, $monthYmd);

        $lines = [];
        foreach ($employees as $employee) {
            $att = $attendanceStatsByEmployee[$employee->id] ?? [];
            $salary = $employee->salaryLegalCompliance;
            $basic = $salary ? (float) $salary->basic_salary : 0;
            $allowances = $salary ? (float) ($salary->allowances ?? 0) : 0;
            $bonus = $salary ? (float) ($salary->bonus ?? 0) : 0;
            $gross = $basic + $allowances;
            $tax = PayrollCalculationService::getTaxAmount($gross, $year);
            $eobi = 0;
            $advance = PayrollCalculationService::getAdvanceDeduction($employee->id, $month, $year);
            $loan = PayrollCalculationService::getLoanDeduction($employee->id);

            $absentDays = (int) ($att['absent_days'] ?? 0);
            $shortExcessHours = (string) ($att['short_excess_hours'] ?? '0:00');
            $workingDays = (int) ($att['working_days'] ?? 0);
            $onLeaveDays = (float) ($att['on_leave_days'] ?? 0);
            $hasLeavesOrHolidaysOrAbsent = $onLeaveDays > 0 || (int) ($att['holiday_days'] ?? 0) > 0 || $absentDays > 0;
            $monthlyExpectedHours = (string) ($hasLeavesOrHolidaysOrAbsent ? ($att['expected_hours_adjusted'] ?? '0:00') : ($att['expected_hours'] ?? '0:00'));
            $shortDeduction = PayrollCalculationService::getShortHoursDeduction($shortExcessHours, $gross, $workingDays);
            $absentDeduction = PayrollCalculationService::getAbsentDeduction($absentDays, $gross, $workingDays);
            $otherDeductions = round($shortDeduction + $absentDeduction, 2);

            $totalDeductions = $tax + $eobi + $advance + $loan + $otherDeductions;
            $netSalary = $gross + $bonus - $totalDeductions;

            $lines[] = [
                'employee_id' => $employee->id,
                'department' => $this->getEmployeeDepartmentName($employee),
                'designation' => $this->getEmployeeDesignationName($employee),
                'working_days' => (int) ($att['working_days'] ?? 0),
                'days_present' => (float) ($att['attended_days'] ?? 0),
                'leave_paid' => $onLeaveDays,
                'leave_unpaid' => 0,
                'leave_lwp' => 0,
                'absent' => $absentDays,
                'holiday' => (int) ($att['holiday_days'] ?? 0),
                'late_days' => (int) ($att['late_days'] ?? 0),
                'total_break_time' => (string) ($att['total_break_time'] ?? '0:00'),
                'total_hours_worked' => (string) ($att['total_hours'] ?? '0:00'),
                'monthly_expected_hours' => $monthlyExpectedHours,
                'short_excess_hours' => (string) ($att['short_excess_hours'] ?? '0:00'),
                'basic_salary' => $basic,
                'allowances' => $allowances,
                'bonus' => $bonus,
                'gross_salary' => $gross + $bonus,
                'tax' => $tax,
                'eobi' => $eobi,
                'advance' => $advance,
                'loan' => $loan,
                'other_deductions' => $otherDeductions,
                'total_deductions' => $totalDeductions,
                'net_salary' => $netSalary,
            ];
        }

        return $lines;
    }

    /**
     * Create a draft payroll run for the given period and processing type.
     */
    public function createDraftRun(int $month, int $year, string $processingType): PayrollRun
    {
        $linesData = $this->buildRunData($month, $year);

        return DB::transaction(function () use ($month, $year, $processingType, $linesData) {
            $run = PayrollRun::create([
                'period_month' => $month,
                'period_year' => $year,
                'processing_type' => $processingType,
                'status' => 'draft',
                'created_by' => Auth::id(),
            ]);

            foreach ($linesData as $line) {
                $run->lines()->create($line);
            }

            return $run->load('lines');
        });
    }

    /**
     * Approve a draft run. Validates and sets status to approved.
     */
    public function approveRun(PayrollRun $run): void
    {
        if (!$run->isDraft()) {
            throw new \InvalidArgumentException(__('Only draft runs can be approved.'));
        }

        $run->update([
            'status' => 'approved',
            'approved_by' => Auth::id() ?? null,
            'approved_at' => now(),
        ]);
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
}
