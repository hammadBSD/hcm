<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use App\Models\Employee;
use App\Models\DeviceAttendance;
use App\Models\Holiday;
use Carbon\Carbon;

class AbsentLateEmployees extends Component
{
    public $absentEmployees = [];
    public $lateEmployees = [];
    public $selectedDate;

    public function mount()
    {
        $this->selectedDate = Carbon::today()->format('Y-m-d');
        $this->loadAbsentLateEmployees();
    }

    public function updatedSelectedDate()
    {
        $this->loadAbsentLateEmployees();
    }

    public function loadAbsentLateEmployees()
    {
        $selectedDate = Carbon::parse($this->selectedDate);
        $now = Carbon::now();
        $isToday = $selectedDate->isToday();
        
        // Get all active employees with their relationships
        $employees = Employee::where('status', 'active')
            ->whereNotNull('punch_code')
            ->with(['department', 'group'])
            ->get();

        $absent = [];
        $late = [];

        // Check if selected date is a holiday
        $isHoliday = Holiday::where('status', 'active')
            ->where(function($query) use ($selectedDate) {
                $query->whereDate('from_date', '<=', $selectedDate)
                      ->whereDate('to_date', '>=', $selectedDate);
            })
            ->exists();

        // Check if selected date is a weekend
        $isWeekend = $selectedDate->isWeekend();

        // For today: Simple logic - check if punch code has any attendance records
        if ($isToday) {
            $todayStart = $selectedDate->copy()->startOfDay();
            $todayEnd = $selectedDate->copy()->endOfDay();
            
            // Get all punch codes that have attendance records for today
            $presentPunchCodes = DeviceAttendance::whereBetween('punch_time', [$todayStart, $todayEnd])
                ->where(function($query) {
                    $query->whereNull('verify_mode')
                          ->orWhere('verify_mode', '!=', 2);
                })
                ->distinct()
                ->pluck('punch_code')
                ->toArray();
            
            foreach ($employees as $employee) {
                // Skip if it's a holiday or weekend
                if ($isHoliday || $isWeekend) {
                    continue;
                }
                
                // Get department and group names safely
                $departmentName = 'N/A';
                if ($employee->department_id) {
                    $department = $employee->department()->first();
                    if ($department && is_object($department)) {
                        $departmentName = $department->title ?? 'N/A';
                    }
                }
                
                $groupName = 'N/A';
                if ($employee->group_id) {
                    $group = $employee->group;
                    if ($group && is_object($group)) {
                        $groupName = $group->name ?? 'N/A';
                    }
                }
                
                // If punch code is not in the present list, mark as absent
                if (!in_array($employee->punch_code, $presentPunchCodes)) {
                    $absent[] = [
                        'id' => $employee->id,
                        'name' => $employee->first_name . ' ' . $employee->last_name,
                        'employee_code' => $employee->employee_code,
                        'department' => $departmentName,
                        'group' => $groupName,
                    ];
                } else {
                    // Has attendance, check if late
                    $checkIn = DeviceAttendance::where('punch_code', $employee->punch_code)
                        ->whereBetween('punch_time', [$todayStart, $todayEnd])
                        ->where('device_type', 'IN')
                        ->where(function($query) {
                            $query->whereNull('verify_mode')
                                  ->orWhere('verify_mode', '!=', 2);
                        })
                        ->orderBy('punch_time', 'asc')
                        ->first();
                    
                    if ($checkIn) {
                        // Get effective shift for today to check if late
                        $shift = $employee->getEffectiveShiftForDate($selectedDate->format('Y-m-d'));
                        
                        if ($shift && $shift->time_from) {
                            $shiftStartParts = explode(':', $shift->time_from);
                            $shiftStartDateTime = $selectedDate->copy()->setTime(
                                (int)($shiftStartParts[0] ?? 0),
                                (int)($shiftStartParts[1] ?? 0),
                                0
                            );
                            
                            $checkInTime = Carbon::parse($checkIn->punch_time);
                            $gracePeriodMinutes = $shift->grace_period_late_in ?? 0;
                            $expectedCheckInTime = $shiftStartDateTime->copy()->addMinutes($gracePeriodMinutes);
                            
                            if ($checkInTime->gt($expectedCheckInTime)) {
                                // Late
                                $lateMinutes = $checkInTime->diffInMinutes($expectedCheckInTime);
                                $late[] = [
                                    'id' => $employee->id,
                                    'name' => $employee->first_name . ' ' . $employee->last_name,
                                    'employee_code' => $employee->employee_code,
                                    'department' => $departmentName,
                                    'group' => $groupName,
                                    'late_minutes' => $lateMinutes,
                                    'check_in_time' => $checkInTime->format('h:i A'),
                                ];
                            }
                        }
                    }
                }
            }
            
            $this->absentEmployees = $absent;
            $this->lateEmployees = $late;
            return;
        }

        // For previous days: Use the existing complex logic
        foreach ($employees as $employee) {
            // Skip if it's a holiday or weekend
            if ($isHoliday || $isWeekend) {
                continue;
            }

            // Get effective shift for selected date
            $shift = $employee->getEffectiveShiftForDate($selectedDate->format('Y-m-d'));
            
            if (!$shift || !$shift->time_from) {
                continue; // Skip employees without shifts
            }

            // Parse shift start and end times
            $shiftStartParts = explode(':', $shift->time_from);
            $shiftEndParts = explode(':', $shift->time_to);
            
            $shiftStartTime = Carbon::createFromTime(
                (int)($shiftStartParts[0] ?? 0),
                (int)($shiftStartParts[1] ?? 0),
                0
            );
            
            $shiftEndTime = Carbon::createFromTime(
                (int)($shiftEndParts[0] ?? 0),
                (int)($shiftEndParts[1] ?? 0),
                0
            );
            
            $isOvernight = $shiftStartTime->gt($shiftEndTime);
            $shiftStartsInPM = $shiftStartTime->hour >= 12;
            
            // Determine shift start datetime
            if ($isOvernight && $shiftStartsInPM) {
                // PM-start overnight shift: started yesterday, ends today
                $shiftStartDateTime = $selectedDate->copy()->subDay()->setTime(
                    $shiftStartTime->hour,
                    $shiftStartTime->minute,
                    0
                );
                $shiftEndDateTime = $selectedDate->copy()->setTime(
                    $shiftEndTime->hour,
                    $shiftEndTime->minute,
                    0
                );
                
                // For today, only process if shift has started and hasn't ended
                if ($isToday) {
                    // If current time is past shift end, this shift is over, skip
                    if ($now->gt($shiftEndDateTime)) {
                        continue;
                    }
                    // If shift hasn't started yet (shouldn't happen for PM-start overnight on same day, but check anyway)
                    if ($now->lt($shiftStartDateTime)) {
                        continue;
                    }
                }
                
                // Shift has started (it started yesterday), so we check attendance
            } else {
                // Regular shift or AM-start overnight shift: starts on selected date
                $shiftStartDateTime = $selectedDate->copy()->setTime(
                    $shiftStartTime->hour,
                    $shiftStartTime->minute,
                    0
                );
                
                // For today, only process if shift has started
                if ($isToday) {
                    if ($now->lt($shiftStartDateTime)) {
                        continue; // Shift hasn't started yet today
                    }
                }
            }

            // Get check-in for the appropriate date
            // For PM-start overnight shifts, check-in might be yesterday or today
            // For regular shifts, check-in should be today
            $checkIn = null;
            
            // Get all attendance records for the appropriate date range (like attendance module)
            // Exclude verify_mode = 2 (like attendance module does)
            $dayRecords = collect();
            
            if ($isOvernight && $shiftStartsInPM) {
                // PM-start overnight shift: check both yesterday and selected date
                $yesterdayStart = $selectedDate->copy()->subDay()->startOfDay();
                $yesterdayEnd = $selectedDate->copy()->subDay()->endOfDay();
                $todayStart = $selectedDate->copy()->startOfDay();
                $todayEnd = $selectedDate->copy()->endOfDay();
                
                $yesterdayRecords = DeviceAttendance::where('punch_code', $employee->punch_code)
                    ->whereBetween('punch_time', [$yesterdayStart, $yesterdayEnd])
                    ->where(function($query) {
                        $query->whereNull('verify_mode')
                              ->orWhere('verify_mode', '!=', 2);
                    })
                    ->get();
                
                $todayRecords = DeviceAttendance::where('punch_code', $employee->punch_code)
                    ->whereBetween('punch_time', [$todayStart, $todayEnd])
                    ->where(function($query) {
                        $query->whereNull('verify_mode')
                              ->orWhere('verify_mode', '!=', 2);
                    })
                    ->get();
                
                $dayRecords = $yesterdayRecords->merge($todayRecords);
            } else {
                // Regular shift or AM-start overnight shift: check selected date
                $dayStart = $selectedDate->copy()->startOfDay();
                $dayEnd = $selectedDate->copy()->endOfDay();
                
                $dayRecords = DeviceAttendance::where('punch_code', $employee->punch_code)
                    ->whereBetween('punch_time', [$dayStart, $dayEnd])
                    ->where(function($query) {
                        $query->whereNull('verify_mode')
                              ->orWhere('verify_mode', '!=', 2);
                    })
                    ->get();
            }
            
            // Determine if there's valid attendance (like attendance module)
            $hasValidAttendance = false;
            if ($shift && $dayRecords->isNotEmpty()) {
                // For PM-start shifts, only count PM check-ins as valid attendance
                if ($isOvernight && $shiftStartsInPM) {
                    foreach ($dayRecords as $record) {
                        if ($record->device_type === 'IN') {
                            $checkInTime = Carbon::parse($record->punch_time);
                            if ($checkInTime->hour >= 12) {
                                $hasValidAttendance = true;
                                break;
                            }
                        }
                    }
                } else {
                    // For other shifts, any records mean attendance
                    $hasValidAttendance = true;
                }
            }
            
            // Find the first check-in for late calculation
            $checkIn = null;
            if ($dayRecords->isNotEmpty()) {
                foreach ($dayRecords as $record) {
                    if ($record->device_type === 'IN') {
                        // For PM-start overnight shifts, only count PM check-ins
                        if ($isOvernight && $shiftStartsInPM) {
                            $checkInTime = Carbon::parse($record->punch_time);
                            if ($checkInTime->hour >= 12) {
                                $checkIn = $record;
                                break;
                            }
                        } else {
                            $checkIn = $record;
                            break;
                        }
                    }
                }
            }

            // Get department and group names safely
            $departmentName = 'N/A';
            if ($employee->department_id) {
                $department = $employee->department()->first();
                if ($department && is_object($department)) {
                    $departmentName = $department->title ?? 'N/A';
                }
            }
            
            $groupName = 'N/A';
            if ($employee->group_id) {
                $group = $employee->group;
                if ($group && is_object($group)) {
                    $groupName = $group->name ?? 'N/A';
                }
            }

            // Determine status like attendance module: No valid attendance = Absent
            // IMPORTANT: Only mark as absent if shift has started (for today) or it's a historical date
            if (!$hasValidAttendance) {
                // No valid attendance = Absent
                // We've already verified that the shift has started (for today) or it's a historical date
                $absent[] = [
                    'id' => $employee->id,
                    'name' => $employee->first_name . ' ' . $employee->last_name,
                    'employee_code' => $employee->employee_code,
                    'department' => $departmentName,
                    'group' => $groupName,
                ];
            } elseif ($checkIn) {
                // Has check-in, check if late
                $checkInTime = Carbon::parse($checkIn->punch_time);
                
                // Calculate expected check-in time (shift start + grace period)
                $gracePeriodMinutes = $shift->grace_period_late_in ?? 0;
                $expectedCheckInTime = $shiftStartDateTime->copy()->addMinutes($gracePeriodMinutes);
                
                if ($checkInTime->gt($expectedCheckInTime)) {
                    // Late
                    $lateMinutes = $checkInTime->diffInMinutes($expectedCheckInTime);
                    $late[] = [
                        'id' => $employee->id,
                        'name' => $employee->first_name . ' ' . $employee->last_name,
                        'employee_code' => $employee->employee_code,
                        'department' => $departmentName,
                        'group' => $groupName,
                        'late_minutes' => $lateMinutes,
                        'check_in_time' => $checkInTime->format('h:i A'),
                    ];
                }
            }
        }

        $this->absentEmployees = $absent;
        $this->lateEmployees = $late;
    }

    public function refresh()
    {
        $this->loadAbsentLateEmployees();
    }

    public function render()
    {
        return view('livewire.dashboard.absent-late-employees');
    }
}
