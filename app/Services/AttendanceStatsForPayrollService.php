<?php

namespace App\Services;

use App\Livewire\Attendance\Report as AttendanceReport;
use App\Models\AttendanceBreakSetting;
use App\Models\DeviceAttendance;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Returns attendance stats for payroll (Master Report) using the same logic as Attendance\Report.
 * Does not modify the attendance module; uses it via reflection.
 */
class AttendanceStatsForPayrollService
{
    /**
     * Get attendance stats for one employee for the given month (Y-m).
     * Returns the same structure as Attendance Report's calculateAttendanceStats.
     *
     * @return array{working_days: int, attended_days: int|float, absent_days: int, on_leave_days: int|float, holiday_days: int, late_days: int, total_break_time: string, ...}
     */
    public function getStatsForEmployeeAndMonth(Employee $employee, string $monthYmd): array
    {
        $defaults = [
            'working_days' => 0,
            'attended_days' => 0,
            'absent_days' => 0,
            'on_leave_days' => 0,
            'holiday_days' => 0,
            'late_days' => 0,
            'total_break_time' => '0:00',
            'total_non_allowed_break_time' => '0:00',
            'total_hours' => '0:00',
            'expected_hours' => '0:00',
            'short_excess_hours' => '0:00',
        ];

        if (empty($employee->punch_code)) {
            return $defaults;
        }

        $startOfMonth = Carbon::createFromFormat('Y-m', $monthYmd)->startOfMonth();
        $endOfMonth = Carbon::createFromFormat('Y-m', $monthYmd)->endOfMonth();
        $extendedEndDate = $endOfMonth->copy()->addHours(5);

        $employee->load(['shift', 'department.shift', 'user.roles']);
        $employeeShift = $employee->getEffectiveShift();
        if ($employeeShift) {
            $timeFromParts = explode(':', $employeeShift->time_from);
            $timeToParts = explode(':', $employeeShift->time_to);
            $timeFrom = Carbon::createFromTime(
                (int) ($timeFromParts[0] ?? 0),
                (int) ($timeFromParts[1] ?? 0),
                (int) ($timeFromParts[2] ?? 0)
            );
            $timeTo = Carbon::createFromTime(
                (int) ($timeToParts[0] ?? 0),
                (int) ($timeToParts[1] ?? 0),
                (int) ($timeToParts[2] ?? 0)
            );
            if ($timeFrom->gt($timeTo) && $timeFrom->hour >= 12) {
                $nextDay = $endOfMonth->copy()->addDay();
                $shiftEndOnNextDay = $nextDay->copy()->setTime(
                    $timeTo->hour,
                    $timeTo->minute,
                    $timeTo->second
                );
                $extendedEndDate = $shiftEndOnNextDay->copy()->addHours(5);
            }
        }

        $attendanceRecords = DeviceAttendance::where('punch_code', $employee->punch_code)
            ->whereBetween('punch_time', [$startOfMonth, $extendedEndDate])
            ->where(function ($query) {
                $query->whereNull('verify_mode')
                    ->orWhere('verify_mode', '!=', 2);
            })
            ->orderBy('punch_time', 'desc')
            ->get();

        try {
            $report = new AttendanceReport();
            $report->selectedMonth = $monthYmd;
            $report->employee = $employee;
            $report->punchCode = $employee->punch_code;
            $report->employeeShift = $employeeShift;
            $report->attendanceData = [];

            $breakSettings = AttendanceBreakSetting::current();
            $report->allowedBreakTime = $breakSettings ? $breakSettings->allowed_break_time : null;

            $determineBreak = new \ReflectionMethod($report, 'determineBreakExclusionStatus');
            $determineBreak->setAccessible(true);
            $report->isBreakTrackingExcluded = $determineBreak->invoke($report);

            $processData = new \ReflectionMethod($report, 'processAttendanceData');
            $processData->setAccessible(true);
            $processedData = $processData->invoke($report, $attendanceRecords);

            $report->attendanceData = $processedData;

            $enrich = new \ReflectionMethod($report, 'enrichAttendanceDataWithLeaveRequests');
            $enrich->setAccessible(true);
            $enrich->invoke($report);

            $processedData = $report->attendanceData;

            $calculateStats = new \ReflectionMethod($report, 'calculateAttendanceStats');
            $calculateStats->setAccessible(true);
            $stats = $calculateStats->invoke($report, $attendanceRecords, $processedData);

            return array_merge($defaults, $stats ?? []);
        } catch (\Throwable $e) {
            return $defaults;
        }
    }

    /**
     * Get attendance stats for multiple employees for the given month.
     * Keyed by employee id for quick lookup.
     *
     * @param  Collection<int, Employee>|array  $employees
     * @return array<int, array>
     */
    public function getStatsForEmployeesAndMonth($employees, string $monthYmd): array
    {
        $result = [];
        $employees = $employees instanceof Collection ? $employees : collect($employees);
        foreach ($employees as $employee) {
            $result[$employee->id] = $this->getStatsForEmployeeAndMonth($employee, $monthYmd);
        }
        return $result;
    }
}
