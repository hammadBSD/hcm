<?php

namespace App\Livewire\Attendance;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\QueryException;
use App\Models\AttendanceBreakExclusion;
use App\Models\DeviceAttendance;
use App\Models\Employee;
use App\Models\User;
use App\Models\Shift;
use App\Models\Constant;
use App\Models\AttendanceBreakSetting;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class Index extends Component
{
    public $currentMonth = '';
    public $selectedMonth = '';
    public $attendanceData = [];
    public $attendanceStats = [];
    public $punchCode = null;
    public $employee = null;
    public $employeeShift = null;
    public $availableMonths = [];
    public $selectedUserId = null;
    public $availableUsers = [];
    public $userSearchTerm = '';
    public bool $canSwitchUsers = false;
    public bool $canViewOtherUsers = false;
    
    // Global grace period settings (loaded from constants)
    public $globalGracePeriodLateIn = 30;
    public $globalGracePeriodEarlyOut = 30;
    
    // Sorting Properties
    public $sortBy = 'date';
    public $sortDirection = 'desc';
    
    // Leave Request Modal Properties
    public $showLeaveRequestModal = false;
    public $selectedDate = '';
    public $leaveType = '';
    public $leaveDuration = '';
    public $leaveDays = '1.00 Working Day';
    public $leaveFrom = '';
    public $leaveTo = '';
    public $reason = '';
    
    // Missing Entry Flyout Properties
    public $showMissingEntryFlyout = false;
    public $missingEntryDate = '';
    public $missingEntryType = ''; // 'IN' or 'OUT'
    public $missingEntryTime = '';
    public $missingEntryNotes = '';
    public $dateAdjusted = false; // Flag to show if date was auto-adjusted
    
    // View Changes Flyout Properties
    public $showViewChangesFlyout = false;
    public $viewChangesDate = '';
    public $manualEntries = [];

    public bool $isBreakTrackingExcluded = false;
    public bool $showBreaksInGrid = true;

    public function mount()
    {
        $this->currentMonth = Carbon::now()->format('F Y');
        $this->selectedMonth = ''; // Default to current month
        $this->loadGlobalGracePeriods();
        $user = Auth::user();
        $this->canSwitchUsers = $user
            ? ($user->can('attendance.manage.switch_user') || $user->hasRole('Super Admin'))
            : false;

        $this->canViewOtherUsers = $user
            ? ($this->canSwitchUsers || $user->can('attendance.view.team') || $user->can('attendance.view.company'))
            : false;

        if ($this->canViewOtherUsers) {
            $this->loadAvailableUsers();
        } else {
            $this->availableUsers = [];
            $this->selectedUserId = null;
        }

        $this->loadUserAttendance();
    }
    
    /**
     * Load global grace period settings from constants table
     */
    private function loadGlobalGracePeriods()
    {
        // Try to get global grace period settings from constants
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
    
    /**
     * Get effective grace period for late check-in
     * Returns shift-specific if set, otherwise global, but respects disable flag
     */
    private function getEffectiveGracePeriodLateIn()
    {
        if (!$this->employeeShift) {
            return $this->globalGracePeriodLateIn;
        }
        
        // If grace period is completely disabled for this shift, return 0
        if ($this->employeeShift->disable_grace_period) {
            return 0;
        }
        
        // Return shift-specific if set, otherwise global
        return $this->employeeShift->grace_period_late_in !== null 
            ? $this->employeeShift->grace_period_late_in 
            : $this->globalGracePeriodLateIn;
    }
    
    /**
     * Get effective grace period for early check-out
     * Returns shift-specific if set, otherwise global, but respects disable flag
     */
    private function getEffectiveGracePeriodEarlyOut()
    {
        if (!$this->employeeShift) {
            return $this->globalGracePeriodEarlyOut;
        }
        
        // If grace period is completely disabled for this shift, return 0
        if ($this->employeeShift->disable_grace_period) {
            return 0;
        }
        
        // Return shift-specific if set, otherwise global
        return $this->employeeShift->grace_period_early_out !== null 
            ? $this->employeeShift->grace_period_early_out 
            : $this->globalGracePeriodEarlyOut;
    }
    
    public function loadAvailableUsers()
    {
        if (!$this->canViewOtherUsers) {
            $this->availableUsers = [];
            return;
        }

        // Get only active employees with punch codes and their associated users
        $employees = Employee::whereNotNull('punch_code')
            ->whereNotNull('user_id')
            ->where('status', 'active')
            ->with('user:id,name,email')
            ->get();
        
        $this->availableUsers = $employees->map(function($employee) {
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

    public function loadAvailableMonths()
    {
        if (!$this->punchCode) {
            return;
        }

        $currentMonth = Carbon::now()->format('Y-m');

        // Get all months that have attendance data, excluding current month
        $months = DeviceAttendance::where('punch_code', $this->punchCode)
            ->selectRaw('DATE_FORMAT(punch_time, "%Y-%m") as month')
            ->distinct()
            ->whereRaw('DATE_FORMAT(punch_time, "%Y-%m") != ?', [$currentMonth])
            ->orderBy('month', 'desc')
            ->pluck('month');

        $this->availableMonths = [];
        foreach ($months as $month) {
            $carbonMonth = Carbon::createFromFormat('Y-m', $month);
            $this->availableMonths[] = [
                'value' => $month,
                'label' => $carbonMonth->format('F Y')
            ];
        }
    }

    private function determineBreakExclusionStatus(): bool
    {
        if (!$this->employee || !$this->employee->user_id) {
            return false;
        }

        $userId = $this->employee->user_id;

        $userExcluded = AttendanceBreakExclusion::query()
            ->where('type', 'user')
            ->where('user_id', $userId)
            ->exists();

        if ($userExcluded) {
            return true;
        }

        $roleIds = $this->employee->user
            ? $this->employee->user->roles->pluck('id')->filter()->all()
            : [];

        if (empty($roleIds)) {
            return false;
        }

        return AttendanceBreakExclusion::query()
            ->where('type', 'role')
            ->whereIn('role_id', $roleIds)
            ->exists();
    }

    private function calculateMinutesFromFirstInToLastOut(Collection $records): ?int
    {
        if ($records->isEmpty()) {
            return null;
        }

        $firstInRecord = $records->first(function ($record) {
            return ($record['device_type'] ?? null) === 'IN';
        });

        $lastOutRecord = $records->reverse()->first(function ($record) {
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

    public function loadUserAttendance()
    {
        // Determine which user to load attendance for
        // If selectedUserId is null or empty, use current logged-in user
        $userId = !empty($this->selectedUserId) ? $this->selectedUserId : Auth::id();
        
        if (!$userId) {
            return;
        }

        // Find the employee record for this user
        // Eager load shift and department (with its shift) for fallback logic
        $this->employee = Employee::where('user_id', $userId)
            ->with(['shift', 'department.shift', 'user.roles'])
            ->first();
        
        if (!$this->employee) {
            return;
        }

        // Determine if break tracking is excluded for this employee or their roles
        $this->isBreakTrackingExcluded = $this->determineBreakExclusionStatus();
        $this->showBreaksInGrid = AttendanceBreakSetting::current()->show_in_attendance_grid;

        // Get the punch code
        $this->punchCode = $this->employee->punch_code;
        
        // Get the effective shift (checks employee shift first, then falls back to department shift)
        $this->employeeShift = $this->employee->getEffectiveShift();
        
        // Debug: Log if shift is found (can be removed later)
        if (!$this->employeeShift && $this->employee->department_id) {
            // Try to ensure department relationship is loaded
            $this->employee->load('department.shift');
            $this->employeeShift = $this->employee->getEffectiveShift();
        }
        
        if (!$this->punchCode) {
            return;
        }

        // Load available months after getting punch code
        $this->loadAvailableMonths();

        // Determine which month to load
        $targetMonth = $this->selectedMonth ?: Carbon::now()->format('Y-m');
        
        // Get attendance data for the selected month
        $startOfMonth = Carbon::createFromFormat('Y-m', $targetMonth)->startOfMonth();
        $endOfMonth = Carbon::createFromFormat('Y-m', $targetMonth)->endOfMonth();
        
        // Extend the end date to include next day's records that might belong to the last day
        // Default: 5 hours after month end (for non-overnight shifts)
        $extendedEndDate = $endOfMonth->copy()->addHours(5);
        
        // For overnight shifts, calculate proper extension based on shift end time + grace period
        if ($this->employeeShift) {
            $timeFromParts = explode(':', $this->employeeShift->time_from);
            $timeToParts = explode(':', $this->employeeShift->time_to);
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
            
            if ($isOvernight && $timeFrom->hour >= 12) {
                // For PM-start overnight shifts, shift ends on next day
                // Calculate: next day at shift end time + 5 hours grace period
                $nextDay = $endOfMonth->copy()->addDay();
                $shiftEndOnNextDay = $nextDay->copy()->setTime(
                    $timeTo->hour,
                    $timeTo->minute,
                    $timeTo->second
                );
                $extendedEndDate = $shiftEndOnNextDay->copy()->addHours(5);
            }
        }

        $attendanceRecords = DeviceAttendance::where('punch_code', $this->punchCode)
            ->whereBetween('punch_time', [$startOfMonth, $extendedEndDate])
            ->orderBy('punch_time', 'desc')
            ->get();

        // Process attendance data
        $this->attendanceData = $this->processAttendanceData($attendanceRecords);
        
        // Calculate statistics
        $this->attendanceStats = $this->calculateAttendanceStats($attendanceRecords, $this->attendanceData);
    }

    private function processAttendanceData($records)
    {
        // Determine which month to process
        $targetMonth = $this->selectedMonth ?: Carbon::now()->format('Y-m');
        $startOfMonth = Carbon::createFromFormat('Y-m', $targetMonth)->startOfMonth();
        $endOfMonth = Carbon::createFromFormat('Y-m', $targetMonth)->endOfMonth();
        
        $processedData = [];
        
        // Group records by date with grace period logic for non-overnight shifts
        $groupedRecords = [];
        
        // First, determine shift characteristics if available
        $isOvernight = false;
        $timeFrom = null;
        $timeTo = null;
        $gracePeriodHours = 5; // 5 hours grace period
        
        if ($this->employeeShift) {
            $timeFromParts = explode(':', $this->employeeShift->time_from);
            $timeToParts = explode(':', $this->employeeShift->time_to);
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
        
        // Group records with grace period logic for non-overnight shifts
        foreach ($records as $record) {
            $punchTime = Carbon::parse($record->punch_time);
            $punchDate = $punchTime->format('Y-m-d');
            
            // For non-overnight shifts, apply grace period logic
            if (!$isOvernight && $this->employeeShift && $timeFrom && $timeTo) {
                // Check if this is a check-out on the next calendar day (after midnight)
                if ($record->device_type === 'OUT') {
                    // Check if punch time is in the early morning (next calendar day)
                    // For non-overnight shifts, check-outs after midnight belong to previous day if within grace period
                    if ($punchTime->hour < 12) {
                        // Get the previous day's date
                        $previousDate = $punchTime->copy()->subDay()->format('Y-m-d');
                        
                        // Calculate the shift end time on the previous day
                        $previousDayShiftEnd = Carbon::parse($previousDate)->setTime(
                            $timeTo->hour,
                            $timeTo->minute,
                            $timeTo->second
                        );
                        
                        // Calculate cutoff: shift end + 5 hours
                        // This gives us the maximum time (on the next day) that still belongs to previous day
                        $checkOutCutoff = $previousDayShiftEnd->copy()->addHours($gracePeriodHours);
                        
                        // If punch time is within grace period (before or equal to cutoff), attribute to previous day
                        // Example: Shift ends 11:30 PM Oct 31, cutoff is 4:30 AM Nov 1
                        // Check-out at 12:22 AM Nov 1 is before 4:30 AM, so it belongs to Oct 31
                        if ($punchTime->lte($checkOutCutoff)) {
                            $groupedRecords[$previousDate][] = $record;
                            continue;
                        }
                    }
                }
                
                // Check if this is a check-in on the next calendar day that still belongs to previous day (after midnight)
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
                    
                    // Calculate cutoff: shift start - 5 hours
                    $checkInCutoff = $currentDayShiftStart->copy()->subHours($gracePeriodHours);
                    
                    // If punch time is before shift start but within grace period, attribute to current day
                    if ($punchTime->lt($currentDayShiftStart) && $punchTime->gte($checkInCutoff)) {
                        $groupedRecords[$punchDate][] = $record;
                        continue;
                    }
                }
            }
            
            // Default: group by punch date
            $groupedRecords[$punchDate][] = $record;
        }
        
        // Process ALL days of the month (including weekends and absent days)
        $current = $startOfMonth->copy();
        $today = Carbon::now();
        
        // For current month, only show days up to today
        // For previous months, show all days
        $endDate = ($targetMonth === $today->format('Y-m')) ? $today : $endOfMonth;
        
        while ($current->lte($endDate)) {
            $date = $current->format('Y-m-d');
            $dayRecords = $groupedRecords[$date] ?? [];
            
            // Determine shift characteristics once for this day
            $isOvernight = false;
            $shiftStartsInPM = false;
            $timeFrom = null;
            $timeTo = null;
            $expectedCheckOutTime = null;
            
            if ($this->employeeShift) {
                $timeFromParts = explode(':', $this->employeeShift->time_from);
                $timeToParts = explode(':', $this->employeeShift->time_to);
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
            if ($this->employeeShift && !empty($dayRecords)) {
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
            
            // Determine day status
            $status = 'absent'; // Default
            if ($current->isWeekend()) {
                $status = 'off';
            } elseif ($hasValidAttendance) {
                $status = 'present';
            }
            
            $processedData[$date] = [
                'date' => $date,
                'formatted_date' => $current->format('M d, Y'),
                'day_name' => $current->format('l'),
                'check_in' => null,
                'check_out' => null,
                'total_hours' => null,
                'breaks' => '0 (0h 0m total)',
                'status' => $status,
                'shift_name' => $this->employeeShift ? $this->employeeShift->shift_name : null,
                'expected_check_in' => null,
                'expected_check_out' => null,
                'is_late' => false,
                'late_minutes' => 0,
                'is_early' => false,
                'early_minutes' => 0,
                'actual_hours' => null,
                'expected_hours' => null,
                'has_manual_entries' => false,
            ];
            
            // Process attendance records for this day if they exist
            if (!empty($dayRecords)) {
                // Sort records by punch_time to get chronological order
                usort($dayRecords, function($a, $b) {
                    return Carbon::parse($a->punch_time)->timestamp - Carbon::parse($b->punch_time)->timestamp;
                });
                
                // Filter records based on shift type (shift characteristics already calculated above)
                $validCheckIns = [];
                $validCheckOuts = [];
                
                if ($isOvernight && $shiftStartsInPM) {
                    // For PM-start overnight shifts:
                    // - Only include PM check-ins (AM check-ins belong to previous day)
                    // - Only include AM check-outs that are before shift end time (these belong to previous day's shift)
                    // - Exclude AM check-outs on current day (these belong to previous shift)
                    
                    $expectedCheckInTime = Carbon::parse($date)->setTime(
                        $timeFrom->hour,
                        $timeFrom->minute,
                        $timeFrom->second
                    );
                    
                    // For check-ins: only PM check-ins count for this day
                    foreach ($dayRecords as $record) {
                        if ($record->device_type === 'IN') {
                            $checkInTime = Carbon::parse($record->punch_time);
                            // Only include check-ins that are PM (12 PM or later) - these are the shift start
                            if ($checkInTime->hour >= 12) {
                                $validCheckIns[] = $checkInTime;
                            }
                            // AM check-ins are ignored (they belong to previous day)
                        } elseif ($record->device_type === 'OUT') {
                            $checkOutTime = Carbon::parse($record->punch_time);
                            // Exclude AM check-outs on current day (they belong to previous shift)
                            // Only include PM check-outs (these could be overtime or same-day)
                            if ($checkOutTime->hour >= 12) {
                                $validCheckOuts[] = $checkOutTime;
                            }
                        }
                    }
                    
                    // Get next day's AM check-ins and check-outs that belong to this shift
                    $nextDate = $current->copy()->addDay()->format('Y-m-d');
                    $nextDayRecords = $groupedRecords[$nextDate] ?? [];
                    
                    // Special handling for last day of month: also check original records
                    // This ensures we catch next month's records that are in the query but might not be grouped yet
                    $isLastDayOfMonth = $current->format('Y-m-d') === $endOfMonth->format('Y-m-d');
                    if ($isLastDayOfMonth && empty($nextDayRecords)) {
                        // For last day of month, check original records array for next day's records
                        foreach ($records as $record) {
                            $recordDate = Carbon::parse($record->punch_time)->format('Y-m-d');
                            if ($recordDate === $nextDate) {
                                $recordTime = Carbon::parse($record->punch_time);
                                
                                if ($record->device_type === 'IN') {
                                    // Include AM check-ins (before 12 PM) on next day
                                    if ($recordTime->hour < 12) {
                                        $validCheckIns[] = $recordTime;
                                    }
                                } elseif ($record->device_type === 'OUT') {
                                    // Calculate shift end time on next day + 5 hours grace period
                                    $shiftEndOnNextDay = Carbon::parse($nextDate)->setTime(
                                        $timeTo->hour,
                                        $timeTo->minute,
                                        $timeTo->second
                                    );
                                    $checkOutCutoff = $shiftEndOnNextDay->copy()->addHours(5);
                                    
                                    // Include AM check-outs within grace period (shift end + 5 hours)
                                    if ($recordTime->hour < 12 && $recordTime->lte($checkOutCutoff)) {
                                        $validCheckOuts[] = $recordTime;
                                    }
                                }
                            }
                        }
                    } else {
                        // For regular days, use grouped records
                        foreach ($nextDayRecords as $record) {
                            $recordTime = Carbon::parse($record->punch_time);
                            
                            if ($record->device_type === 'IN') {
                                // For PM-start overnight shifts, include AM check-ins (before 12 PM) on next day
                                // These are check-ins that belong to the previous day's shift (e.g., break end)
                                if ($recordTime->hour < 12) {
                                    $validCheckIns[] = $recordTime;
                                }
                            } elseif ($record->device_type === 'OUT') {
                                $checkOutTime = Carbon::parse($record->punch_time);
                                $shiftEndOnNextDay = Carbon::parse($nextDate)->setTime(
                                    $timeTo->hour,
                                    $timeTo->minute,
                                    $timeTo->second
                                );
                                $checkOutCutoff = $shiftEndOnNextDay->copy()->addHours($gracePeriodHours);

                                // Only include check-outs that fall within the grace window (shift end + 5 hours)
                                // This prevents assigning next-shift activity (e.g., afternoon punches) to the previous day
                                if ($checkOutTime->lte($checkOutCutoff)) {
                                    $validCheckOuts[] = $checkOutTime;
                                }
                            }
                        }
                    }
                } else {
                    // For regular shifts or AM-start overnight shifts, use all records
                    foreach ($dayRecords as $record) {
                        if ($record->device_type === 'IN') {
                            $validCheckIns[] = Carbon::parse($record->punch_time);
                        } elseif ($record->device_type === 'OUT') {
                            $validCheckOuts[] = Carbon::parse($record->punch_time);
                        }
                    }
                    
                    // Note: For non-overnight shifts, next day's check-outs within grace period
                    // are already grouped into the current day's records by the grouping logic above.
                    // So we don't need to check next day's records here - they're already in $dayRecords.
                    
                    // For AM-start overnight shifts, get next day's check-outs
                    if ($isOvernight && !$shiftStartsInPM) {
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
                    }
                }
                
                // For off days with PM-start shifts: if only AM check-out exists, don't show it
                if ($current->isWeekend() && $isOvernight && $shiftStartsInPM && empty($validCheckIns)) {
                    // This is an off day and the only records are AM check-outs from previous shift
                    // Don't show them - they belong to the previous day
                    $validCheckOuts = [];
                }
                
                // Get first check-in and last check-out
                usort($validCheckIns, function($a, $b) {
                    return $a->timestamp - $b->timestamp;
                });
                usort($validCheckOuts, function($a, $b) {
                    return $a->timestamp - $b->timestamp;
                });
                
                $firstCheckIn = !empty($validCheckIns) ? $validCheckIns[0] : null;
                $lastCheckOut = !empty($validCheckOuts) ? end($validCheckOuts) : null;
                
                // Update status: only mark as present if there's a valid check-in for PM-start shifts
                if ($isOvernight && $shiftStartsInPM && empty($validCheckIns) && !empty($validCheckOuts)) {
                    // Only AM check-out exists (belongs to previous day), don't mark as present
                    $firstCheckIn = null;
                    $lastCheckOut = null;
                    if ($current->isWeekend()) {
                        $status = 'off';
                    } else {
                        $status = 'absent';
                    }
                    $processedData[$date]['status'] = $status;
                }
                
                $processedData[$date]['check_in'] = $firstCheckIn ? $firstCheckIn->format('h:i:s A') : null;
                $processedData[$date]['check_out'] = $lastCheckOut ? $lastCheckOut->format('h:i:s A') : null;
                
                // Validate against shift and calculate late/early if shift exists
                if ($this->employeeShift && $firstCheckIn) {
                    $shiftValidation = $this->validateShiftAttendance($current, $firstCheckIn, $lastCheckOut);
                    $processedData[$date]['expected_check_in'] = $shiftValidation['expected_check_in'];
                    $processedData[$date]['expected_check_out'] = $shiftValidation['expected_check_out'];
                    $processedData[$date]['is_late'] = $shiftValidation['is_late'];
                    $processedData[$date]['late_minutes'] = $shiftValidation['late_minutes'];
                    $processedData[$date]['is_early'] = $shiftValidation['is_early'];
                    $processedData[$date]['early_minutes'] = $shiftValidation['early_minutes'];
                    $processedData[$date]['expected_hours'] = $shiftValidation['expected_hours'];
                    
                    // Update status to include late/early information
                    if ($processedData[$date]['status'] === 'present') {
                        if ($processedData[$date]['is_late'] && $processedData[$date]['is_early']) {
                            $processedData[$date]['status'] = 'present_late_early';
                        } elseif ($processedData[$date]['is_late']) {
                            $processedData[$date]['status'] = 'present_late';
                        } elseif ($processedData[$date]['is_early']) {
                            $processedData[$date]['status'] = 'present_early';
                        }
                    }
                }
                
                // Calculate total hours and breaks using the correct logic
                $dayTotalMinutes = 0;
                $breaksCount = 0;
                $totalBreakMinutes = 0;
                
                // For overnight shifts, merge next day's check-out records into current day's records for calculation
                // Use the same filtering logic as above for consistency
                $recordsForCalculation = [];
                if ($this->employeeShift && $isOvernight && $shiftStartsInPM) {
                    // For PM-start overnight shifts, use the same filtered logic
                    // Include PM check-ins and PM check-outs from current day, plus next-day AM check-outs
                    foreach ($dayRecords as $record) {
                        $recordTime = Carbon::parse($record->punch_time);
                        // Include PM check-ins (12 PM or later) - these are the shift start
                        if ($record->device_type === 'IN' && $recordTime->hour >= 12) {
                            $recordsForCalculation[] = $record;
                        }
                        // Include PM check-outs on current day - these are the shift end (or overtime)
                        if ($record->device_type === 'OUT' && $recordTime->hour >= 12) {
                            $recordsForCalculation[] = $record;
                        }
                    }
                    
                    // Get next day's AM check-ins and check-outs that belong to this shift
                    $nextDate = $current->copy()->addDay()->format('Y-m-d');
                    $nextDayRecords = $groupedRecords[$nextDate] ?? [];
                    
                    foreach ($nextDayRecords as $record) {
                        $recordTime = Carbon::parse($record->punch_time);
                        
                        if ($record->device_type === 'IN') {
                            // For PM-start overnight shifts, include AM check-ins (before 12 PM) on next day
                            // These are check-ins that belong to the previous day's shift
                            // Example: Shift starts 9 PM Oct 14, check-in at 6:30 AM Oct 15 belongs to Oct 14 shift
                            if ($recordTime->hour < 12) {
                                $recordsForCalculation[] = $record;
                            }
                        } elseif ($record->device_type === 'OUT') {
                            $checkOutTime = Carbon::parse($record->punch_time);
                            $shiftEndOnNextDay = Carbon::parse($nextDate)->setTime(
                                $timeTo->hour,
                                $timeTo->minute,
                                $timeTo->second
                            );
                            $checkOutCutoff = $shiftEndOnNextDay->copy()->addHours($gracePeriodHours);

                            // Only include check-outs within the grace window of shift end
                            if ($checkOutTime->lte($checkOutCutoff)) {
                                $recordsForCalculation[] = $record;
                            }
                        }
                    }
                } elseif ($this->employeeShift && $isOvernight && !$shiftStartsInPM) {
                    // For AM-start overnight shifts
                    $expectedCheckInTime = Carbon::parse($date)->setTime(
                        $timeFrom->hour,
                        $timeFrom->minute,
                        $timeFrom->second
                    );
                    
                    foreach ($dayRecords as $record) {
                        $recordTime = Carbon::parse($record->punch_time);
                        // Include all check-ins, but only include check-outs that are after shift start time
                        if ($record->device_type === 'IN' || 
                            ($record->device_type === 'OUT' && $recordTime->gte($expectedCheckInTime))) {
                            $recordsForCalculation[] = $record;
                        }
                    }
                    
                    // Get next day's check-outs that belong to this shift
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
                
                if (!empty($recordsForCalculation)) {
                    // Check if any records are manual entries
                    $hasManualEntries = false;
                    foreach ($recordsForCalculation as $record) {
                        if (is_object($record) && isset($record->is_manual_entry) && $record->is_manual_entry) {
                            $hasManualEntries = true;
                            break;
                        } elseif (is_array($record) && isset($record['is_manual_entry']) && $record['is_manual_entry']) {
                            $hasManualEntries = true;
                            break;
                        }
                    }
                    $processedData[$date]['has_manual_entries'] = $hasManualEntries;
                    
                    // Convert DeviceAttendance models to arrays with all attributes
                    $recordsArray = collect($recordsForCalculation)->map(function($record) {
                        if (is_object($record) && method_exists($record, 'toArray')) {
                            return $record->toArray();
                        }
                        return $record;
                    })->toArray();
                    
                    $sortedRecords = collect($recordsArray)->sortBy('punch_time');
                    
                    // Deduplicate records before processing
                    $deduplicatedRecords = $this->deduplicateRecords($sortedRecords->toArray());
                    
                    // Convert back to collection for slice operations
                    $deduplicatedCollection = collect($deduplicatedRecords);
                    
                    // Skip first check-in and last check-out, process everything in between
                    $middleRecords = $deduplicatedCollection->slice(1, -1);
                    
                    $currentCheckIn = null;
                    $lastCheckOut = null;
                    
                    foreach ($middleRecords as $record) {
                        $recordTime = Carbon::parse($record['punch_time']);
                        
                        if ($record['device_type'] === 'OUT') {
                            // Store the check-out time
                            $lastCheckOut = $recordTime;
                        } elseif ($record['device_type'] === 'IN' && $lastCheckOut) {
                            // This is a break: check-out → check-in
                            $breakDuration = $lastCheckOut->diffInMinutes($recordTime);
                            if ($breakDuration > 0) {
                                $breaksCount++;
                                $totalBreakMinutes += $breakDuration;
                            }
                            $lastCheckOut = null; // Reset for next break
                        } elseif ($record['device_type'] === 'IN' && !$lastCheckOut) {
                            // This is start of a work session (not a break)
                            $currentCheckIn = $recordTime;
                        }
                    }
                    
                    // Now calculate total working hours by processing all records for work sessions
                    $currentWorkStart = null;
                    $hasMissingPair = false;
                    
                    foreach ($deduplicatedCollection as $record) {
                        $recordTime = Carbon::parse($record['punch_time']);
                        
                        if ($record['device_type'] === 'IN') {
                            // If we already have an IN pending, missing OUT before this
                            if ($currentWorkStart !== null) {
                                $hasMissingPair = true;
                            }
                            $currentWorkStart = $recordTime;
                        } elseif ($record['device_type'] === 'OUT' && $currentWorkStart) {
                            // Valid work session: check-in → check-out
                            $workDuration = $currentWorkStart->diffInMinutes($recordTime);
                            if ($workDuration > 0) {
                                $dayTotalMinutes += $workDuration;
                            }
                            $currentWorkStart = null; // Reset for next session
                        } elseif ($record['device_type'] === 'OUT' && $currentWorkStart === null) {
                            // OUT without a prior IN indicates missing IN
                            $hasMissingPair = true;
                        }
                    }
                    
                    // If we ended with an unmatched IN, missing OUT
                    if ($currentWorkStart !== null) {
                        $hasMissingPair = true;
                    }
                    
                    // Format total hours - show N/A if missing pairs detected
                    if ($hasMissingPair) {
                        $calculatedMinutes = null;

                        if ($this->isBreakTrackingExcluded) {
                            $calculatedMinutes = $this->calculateMinutesFromFirstInToLastOut($deduplicatedCollection);
                        }

                        if ($calculatedMinutes !== null) {
                            $hours = floor($calculatedMinutes / 60);
                            $minutes = $calculatedMinutes % 60;
                            $processedData[$date]['total_hours'] = sprintf('%d:%02d', $hours, $minutes);
                            $processedData[$date]['actual_hours'] = sprintf('%d:%02d', $hours, $minutes);
                        } else {
                            $processedData[$date]['total_hours'] = 'N/A';
                            $processedData[$date]['actual_hours'] = null;
                        }
                    } elseif ($dayTotalMinutes > 0) {
                        $hours = floor($dayTotalMinutes / 60);
                        $minutes = $dayTotalMinutes % 60;
                        $processedData[$date]['total_hours'] = sprintf('%d:%02d', $hours, $minutes);
                        $processedData[$date]['actual_hours'] = sprintf('%d:%02d', $hours, $minutes);
                    }
                    
                    // Format breaks information
                    $breakHours = floor($totalBreakMinutes / 60);
                    $breakMinutes = $totalBreakMinutes % 60;
                    $processedData[$date]['breaks'] = sprintf('%d (%dh %dm total)', $breaksCount, $breakHours, $breakMinutes);
                    
                    // Store individual break details for tooltip
                    $processedData[$date]['break_details'] = $this->getBreakDetails($deduplicatedCollection);
                }
            }
            
            // Move to next day
            $current->addDay();
        }
        
        // Apply sorting based on sortBy and sortDirection
        $this->sortAttendanceData($processedData);
        
        return array_values($processedData);
    }

    /**
     * Validate attendance against employee's shift
     * Handles regular shifts and overnight shifts (where time_from > time_to)
     */
    private function validateShiftAttendance($date, $actualCheckIn, $actualCheckOut = null)
    {
        if (!$this->employeeShift) {
            return [
                'expected_check_in' => null,
                'expected_check_out' => null,
                'is_late' => false,
                'late_minutes' => 0,
                'is_early' => false,
                'early_minutes' => 0,
                'expected_hours' => null,
            ];
        }

        $shift = $this->employeeShift;
        $dateString = $date->format('Y-m-d');
        
        // Parse shift times - handle various formats
        // Use Carbon's parse which is more flexible, then extract time components
        $timeFromStr = $shift->time_from;
        $timeToStr = $shift->time_to;
        
        // Parse using Carbon's flexible parser, then create a Carbon instance for today to get time
        $timeFromParts = explode(':', $timeFromStr);
        $timeToParts = explode(':', $timeToStr);
        
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
        
        // Check if it's an overnight shift (time_from > time_to)
        $isOvernight = $timeFrom->gt($timeTo);
        
        // Calculate expected check-in time for this date
        $expectedCheckIn = Carbon::parse($dateString)->setTime(
            $timeFrom->hour,
            $timeFrom->minute,
            $timeFrom->second
        );
        
        // Calculate expected check-out time
        if ($isOvernight) {
            // For overnight shifts, check-out is on the next day
            $expectedCheckOut = Carbon::parse($dateString)->addDay()->setTime(
                $timeTo->hour,
                $timeTo->minute,
                $timeTo->second
            );
        } else {
            $expectedCheckOut = Carbon::parse($dateString)->setTime(
                $timeTo->hour,
                $timeTo->minute,
                $timeTo->second
            );
        }
        
        // Validate check-in
        $isLate = false;
        $lateMinutes = 0;
        
        if ($actualCheckIn) {
            // Get effective grace period (shift-specific or global, respecting disable flag)
            $gracePeriodLateIn = $this->getEffectiveGracePeriodLateIn();
            $checkInDeadline = $expectedCheckIn->copy()->addMinutes($gracePeriodLateIn);
            
            if ($actualCheckIn->gt($checkInDeadline)) {
                $isLate = true;
                $lateMinutes = $expectedCheckIn->diffInMinutes($actualCheckIn);
            }
        }
        
        // Validate check-out
        $isEarly = false;
        $earlyMinutes = 0;
        
        if ($actualCheckOut) {
            // For overnight shifts, we need to handle the check-out time properly
            // If it's an overnight shift and check-out is earlier than expected check-in on same day,
            // it means the check-out belongs to the next day (previous shift's check-out)
            if ($isOvernight && $actualCheckOut->lt($expectedCheckIn)) {
                // Check-out happened before midnight, so it's on the next day
                $actualCheckOutDate = $actualCheckOut->copy()->addDay();
            } else {
                $actualCheckOutDate = $actualCheckOut->copy();
            }
            
            // Get effective grace period for early check-out
            $gracePeriodEarlyOut = $this->getEffectiveGracePeriodEarlyOut();
            $checkOutDeadline = $expectedCheckOut->copy()->subMinutes($gracePeriodEarlyOut);
            
            if ($actualCheckOutDate->lt($checkOutDeadline)) {
                $isEarly = true;
                $earlyMinutes = $actualCheckOutDate->diffInMinutes($expectedCheckOut);
            }
        }
        
        // Calculate expected shift duration in hours
        $expectedMinutes = $expectedCheckIn->diffInMinutes($expectedCheckOut);
        $expectedHours = floor($expectedMinutes / 60);
        $expectedMins = $expectedMinutes % 60;
        
        return [
            'expected_check_in' => $expectedCheckIn->format('h:i A'),
            'expected_check_out' => $expectedCheckOut->format('h:i A'),
            'is_late' => $isLate,
            'late_minutes' => $lateMinutes,
            'is_early' => $isEarly,
            'early_minutes' => $earlyMinutes,
            'expected_hours' => sprintf('%d:%02d', $expectedHours, $expectedMins),
        ];
    }

    private function getBreakDetails($sortedRecords)
    {
        $breakDetails = [];
        $lastCheckOut = null;
        $lastCheckOutRecord = null; // Track the record for manual entry check
        
        // Convert Collection to array if needed
        $recordsArray = is_array($sortedRecords) ? $sortedRecords : $sortedRecords->toArray();
        
        // Deduplicate records before processing
        $deduplicatedRecords = $this->deduplicateRecords($recordsArray);
        
        // Skip first check-in and last check-out (boundary times)
        $middleRecords = array_slice($deduplicatedRecords, 1, -1);
        
        foreach ($middleRecords as $record) {
            $recordTime = Carbon::parse($record['punch_time']);
            $type = $record['device_type'];
            
            if ($type === 'OUT') {
                // Two OUTs in a row means previous IN missing → close prior as '--'
                if ($lastCheckOut !== null) {
                    $breakDetails[] = [
                        'start' => $lastCheckOut->format('h:i:s A'),
                        'end' => '--',
                        'duration' => '--',
                        'start_manual' => $lastCheckOutRecord && isset($lastCheckOutRecord['is_manual_entry']) && $lastCheckOutRecord['is_manual_entry'] ? true : false,
                    ];
                }
                // Start a new potential break at this OUT
                $lastCheckOut = $recordTime;
                $lastCheckOutRecord = $record; // Store the record
            } elseif ($type === 'IN') {
                $isManualCheckIn = isset($record['is_manual_entry']) && $record['is_manual_entry'] ? true : false;
                
                if ($lastCheckOut) {
                    // Normal complete pair: OUT → IN
                    $breakDuration = $lastCheckOut->diffInMinutes($recordTime);
                    if ($breakDuration > 0) {
                        $breakDetails[] = [
                            'start' => $lastCheckOut->format('h:i:s A'),
                            'end' => $recordTime->format('h:i:s A'),
                            'duration' => $this->formatDuration($breakDuration),
                            'start_manual' => $lastCheckOutRecord && isset($lastCheckOutRecord['is_manual_entry']) && $lastCheckOutRecord['is_manual_entry'] ? true : false,
                            'end_manual' => $isManualCheckIn,
                        ];
                    }
                    $lastCheckOut = null; // Reset for next break
                    $lastCheckOutRecord = null;
                } else {
                    // IN without prior OUT → missing OUT, show '--' → IN
                    $breakDetails[] = [
                        'start' => '--',
                        'end' => $recordTime->format('h:i:s A'),
                        'duration' => '--',
                        'end_manual' => $isManualCheckIn,
                    ];
                }
            }
        }
        
        // If the sequence ended with an OUT and no following IN, show OUT → '--'
        if ($lastCheckOut !== null) {
            $breakDetails[] = [
                'start' => $lastCheckOut->format('h:i:s A'),
                'end' => '--',
                'duration' => '--',
                'start_manual' => $lastCheckOutRecord && isset($lastCheckOutRecord['is_manual_entry']) && $lastCheckOutRecord['is_manual_entry'] ? true : false,
            ];
        }
        
        return $breakDetails;
    }
    
    private function formatDuration($minutes)
    {
        // Round the minutes to the nearest whole number
        $roundedMinutes = round($minutes);
        
        if ($roundedMinutes < 60) {
            return $roundedMinutes . 'm';
        }
        
        $hours = floor($roundedMinutes / 60);
        $remainingMinutes = $roundedMinutes % 60;
        
        if ($remainingMinutes === 0) {
            return $hours . 'h';
        }
        
        return $hours . 'h ' . $remainingMinutes . 'm';
    }

    /**
     * Deduplicate attendance records that occur within 5 seconds of each other
     * For consecutive same-type records, keep the latest one
     */
    private function deduplicateRecords($records)
    {
        if (empty($records)) {
            return $records;
        }

        // Convert Collection to array if needed
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
                // First record, always include
                $deduplicated[] = $record;
                $lastRecord = $record;
                $lastTime = $recordTime;
            } else {
                $lastType = $lastRecord['device_type'];
                $timeDiff = $lastTime->diffInSeconds($recordTime);
                
                // If same type and within 5 seconds, keep the later one (replace)
                if ($recordType === $lastType && $timeDiff <= 10) {
                    // Replace the last record with this one (keep the latest)
                    array_pop($deduplicated);
                    $deduplicated[] = $record;
                    $lastRecord = $record;
                    $lastTime = $recordTime;
                } else {
                    // Different type or more than 5 seconds apart, keep both
                    $deduplicated[] = $record;
                    $lastRecord = $record;
                    $lastTime = $recordTime;
                }
            }
        }
        
        return $deduplicated;
    }

    /**
     * Calculate expected hours based on employee's shift
     */
    private function calculateExpectedHours($workingDays)
    {
        if (!$this->employeeShift) {
            // Default to 8 hours per day if no shift assigned
            return sprintf('%d:%02d', $workingDays * 8, 0);
        }

        $shift = $this->employeeShift;
        
        // Parse shift times - handle various formats
        $timeFromStr = $shift->time_from;
        $timeToStr = $shift->time_to;
        
        // Parse using time components
        $timeFromParts = explode(':', $timeFromStr);
        $timeToParts = explode(':', $timeToStr);
        
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
        
        // Calculate shift duration
        $timeToCopy = $timeTo->copy();
        if ($timeFrom->gt($timeTo)) {
            // Overnight shift - add 24 hours to time_to
            $timeToCopy->addDay();
        }
        
        $shiftMinutes = $timeFrom->diffInMinutes($timeToCopy);
        $totalExpectedMinutes = $workingDays * $shiftMinutes;
        $expectedHours = floor($totalExpectedMinutes / 60);
        $expectedMins = $totalExpectedMinutes % 60;
        
        return sprintf('%d:%02d', $expectedHours, $expectedMins);
    }

    private function calculateAttendanceStats($records, $processedData = null)
    {
        // Use the same month as the records
        $targetMonth = $this->selectedMonth ?: Carbon::now()->format('Y-m');
        $startOfMonth = Carbon::createFromFormat('Y-m', $targetMonth)->startOfMonth();
        $endOfMonth = Carbon::createFromFormat('Y-m', $targetMonth)->endOfMonth();
        
        // Get all working days in the month (excluding weekends)
        $workingDays = 0;
        $current = $startOfMonth->copy();
        
        while ($current->lte($endOfMonth)) {
            if ($current->isWeekday()) {
                $workingDays++;
            }
            $current->addDay();
        }

        // Count unique working days with attendance (excluding weekends)
        $attendedDays = $records->groupBy(function ($record) {
            return Carbon::parse($record->punch_time)->format('Y-m-d');
        })->filter(function ($dayRecords, $date) {
            // Only count working days (exclude weekends)
            return !Carbon::parse($date)->isWeekend();
        })->count();

        // Calculate total working hours using processed attendance data when available
        $totalMinutes = 0;

        $lateDays = 0;

        if (!empty($processedData) && is_array($processedData)) {
            foreach ($processedData as $record) {
                $totalHoursString = $record['total_hours'] ?? null;

                if (!$totalHoursString || strtoupper($totalHoursString) === 'N/A') {
                    continue;
                }

                if (strpos($totalHoursString, ':') === false) {
                    continue;
                }

                [$hoursPart, $minutesPart] = array_pad(explode(':', $totalHoursString), 2, '0');
                $hours = (int) $hoursPart;
                $minutes = (int) $minutesPart;

                $totalMinutes += ($hours * 60) + $minutes;

                $isLate = false;
                if (isset($record['is_late']) && $record['is_late']) {
                    $isLate = true;
                } elseif (!empty($record['status']) && str_contains($record['status'], 'present_late')) {
                    $isLate = true;
                }

                if ($isLate) {
                    $lateDays++;
                }
            }
        } else {
            $groupedRecords = [];

            foreach ($records as $record) {
                $date = Carbon::parse($record->punch_time)->format('Y-m-d');
                $groupedRecords[$date][] = $record;
            }

            foreach ($groupedRecords as $dayRecords) {
                // Sort records by punch_time to get chronological order
                usort($dayRecords, function($a, $b) {
                    return Carbon::parse($a->punch_time)->timestamp - Carbon::parse($b->punch_time)->timestamp;
                });

                $checkInTime = null;

                // Process records chronologically and pair check-ins with check-outs
                foreach ($dayRecords as $record) {
                    if ($record->device_type === 'IN') {
                        $checkInTime = Carbon::parse($record->punch_time);
                    } elseif ($record->device_type === 'OUT' && $checkInTime) {
                        $checkOutTime = Carbon::parse($record->punch_time);
                        if ($checkOutTime->gt($checkInTime)) {
                            $totalMinutes += $checkInTime->diffInMinutes($checkOutTime);
                        }
                        $checkInTime = null; // Reset for next pair
                    }
                }
            }
        }

        $totalHours = floor($totalMinutes / 60);
        $remainingMinutes = $totalMinutes % 60;

        return [
            'working_days' => $workingDays,
            'attended_days' => $attendedDays,
            'absent_days' => $workingDays - $attendedDays,
            'attendance_percentage' => $workingDays > 0 ? round(($attendedDays / $workingDays) * 100, 1) : 0,
            'total_hours' => sprintf('%d:%02d', $totalHours, $remainingMinutes),
            'expected_hours' => $this->calculateExpectedHours($workingDays), // Calculate based on shift
            'late_days' => $lateDays,
        ];
    }

    public function updatedSelectedUserId()
    {
        if (!$this->canSwitchUsers) {
            $this->selectedUserId = null;
            return;
        }

        // Reset month to current month when user changes
        $this->selectedMonth = '';
        // Reload attendance data when user filter changes
        $this->loadUserAttendance();
    }

    public function updatedSelectedMonth()
    {
        // Reload attendance data when month filter changes
        $this->loadUserAttendance();
    }

    public function sort($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
        
        // Re-process attendance data with new sorting
        $this->processAttendanceData();
    }

    private function sortAttendanceData(&$data)
    {
        switch ($this->sortBy) {
            case 'date':
                if ($this->sortDirection === 'asc') {
                    ksort($data);
                } else {
                    krsort($data);
                }
                break;
            case 'day_name':
                uasort($data, function($a, $b) {
                    $dayOrder = ['Monday' => 1, 'Tuesday' => 2, 'Wednesday' => 3, 'Thursday' => 4, 'Friday' => 5, 'Saturday' => 6, 'Sunday' => 7];
                    $aOrder = $dayOrder[$a['day_name']] ?? 8;
                    $bOrder = $dayOrder[$b['day_name']] ?? 8;
                    return $this->sortDirection === 'asc' ? $aOrder - $bOrder : $bOrder - $aOrder;
                });
                break;
            case 'check_in':
                uasort($data, function($a, $b) {
                    $aTime = $a['check_in'] ? strtotime($a['check_in']) : 0;
                    $bTime = $b['check_in'] ? strtotime($b['check_in']) : 0;
                    return $this->sortDirection === 'asc' ? $aTime - $bTime : $bTime - $aTime;
                });
                break;
            case 'check_out':
                uasort($data, function($a, $b) {
                    $aTime = $a['check_out'] ? strtotime($a['check_out']) : 0;
                    $bTime = $b['check_out'] ? strtotime($b['check_out']) : 0;
                    return $this->sortDirection === 'asc' ? $aTime - $bTime : $bTime - $aTime;
                });
                break;
            case 'total_hours':
                uasort($data, function($a, $b) {
                    $aHours = $a['total_hours'] ? strtotime('1970-01-01 ' . $a['total_hours']) : 0;
                    $bHours = $b['total_hours'] ? strtotime('1970-01-01 ' . $b['total_hours']) : 0;
                    return $this->sortDirection === 'asc' ? $aHours - $bHours : $bHours - $aHours;
                });
                break;
            case 'breaks':
                uasort($data, function($a, $b) {
                    // Extract breaks count from the formatted string (e.g., "2 (1h 30m total)" -> 2)
                    preg_match('/^(\d+)/', $a['breaks'], $aMatches);
                    preg_match('/^(\d+)/', $b['breaks'], $bMatches);
                    $aBreaks = isset($aMatches[1]) ? (int)$aMatches[1] : 0;
                    $bBreaks = isset($bMatches[1]) ? (int)$bMatches[1] : 0;
                    return $this->sortDirection === 'asc' ? $aBreaks - $bBreaks : $bBreaks - $aBreaks;
                });
                break;
            case 'status':
                uasort($data, function($a, $b) {
                    $statusOrder = [
                        'present' => 1,
                        'present_late' => 2,
                        'present_early' => 3,
                        'present_late_early' => 4,
                        'off' => 5,
                        'absent' => 6
                    ];
                    $aOrder = $statusOrder[$a['status']] ?? 7;
                    $bOrder = $statusOrder[$b['status']] ?? 7;
                    return $this->sortDirection === 'asc' ? $aOrder - $bOrder : $bOrder - $aOrder;
                });
                break;
            default:
                // Default to date descending
                krsort($data);
                break;
        }
    }

    public function requestLeave($date)
    {
        // Set the selected date and pre-fill the date fields
        $this->selectedDate = $date;
        $this->leaveFrom = $date;
        $this->leaveTo = $date;
        
        // Reset form fields
        $this->leaveType = '';
        $this->leaveDuration = '';
        $this->reason = '';
        $this->leaveDays = '1.00 Working Day';
        
        // Show the modal
        $this->showLeaveRequestModal = true;
    }

    public function requestExcuse($date)
    {
        // Handle excuse request for the specific date
        session()->flash('message', "Excuse request initiated for {$date}");
        
        $this->dispatch('show-toast', [
            'type' => 'info',
            'message' => "Excuse request form opened for {$date}"
        ]);
    }

    public function requestExplanation($date)
    {
        // Handle explanation request for the specific date
        session()->flash('message', "Explanation form opened for {$date}");
        
        $this->dispatch('show-toast', [
            'type' => 'info',
            'message' => "Explanation form opened for {$date}"
        ]);
    }

    public function closeLeaveRequestModal()
    {
        $this->showLeaveRequestModal = false;
        $this->resetLeaveRequestForm();
    }

    public function submitLeaveRequest()
    {
        // Validate the form
        $this->validate([
            'leaveType' => 'required|string',
            'leaveDuration' => 'required|string',
            'reason' => 'required|string|min:10',
        ], [
            'leaveType.required' => 'Please select a leave type.',
            'leaveDuration.required' => 'Please select leave duration.',
            'reason.required' => 'Please provide a reason for the leave request.',
            'reason.min' => 'Reason must be at least 10 characters long.',
        ]);

        // Here you would typically save the leave request to database
        // For now, we'll just show a success message
        
        session()->flash('success', "Leave request submitted successfully for {$this->selectedDate}");
        
        // Close the modal and reset the form
        $this->closeLeaveRequestModal();
        
        // You could also dispatch an event or redirect here
        $this->dispatch('show-toast', [
            'type' => 'success',
            'message' => "Leave request submitted for {$this->selectedDate}"
        ]);
    }

    public function updatedLeaveDuration()
    {
        // Auto-calculate leave days based on duration
        switch ($this->leaveDuration) {
            case 'full_day':
                $this->leaveDays = '1.00 Working Day';
                break;
            case 'half_day_morning':
            case 'half_day_afternoon':
                $this->leaveDays = '0.50 Working Day';
                break;
            default:
                $this->leaveDays = '1.00 Working Day';
        }
    }

    private function resetLeaveRequestForm()
    {
        $this->selectedDate = '';
        $this->leaveType = '';
        $this->leaveDuration = '';
        $this->leaveFrom = '';
        $this->leaveTo = '';
        $this->reason = '';
        $this->leaveDays = '1.00 Working Day';
    }

    public function getFilteredUsersProperty()
    {
        if (empty($this->userSearchTerm)) {
            return $this->availableUsers;
        }
        
        $searchTerm = strtolower($this->userSearchTerm);
        return array_filter($this->availableUsers, function($user) use ($searchTerm) {
            return str_contains(strtolower($user['name']), $searchTerm);
        });
    }

    /**
     * Open missing entry flyout
     */
    public function openMissingEntryFlyout($date)
    {
        if (!Auth::user()?->can('attendance.manage.missing_entries')) {
            abort(403);
        }

        $this->missingEntryDate = $date;
        $this->showMissingEntryFlyout = true;
    }

    /**
     * Close missing entry flyout
     */
    public function closeMissingEntryFlyout()
    {
        $this->showMissingEntryFlyout = false;
        $this->resetMissingEntryForm();
    }

    /**
     * Reset missing entry form
     */
    private function resetMissingEntryForm()
    {
        $this->missingEntryDate = '';
        $this->missingEntryType = '';
        $this->missingEntryTime = '';
        $this->missingEntryNotes = '';
        $this->dateAdjusted = false;
    }

    /**
     * Check if employee has PM-start overnight shift
     */
    private function isPMStartOvernightShift()
    {
        if (!$this->employeeShift) {
            return false;
        }

        $timeFromParts = explode(':', $this->employeeShift->time_from);
        $timeToParts = explode(':', $this->employeeShift->time_to);
        
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
        
        // Check if it's overnight (time_from > time_to) and starts in PM (hour >= 12)
        return $timeFrom->gt($timeTo) && $timeFrom->hour >= 12;
    }

    /**
     * Watch for time changes and adjust date if needed for PM-start overnight shifts
     */
    public function updatedMissingEntryTime()
    {
        if (!$this->missingEntryTime || !$this->missingEntryDate) {
            $this->dateAdjusted = false;
            return;
        }

        // Check if shift is PM-start overnight
        if (!$this->isPMStartOvernightShift()) {
            $this->dateAdjusted = false;
            return;
        }

        // Parse the time to check if it's AM (hour < 12)
        $timeParts = explode(':', $this->missingEntryTime);
        $hour = (int)($timeParts[0] ?? 0);

        // If time is AM (hour < 12), adjust date forward by 1 day
        if ($hour < 12) {
            $originalDate = Carbon::parse($this->missingEntryDate);
            $adjustedDate = $originalDate->copy()->addDay();
            $this->missingEntryDate = $adjustedDate->format('Y-m-d');
            $this->dateAdjusted = true;
        } else {
            $this->dateAdjusted = false;
        }
    }

    /**
     * Save missing entry
     */
    public function saveMissingEntry()
    {
        if (!Auth::user()?->can('attendance.manage.missing_entries')) {
            abort(403);
        }

        $this->validate([
            'missingEntryDate' => 'required|date',
            'missingEntryType' => 'required|in:IN,OUT',
            'missingEntryTime' => 'required',
            'missingEntryNotes' => 'nullable|string|max:500',
        ], [
            'missingEntryDate.required' => 'Date is required.',
            'missingEntryType.required' => 'Entry type is required.',
            'missingEntryType.in' => 'Entry type must be Check-in or Check-out.',
            'missingEntryTime.required' => 'Time is required.',
        ]);

        if (!$this->punchCode) {
            session()->flash('error', 'Punch code not found. Please contact HR.');
            return;
        }

        try {
            // Combine date and time
            // Carbon::parse can handle both H:i and H:i:s formats automatically
            $dateTime = Carbon::parse($this->missingEntryDate . ' ' . $this->missingEntryTime);

            // Create manual entry
            // Note: punch_time has a unique constraint, so we need to handle potential conflicts
            try {
                DeviceAttendance::create([
                    'punch_code' => $this->punchCode,
                    'device_ip' => '0.0.0.0', // Manual entry indicator
                    'device_type' => $this->missingEntryType,
                    'punch_time' => $dateTime,
                    'punch_type' => $this->missingEntryType === 'IN' ? 'check_in' : 'check_out',
                    'status' => null,
                    'verify_mode' => null,
                    'is_processed' => false,
                    'is_manual_entry' => true,
                    'updated_by' => Auth::id(),
                    'notes' => $this->missingEntryNotes,
                ]);
            } catch (\Illuminate\Database\QueryException $e) {
                // Handle unique constraint violation
                if ($e->getCode() === '23000' || str_contains($e->getMessage(), 'Duplicate entry')) {
                    session()->flash('error', 'An entry already exists for this exact date and time. Please choose a different time.');
                    return;
                }
                throw $e; // Re-throw if it's a different error
            }

            session()->flash('success', 'Missing entry added successfully.');
            
            // Close flyout and reload attendance
            $this->closeMissingEntryFlyout();
            $this->loadUserAttendance();
            
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to add missing entry: ' . $e->getMessage());
        }
    }

    /**
     * Open view changes flyout
     */
    public function openViewChangesFlyout($date)
    {
        $this->viewChangesDate = $date;
        
        if (!$this->punchCode) {
            session()->flash('error', 'Punch code not found.');
            return;
        }
        
        // Get all manual entries for this date
        $startOfDay = Carbon::parse($date)->startOfDay();
        $endOfDay = Carbon::parse($date)->endOfDay();
        
        // For PM-start overnight shifts, we need to also check the next day for AM entries
        // that were date-adjusted when created (AM times get adjusted to next day)
        $query = DeviceAttendance::where('punch_code', $this->punchCode)
            ->where('is_manual_entry', true);
        
        // Check if employee has PM-start overnight shift
        if ($this->isPMStartOvernightShift()) {
            // For PM-start overnight shifts, check:
            // - Current day (for all entries on current day)
            // - Next day AM (for AM entries that were date-adjusted and belong to current day's shift)
            $nextDayStart = $endOfDay->copy()->addDay()->startOfDay();
            
            $query->where(function($q) use ($startOfDay, $endOfDay, $nextDayStart) {
                // Current day (all entries)
                $q->whereBetween('punch_time', [$startOfDay, $endOfDay])
                  // Next day AM entries (before 12 PM) that belong to current day's shift
                  ->orWhere(function($q2) use ($nextDayStart) {
                      $q2->whereBetween('punch_time', [$nextDayStart, $nextDayStart->copy()->setTime(11, 59, 59)]);
                  });
            });
        } else {
            // For non-overnight shifts, just check the current day
            $query->whereBetween('punch_time', [$startOfDay, $endOfDay]);
        }
        
        $this->manualEntries = $query->with('updatedBy:id,name')
            ->orderBy('punch_time', 'asc')
            ->get()
            ->map(function($entry) {
                return [
                    'id' => $entry->id,
                    'type' => $entry->device_type,
                    'type_label' => $entry->device_type === 'IN' ? 'Check-in' : 'Check-out',
                    'time' => Carbon::parse($entry->punch_time)->format('h:i:s A'),
                    'date_time' => Carbon::parse($entry->punch_time)->format('M d, Y h:i:s A'),
                    'created_at' => $entry->created_at ? $entry->created_at->format('M d, Y h:i:s A') : null,
                    'updated_by' => $entry->updatedBy ? $entry->updatedBy->name : 'Unknown',
                    'notes' => $entry->notes,
                ];
            })
            ->toArray();
        
        $this->showViewChangesFlyout = true;
    }

    /**
     * Close view changes flyout
     */
    public function closeViewChangesFlyout()
    {
        $this->showViewChangesFlyout = false;
        $this->viewChangesDate = '';
        $this->manualEntries = [];
    }

    /**
     * Delete manual entry
     */
    public function deleteManualEntry($entryId)
    {
        try {
            $entry = DeviceAttendance::find($entryId);
            
            if (!$entry) {
                session()->flash('error', 'Manual entry not found.');
                return;
            }
            
            // Verify it's a manual entry
            if (!$entry->is_manual_entry) {
                session()->flash('error', 'Only manual entries can be deleted.');
                return;
            }
            
            // Verify it belongs to the current user's punch code
            if ($entry->punch_code !== $this->punchCode) {
                session()->flash('error', 'You do not have permission to delete this entry.');
                return;
            }
            
            $entry->delete();
            
            session()->flash('success', 'Manual entry deleted successfully.');
            
            // Reload manual entries for the current date
            if ($this->viewChangesDate) {
                $this->openViewChangesFlyout($this->viewChangesDate);
            }
            
            // Reload attendance data
            $this->loadUserAttendance();
            
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to delete manual entry: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $user = Auth::user();
        $this->canSwitchUsers = $user
            ? ($user->can('attendance.manage.switch_user') || $user->hasRole('Super Admin'))
            : false;

        $this->canViewOtherUsers = $user
            ? ($this->canSwitchUsers || $user->can('attendance.view.team') || $user->can('attendance.view.company'))
            : false;

        if ($this->canViewOtherUsers && empty($this->availableUsers)) {
            $this->loadAvailableUsers();
        } elseif (!$this->canViewOtherUsers) {
            $this->availableUsers = [];
            $this->selectedUserId = null;
        }

        return view('livewire.attendance.index', [
            'canViewOtherUsers' => $this->canViewOtherUsers,
            'canSwitchUsers' => $this->canSwitchUsers,
        ])
            ->layout('components.layouts.app');
    }
}