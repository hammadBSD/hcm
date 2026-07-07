<?php

namespace App\Services;

use App\Models\DeductionExemption;
use App\Models\Employee;
use App\Models\PayrollLateDeductionAdjustment;
use App\Models\PayrollLateDeductionSetting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class PayrollLatesAdjustmentService
{
    public function __construct(
        protected AttendanceStatsForPayrollService $attendanceStatsService,
    ) {}

    /**
     * @return array<int, int> employee_id => waived_deduction_late_days
     */
    public function getAdjustmentMap(string $yearMonth): array
    {
        return PayrollLateDeductionAdjustment::query()
            ->where('year_month', $yearMonth)
            ->pluck('waived_deduction_late_days', 'employee_id')
            ->map(fn ($days) => (int) $days)
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function qualifyingEmployees(
        string $yearMonth,
        string $search = '',
        string $department = '',
    ): array {
        $employees = $this->payrollEmployeesForMonth($yearMonth);
        if ($employees->isEmpty()) {
            return [];
        }

        $attendanceStats = $this->attendanceStatsService->getStatsForEmployeesAndMonth($employees, $yearMonth);
        $exemptionMap = $this->getDeductionExemptionMap($yearMonth, $employees);
        $adjustmentMap = $this->getAdjustmentMap($yearMonth);
        $adjustmentRecords = PayrollLateDeductionAdjustment::query()
            ->where('year_month', $yearMonth)
            ->get(['id', 'employee_id'])
            ->keyBy('employee_id');
        $rule = PayrollLateDeductionSetting::forPayrollMonth($yearMonth);
        $latesPerDay = (int) ($rule?->lates_per_day_deduction ?? 0);

        $rows = [];

        foreach ($employees as $employee) {
            $att = $attendanceStats[$employee->id] ?? [];
            $lateDays = (int) ($att['late_days'] ?? 0);
            $workingDays = max(1, (int) ($att['working_days'] ?? 0));
            $flags = $exemptionMap[$employee->id] ?? [
                'exempt_absent_days' => false,
                'exempt_short_hours' => false,
                'exempt_lates' => false,
                'exempt_all' => false,
            ];

            if (!empty($flags['exempt_all']) || !empty($flags['exempt_lates'])) {
                continue;
            }

            $calculatedDays = PayrollCalculationService::getLateDeductionSalaryDays($lateDays, $yearMonth);
            if ($calculatedDays <= 0) {
                continue;
            }

            $salary = $employee->salaryLegalCompliance;
            $gross = $salary
                ? round((float) $salary->basic_salary + (float) ($salary->allowances ?? 0), 2)
                : 0.0;
            $calculatedAmount = PayrollCalculationService::getLateDeductionAmountForSalaryDays(
                $calculatedDays,
                $gross,
                $workingDays
            );

            $adjustedDays = array_key_exists($employee->id, $adjustmentMap)
                ? max(0, min((int) $adjustmentMap[$employee->id], $calculatedDays))
                : 0;
            $finalDeductionDays = max(0, $calculatedDays - $adjustedDays);

            $adjustedAmount = PayrollCalculationService::getLateDeductionAmountForSalaryDays(
                $finalDeductionDays,
                $gross,
                $workingDays
            );

            $departmentName = $employee->department?->title
                ?? (is_string($employee->department) ? $employee->department : 'N/A');

            $rows[] = [
                'employee_id' => $employee->id,
                'employee_name' => trim($employee->first_name . ' ' . $employee->last_name),
                'employee_code' => $employee->employee_code ?? '—',
                'department' => $departmentName,
                'late_days' => $lateDays,
                'lates_per_day_deduction' => $latesPerDay,
                'calculated_deduction_late_days' => $calculatedDays,
                'calculated_deduction_late_amount' => $calculatedAmount,
                'waived_deduction_late_days' => $adjustedDays,
                'final_deduction_late_days' => $finalDeductionDays,
                'final_deduction_late_amount' => $adjustedAmount,
                'has_adjustment' => array_key_exists($employee->id, $adjustmentMap),
                'adjustment_id' => $adjustmentRecords->get($employee->id)?->id,
            ];
        }

        $search = trim($search);
        if ($search !== '') {
            $needle = mb_strtolower($search);
            $rows = array_values(array_filter($rows, function (array $row) use ($needle) {
                return str_contains(mb_strtolower($row['employee_name']), $needle)
                    || str_contains(mb_strtolower((string) $row['employee_code']), $needle);
            }));
        }

        if ($department !== '') {
            $rows = array_values(array_filter($rows, fn (array $row) => $row['department'] === $department));
        }

        usort($rows, fn (array $a, array $b) => strcmp($a['employee_name'], $b['employee_name']));

        return $rows;
    }

    public function currentRuleSummary(string $yearMonth): ?string
    {
        $rule = PayrollLateDeductionSetting::forPayrollMonth($yearMonth);
        if (!$rule || $rule->lates_per_day_deduction < 1) {
            return null;
        }

        $n = (int) $rule->lates_per_day_deduction;
        $allowed = max(0, $n - 1);

        return __('On the :nth late, 1 day of salary is deducted (e.g. :double lates = 2 day(s); :allowed lates allowed with no deduction).', [
            'nth' => $n,
            'double' => $n * 2,
            'allowed' => $allowed,
        ]);
    }

    /**
     * @return Collection<int, Employee>
     */
    public function payrollEmployeesForMonth(string $month): Collection
    {
        $monthStart = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $monthEnd = Carbon::createFromFormat('Y-m', $month)->endOfMonth();

        return Employee::with(['department', 'salaryLegalCompliance', 'organizationalInfo', 'user.roles', 'group'])
            ->where(function ($q) use ($monthStart, $monthEnd) {
                $q->where(function ($ongoing) use ($monthEnd) {
                    $ongoing->where('status', 'active')
                        ->where(function ($inner) use ($monthEnd) {
                            $inner->whereDoesntHave('organizationalInfo')
                                ->orWhereHas('organizationalInfo', function ($oq) use ($monthEnd) {
                                    $oq->where(function ($j) use ($monthEnd) {
                                        $j->whereNull('joining_date')
                                            ->orWhere('joining_date', '<=', $monthEnd);
                                    })->where(function ($dates) {
                                        $dates->whereNull('leaving_date')
                                            ->whereNull('resign_date');
                                    });
                                });
                        });
                })->orWhere(function ($withEnd) use ($monthStart, $monthEnd) {
                    $withEnd->whereHas('organizationalInfo', function ($oq) use ($monthStart, $monthEnd) {
                        $oq->where(function ($j) use ($monthEnd) {
                            $j->whereNull('joining_date')
                                ->orWhere('joining_date', '<=', $monthEnd);
                        })->where(function ($e) use ($monthStart) {
                            $e->where(function ($x) use ($monthStart) {
                                $x->whereNotNull('leaving_date')
                                    ->where('leaving_date', '>=', $monthStart);
                            })->orWhere(function ($x) use ($monthStart) {
                                $x->whereNull('leaving_date')
                                    ->whereNotNull('resign_date')
                                    ->where('resign_date', '>=', $monthStart);
                            });
                        });
                    });
                });
            })
            ->get();
    }

    /**
     * @return array<int, array<string, bool>>
     */
    public function getDeductionExemptionMap(string $yearMonth, Collection $employees): array
    {
        $reportYear = substr($yearMonth, 0, 4);
        $exemptions = DeductionExemption::with(['role', 'department'])
            ->where(function ($q) use ($yearMonth, $reportYear) {
                $q->where(function ($q1) use ($yearMonth) {
                    $q1->where('duration', DeductionExemption::DURATION_MONTHLY)
                        ->where('year_month', $yearMonth);
                })->orWhere(function ($q2) use ($reportYear) {
                    $q2->where('duration', DeductionExemption::DURATION_YEARLY)
                        ->where('year_month', 'like', $reportYear . '-%');
                });
            })
            ->get();

        $map = [];
        foreach ($employees as $employee) {
            $map[$employee->id] = [
                'exempt_absent_days' => false,
                'exempt_short_hours' => false,
                'exempt_lates' => false,
                'exempt_all' => false,
            ];
        }

        $allEmployeeIds = $employees->pluck('id')->flip()->all();

        foreach ($exemptions as $exemption) {
            $coveredIds = $this->resolveExemptionCoverage($exemption, $employees, $allEmployeeIds);
            $type = $exemption->exemption_type;

            foreach ($coveredIds as $employeeId) {
                if (!isset($map[$employeeId])) {
                    continue;
                }

                if ($type === 'all') {
                    $map[$employeeId]['exempt_all'] = true;
                    $map[$employeeId]['exempt_absent_days'] = true;
                    $map[$employeeId]['exempt_short_hours'] = true;
                    $map[$employeeId]['exempt_lates'] = true;
                } elseif ($type === 'absent_days') {
                    $map[$employeeId]['exempt_absent_days'] = true;
                } elseif ($type === 'hourly_deduction_short_hours') {
                    $map[$employeeId]['exempt_short_hours'] = true;
                } elseif ($type === 'lates') {
                    $map[$employeeId]['exempt_lates'] = true;
                }
            }
        }

        return $map;
    }

    /**
     * @param  array<int, int>  $allEmployeeIds
     * @return array<int, int>
     */
    protected function resolveExemptionCoverage(
        DeductionExemption $exemption,
        Collection $employees,
        array $allEmployeeIds,
    ): array {
        if ($exemption->scope_type === 'all') {
            return array_keys($allEmployeeIds);
        }

        if ($exemption->scope_type === 'department' && $exemption->department_id !== null && $exemption->department_id !== '') {
            $departmentId = (int) $exemption->department_id;

            return $employees->where('department_id', $departmentId)->pluck('id')->values()->all();
        }

        if ($exemption->scope_type === 'role' && $exemption->role_id) {
            $role = $exemption->role;
            if (!$role) {
                return [];
            }

            $userIds = User::role($role->name)->pluck('id')->all();

            return $employees->whereIn('user_id', $userIds)->pluck('id')->values()->all();
        }

        if ($exemption->scope_type === 'group' && $exemption->group_id !== null && $exemption->group_id !== '') {
            $groupId = (int) $exemption->group_id;

            return $employees->where('group_id', $groupId)->pluck('id')->values()->all();
        }

        if ($exemption->scope_type === 'user' && $exemption->user_id !== null && $exemption->user_id !== '') {
            $userId = (int) $exemption->user_id;
            $employee = $employees->firstWhere('user_id', $userId);

            return $employee ? [$employee->id] : [];
        }

        return [];
    }
}
