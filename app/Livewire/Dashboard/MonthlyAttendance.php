<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\DeviceAttendance;
use App\Models\Employee;
use App\Models\LeaveRequest as LeaveRequestModel;
use App\Models\Shift;
use App\Models\Constant;
use App\Models\ExemptionDay;
use Carbon\Carbon;
use App\Models\User;

class MonthlyAttendance extends Component
{
    public $dailyStats = [];
    public $currentMonth = '';
    public $selectedMonth = '';
    public $availableMonths = [];
    public $selectedUserId = null;
    public $availableUsers = [];
    public bool $canSwitchUsers = false;
    public bool $canViewOtherUsers = false;
    
    // Global grace period settings
    public $globalGracePeriodLateIn = 30;
    public $globalGracePeriodEarlyOut = 30;

    public function mount()
    {
        $this->currentMonth = Carbon::now()->format('F Y');
        $this->selectedMonth = Carbon::now()->format('Y-m'); // Default to current month
        $user = Auth::user();
        $this->canSwitchUsers = $user
            ? ($user->can('attendance.manage.switch_user') || $user->hasRole('Super Admin'))
            : false;

        $this->canViewOtherUsers = $user
            ? ($this->canSwitchUsers || $user->can('attendance.view.team') || $user->can('attendance.view.company'))
            : false;

        if ($this->canViewOtherUsers) {
            $this->loadAvailableUsers();
        }

        $this->loadGlobalGracePeriods();
        $this->loadAvailableMonths();
        $this->calculateDailyAttendance();
    }
    
    private function loadGlobalGracePeriods()
    {
        $lateInConstant = Constant::where('key', 'attendance_grace_period_late_in')
            ->where('status', 'active')
            ->first();
        
        $earlyOutConstant = Constant::where('key', 'attendance_grace_period_early_out')
            ->where('status', 'active')
            ->first();
        
        if ($lateInConstant) {
            $this->globalGracePeriodLateIn = (int) $lateInConstant->value;
        }
        
        if ($earlyOutConstant) {
            $this->globalGracePeriodEarlyOut = (int) $earlyOutConstant->value;
        }
    }
    
    private function getEffectiveGracePeriodLateIn($shift)
    {
        if (!$shift) {
            return $this->globalGracePeriodLateIn;
        }
        
        if ($shift->disable_grace_period) {
            return 0;
        }
        
        return $shift->grace_period_late_in !== null 
            ? $shift->grace_period_late_in 
            : $this->globalGracePeriodLateIn;
    }
    
    private function getEffectiveGracePeriodEarlyOut($shift)
    {
        if (!$shift) {
            return $this->globalGracePeriodEarlyOut;
        }
        
        if ($shift->disable_grace_period) {
            return 0;
        }
        
        return $shift->grace_period_early_out !== null 
            ? $shift->grace_period_early_out 
            : $this->globalGracePeriodEarlyOut;
    }

    public function loadAvailableUsers()
    {
        if (!$this->canViewOtherUsers) {
            $this->availableUsers = [];
            return;
        }

        $employees = Employee::whereNotNull('punch_code')
            ->whereNotNull('user_id')
            ->where('status', 'active')
            ->with('user:id,name,email')
            ->get();

        $this->availableUsers = $employees->map(function ($employee) {
            return [
                'id' => $employee->user_id,
                'name' => trim($employee->first_name . ' ' . $employee->last_name),
                'punch_code' => $employee->punch_code,
            ];
        })
        ->sortBy('name')
        ->values()
        ->toArray();
    }

    public function updatedSelectedUserId()
    {
        $this->loadAvailableMonths();
        $this->calculateDailyAttendance();
        $this->dispatch('monthly-attendance-updated');
    }

    public function loadAvailableMonths()
    {
        // Get current logged-in user
        $userId = $this->selectedUserId ?: Auth::id();
        $user = $userId ? User::find($userId) : null;
        
        if (!$user) {
            $this->availableMonths = [];
            return;
        }
        
        // Get the employee record for this user
        $employee = Employee::where('user_id', $user->id)->first();
        
        if (!$employee || !$employee->punch_code) {
            $this->availableMonths = [];
            return;
        }

        $currentMonth = Carbon::now()->format('Y-m');

        // Get all months that have attendance data
        $months = DeviceAttendance::where('punch_code', $employee->punch_code)
            ->selectRaw('DATE_FORMAT(punch_time, "%Y-%m") as month')
            ->distinct()
            ->orderBy('month', 'desc')
            ->pluck('month');

        $this->availableMonths = [];
        
        // Always include current month
        $carbonCurrentMonth = Carbon::createFromFormat('Y-m', $currentMonth);
        $this->availableMonths[] = [
            'value' => $currentMonth,
            'label' => $carbonCurrentMonth->format('F Y') . ' (Current)'
        ];
        
        // Add other months
        foreach ($months as $month) {
            if ($month !== $currentMonth) {
                $carbonMonth = Carbon::createFromFormat('Y-m', $month);
                $this->availableMonths[] = [
                    'value' => $month,
                    'label' => $carbonMonth->format('F Y')
                ];
            }
        }

        $this->availableMonths = array_values($this->availableMonths);

        $availableValues = collect($this->availableMonths)->pluck('value');
        if (!$availableValues->contains($this->selectedMonth)) {
            $this->selectedMonth = $this->availableMonths[0]['value'] ?? Carbon::now()->format('Y-m');
        }
    }

    public function updatedSelectedMonth()
    {
        $this->calculateDailyAttendance();
        $this->dispatch('monthly-attendance-updated');
    }

    /**
     * Check if a date is exempted for the employee
     */
    private function isDateExempted($date, $employee)
    {
        if (!$employee) {
            return false;
        }

        $dateCarbon = Carbon::parse($date);
        $userId = $employee->user_id;
        $departmentId = $employee->department_id;
        $user = $employee->user;
        $userRoles = $user ? $user->roles->pluck('id')->toArray() : [];

        // Check for exemption days that apply to this employee
        $exemptions = ExemptionDay::where(function($query) use ($dateCarbon) {
                $query->where('from_date', '<=', $dateCarbon->format('Y-m-d'))
                      ->where('to_date', '>=', $dateCarbon->format('Y-m-d'));
            })
            ->where(function($query) use ($userId, $departmentId, $userRoles) {
                $query->where('scope_type', 'all')
                      ->orWhere(function($q) use ($userId) {
                          $q->where('scope_type', 'user')->where('user_id', $userId);
                      })
                      ->orWhere(function($q) use ($departmentId) {
                          $q->where('scope_type', 'department')->where('department_id', $departmentId);
                      })
                      ->orWhere(function($q) use ($userRoles) {
                          $q->where('scope_type', 'role')->whereIn('role_id', $userRoles);
                      });
            })
            ->exists();

        return $exemptions;
    }

    /**
     * Deduplicate records (same as attendance module)
     */
    private function deduplicateRecords($records)
    {
        if (empty($records)) {
            return $records;
        }

        $recordsArray = is_array($records) ? $records : $records->toArray();

        if (count($recordsArray) <= 1) {
            return $recordsArray;
        }

        $deduplicated = [];
        $lastRecord = null;
        $lastTime = null;

        foreach ($recordsArray as $record) {
            $recordTime = Carbon::parse($record['punch_time']);
            $recordType = $record['device_type'];

            if ($lastRecord === null) {
                $deduplicated[] = $record;
                $lastRecord = $record;
                $lastTime = $recordTime;
            } else {
                $lastType = $lastRecord['device_type'];
                $timeDiff = $lastTime->diffInSeconds($recordTime);

                // If same type and within 10 seconds, keep the later one (replace)
                if ($recordType === $lastType && $timeDiff <= 10) {
                    array_pop($deduplicated);
                    $deduplicated[] = $record;
                    $lastRecord = $record;
                    $lastTime = $recordTime;
                } else {
                    $deduplicated[] = $record;
                    $lastRecord = $record;
                    $lastTime = $recordTime;
                }
            }
        }

        return $deduplicated;
    }

    /**
     * Calculate minutes from first IN to last OUT (for missing pairs with break exclusion)
     */
    private function calculateMinutesFromFirstInToLastOut($records)
    {
        if (empty($records)) {
            return null;
        }

        $firstInRecord = collect($records)->first(function ($record) {
            return ($record['device_type'] ?? null) === 'IN';
        });

        $lastOutRecord = collect($records)->reverse()->first(function ($record) {
            return ($record['device_type'] ?? null) === 'OUT';
        });

        if (!$firstInRecord || !$lastOutRecord) {
            return null;
        }

        $firstInTime = Carbon::parse($firstInRecord['punch_time']);
        $lastOutTime = Carbon::parse($lastOutRecord['punch_time']);

        if ($lastOutTime->lessThanOrEqualTo($firstInTime)) {
            return null;
        }

        return $firstInTime->diffInMinutes($lastOutTime);
    }

    private function calculateDailyAttendance()
    {
        // Get current logged-in user
        $userId = $this->selectedUserId ?: Auth::id();
        $user = $userId ? User::find($userId) : null;
        
        if (!$user) {
            $this->dailyStats = [];
            return;
        }
        
        // Get the employee record with shift and department
        $employee = Employee::where('user_id', $user->id)
            ->with(['shift', 'department.shift', 'user.roles'])
            ->first();
        
        if (!$employee || !$employee->punch_code) {
            $this->dailyStats = [];
            return;
        }
        
        // Get effective shift (employee shift or department shift fallback)
        $employeeShift = $employee->getEffectiveShift();
        
        // Determine which month to load
        $targetMonth = $this->selectedMonth ?: Carbon::now()->format('Y-m');
        $startOfMonth = Carbon::createFromFormat('Y-m', $targetMonth)->startOfMonth();
        $endOfMonth = Carbon::createFromFormat('Y-m', $targetMonth)->endOfMonth();
        $today = Carbon::now();
        
        // For current month, only count days up to today (end of day)
        // For past/future months, use end of month
        if ($targetMonth === $today->format('Y-m')) {
            $endDate = $today->copy()->endOfDay();
        } else {
            $endDate = $endOfMonth->copy()->endOfDay();
        }
        
        // Get all attendance records for this month (need to extend range for overnight shifts)
        $records = DeviceAttendance::where('punch_code', $employee->punch_code)
            ->whereBetween('punch_time', [
                $startOfMonth->copy()->subDay()->format('Y-m-d 00:00:00'), // Include previous day for overnight shifts
                $endDate->copy()->addDay()->format('Y-m-d 23:59:59') // Include next day for overnight shifts
            ])
            ->orderBy('punch_time')
            ->get();
        
        // Group records by date with grace period logic (same as attendance module)
        $groupedRecords = [];
        
        // First, determine shift characteristics if available
        $isOvernight = false;
        $timeFrom = null;
        $timeTo = null;
        $gracePeriodHours = 5; // 5 hours grace period
        
        if ($employeeShift) {
            $timeFromParts = explode(':', $employeeShift->time_from);
            $timeToParts = explode(':', $employeeShift->time_to);
            $timeFrom = Carbon::createFromTime(
                (int)($timeFromParts[0] ?? 0),
                (int)($timeFromParts[1] ?? 0),
                (int)($timeFromParts[2] ?? 0)
            );
            $timeTo = Carbon::createFromTime(
                (int)($timeToParts[0] ?? 0),
                (int)($timeToParts[1] ?? 0),
                (int)($timeToParts[2] ?? 0)
            );
            $isOvernight = $timeFrom->gt($timeTo);
        }
        
        // Group records with grace period logic for non-overnight shifts (same as attendance module)
        foreach ($records as $record) {
            $punchTime = Carbon::parse($record->punch_time);
            $punchDate = $punchTime->format('Y-m-d');
            
            // For non-overnight shifts, apply grace period logic
            if (!$isOvernight && $employeeShift && $timeFrom && $timeTo) {
                // Check if this is a check-out on the next calendar day (after midnight)
                if ($record->device_type === 'OUT') {
                    if ($punchTime->hour < 12) {
                        $previousDate = $punchTime->copy()->subDay()->format('Y-m-d');
                        $previousDayShiftEnd = Carbon::parse($previousDate)->setTime(
                            $timeTo->hour,
                            $timeTo->minute,
                            $timeTo->second
                        );
                        $checkOutCutoff = $previousDayShiftEnd->copy()->addHours($gracePeriodHours);
                        
                        if ($punchTime->lte($checkOutCutoff)) {
                            $groupedRecords[$previousDate][] = $record;
                            continue;
                        }
                    }
                }
                
                // Check if this is a check-in on the next calendar day that still belongs to previous day
                if ($record->device_type === 'IN' && $punchTime->hour < 12) {
                    $previousDate = $punchTime->copy()->subDay()->format('Y-m-d');
                    $previousDayShiftEnd = Carbon::parse($previousDate)->setTime(
                        $timeTo->hour,
                        $timeTo->minute,
                        $timeTo->second
                    );
                    $checkOutCutoff = $previousDayShiftEnd->copy()->addHours($gracePeriodHours);
                    
                    if ($punchTime->lte($checkOutCutoff)) {
                        $groupedRecords[$previousDate][] = $record;
                        continue;
                    }
                }
                
                // Check if this is an early check-in (before shift start but within grace period)
                if ($record->device_type === 'IN') {
                    $currentDayShiftStart = Carbon::parse($punchDate)->setTime(
                        $timeFrom->hour,
                        $timeFrom->minute,
                        $timeFrom->second
                    );
                    $checkInCutoff = $currentDayShiftStart->copy()->subHours($gracePeriodHours);
                    
                    if ($punchTime->lt($currentDayShiftStart) && $punchTime->gte($checkInCutoff)) {
                        $groupedRecords[$punchDate][] = $record;
                        continue;
                    }
                }
            }
            
            // Default: group by punch date
            $groupedRecords[$punchDate][] = $record;
        }
        
        // Get approved leave requests for this month
        // Use start of day for date comparisons
        $startDateForLeave = $startOfMonth->copy()->startOfDay()->format('Y-m-d');
        $endDateForLeave = $endDate->copy()->startOfDay()->format('Y-m-d');
        
        $leaveRequests = LeaveRequestModel::where('employee_id', $employee->id)
            ->where('status', LeaveRequestModel::STATUS_APPROVED)
            ->where(function($query) use ($startDateForLeave, $endDateForLeave) {
                $query->whereBetween('start_date', [$startDateForLeave, $endDateForLeave])
                      ->orWhereBetween('end_date', [$startDateForLeave, $endDateForLeave])
                      ->orWhere(function($q) use ($startDateForLeave, $endDateForLeave) {
                          $q->where('start_date', '<=', $startDateForLeave)
                            ->where('end_date', '>=', $endDateForLeave);
                      });
            })
            ->get();
        
        // Create a map of dates that have approved leave requests
        $leaveRequestMap = [];
        foreach ($leaveRequests as $request) {
            $start = Carbon::parse($request->start_date);
            $end = Carbon::parse($request->end_date);
            $currentDate = $start->copy();
            
            while ($currentDate->lte($end)) {
                $dateKey = $currentDate->format('Y-m-d');
                if (!isset($leaveRequestMap[$dateKey])) {
                    $leaveRequestMap[$dateKey] = $request;
                }
                $currentDate->addDay();
            }
        }
        
        // Process ALL days of the month (including weekends and absent days) - same as attendance module
        $current = $startOfMonth->copy();
        $today = Carbon::now();
        
        // For current month, only show days up to today
        // For previous months, show all days
        $endDateForLoop = ($targetMonth === $today->format('Y-m')) ? $today : $endOfMonth;
        
        $dailyData = [];
        
        while ($current->lte($endDateForLoop)) {
            $date = $current->format('Y-m-d');
            $dayNumber = $current->format('d');
            $dayRecords = $groupedRecords[$date] ?? [];
            
            // Determine shift characteristics once for this day
            $isOvernight = false;
            $shiftStartsInPM = false;
            $timeFrom = null;
            $timeTo = null;
            $expectedCheckOutTime = null;
            
            if ($employeeShift) {
                $timeFromParts = explode(':', $employeeShift->time_from);
                $timeToParts = explode(':', $employeeShift->time_to);
                $timeFrom = Carbon::createFromTime(
                    (int)($timeFromParts[0] ?? 0),
                    (int)($timeFromParts[1] ?? 0),
                    (int)($timeFromParts[2] ?? 0)
                );
                $timeTo = Carbon::createFromTime(
                    (int)($timeToParts[0] ?? 0),
                    (int)($timeToParts[1] ?? 0),
                    (int)($timeToParts[2] ?? 0)
                );
                $isOvernight = $timeFrom->gt($timeTo);
                $shiftStartsInPM = $timeFrom->hour >= 12;
                
                if ($isOvernight) {
                    $nextDate = $current->copy()->addDay()->format('Y-m-d');
                    $expectedCheckOutTime = Carbon::parse($nextDate)->setTime(
                        $timeTo->hour,
                        $timeTo->minute,
                        $timeTo->second
                    );
                }
            }
            
            // Determine if there's valid attendance for this day
            $hasValidAttendance = false;
            if ($employeeShift && !empty($dayRecords)) {
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
            } elseif (!empty($dayRecords)) {
                // No shift assigned, any records mean attendance
                $hasValidAttendance = true;
            }
            
            // Check if this date is exempted
            $isExempted = $this->isDateExempted($date, $employee);
            
            // Check if this date has an approved leave request
            $hasApprovedLeave = isset($leaveRequestMap[$date]);
            
            // Determine day status
            $status = 'absent'; // Default
            if ($current->isWeekend()) {
                $status = 'off';
            } elseif ($hasValidAttendance) {
                $status = 'present';
            } elseif ($isExempted) {
                $status = 'exempted';
            }
            
            // Initialize day data
            $checkIn = null;
            $checkOut = null;
            $totalHours = null;
            $hoursDecimal = 0;
            $isLate = false;
            $isEarly = false;
            
            // Process attendance records for this day if they exist
            if (!empty($dayRecords)) {
                // Sort records by punch_time to get chronological order
                usort($dayRecords, function($a, $b) {
                    return Carbon::parse($a->punch_time)->timestamp - Carbon::parse($b->punch_time)->timestamp;
                });
                
                // Filter records based on shift type (same logic as attendance module)
                $validCheckIns = [];
                $validCheckOuts = [];
                
                if ($isOvernight && $shiftStartsInPM) {
                    // For PM-start overnight shifts
                    $expectedCheckInTime = Carbon::parse($date)->setTime(
                        $timeFrom->hour,
                        $timeFrom->minute,
                        $timeFrom->second
                    );
                    
                    // For check-ins: only PM check-ins count for this day
                    foreach ($dayRecords as $record) {
                    if ($record->device_type === 'IN') {
                            $checkInTime = Carbon::parse($record->punch_time);
                            if ($checkInTime->hour >= 12) {
                                $validCheckIns[] = $checkInTime;
                            }
                        } elseif ($record->device_type === 'OUT') {
                            $checkOutTime = Carbon::parse($record->punch_time);
                            if ($checkOutTime->hour >= 12) {
                                $validCheckOuts[] = $checkOutTime;
                            }
                        }
                    }
                    
                    // Get next day's AM check-ins and check-outs that belong to this shift
                    $nextDate = $current->copy()->addDay()->format('Y-m-d');
                    $nextDayRecords = $groupedRecords[$nextDate] ?? [];
                    
                    // Special handling for last day of month
                    $isLastDayOfMonth = $current->format('Y-m-d') === $endOfMonth->format('Y-m-d');
                    if ($isLastDayOfMonth && empty($nextDayRecords)) {
                        foreach ($records as $record) {
                            $recordDate = Carbon::parse($record->punch_time)->format('Y-m-d');
                            if ($recordDate === $nextDate) {
                                $recordTime = Carbon::parse($record->punch_time);
                                
                                if ($record->device_type === 'IN' && $recordTime->hour < 12) {
                                    $validCheckIns[] = $recordTime;
                    } elseif ($record->device_type === 'OUT') {
                                    $shiftEndOnNextDay = Carbon::parse($nextDate)->setTime(
                                        $timeTo->hour,
                                        $timeTo->minute,
                                        $timeTo->second
                                    );
                                    $checkOutCutoff = $shiftEndOnNextDay->copy()->addHours($gracePeriodHours);
                                    
                                    if ($recordTime->hour < 12 && $recordTime->lte($checkOutCutoff)) {
                                        $validCheckOuts[] = $recordTime;
                                    }
                                }
                            }
                            }
                        } else {
                        foreach ($nextDayRecords as $record) {
                            $recordTime = Carbon::parse($record->punch_time);
                            
                            if ($record->device_type === 'IN' && $recordTime->hour < 12) {
                                $validCheckIns[] = $recordTime;
                            } elseif ($record->device_type === 'OUT') {
                                $checkOutTime = Carbon::parse($record->punch_time);
                                $shiftEndOnNextDay = Carbon::parse($nextDate)->setTime(
                                    $timeTo->hour,
                                    $timeTo->minute,
                                    $timeTo->second
                                );
                                $checkOutCutoff = $shiftEndOnNextDay->copy()->addHours($gracePeriodHours);
                                
                                if ($checkOutTime->lte($checkOutCutoff)) {
                                    $validCheckOuts[] = $checkOutTime;
                                }
                            }
                        }
                    }
                } elseif ($isOvernight && !$shiftStartsInPM) {
                    // For AM-start overnight shifts
                    $expectedCheckInTime = Carbon::parse($date)->setTime(
                        $timeFrom->hour,
                        $timeFrom->minute,
                        $timeFrom->second
                    );
                    
                    foreach ($dayRecords as $record) {
                        $recordTime = Carbon::parse($record->punch_time);
                        if ($record->device_type === 'IN' || 
                            ($record->device_type === 'OUT' && $recordTime->gte($expectedCheckInTime))) {
                            if ($record->device_type === 'IN') {
                                $validCheckIns[] = $recordTime;
                            } else {
                                $validCheckOuts[] = $recordTime;
                            }
                        }
                    }
                    
                    // Get next day's check-outs that belong to this shift
                    $nextDate = $current->copy()->addDay()->format('Y-m-d');
                    $nextDayRecords = $groupedRecords[$nextDate] ?? [];
                    
                    foreach ($nextDayRecords as $record) {
                        if ($record->device_type === 'OUT') {
                            $checkOutTime = Carbon::parse($record->punch_time);
                            if ($checkOutTime->lte($expectedCheckOutTime)) {
                                $validCheckOuts[] = $checkOutTime;
                            }
                        }
                    }
                } else {
                    // For regular shifts, use all records
                    foreach ($dayRecords as $record) {
                        if ($record->device_type === 'IN') {
                            $validCheckIns[] = Carbon::parse($record->punch_time);
                        } elseif ($record->device_type === 'OUT') {
                            $validCheckOuts[] = Carbon::parse($record->punch_time);
                        }
                    }
                }
                
                // For off days with PM-start shifts: if only AM check-out exists, don't show it
                if ($current->isWeekend() && $isOvernight && $shiftStartsInPM && empty($validCheckIns)) {
                    $validCheckOuts = [];
                }
                
                // Sort and get first check-in and last check-out
                usort($validCheckIns, function($a, $b) {
                    return $a->timestamp - $b->timestamp;
                });
                usort($validCheckOuts, function($a, $b) {
                    return $a->timestamp - $b->timestamp;
                });
                
                $firstCheckIn = !empty($validCheckIns) ? $validCheckIns[0] : null;
                $lastCheckOut = !empty($validCheckOuts) ? end($validCheckOuts) : null;
                
                // For exempted days, if we have records, mark as present
                if ($isExempted && ($firstCheckIn || $lastCheckOut)) {
                    $status = 'present';
                }
                
                // If there's a check-out (even without check-in), mark as present (incomplete attendance)
                // This handles cases where employee forgot to check-in but checked out
                if (!$isExempted && empty($validCheckIns) && !empty($validCheckOuts)) {
                    // Don't clear check-out - we want to show it as present with incomplete attendance
                    $status = 'present';
                }
                
                // Update status: only mark as absent if there's no check-in AND no check-out for PM-start shifts
                // But if there's a check-out, we already set status to 'present' above
                if ($isOvernight && $shiftStartsInPM && empty($validCheckIns) && empty($validCheckOuts)) {
                    if (!$isExempted) {
                        $firstCheckIn = null;
                        $lastCheckOut = null;
                        if ($current->isWeekend()) {
                            $status = 'off';
                        } else {
                            $status = 'absent';
                        }
                    }
                }
                
                $checkIn = $firstCheckIn;
                $checkOut = $lastCheckOut;
                
                // If there's an approved leave request, always set to on_leave (even if attendance records exist)
                // This ensures leave days are clearly visible in the dashboard bar graph
                if ($hasApprovedLeave) {
                    $status = 'on_leave';
                }
                
                // Now calculate total hours using the same logic as attendance module
                if ($checkIn && $checkOut) {
                    // Build recordsForCalculation array (same as attendance module)
                    $recordsForCalculation = [];
                    
                    if ($isOvernight && $shiftStartsInPM) {
                        // For PM-start overnight shifts
                        foreach ($dayRecords as $record) {
                            $recordTime = Carbon::parse($record->punch_time);
                            if ($record->device_type === 'IN' && $recordTime->hour >= 12) {
                                $recordsForCalculation[] = $record;
                            }
                            if ($record->device_type === 'OUT' && $recordTime->hour >= 12) {
                                $recordsForCalculation[] = $record;
                            }
                        }
                        
                        $nextDate = $current->copy()->addDay()->format('Y-m-d');
                        $nextDayRecords = $groupedRecords[$nextDate] ?? [];
                        
                        foreach ($nextDayRecords as $record) {
                            $recordTime = Carbon::parse($record->punch_time);
                            
                            if ($record->device_type === 'IN' && $recordTime->hour < 12) {
                                $recordsForCalculation[] = $record;
                            } elseif ($record->device_type === 'OUT') {
                                $checkOutTime = Carbon::parse($record->punch_time);
                                $shiftEndOnNextDay = Carbon::parse($nextDate)->setTime(
                                    $timeTo->hour,
                                    $timeTo->minute,
                                    $timeTo->second
                                );
                                $checkOutCutoff = $shiftEndOnNextDay->copy()->addHours($gracePeriodHours);
                                
                                if ($checkOutTime->lte($checkOutCutoff)) {
                                    $recordsForCalculation[] = $record;
                                }
                            }
                        }
                    } elseif ($isOvernight && !$shiftStartsInPM) {
                        // For AM-start overnight shifts
                        $expectedCheckInTime = Carbon::parse($date)->setTime(
                            $timeFrom->hour,
                            $timeFrom->minute,
                            $timeFrom->second
                        );
                        
                        foreach ($dayRecords as $record) {
                            $recordTime = Carbon::parse($record->punch_time);
                            if ($record->device_type === 'IN' || 
                                ($record->device_type === 'OUT' && $recordTime->gte($expectedCheckInTime))) {
                                $recordsForCalculation[] = $record;
                            }
                        }
                        
                        $nextDate = $current->copy()->addDay()->format('Y-m-d');
                        $nextDayRecords = $groupedRecords[$nextDate] ?? [];
                        
                        foreach ($nextDayRecords as $record) {
                            if ($record->device_type === 'OUT') {
                                $checkOutTime = Carbon::parse($record->punch_time);
                                if ($checkOutTime->lte($expectedCheckOutTime)) {
                                    $recordsForCalculation[] = $record;
                                }
                            }
                        }
                    } else {
                        // For regular (non-overnight) shifts, use all records from the day
                        $recordsForCalculation = $dayRecords;
                    }
                    
                    // For exempted days, only use first check-in and last check-out
                    if ($isExempted && !empty($recordsForCalculation)) {
                        $originalRecordsForCalculation = $recordsForCalculation;
                        
                        $checkIns = [];
                        $checkOuts = [];
                        
                        foreach ($recordsForCalculation as $record) {
                            $recordTime = Carbon::parse($record->punch_time ?? (is_object($record) ? $record->punch_time : $record['punch_time']));
                            $deviceType = is_object($record) ? $record->device_type : $record['device_type'];
                            if ($deviceType === 'IN') {
                                $checkIns[] = $recordTime;
                            } elseif ($deviceType === 'OUT') {
                                $checkOuts[] = $recordTime;
                            }
                        }
                        
                        usort($checkIns, function($a, $b) {
                            return $a->timestamp - $b->timestamp;
                        });
                        usort($checkOuts, function($a, $b) {
                            return $a->timestamp - $b->timestamp;
                        });
                        
                        $recordsForCalculation = [];
                        if (!empty($checkIns)) {
                            foreach ($originalRecordsForCalculation as $record) {
                                $recordTime = Carbon::parse($record->punch_time ?? (is_object($record) ? $record->punch_time : $record['punch_time']));
                                $deviceType = is_object($record) ? $record->device_type : $record['device_type'];
                                if ($deviceType === 'IN' && $recordTime->equalTo($checkIns[0])) {
                                    $recordsForCalculation[] = $record;
                                    break;
                                }
                            }
                        }
                        if (!empty($checkOuts)) {
                            $lastCheckOutTime = end($checkOuts);
                            foreach ($originalRecordsForCalculation as $record) {
                                $recordTime = Carbon::parse($record->punch_time ?? (is_object($record) ? $record->punch_time : $record['punch_time']));
                                $deviceType = is_object($record) ? $record->device_type : $record['device_type'];
                                if ($deviceType === 'OUT' && $recordTime->equalTo($lastCheckOutTime)) {
                                    $recordsForCalculation[] = $record;
                                    break;
                                }
                            }
                        }
                    }
                    
                    if (!empty($recordsForCalculation)) {
                        // Convert DeviceAttendance models to arrays
                        $recordsArray = collect($recordsForCalculation)->map(function($record) {
                            if (is_object($record) && method_exists($record, 'toArray')) {
                                return $record->toArray();
                            }
                            return $record;
                        })->toArray();
                        
                        $sortedRecords = collect($recordsArray)->sortBy('punch_time');
                        
                        // Deduplicate records before processing
                        $deduplicatedRecords = $this->deduplicateRecords($sortedRecords->toArray());
                        $deduplicatedCollection = collect($deduplicatedRecords);
                        
                        // For exempted days, calculate hours from first check-in to last check-out
                        if ($isExempted && $firstCheckIn && $lastCheckOut) {
                            $exemptedMinutes = $firstCheckIn->diffInMinutes($lastCheckOut);
                            if ($exemptedMinutes > 0) {
                                $hours = floor($exemptedMinutes / 60);
                                $minutes = $exemptedMinutes % 60;
                                $totalHours = sprintf('%d:%02d', $hours, $minutes);
                                $hoursDecimal = $hours + ($minutes / 60);
                            } else {
                                $totalHours = 'N/A';
                                $hoursDecimal = 0;
                            }
                        } else {
                            // Calculate total working hours by processing all records for work sessions
                            $dayTotalMinutes = 0;
                            $currentWorkStart = null;
                            $hasMissingPair = false;
                            
                            foreach ($deduplicatedCollection as $record) {
                                $recordTime = Carbon::parse($record['punch_time']);
                                
                                if ($record['device_type'] === 'IN') {
                                    if ($currentWorkStart !== null) {
                                        $hasMissingPair = true;
                                    }
                                    $currentWorkStart = $recordTime;
                                } elseif ($record['device_type'] === 'OUT' && $currentWorkStart) {
                                    $workDuration = $currentWorkStart->diffInMinutes($recordTime);
                                    if ($workDuration > 0) {
                                        $dayTotalMinutes += $workDuration;
                                    }
                                    $currentWorkStart = null;
                                } elseif ($record['device_type'] === 'OUT' && $currentWorkStart === null) {
                                    $hasMissingPair = true;
                                }
                            }
                            
                            if ($currentWorkStart !== null) {
                                $hasMissingPair = true;
                            }
                            
                            // Format total hours - show N/A if missing pairs detected
                            if ($hasMissingPair) {
                                $calculatedMinutes = null;
                                // For break exclusion, use first IN to last OUT
                                $calculatedMinutes = $this->calculateMinutesFromFirstInToLastOut($deduplicatedCollection->toArray());
                                
                                if ($calculatedMinutes !== null) {
                                    $hours = floor($calculatedMinutes / 60);
                                    $minutes = $calculatedMinutes % 60;
                                    $totalHours = sprintf('%d:%02d', $hours, $minutes);
                                    $hoursDecimal = $hours + ($minutes / 60);
                                } else {
                                    $totalHours = 'N/A';
                                    $hoursDecimal = 0;
                                }
                            } elseif ($dayTotalMinutes > 0) {
                                $hours = floor($dayTotalMinutes / 60);
                                $minutes = $dayTotalMinutes % 60;
                    $totalHours = sprintf('%d:%02d', $hours, $minutes);
                    $hoursDecimal = $hours + ($minutes / 60);
                            } else {
                                $totalHours = 'N/A';
                                $hoursDecimal = 0;
                            }
                        }
                    } else {
                        $totalHours = 'N/A';
                        $hoursDecimal = 0;
                    }
                } elseif ($checkIn && !$checkOut) {
                    // For current day with only check-in
                    $isToday = $date === Carbon::today()->format('Y-m-d');
                    if ($isToday) {
                        $now = Carbon::now();
                        $totalMinutes = $checkIn->diffInMinutes($now);
                        $hours = floor($totalMinutes / 60);
                        $minutes = $totalMinutes % 60;
                        $totalHours = sprintf('%d:%02d', $hours, $minutes);
                        $hoursDecimal = $hours + ($minutes / 60);
                        if ($hoursDecimal < 1) {
                            $hoursDecimal = 1;
                        }
                    } else {
                        // For past days with only check-in, show minimum bar height to indicate presence
                        // Calculate hours from check-in to expected shift end (or reasonable end time)
                        $totalHours = 'N/A';
                        $hoursDecimal = 0;
                        
                        // If we have shift information, calculate from check-in to expected shift end
                        if ($employeeShift && $timeTo) {
                            // Determine expected check-out time
                            if ($isOvernight) {
                                // For overnight shifts, check-out is on next day
                                $nextDate = Carbon::parse($date)->addDay();
                                $expectedCheckOut = $nextDate->setTime(
                                    $timeTo->hour,
                                    $timeTo->minute,
                                    $timeTo->second
                                );
                            } else {
                                // For regular shifts, check-out is on same day
                                $expectedCheckOut = Carbon::parse($date)->setTime(
                                    $timeTo->hour,
                                    $timeTo->minute,
                                    $timeTo->second
                                );
                            }
                            
                            // Calculate hours from check-in to expected check-out
                            $totalMinutes = $checkIn->diffInMinutes($expectedCheckOut);
                            if ($totalMinutes > 0) {
                                $hours = floor($totalMinutes / 60);
                                $minutes = $totalMinutes % 60;
                                $totalHours = sprintf('%d:%02d', $hours, $minutes);
                                $hoursDecimal = $hours + ($minutes / 60);
                            } else {
                                // If check-in is after expected check-out, show minimum 1 hour
                                $hoursDecimal = 1;
                                $totalHours = '1:00';
                            }
                        } else {
                            // No shift info, show minimum bar height (1 hour) to indicate presence
                            $hoursDecimal = 1;
                            $totalHours = '1:00';
                        }
                    }
                } elseif (!$checkIn && $checkOut) {
                    // For days with only check-out (missing check-in)
                    // Calculate hours from a reasonable start time to check-out
                    $totalHours = 'N/A';
                    $hoursDecimal = 0;
                    
                    // If we have shift information, calculate from shift start to check-out
                    if ($employeeShift && $timeFrom) {
                        // Determine expected check-in time
                        if ($isOvernight && $shiftStartsInPM) {
                            // For PM-start overnight shifts, check-in is on the same day (PM)
                            $expectedCheckIn = Carbon::parse($date)->setTime(
                                $timeFrom->hour,
                                $timeFrom->minute,
                                $timeFrom->second
                            );
                        } elseif ($isOvernight && !$shiftStartsInPM) {
                            // For AM-start overnight shifts, check-in is on previous day
                            $prevDate = Carbon::parse($date)->subDay();
                            $expectedCheckIn = $prevDate->setTime(
                                $timeFrom->hour,
                                $timeFrom->minute,
                                $timeFrom->second
                            );
                        } else {
                            // For regular shifts, check-in is on same day
                            $expectedCheckIn = Carbon::parse($date)->setTime(
                                $timeFrom->hour,
                                $timeFrom->minute,
                                $timeFrom->second
                            );
                        }
                        
                        // Ensure expected check-in is before check-out
                        if ($expectedCheckIn->lt($checkOut)) {
                            $totalMinutes = $expectedCheckIn->diffInMinutes($checkOut);
                            $hours = floor($totalMinutes / 60);
                            $minutes = $totalMinutes % 60;
                            $totalHours = sprintf('%d:%02d', $hours, $minutes);
                            $hoursDecimal = $hours + ($minutes / 60);
                            // Ensure minimum bar height for visibility (at least 1 hour)
                            if ($hoursDecimal < 1) {
                                $hoursDecimal = 1;
                            }
                        } else {
                            // If expected check-in is after check-out, show minimum 1 hour
                            $hoursDecimal = 1;
                            $totalHours = '1:00';
                        }
                    } else {
                        // No shift info, calculate from start of day to check-out, or show minimum 1 hour
                        $startOfDay = Carbon::parse($date)->startOfDay();
                        if ($startOfDay->lt($checkOut)) {
                            $totalMinutes = $startOfDay->diffInMinutes($checkOut);
                            $hours = floor($totalMinutes / 60);
                            $minutes = $totalMinutes % 60;
                            $totalHours = sprintf('%d:%02d', $hours, $minutes);
                            $hoursDecimal = $hours + ($minutes / 60);
                            if ($hoursDecimal < 1) {
                                $hoursDecimal = 1;
                            }
                        } else {
                            // Show minimum bar height (1 hour) to indicate presence
                            $hoursDecimal = 1;
                            $totalHours = '1:00';
                        }
                    }
                } else {
                    $totalHours = 'N/A';
                    $hoursDecimal = 0;
                }
                
                // Check if late or early (if shift exists)
                if ($employeeShift && $checkIn && $timeFrom) {
                    $expectedCheckIn = Carbon::parse($date)->setTime(
                        $timeFrom->hour,
                        $timeFrom->minute,
                        $timeFrom->second
                    );
                    
                    $gracePeriod = $this->getEffectiveGracePeriodLateIn($employeeShift);
                    $gracePeriodTime = $expectedCheckIn->copy()->addMinutes($gracePeriod);
                    
                    if ($checkIn->gt($gracePeriodTime)) {
                        $isLate = true;
                    }
                    
                    // Check if early (if check-out exists)
                    if ($checkOut && $timeTo) {
                        // Determine expected check-out time
                        if ($isOvernight) {
                            // For overnight shifts, check-out is on next day
                            $expectedCheckOut = Carbon::parse($date)->addDay()->setTime(
                                $timeTo->hour,
                                $timeTo->minute,
                                $timeTo->second
                            );
                        } else {
                            // For regular shifts, check-out is on same day
                            $expectedCheckOut = Carbon::parse($date)->setTime(
                                $timeTo->hour,
                                $timeTo->minute,
                                $timeTo->second
                            );
                        }
                        
                        $gracePeriodEarly = $this->getEffectiveGracePeriodEarlyOut($employeeShift);
                        $gracePeriodEarlyTime = $expectedCheckOut->copy()->subMinutes($gracePeriodEarly);
                        
                        if ($checkOut->lt($gracePeriodEarlyTime)) {
                            $isEarly = true;
                        }
                    }
                    
                    // Update status to include late/early information (but don't override on_leave)
                    if ($status === 'present' && !$hasApprovedLeave) {
                        if ($isLate && $isEarly) {
                            $status = 'present_late_early';
                        } elseif ($isLate) {
                            $status = 'present_late';
                        } elseif ($isEarly) {
                            $status = 'present_early';
                        }
                    }
                }
            }
            
            // Final check: if there's an approved leave request, always set to on_leave
            // This ensures leave days are clearly visible in the dashboard bar graph, even if attendance records exist
            if ($hasApprovedLeave) {
                $status = 'on_leave';
            }
            
            // Check if attendance is incomplete (missing check-in or check-out)
            $hasIncompleteAttendance = false;
            if ($status === 'present' || $status === 'present_late' || $status === 'present_early' || $status === 'present_late_early') {
                // Only mark as incomplete if we have attendance status but missing check-in or check-out
                if (empty($checkIn) || empty($checkOut)) {
                    $hasIncompleteAttendance = true;
                }
            }
            
            $dailyData[] = [
                'date' => $date,
                'label' => $dayNumber . '-' . $current->format('M'),
                'hours' => $hoursDecimal,
                'status' => $status,
                'check_in' => $checkIn ? $checkIn->format('h:i A') : null,
                'check_out' => $checkOut ? $checkOut->format('h:i A') : null,
                'total_hours' => $totalHours,
                'is_late' => $isLate,
                'is_early' => isset($isEarly) ? $isEarly : false,
                'has_incomplete_attendance' => $hasIncompleteAttendance,
            ];
            
            $current->addDay();
        }
        
        $this->dailyStats = $dailyData;
    }

    public function render()
    {
        return view('livewire.dashboard.monthly-attendance');
    }
}
