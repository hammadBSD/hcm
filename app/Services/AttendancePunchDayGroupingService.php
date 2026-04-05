<?php

namespace App\Services;

use App\Models\Employee;
use Carbon\Carbon;

/**
 * Maps device punches to calendar workdays using getEffectiveShiftForDate (historical assignments),
 * so early-morning check-outs after a same-calendar-day shift attach to the correct prior workday.
 */
class AttendancePunchDayGroupingService
{
    /**
     * @param  iterable<int, object>  $records  DeviceAttendance-like rows with punch_time, device_type
     * @return array<string, list<object>> date Y-m-d => punches for that workday
     */
    public static function groupPunchesByWorkday(?Employee $employee, iterable $records, int $gracePeriodHours = 5): array
    {
        $groupedRecords = [];

        if (!$employee) {
            foreach ($records as $record) {
                $punchDate = Carbon::parse($record->punch_time)->format('Y-m-d');
                $groupedRecords[$punchDate][] = $record;
            }

            return $groupedRecords;
        }

        foreach ($records as $record) {
            self::appendPunch($employee, $groupedRecords, $record, $gracePeriodHours);
        }

        return $groupedRecords;
    }

    /**
     * @return array{timeFrom: Carbon, timeTo: Carbon, isOvernight: bool}|null
     */
    public static function getShiftTimingForDate(Employee $employee, string $dateYmd): ?array
    {
        $dayShift = $employee->getEffectiveShiftForDate($dateYmd);
        if (!$dayShift) {
            return null;
        }
        $timeFromParts = explode(':', $dayShift->time_from);
        $timeToParts = explode(':', $dayShift->time_to);
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

        return [
            'timeFrom' => $timeFrom,
            'timeTo' => $timeTo,
            'isOvernight' => $timeFrom->gt($timeTo),
        ];
    }

    /**
     * @param  array<string, list<object>>  $groupedRecords
     */
    private static function appendPunch(Employee $employee, array &$groupedRecords, object $record, int $gracePeriodHours): void
    {
        $punchTime = Carbon::parse($record->punch_time);
        $punchDate = $punchTime->format('Y-m-d');

        if ($record->device_type === 'OUT' && $punchTime->hour < 12) {
            $previousDate = $punchTime->copy()->subDay()->format('Y-m-d');
            $timing = self::getShiftTimingForDate($employee, $previousDate);
            if ($timing && !$timing['isOvernight']) {
                $timeTo = $timing['timeTo'];
                $previousDayShiftEnd = Carbon::parse($previousDate)->setTime(
                    $timeTo->hour,
                    $timeTo->minute,
                    $timeTo->second
                );
                $checkOutCutoff = $previousDayShiftEnd->copy()->addHours($gracePeriodHours);
                if ($punchTime->lte($checkOutCutoff)) {
                    $groupedRecords[$previousDate][] = $record;

                    return;
                }
            }
        }

        if ($record->device_type === 'IN' && $punchTime->hour < 12) {
            $previousDate = $punchTime->copy()->subDay()->format('Y-m-d');
            $timing = self::getShiftTimingForDate($employee, $previousDate);
            if ($timing && !$timing['isOvernight']) {
                $timeTo = $timing['timeTo'];
                $previousDayShiftEnd = Carbon::parse($previousDate)->setTime(
                    $timeTo->hour,
                    $timeTo->minute,
                    $timeTo->second
                );
                $checkOutCutoff = $previousDayShiftEnd->copy()->addHours($gracePeriodHours);
                if ($punchTime->lte($checkOutCutoff)) {
                    $groupedRecords[$previousDate][] = $record;

                    return;
                }
            }
        }

        if ($record->device_type === 'IN') {
            $timing = self::getShiftTimingForDate($employee, $punchDate);
            if ($timing && !$timing['isOvernight']) {
                $timeFrom = $timing['timeFrom'];
                $currentDayShiftStart = Carbon::parse($punchDate)->setTime(
                    $timeFrom->hour,
                    $timeFrom->minute,
                    $timeFrom->second
                );
                $checkInCutoff = $currentDayShiftStart->copy()->subHours($gracePeriodHours);
                if ($punchTime->lt($currentDayShiftStart) && $punchTime->gte($checkInCutoff)) {
                    $groupedRecords[$punchDate][] = $record;

                    return;
                }
            }
        }

        $groupedRecords[$punchDate][] = $record;
    }
}
