<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\ExemptionDay;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class AttendanceExemptionService
{
    public const TYPE_LATES = 'lates';

    public const TYPE_NA = 'n/a';

    /**
     * @return array<string, string|null> date (Y-m-d) => exemption_type|null
     */
    public function exemptionTypesByDateForEmployee(
        Employee $employee,
        Carbon $startDate,
        Carbon $endDate,
    ): array {
        $rows = $this->matchingExemptionsQuery($employee, $startDate, $endDate)
            ->get(['from_date', 'to_date', 'exemption_type']);

        $map = [];
        foreach (CarbonPeriod::create($startDate->copy()->startOfDay(), $endDate->copy()->startOfDay()) as $day) {
            $map[$day->format('Y-m-d')] = null;
        }

        foreach ($rows as $row) {
            $from = Carbon::parse($row->from_date)->startOfDay();
            $to = Carbon::parse($row->to_date)->startOfDay();
            $periodStart = $from->greaterThan($startDate) ? $from : $startDate->copy()->startOfDay();
            $periodEnd = $to->lessThan($endDate) ? $to : $endDate->copy()->startOfDay();

            if ($periodEnd->lt($periodStart)) {
                continue;
            }

            foreach (CarbonPeriod::create($periodStart, $periodEnd) as $day) {
                $dateKey = $day->format('Y-m-d');
                if (!array_key_exists($dateKey, $map)) {
                    continue;
                }

                $map[$dateKey] = $row->exemption_type ?: self::TYPE_NA;
            }
        }

        return $map;
    }

    public function isDateExemptedFromLates(?string $exemptionType): bool
    {
        return $exemptionType === self::TYPE_LATES;
    }

    public function isDateExemptedForPunchProcessing(?string $exemptionType): bool
    {
        return $exemptionType === self::TYPE_NA;
    }

    private function matchingExemptionsQuery(Employee $employee, Carbon $startDate, Carbon $endDate)
    {
        $userId = $employee->user_id;
        $departmentId = $employee->department_id;
        $groupId = $employee->group_id;
        $userRoles = $employee->relationLoaded('user') && $employee->user
            ? $employee->user->roles->pluck('id')->all()
            : ($employee->user?->roles()->pluck('id')->all() ?? []);

        return ExemptionDay::query()
            ->where('from_date', '<=', $endDate->format('Y-m-d'))
            ->where('to_date', '>=', $startDate->format('Y-m-d'))
            ->where(function ($query) use ($userId, $departmentId, $groupId, $userRoles) {
                $query->where('scope_type', 'all')
                    ->orWhere(function ($q) use ($userId) {
                        $q->where('scope_type', 'user')->where('user_id', $userId);
                    })
                    ->orWhere(function ($q) use ($departmentId) {
                        $q->where('scope_type', 'department')->where('department_id', $departmentId);
                    })
                    ->orWhere(function ($q) use ($userRoles) {
                        if (!empty($userRoles)) {
                            $q->where('scope_type', 'role')->whereIn('role_id', $userRoles);
                        } else {
                            $q->whereRaw('0 = 1');
                        }
                    })
                    ->orWhere(function ($q) use ($groupId) {
                        $q->where('scope_type', 'group')->where('group_id', $groupId);
                    });
            });
    }
}
