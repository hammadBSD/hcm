<?php

namespace App\Livewire\Attendance;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Database\QueryException;
use App\Models\AttendanceBreakExclusion;
use App\Models\DeviceAttendance;
use App\Models\Employee;
use App\Models\EmployeeLeaveBalance;
use App\Models\ExemptionDay;
use App\Models\LeaveRequest as LeaveRequestModel;
use App\Models\LeaveRequestEvent;
use App\Models\LeaveSetting;
use App\Models\User;
use App\Models\Shift;
use App\Models\Constant;
use App\Models\AttendanceBreakSetting;
use App\Models\LeaveType;
use App\Models\Holiday;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class Report extends Component
{
    use AuthorizesRequests;
    public $currentMonth = '';
    public $selectedMonth = '';
    public $attendanceData = [];
    public $attendanceStats = [];
    public $employeesStats = []; // Array of stats for all employees
    public $punchCode = null;
    public $employee = null;
    public $employeeShift = null;
    public $availableMonths = [];
    public $selectedUserId = null;
    public $availableUsers = [];
    public $userSearchTerm = '';
    public $employeeSearchTerm = '';
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
    public array $leaveSummary = [];
    public array $leaveBalances = [];
    public $leaveTypeOptions = [];
    public bool $leaveBalanceDepleted = false;
    
    // Missing Entry Flyout Properties
    public $showMissingEntryFlyout = false;
    public $missingEntryDate = '';
    public $missingEntryType = ''; // 'IN', 'OUT', 'edit_checkin_checkout', 'edit_checkin_checkout_exclude_breaks'
    public $missingEntryTime = '';
    public $missingEntryNotes = '';
    public $dateAdjusted = false; // Flag to show if date was auto-adjusted
    
    // Edit Checkin/Checkout Properties
    public $missingEntryDateFrom = '';
    public $missingEntryDateTo = '';
    public $missingEntryCheckinTime = '';
    public $missingEntryCheckoutTime = '';
    
    // View Changes Flyout Properties
    public $showViewChangesFlyout = false;
    public $viewChangesDate = '';
    public $manualEntries = [];

    public bool $isBreakTrackingExcluded = false;
    public bool $showBreaksInGrid = true;
    public $allowedBreakTime = null; // Allowed break time in minutes

    public function mount()
    {
        // Authorize access to attendance report
        $user = Auth::user();
        if (!$user) {
            abort(403, 'Unauthorized');
        }
        
        // Check if user has permission to view attendance report
        if (!$user->can('attendance.view.report') && !$user->hasRole('Super Admin')) {
            abort(403, 'You do not have permission to view the attendance report.');
        }
        
        $this->currentMonth = Carbon::now()->format('F Y');
        $this->selectedMonth = ''; // Default to current month
        $this->loadGlobalGracePeriods();
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

        $this->loadAllEmployeesAttendance();
    }
    
    /**
     * Load attendance for all active employees
     */
    public function loadAllEmployeesAttendance()
    {
        // Load break settings (global settings, not per-employee)
        $breakSettings = AttendanceBreakSetting::current();
        $this->showBreaksInGrid = $breakSettings->show_in_attendance_grid;
        $this->allowedBreakTime = $breakSettings->allowed_break_time; // Load allowed break time
        
        // Get all active employees with punch codes
        $employees = Employee::whereNotNull('punch_code')
            ->where('status', 'active')
            ->with(['shift', 'department.shift', 'user.roles'])
            ->get();
        
        $this->employeesStats = [];
        
        // Determine which month to load
        $targetMonth = $this->selectedMonth ?: Carbon::now()->format('Y-m');
        $startOfMonth = Carbon::createFromFormat('Y-m', $targetMonth)->startOfMonth();
        $endOfMonth = Carbon::createFromFormat('Y-m', $targetMonth)->endOfMonth();
        
        foreach ($employees as $employee) {
            // Temporarily set employee properties for processing
            $originalEmployee = $this->employee;
            $originalPunchCode = $this->punchCode;
            $originalEmployeeShift = $this->employeeShift;
            $originalIsBreakTrackingExcluded = $this->isBreakTrackingExcluded;
            
            $this->employee = $employee;
            $this->punchCode = $employee->punch_code;
            $this->employeeShift = $employee->getEffectiveShift();
            
            // Debug: Log if shift is found (can be removed later)
            if (!$this->employeeShift && $this->employee->department_id) {
                // Try to ensure department relationship is loaded
                $this->employee->load('department.shift');
                $this->employeeShift = $this->employee->getEffectiveShift();
            }
            
            $this->isBreakTrackingExcluded = $this->determineBreakExclusionStatus();
            
            if (!$this->punchCode) {
                // Restore original values before continuing
                $this->employee = $originalEmployee;
                $this->punchCode = $originalPunchCode;
                $this->employeeShift = $originalEmployeeShift;
                $this->isBreakTrackingExcluded = $originalIsBreakTrackingExcluded;
                continue;
            }
            
            // Extend the end date to include next day's records that might belong to the last day
            $extendedEndDate = $endOfMonth->copy()->addHours(5);
            
            // For overnight shifts, calculate proper extension
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
                    $nextDay = $endOfMonth->copy()->addDay();
                    $shiftEndOnNextDay = $nextDay->copy()->setTime(
                        $timeTo->hour,
                        $timeTo->minute,
                        $timeTo->second
                    );
                    $extendedEndDate = $shiftEndOnNextDay->copy()->addHours(5);
                }
            }
            
            // Get attendance records for this employee (exclude verify_mode = 2)
            $attendanceRecords = DeviceAttendance::where('punch_code', $this->punchCode)
                ->whereBetween('punch_time', [$startOfMonth, $extendedEndDate])
                ->where(function($query) {
                    $query->whereNull('verify_mode')
                          ->orWhere('verify_mode', '!=', 2);
                })
                ->orderBy('punch_time', 'desc')
                ->get();
            
            // Clear attendanceData to avoid any stale data
            $this->attendanceData = [];
            
            // Process attendance data
            $attendanceData = $this->processAttendanceData($attendanceRecords);
            
            // Temporarily set attendanceData for enrichment (some methods might use $this->attendanceData)
            $this->attendanceData = $attendanceData;
            
            // Enrich with leave requests (this modifies $this->attendanceData)
            $this->enrichAttendanceDataWithLeaveRequests();
            
            // Get the enriched data back
            $attendanceData = $this->attendanceData;
            
            // Calculate statistics
            $stats = $this->calculateAttendanceStats($attendanceRecords, $attendanceData);
            
            // Store employee stats
            $this->employeesStats[] = [
                'employee' => $employee,
                'stats' => $stats,
            ];
            
            // Restore original values
            $this->employee = $originalEmployee;
            $this->punchCode = $originalPunchCode;
            $this->employeeShift = $originalEmployeeShift;
            $this->isBreakTrackingExcluded = $originalIsBreakTrackingExcluded;
        }
        
        // Set the first employee for backward compatibility (if needed)
        if (!empty($this->employeesStats)) {
            $firstEmployeeData = $this->employeesStats[0];
            $this->employee = $firstEmployeeData['employee'];
            $this->punchCode = $this->employee->punch_code;
            $this->attendanceStats = $firstEmployeeData['stats'];
        }
        
        // Load available months from all employees
        $this->loadAvailableMonths();
    }
    
    /**
     * Enrich attendance data with leave requests for a specific employee
     */
    private function enrichAttendanceDataWithLeaveRequestsForEmployee($employee, &$attendanceData): void
    {
        if (!$employee) {
            return;
        }

        // Determine which month to process
        $targetMonth = $this->selectedMonth ?: Carbon::now()->format('Y-m');
        $startOfMonth = Carbon::createFromFormat('Y-m', $targetMonth)->startOfMonth();
        $endOfMonth = Carbon::createFromFormat('Y-m', $targetMonth)->endOfMonth();

        // Load leave requests for this employee in the target month
        $leaveRequests = LeaveRequestModel::where('employee_id', $employee->id)
            ->where(function ($query) use ($startOfMonth, $endOfMonth) {
                $query->whereBetween('start_date', [$startOfMonth, $endOfMonth])
                    ->orWhereBetween('end_date', [$startOfMonth, $endOfMonth])
                    ->orWhere(function ($q) use ($startOfMonth, $endOfMonth) {
                        $q->where('start_date', '<=', $startOfMonth)
                          ->where('end_date', '>=', $endOfMonth);
                    });
            })
            ->with('leaveType')
            ->get();

        // Create a map of date => leave request
        $leaveRequestMap = [];
        foreach ($leaveRequests as $request) {
            $start = Carbon::parse($request->start_date);
            $end = Carbon::parse($request->end_date);
            $current = $start->copy();
            
            while ($current->lte($end)) {
                $dateKey = $current->format('Y-m-d');
                if (!isset($leaveRequestMap[$dateKey])) {
                    $leaveRequestMap[$dateKey] = $request;
                }
                $current->addDay();
            }
        }

        // Add leave request info to attendance data and adjust attendance status
        foreach ($attendanceData as $key => $record) {
            $dateKey = $record['date'] ?? null;
            if ($dateKey && isset($leaveRequestMap[$dateKey])) {
                $leaveRequest = $leaveRequestMap[$dateKey];
                $attendanceData[$key]['leave_request'] = [
                    'id' => $leaveRequest->id,
                    'status' => $leaveRequest->status,
                    'leave_type' => $leaveRequest->leaveType->name ?? __('Unknown'),
                    'total_days' => $leaveRequest->total_days,
                    'duration' => $leaveRequest->duration, // Store duration for half-day calculation
                ];
                
                // If leave is approved, mark as on_leave
                if ($leaveRequest->status === 'approved') {
                    $currentStatus = $attendanceData[$key]['status'] ?? 'absent';
                    if ($currentStatus === 'absent') {
                        $attendanceData[$key]['status'] = 'on_leave';
                        unset($attendanceData[$key]['is_late']);
                        unset($attendanceData[$key]['is_early']);
                    }
                }
            }
        }
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
    private function getEffectiveGracePeriodLateIn($shift = null)
    {
        $shift = $shift ?? $this->employeeShift;
        
        if (!$shift) {
            return $this->globalGracePeriodLateIn;
        }
        
        // If grace period is completely disabled for this shift, return 0
        if ($shift->disable_grace_period) {
            return 0;
        }
        
        // Return shift-specific if set, otherwise global
        return $shift->grace_period_late_in !== null 
            ? $shift->grace_period_late_in 
            : $this->globalGracePeriodLateIn;
    }
    
    /**
     * Get effective grace period for early check-out
     * Returns shift-specific if set, otherwise global, but respects disable flag
     */
    private function getEffectiveGracePeriodEarlyOut($shift = null)
    {
        $shift = $shift ?? $this->employeeShift;
        
        if (!$shift) {
            return $this->globalGracePeriodEarlyOut;
        }
        
        // If grace period is completely disabled for this shift, return 0
        if ($shift->disable_grace_period) {
            return 0;
        }
        
        // Return shift-specific if set, otherwise global
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
        $currentMonth = Carbon::now()->format('Y-m');

        // Get all active employees with punch codes
        $punchCodes = Employee::whereNotNull('punch_code')
            ->where('status', 'active')
            ->pluck('punch_code')
            ->filter()
            ->toArray();

        if (empty($punchCodes)) {
            $this->availableMonths = [];
            return;
        }

        // Get all months that have attendance data from all active employees, excluding current month
        $months = DeviceAttendance::whereIn('punch_code', $punchCodes)
            ->where(function($query) {
                $query->whereNull('verify_mode')
                      ->orWhere('verify_mode', '!=', 2);
            })
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
        $breakSettings = AttendanceBreakSetting::current();
        $this->showBreaksInGrid = $breakSettings->show_in_attendance_grid;
        $this->allowedBreakTime = $breakSettings->allowed_break_time; // Load allowed break time

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
            ->where(function($query) {
                $query->whereNull('verify_mode')
                      ->orWhere('verify_mode', '!=', 2);
            })
            ->orderBy('punch_time', 'desc')
            ->get();

        // Process attendance data
        $this->attendanceData = $this->processAttendanceData($attendanceRecords);
        
        // Enrich attendance data with leave requests
        $this->enrichAttendanceDataWithLeaveRequests();
        
        // Calculate statistics
        $this->attendanceStats = $this->calculateAttendanceStats($attendanceRecords, $this->attendanceData);
    }

    private function enrichAttendanceDataWithLeaveRequests(): void
    {
        if (!$this->employee) {
            return;
        }

        // Determine which month to process
        $targetMonth = $this->selectedMonth ?: Carbon::now()->format('Y-m');
        $startOfMonth = Carbon::createFromFormat('Y-m', $targetMonth)->startOfMonth();
        $endOfMonth = Carbon::createFromFormat('Y-m', $targetMonth)->endOfMonth();

        // Load leave requests for this employee in the target month
        $leaveRequests = LeaveRequestModel::where('employee_id', $this->employee->id)
            ->where(function ($query) use ($startOfMonth, $endOfMonth) {
                $query->whereBetween('start_date', [$startOfMonth, $endOfMonth])
                    ->orWhereBetween('end_date', [$startOfMonth, $endOfMonth])
                    ->orWhere(function ($q) use ($startOfMonth, $endOfMonth) {
                        $q->where('start_date', '<=', $startOfMonth)
                          ->where('end_date', '>=', $endOfMonth);
                    });
            })
            ->with('leaveType')
            ->get();

        // Create a map of date => leave request
        $leaveRequestMap = [];
        foreach ($leaveRequests as $request) {
            $start = Carbon::parse($request->start_date);
            $end = Carbon::parse($request->end_date);
            $current = $start->copy();
            
            while ($current->lte($end)) {
                $dateKey = $current->format('Y-m-d');
                if (!isset($leaveRequestMap[$dateKey])) {
                    $leaveRequestMap[$dateKey] = $request;
                }
                $current->addDay();
            }
        }

        // Add leave request info to attendance data and adjust attendance status
        foreach ($this->attendanceData as $key => $record) {
            $dateKey = $record['date'] ?? null;
            if ($dateKey && isset($leaveRequestMap[$dateKey])) {
                $leaveRequest = $leaveRequestMap[$dateKey];
                $this->attendanceData[$key]['leave_request'] = [
                    'id' => $leaveRequest->id,
                    'status' => $leaveRequest->status,
                    'leave_type' => $leaveRequest->leaveType->name ?? __('Unknown'),
                    'total_days' => $leaveRequest->total_days,
                    'duration' => $leaveRequest->duration, // Store duration for half-day calculation
                ];

                // If leave is approved, adjust attendance status from "absent" to "on_leave"
                if ($leaveRequest->status === LeaveRequestModel::STATUS_APPROVED) {
                    $currentStatus = $this->attendanceData[$key]['status'] ?? 'absent';
                    
                    // Only change status if it's currently absent or if there's no attendance
                    if ($currentStatus === 'absent' || (empty($record['check_in']) && empty($record['check_out']))) {
                        $this->attendanceData[$key]['status'] = 'on_leave';
                        // Clear any absent-related flags
                        unset($this->attendanceData[$key]['is_late']);
                        unset($this->attendanceData[$key]['is_early']);
                    }
                }
            }
        }
    }

    /**
     * Check if a date is exempted for the current employee
     */
    private function isDateExempted($date)
    {
        if (!$this->employee) {
            return false;
        }

        $dateCarbon = Carbon::parse($date);
        $userId = $this->employee->user_id;
        $departmentId = $this->employee->department_id;
        $userRoles = Auth::user()->roles->pluck('id')->toArray();

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
     * Check if a date has "Exclude Breaks" manual entries
     */
    private function hasExcludeBreaksEntry($date)
    {
        if (!$this->punchCode) {
            return false;
        }

        $dateCarbon = Carbon::parse($date)->startOfDay();
        $endOfDay = $dateCarbon->copy()->endOfDay();

        // Check if there are manual entries with "Exclude Breaks" in notes for this date
        $hasExcludeBreaks = DeviceAttendance::where('punch_code', $this->punchCode)
            ->where('is_manual_entry', true)
            ->whereBetween('punch_time', [$dateCarbon, $endOfDay])
            ->where('notes', 'like', '%Exclude Breaks%')
            ->where(function($query) {
                $query->whereNull('verify_mode')
                      ->orWhere('verify_mode', '!=', 2);
            })
            ->exists();

        return $hasExcludeBreaks;
    }

    private function loadHolidaysForMonth($startOfMonth, $endOfMonth)
    {
        if (!$this->employee) {
            return [];
        }

        $holidaysMap = [];
        $employeeId = $this->employee->id;
        $departmentId = $this->employee->department_id;
        $groupId = $this->employee->group_id;
        $userRoles = Auth::user()->roles->pluck('id')->toArray();

        // Load active holidays that fall within the month range
        $holidays = Holiday::where('status', 'active')
            ->where(function($query) use ($startOfMonth, $endOfMonth) {
                $query->whereBetween('from_date', [$startOfMonth->format('Y-m-d'), $endOfMonth->format('Y-m-d')])
                      ->orWhereBetween('to_date', [$startOfMonth->format('Y-m-d'), $endOfMonth->format('Y-m-d')])
                      ->orWhere(function($q) use ($startOfMonth, $endOfMonth) {
                          $q->where('from_date', '<=', $startOfMonth->format('Y-m-d'))
                            ->where('to_date', '>=', $endOfMonth->format('Y-m-d'));
                      });
            })
            ->with(['departments', 'roles', 'groups', 'employees'])
            ->get();

        foreach ($holidays as $holiday) {
            // Check if this holiday applies to the employee
            $appliesToEmployee = false;

            if ($holiday->scope_type === 'all_employees') {
                $appliesToEmployee = true;
            } elseif ($holiday->scope_type === 'department') {
                // Check if employee's department is in the holiday's departments
                if ($departmentId && $holiday->departments->contains('id', $departmentId)) {
                    $appliesToEmployee = true;
                }
                // Also check if employee is specifically included
                if ($holiday->employees->contains('id', $employeeId)) {
                    $appliesToEmployee = true;
                }
            } elseif ($holiday->scope_type === 'role') {
                // Check if employee's role is in the holiday's roles
                if (!empty(array_intersect($userRoles, $holiday->roles->pluck('id')->toArray()))) {
                    $appliesToEmployee = true;
                }
                // Also check if employee is specifically included
                if ($holiday->employees->contains('id', $employeeId)) {
                    $appliesToEmployee = true;
                }
            } elseif ($holiday->scope_type === 'group') {
                // Check if employee's group is in the holiday's groups
                if ($groupId && $holiday->groups->contains('id', $groupId)) {
                    $appliesToEmployee = true;
                }
                // Also check if employee is specifically included
                if ($holiday->employees->contains('id', $employeeId)) {
                    $appliesToEmployee = true;
                }
            } elseif ($holiday->scope_type === 'employee') {
                // Check if employee is specifically included
                if ($holiday->employees->contains('id', $employeeId)) {
                    $appliesToEmployee = true;
                }
            }

            if ($appliesToEmployee) {
                // Generate all dates for this holiday
                $currentDate = Carbon::parse($holiday->from_date);
                $endDate = Carbon::parse($holiday->to_date ?: $holiday->from_date);

                while ($currentDate->lte($endDate)) {
                    $dateKey = $currentDate->format('Y-m-d');
                    // Only add if within the month range
                    if ($currentDate->gte($startOfMonth) && $currentDate->lte($endOfMonth)) {
                        $holidaysMap[$dateKey] = [
                            'name' => $holiday->name,
                            'id' => $holiday->id,
                        ];
                    }
                    $currentDate->addDay();
                }
            }
        }

        return $holidaysMap;
    }

    private function processAttendanceData($records)
    {
        // Determine which month to process
        $targetMonth = $this->selectedMonth ?: Carbon::now()->format('Y-m');
        $startOfMonth = Carbon::createFromFormat('Y-m', $targetMonth)->startOfMonth();
        $endOfMonth = Carbon::createFromFormat('Y-m', $targetMonth)->endOfMonth();
        
        // Load holidays for this month
        $holidaysMap = $this->loadHolidaysForMonth($startOfMonth, $endOfMonth);
        
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
            
            // Get the effective shift for this specific date (considers EmployeeShift assignments with start_date)
            $dayShift = $this->employee->getEffectiveShiftForDate($date);
            
            // Check if this date has "Exclude Breaks" manual entries
            $hasExcludeBreaksEntry = $this->hasExcludeBreaksEntry($date);
            
            // Determine shift characteristics once for this day
            $isOvernight = false;
            $shiftStartsInPM = false;
            $timeFrom = null;
            $timeTo = null;
            $expectedCheckOutTime = null;
            
            if ($dayShift) {
                $timeFromParts = explode(':', $dayShift->time_from);
                $timeToParts = explode(':', $dayShift->time_to);
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
            if ($dayShift && !empty($dayRecords)) {
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
            $isExempted = $this->isDateExempted($date);
            
            // Check if this date is a holiday
            $holiday = $holidaysMap[$date] ?? null;
            $isHoliday = $holiday !== null;
            
            // Determine day status
            // Priority: Holiday > Weekend > Present > Absent > Exempted (only if present)
            $status = 'absent'; // Default
            if ($isHoliday) {
                // Always show as holiday if it's a holiday, even if employee worked
                $status = 'holiday';
            } elseif ($current->isWeekend()) {
                $status = 'off';
            } elseif ($hasValidAttendance) {
                // If employee is present and day is exempted, show as exempted
                // Otherwise show as present (will be updated to present_late, etc. later)
                if ($isExempted) {
                    $status = 'exempted';
                } else {
                    $status = 'present';
                }
            } else {
                // No attendance - always show as absent, even if day is exempted
                $status = 'absent';
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
                'holiday_name' => $isHoliday ? $holiday['name'] : null,
                'shift_name' => $dayShift ? $dayShift->shift_name : null,
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
            
            // Process attendance records even on holidays to show check-in/check-out times
            // Don't skip holidays - we want to show attendance data even if status is "holiday"
            
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
                
                // For exempted days, only use first check-in and last check-out
                // (exclude intermediate entries)
                usort($validCheckIns, function($a, $b) {
                    return $a->timestamp - $b->timestamp;
                });
                usort($validCheckOuts, function($a, $b) {
                    return $a->timestamp - $b->timestamp;
                });
                
                // Get first check-in and last check-out
                // For exempted days, this ensures we only use the first and last entries
                $firstCheckIn = !empty($validCheckIns) ? $validCheckIns[0] : null;
                $lastCheckOut = !empty($validCheckOuts) ? end($validCheckOuts) : null;
                
                // For exempted days with records, status should already be 'exempted' from initial determination
                // Don't override it - exempted status is only for present employees
                // If no records, status will remain 'absent' (prioritized over exempted)
                
                // Update status: only mark as present if there's a valid check-in for PM-start shifts
                // But don't override holiday status
                // Note: Absent takes priority over exempted - if no valid attendance, show as absent
                if ($isOvernight && $shiftStartsInPM && empty($validCheckIns) && !empty($validCheckOuts)) {
                    // Only AM check-out exists (belongs to previous day), don't mark as present
                    // Absent takes priority - even if exempted, if no valid attendance, show as absent
                    if (!$isHoliday) {
                        $firstCheckIn = null;
                        $lastCheckOut = null;
                        if ($current->isWeekend()) {
                            $status = 'off';
                        } else {
                            // Always show as absent if no valid attendance, even if exempted
                            $status = 'absent';
                        }
                        $processedData[$date]['status'] = $status;
                    }
                }
                
                $processedData[$date]['check_in'] = $firstCheckIn ? $firstCheckIn->format('h:i:s A') : null;
                $processedData[$date]['check_out'] = $lastCheckOut ? $lastCheckOut->format('h:i:s A') : null;
                
                // Validate against shift and calculate late/early if shift exists
                if ($dayShift && $firstCheckIn) {
                    $shiftValidation = $this->validateShiftAttendance($current, $firstCheckIn, $lastCheckOut, $dayShift);
                    $processedData[$date]['expected_check_in'] = $shiftValidation['expected_check_in'];
                    $processedData[$date]['expected_check_out'] = $shiftValidation['expected_check_out'];
                    $processedData[$date]['is_late'] = $shiftValidation['is_late'];
                    $processedData[$date]['late_minutes'] = $shiftValidation['late_minutes'];
                    $processedData[$date]['is_early'] = $shiftValidation['is_early'];
                    $processedData[$date]['early_minutes'] = $shiftValidation['early_minutes'];
                    $processedData[$date]['expected_hours'] = $shiftValidation['expected_hours'];
                    
                    // Update status to include late/early information
                    // But don't change status if it's a holiday - keep it as "holiday"
                    if ($processedData[$date]['status'] === 'present' && !$isHoliday) {
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
                if ($dayShift && $isOvernight && $shiftStartsInPM) {
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
                } elseif ($dayShift && $isOvernight && !$shiftStartsInPM) {
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
                
                // For exempted days or dates with "Exclude Breaks" entries, only use first check-in and last check-out
                // Exclude intermediate entries and missing pairs
                if (($isExempted || $hasExcludeBreaksEntry) && !empty($recordsForCalculation)) {
                    // Store the original merged records before we rebuild
                    // This includes next day's records for overnight shifts
                    $originalRecordsForCalculation = $recordsForCalculation;
                    
                    $checkIns = [];
                    $checkOuts = [];
                    
                    foreach ($recordsForCalculation as $record) {
                        $recordTime = Carbon::parse($record->punch_time ?? $record['punch_time']);
                        if (($record->device_type ?? $record['device_type']) === 'IN') {
                            $checkIns[] = $recordTime;
                        } elseif (($record->device_type ?? $record['device_type']) === 'OUT') {
                            $checkOuts[] = $recordTime;
                        }
                    }
                    
                    // Sort and get only first check-in and last check-out
                    usort($checkIns, function($a, $b) {
                        return $a->timestamp - $b->timestamp;
                    });
                    usort($checkOuts, function($a, $b) {
                        return $a->timestamp - $b->timestamp;
                    });
                    
                    // Rebuild recordsForCalculation with only first check-in and last check-out
                    // Search in the original merged records (which includes next day's records for overnight shifts)
                    $recordsForCalculation = [];
                    if (!empty($checkIns)) {
                        // Find the record matching the first check-in
                        foreach ($originalRecordsForCalculation as $record) {
                            $recordTime = Carbon::parse($record->punch_time ?? $record['punch_time']);
                            $deviceType = is_object($record) ? $record->device_type : $record['device_type'];
                            if ($deviceType === 'IN' && $recordTime->equalTo($checkIns[0])) {
                                $recordsForCalculation[] = $record;
                                break;
                            }
                        }
                    }
                    if (!empty($checkOuts)) {
                        // Find the record matching the last check-out
                        $lastCheckOutTime = end($checkOuts);
                        foreach ($originalRecordsForCalculation as $record) {
                            $recordTime = Carbon::parse($record->punch_time ?? $record['punch_time']);
                            $deviceType = is_object($record) ? $record->device_type : $record['device_type'];
                            if ($deviceType === 'OUT' && $recordTime->equalTo($lastCheckOutTime)) {
                                $recordsForCalculation[] = $record;
                                break;
                            }
                        }
                    }
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
                    
                    // For exempted days or dates with "Exclude Breaks" entries, calculate hours from first check-in to last check-out
                    // Ignore missing pairs and intermediate entries
                    if (($isExempted || $hasExcludeBreaksEntry) && $firstCheckIn && $lastCheckOut) {
                        $exemptedMinutes = $firstCheckIn->diffInMinutes($lastCheckOut);
                        if ($exemptedMinutes > 0) {
                            // For exempted days, apply allowed break time logic if set
                            if ($this->allowedBreakTime !== null && $this->allowedBreakTime > 0 && $totalBreakMinutes > 0) {
                                $excessBreakMinutes = max(0, $totalBreakMinutes - $this->allowedBreakTime);
                                $exemptedMinutes = $exemptedMinutes - $excessBreakMinutes;
                            }
                            
                            $hours = floor($exemptedMinutes / 60);
                            $minutes = $exemptedMinutes % 60;
                            $processedData[$date]['total_hours'] = sprintf('%d:%02d', $hours, $minutes);
                            $processedData[$date]['actual_hours'] = sprintf('%d:%02d', $hours, $minutes);
                            // For exempted days, don't count breaks (we're only using first and last)
                            $processedData[$date]['breaks'] = '0 (0h 0m total)';
                        } else {
                            $processedData[$date]['total_hours'] = 'N/A';
                            $processedData[$date]['actual_hours'] = null;
                        }
                    } else {
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
                                // Apply allowed break time logic if set
                                if ($this->allowedBreakTime !== null && $this->allowedBreakTime > 0 && $totalBreakMinutes > 0) {
                                    $excessBreakMinutes = max(0, $totalBreakMinutes - $this->allowedBreakTime);
                                    $calculatedMinutes = $calculatedMinutes - $excessBreakMinutes;
                                }
                                
                                $hours = floor($calculatedMinutes / 60);
                                $minutes = $calculatedMinutes % 60;
                                $processedData[$date]['total_hours'] = sprintf('%d:%02d', $hours, $minutes);
                                $processedData[$date]['actual_hours'] = sprintf('%d:%02d', $hours, $minutes);
                            } else {
                                $processedData[$date]['total_hours'] = 'N/A';
                                $processedData[$date]['actual_hours'] = null;
                            }
                        } elseif ($dayTotalMinutes > 0) {
                            // Calculate working hours considering allowed break time
                            // dayTotalMinutes is the working time (breaks already excluded)
                            // totalBreakMinutes is the total break time
                            // Total time = working time + break time
                            
                            $workingMinutes = $dayTotalMinutes;
                            
                            // If allowed break time is set, only deduct break time exceeding the allowed time
                            if ($this->allowedBreakTime !== null && $this->allowedBreakTime > 0 && $totalBreakMinutes > 0) {
                                // Calculate excess break time (break time exceeding allowed)
                                $excessBreakMinutes = max(0, $totalBreakMinutes - $this->allowedBreakTime);
                                // Total time = working time + break time
                                // Working hours = Total time - excess break time
                                // = (working time + break time) - excess break time
                                // = working time + break time - max(0, break time - allowed)
                                // = working time + min(break time, allowed)
                                $totalTimeMinutes = $dayTotalMinutes + $totalBreakMinutes;
                                $workingMinutes = $totalTimeMinutes - $excessBreakMinutes;
                            } else {
                                // If no allowed break time is set, use current logic (working time already excludes breaks)
                                $workingMinutes = $dayTotalMinutes;
                            }
                            
                            $hours = floor($workingMinutes / 60);
                            $minutes = $workingMinutes % 60;
                            $processedData[$date]['total_hours'] = sprintf('%d:%02d', $hours, $minutes);
                            $processedData[$date]['actual_hours'] = sprintf('%d:%02d', $hours, $minutes);
                        }
                    }
                    
                    // Format breaks information (skip for exempted days as breaks are already set to 0)
                    if (!$isExempted) {
                        $breakHours = floor($totalBreakMinutes / 60);
                        $breakMinutes = $totalBreakMinutes % 60;
                        $processedData[$date]['breaks'] = sprintf('%d (%dh %dm total)', $breaksCount, $breakHours, $breakMinutes);
                        
                        // Store individual break details for tooltip
                        $processedData[$date]['break_details'] = $this->getBreakDetails($deduplicatedCollection);
                    }
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
    private function validateShiftAttendance($date, $actualCheckIn, $actualCheckOut = null, $shift = null)
    {
        // Use provided shift or fall back to employeeShift
        $shift = $shift ?? $this->employeeShift;
        
        if (!$shift) {
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
            $gracePeriodLateIn = $this->getEffectiveGracePeriodLateIn($shift);
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
            $gracePeriodEarlyOut = $this->getEffectiveGracePeriodEarlyOut($shift);
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
        
        // Check if first record is an OUT (missing check-in scenario)
        // If so, add it to break details and include it in processing
        $firstOutTime = null;
        $firstOutRecord = null;
        $firstOutWasPaired = false;
        
        if (!empty($deduplicatedRecords)) {
            $firstRecord = $deduplicatedRecords[0];
            $firstType = $firstRecord['device_type'] ?? null;
            
            if ($firstType === 'OUT') {
                // First record is OUT (missing check-in)
                $firstOutTime = Carbon::parse($firstRecord['punch_time']);
                $firstOutRecord = $firstRecord;
                $firstOutManual = isset($firstRecord['is_manual_entry']) && $firstRecord['is_manual_entry'] ? true : false;
                
                // Set this as the last check-out so it can pair with subsequent IN
                // We'll add "Missing Check-in" entry later only if it doesn't pair with anything
                $lastCheckOut = $firstOutTime;
                $lastCheckOutRecord = $firstRecord;
            }
        }
        
        // Skip first check-in and last check-out (boundary times)
        // If first was OUT, we've already added "Missing Check-in" entry, but we still need to process it
        // to pair with subsequent IN, so we'll include it in middle records
        // Only skip first if it's an IN (normal check-in)
        $skipFirst = !empty($deduplicatedRecords) && ($deduplicatedRecords[0]['device_type'] ?? null) === 'IN' ? 1 : 0;
        $middleRecords = array_slice($deduplicatedRecords, $skipFirst, -1);
        
        foreach ($middleRecords as $record) {
            $recordTime = Carbon::parse($record['punch_time']);
            $type = $record['device_type'];
            
            if ($type === 'OUT') {
                // Check if this is the first OUT (we've already added "Missing Check-in" for it)
                $isFirstOut = $firstOutTime && $recordTime->equalTo($firstOutTime);
                
                // Two OUTs in a row means previous IN missing → close prior as '--'
                // But don't add entry if this is the first OUT (we already added "Missing Check-in")
                if ($lastCheckOut !== null && !$isFirstOut) {
                    $breakDetails[] = [
                        'start' => $lastCheckOut->format('h:i:s A'),
                        'end' => '--',
                        'duration' => '--',
                        'start_manual' => $lastCheckOutRecord && isset($lastCheckOutRecord['is_manual_entry']) && $lastCheckOutRecord['is_manual_entry'] ? true : false,
                    ];
                }
                // Start a new potential break at this OUT
                // If this is the first OUT, $lastCheckOut is already set, so don't override it
                if (!$isFirstOut) {
                    $lastCheckOut = $recordTime;
                    $lastCheckOutRecord = $record; // Store the record
                }
            } elseif ($type === 'IN') {
                $isManualCheckIn = isset($record['is_manual_entry']) && $record['is_manual_entry'] ? true : false;
                
                if ($lastCheckOut) {
                    // Normal complete pair: OUT → IN
                    $breakDuration = $lastCheckOut->diffInMinutes($recordTime);
                    if ($breakDuration > 0) {
                        // Check if this is pairing with the first OUT
                        if ($firstOutTime && $lastCheckOut->equalTo($firstOutTime)) {
                            $firstOutWasPaired = true;
                        }
                        
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
        
        // If first OUT wasn't paired with an IN, show "Missing Check-in"
        if ($firstOutTime !== null && !$firstOutWasPaired) {
            $firstOutManual = isset($firstOutRecord['is_manual_entry']) && $firstOutRecord['is_manual_entry'] ? true : false;
            $breakDetails[] = [
                'start' => $firstOutTime->format('h:i:s A'),
                'end' => '--',
                'duration' => '--',
                'start_manual' => $firstOutManual,
            ];
        }
        
        // If the sequence ended with an OUT and no following IN, show OUT → '--'
        // But only if it's not the first OUT (which we already handled above)
        if ($lastCheckOut !== null && (!$firstOutTime || !$lastCheckOut->equalTo($firstOutTime))) {
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
     * Get grace time per day in minutes (late + early)
     */
    private function getGraceTimePerDay($shift = null)
    {
        $shift = $shift ?? $this->employeeShift;
        $gracePeriodLateIn = $this->getEffectiveGracePeriodLateIn($shift);
        $gracePeriodEarlyOut = $this->getEffectiveGracePeriodEarlyOut($shift);
        return $gracePeriodLateIn + $gracePeriodEarlyOut;
    }

    /**
     * Calculate expected hours based on employee's shift
     * @param int $workingDays Number of working days
     * @param bool $includeGraceTime Whether to include grace time in the calculation
     */
    private function calculateExpectedHours($workingDays, $includeGraceTime = false)
    {
        if (!$this->employeeShift) {
            // Default to 8 hours per day if no shift assigned
            $defaultHours = $workingDays * 8;
            if ($includeGraceTime) {
                // Deduct grace time (default 60 minutes = 1 hour per day)
                $graceTimeHours = $workingDays * 1; // 30 + 30 = 60 minutes = 1 hour
                $defaultHours -= $graceTimeHours; // Deduct, not add
            }
            return sprintf('%d:%02d', $defaultHours, 0);
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
        
        // Deduct grace time if requested (grace time reduces expected hours)
        if ($includeGraceTime) {
            $graceTimePerDay = $this->getGraceTimePerDay($shift);
            $totalGraceMinutes = $workingDays * $graceTimePerDay;
            $totalExpectedMinutes -= $totalGraceMinutes; // Deduct, not add
        }
        
        $expectedHours = floor($totalExpectedMinutes / 60);
        $expectedMins = $totalExpectedMinutes % 60;
        
        return sprintf('%d:%02d', $expectedHours, $expectedMins);
    }

    private function calculateExpectedHoursTillToday($targetMonth)
    {
        // Only calculate for current month
        $currentMonth = Carbon::now()->format('Y-m');
        if ($targetMonth !== $currentMonth) {
            // If viewing a past month, return the full expected hours for that month
            // If viewing a future month, return 0:00
            $targetDate = Carbon::createFromFormat('Y-m', $targetMonth);
            $now = Carbon::now();
            
            if ($targetDate->isFuture()) {
                return '0:00';
            }
            
            // For past months, return the full expected hours
            $startOfMonth = $targetDate->copy()->startOfMonth();
            $endOfMonth = $targetDate->copy()->endOfMonth();
            $workingDays = 0;
            $current = $startOfMonth->copy();
            
            while ($current->lte($endOfMonth)) {
                if ($current->isWeekday()) {
                    $workingDays++;
                }
                $current->addDay();
            }
            
            // For past months, return expected hours with grace time
            return $this->calculateExpectedHours($workingDays, true);
        }

        // For current month, calculate working days from start of month till today (including today)
        $startOfMonth = Carbon::now()->startOfMonth();
        $today = Carbon::now();
        
        $workingDaysTillToday = 0;
        $current = $startOfMonth->copy();
        
        while ($current->lte($today)) {
            if ($current->isWeekday()) {
                $workingDaysTillToday++;
            }
            $current->addDay();
        }

        // Return expected hours with grace time included
        return $this->calculateExpectedHours($workingDaysTillToday, true);
    }

    private function calculateAttendanceStats($records, $processedData = null)
    {
        // Use the same month as the records
        $targetMonth = $this->selectedMonth ?: Carbon::now()->format('Y-m');
        $startOfMonth = Carbon::createFromFormat('Y-m', $targetMonth)->startOfMonth();
        $endOfMonth = Carbon::createFromFormat('Y-m', $targetMonth)->endOfMonth();
        
        // Load holidays for this month
        $holidaysMap = $this->loadHolidaysForMonth($startOfMonth, $endOfMonth);
        
        // Get all working days in the month (excluding weekends)
        $workingDays = 0;
        $current = $startOfMonth->copy();
        
        while ($current->lte($endOfMonth)) {
            if ($current->isWeekday()) {
                $workingDays++;
            }
            $current->addDay();
        }

        // For current month, calculate working days up to today (including today) for absent calculation
        $currentMonth = Carbon::now()->format('Y-m');
        $workingDaysTillToday = $workingDays; // Default to full month
        
        if ($targetMonth === $currentMonth) {
            // Only count working days from start of month till today (including today)
            $workingDaysTillToday = 0;
            $current = $startOfMonth->copy();
            $today = Carbon::now();
            
            while ($current->lte($today)) {
                if ($current->isWeekday()) {
                    $workingDaysTillToday++;
                }
                $current->addDay();
            }
        }

        // Count unique working days with attendance (excluding weekends)
        $today = Carbon::now();
        $attendedDays = 0;
        
        // If we have processed data, count days with "present" status from it
        if (!empty($processedData) && is_array($processedData)) {
            foreach ($processedData as $key => $record) {
                // Get date from record - try 'date' field first, then use key if it looks like a date
                $date = null;
                if (is_array($record) && isset($record['date'])) {
                    $date = $record['date'];
                } elseif (is_string($key) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $key)) {
                    $date = $key;
                }
                
                if (!$date) {
                    continue;
                }
                
                try {
                    $dateCarbon = Carbon::parse($date);
                } catch (\Exception $e) {
                    continue;
                }
                
                // Only count working days (exclude weekends)
                if ($dateCarbon->isWeekend()) {
                    continue;
                }
                
                // For current month, only count days up to today (don't count future days)
                if ($targetMonth === $currentMonth && $dateCarbon->isFuture()) {
                    continue;
                }
                
                // Only count if the date is within the target month
                $recordMonth = $dateCarbon->format('Y-m');
                if ($recordMonth !== $targetMonth) {
                    continue;
                }
                
                // Count days with "present" status (including present_late, present_early, etc.)
                // Also count "exempted" days as attended since exempted only applies when employee is present
                $status = is_array($record) ? ($record['status'] ?? '') : '';
                // Check for present status (case-insensitive, including variations like present_late, present_early, etc.)
                // Also count exempted as attended (exempted means present on an exempted day)
                if (!empty($status)) {
                    $statusLower = strtolower(trim($status));
                    if ($statusLower === 'present' || 
                        str_starts_with($statusLower, 'present_') || 
                        str_starts_with($statusLower, 'present ') ||
                        $statusLower === 'exempted') {
                        // Check if there's a half-day leave request for this day
                        $leaveRequest = $record['leave_request'] ?? null;
                        if ($leaveRequest && isset($leaveRequest['status']) && $leaveRequest['status'] === LeaveRequestModel::STATUS_APPROVED) {
                            // If it's a half-day leave, count as 0.5 present days
                            if (isset($leaveRequest['total_days']) && (float)$leaveRequest['total_days'] == 0.5) {
                                $attendedDays += 0.5;
                            } else {
                                // Full-day leave or no leave info - count as full day
                        $attendedDays++;
                            }
                        } else {
                            // No leave request - count as full day
                            $attendedDays++;
                        }
                    }
                }
            }
        } else {
            // Fallback to counting from raw records
        $attendedDays = $records->groupBy(function ($record) {
            return Carbon::parse($record->punch_time)->format('Y-m-d');
        })->filter(function ($dayRecords, $date) use ($targetMonth, $currentMonth) {
            // Only count working days (exclude weekends)
            $dateCarbon = Carbon::parse($date);
            
            // For current month, only count days up to today (don't count future days)
            if ($targetMonth === $currentMonth && $dateCarbon->isFuture()) {
                return false; // Don't count future days
            }
            
            // Only count if the date is within the target month
            $recordMonth = $dateCarbon->format('Y-m');
            if ($recordMonth !== $targetMonth) {
                return false;
            }
            
            return !$dateCarbon->isWeekend();
        })->count();
        }

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

        // Calculate expected hours till today (including today) for current month
        $expectedHoursTillToday = $this->calculateExpectedHoursTillToday($targetMonth);

        // Count on_leave days, holidays, and exempted days (exclude from absent calculation)
        $onLeaveDays = 0;
        $holidayDays = 0;
        $exemptedDays = 0;
        $totalNonAllowedBreakMinutes = 0;
        $totalBreakMinutes = 0;
        
        // Count holidays from holidaysMap for the entire month (including future holidays)
        // This ensures upcoming holidays are also counted, not just past ones from processedData
        $current = $startOfMonth->copy();
        while ($current->lte($endOfMonth)) {
            if ($current->isWeekday()) {
                $dateStr = $current->format('Y-m-d');
                if (isset($holidaysMap[$dateStr])) {
                    $holidayDays++;
                }
            }
            $current->addDay();
        }
        
        // Load ALL leave requests (not just approved) to identify rejected dates
        $allLeaveRequests = [];
        if ($this->employee) {
            $allLeaveRequests = LeaveRequestModel::where('employee_id', $this->employee->id)
                ->where(function ($query) use ($startOfMonth, $endOfMonth) {
                    $query->whereBetween('start_date', [$startOfMonth, $endOfMonth])
                        ->orWhereBetween('end_date', [$startOfMonth, $endOfMonth])
                        ->orWhere(function ($q) use ($startOfMonth, $endOfMonth) {
                            $q->where('start_date', '<=', $startOfMonth)
                              ->where('end_date', '>=', $endOfMonth);
                        });
                })
                ->get();
        }
        
        // Build a map of dates that have rejected leave requests (these should NOT count as leaves)
        $rejectedLeaveDates = [];
        foreach ($allLeaveRequests as $request) {
            if ($request->status === LeaveRequestModel::STATUS_REJECTED) {
                $start = Carbon::parse($request->start_date);
                $end = Carbon::parse($request->end_date);
                $current = $start->copy();
                
                while ($current->lte($end)) {
                    $dateKey = $current->format('Y-m-d');
                    if ($current->isWeekday() && 
                        $current->gte($startOfMonth) && 
                        $current->lte($endOfMonth)) {
                        $rejectedLeaveDates[$dateKey] = true;
                    }
                    $current->addDay();
                }
            }
        }
        
        // Load only approved leave requests for counting
        $leaveRequests = [];
        if ($this->employee) {
            $leaveRequests = LeaveRequestModel::where('employee_id', $this->employee->id)
                ->where('status', LeaveRequestModel::STATUS_APPROVED)
                ->where(function ($query) use ($startOfMonth, $endOfMonth) {
                    $query->whereBetween('start_date', [$startOfMonth, $endOfMonth])
                        ->orWhereBetween('end_date', [$startOfMonth, $endOfMonth])
                        ->orWhere(function ($q) use ($startOfMonth, $endOfMonth) {
                            $q->where('start_date', '<=', $startOfMonth)
                              ->where('end_date', '>=', $endOfMonth);
                        });
                })
                ->get();
        }
        
        // Build a map of dates that have leave requests (to exclude from fallback counting)
        $leaveRequestDates = [];
        foreach ($leaveRequests as $request) {
            $start = Carbon::parse($request->start_date);
            $end = Carbon::parse($request->end_date);
            $current = $start->copy();
            
            while ($current->lte($end)) {
                $dateKey = $current->format('Y-m-d');
                $leaveRequestDates[$dateKey] = true;
                $current->addDay();
            }
        }
        
        // Count leave days by counting only weekdays (excluding weekends) for each approved leave request
        // But exclude dates that have rejected leave requests
        $countedLeaveRequestIds = [];
        foreach ($leaveRequests as $request) {
            $leaveRequestId = $request->id;
            
            // Skip if already counted
            if (isset($countedLeaveRequestIds[$leaveRequestId])) {
                continue;
            }
            
            // Check if it's a half-day leave
            $isHalfDay = in_array($request->duration ?? '', ['half_day_morning', 'half_day_afternoon'], true);
            
            if ($isHalfDay) {
                // For half-day leaves, count as 0.5 days (only if it falls on a weekday, within target month, and not rejected)
                $start = Carbon::parse($request->start_date);
                $dateKey = $start->format('Y-m-d');
                if ($start->isWeekday() && 
                    $start->gte($startOfMonth) && 
                    $start->lte($endOfMonth) &&
                    !isset($rejectedLeaveDates[$dateKey])) {
                    $onLeaveDays += 0.5;
                }
            } else {
                // For full-day leaves, count only weekdays between start_date and end_date
                // But exclude dates that have rejected leave requests
                $start = Carbon::parse($request->start_date);
                $end = Carbon::parse($request->end_date);
                $current = $start->copy();
                
                $weekdayCount = 0;
                while ($current->lte($end)) {
                    // Only count weekdays that fall within the target month AND don't have rejected leave requests
                    $dateKey = $current->format('Y-m-d');
                    if ($current->isWeekday() && 
                        $current->gte($startOfMonth) && 
                        $current->lte($endOfMonth) &&
                        !isset($rejectedLeaveDates[$dateKey])) {
                        $weekdayCount++;
                    }
                    $current->addDay();
                }
                
                $onLeaveDays += $weekdayCount;
            }
            
            // Mark this leave request as counted
            $countedLeaveRequestIds[$leaveRequestId] = true;
        }
        
        // Fallback: Count days with on_leave status from processedData (for backward compatibility)
        // Only count if they don't already have a leave request (to avoid double-counting)
        if (!empty($processedData) && is_array($processedData)) {
            // Track dates with on_leave status to avoid double-counting
            $countedLeaveDates = [];
            
            foreach ($processedData as $record) {
                // Only count if status is on_leave and we haven't already counted a leave request for this date
                if (isset($record['status']) && $record['status'] === 'on_leave') {
                    $date = is_array($record) && isset($record['date']) ? $record['date'] : null;
                    // Skip if this date already has a leave request (already counted above)
                    if ($date && !isset($leaveRequestDates[$date]) && !isset($countedLeaveDates[$date])) {
                        // Only count if it's a weekday
                        try {
                            $dateCarbon = Carbon::parse($date);
                            if ($dateCarbon->isWeekday()) {
                                $onLeaveDays++;
                                $countedLeaveDates[$date] = true;
                            }
                        } catch (\Exception $e) {
                            // Skip invalid dates
                        }
                    }
                }
                
                if (isset($record['status']) && $record['status'] === 'exempted') {
                    $exemptedDays++;
                }
                
                // Calculate break time for this day
                if (isset($record['breaks'])) {
                    // Parse break time from format like "2 (1h 15m total)" or "0 (0h 0m total)"
                    $breaksString = $record['breaks'];
                    if (preg_match('/(\d+)h\s+(\d+)m\s+total/', $breaksString, $matches)) {
                        $breakHours = (int)$matches[1];
                        $breakMinutes = (int)$matches[2];
                        $dayBreakMinutes = ($breakHours * 60) + $breakMinutes;
                        $totalBreakMinutes += $dayBreakMinutes;
                        
                        // Calculate excess break time (non-allowed)
                        // If allowedBreakTime is null or 0, all break time is non-allowed
                        if ($this->allowedBreakTime === null || $this->allowedBreakTime <= 0) {
                            // All break time is non-allowed when no allowed break time is set
                            $totalNonAllowedBreakMinutes += $dayBreakMinutes;
                        } else {
                            // Only break time exceeding allowed time is non-allowed
                            $excessBreakMinutes = max(0, $dayBreakMinutes - $this->allowedBreakTime);
                            $totalNonAllowedBreakMinutes += $excessBreakMinutes;
                        }
                    }
                }
            }
        }

        // Calculate absent days: Count directly from processedData where status is 'absent'
        // This ensures we count actual absent days, not calculated ones
        $absentDays = 0;
        if (!empty($processedData) && is_array($processedData)) {
            foreach ($processedData as $record) {
                $date = is_array($record) && isset($record['date']) ? $record['date'] : null;
                if (!$date) {
                    continue;
                }
                
                try {
                    $dateCarbon = Carbon::parse($date);
                } catch (\Exception $e) {
                    continue;
                }
                
                // Only count working days (exclude weekends)
                if ($dateCarbon->isWeekend()) {
                    continue;
                }
                
                // For current month, only count days up to today
                if ($targetMonth === $currentMonth && $dateCarbon->isFuture()) {
                    continue;
                }
                
                // Only count if the date is within the target month
                $recordMonth = $dateCarbon->format('Y-m');
                if ($recordMonth !== $targetMonth) {
                    continue;
                }
                
                // Count days with "absent" status
                $status = is_array($record) ? ($record['status'] ?? '') : '';
                if ($status === 'absent') {
                    $absentDays++;
                }
            }
        }
        
        // Fallback calculation if processedData is not available
        if ($absentDays === 0 && empty($processedData)) {
            // Calculate absent days: (working days - holidays) - attended days - on_leave days
            // Note: Exempted days where employee is present are already in attendedDays, so don't subtract them
            $workingDaysExcludingHolidays = $workingDaysTillToday;
            $current = $startOfMonth->copy();
            $today = Carbon::now();
            $endDate = ($targetMonth === $currentMonth) ? $today : $endOfMonth;
            while ($current->lte($endDate)) {
                if ($current->isWeekday()) {
                    $dateStr = $current->format('Y-m-d');
                    if (isset($holidaysMap[$dateStr])) {
                        $workingDaysExcludingHolidays--;
                    }
                }
                $current->addDay();
            }
            
            $absentDays = max(0, $workingDaysExcludingHolidays - $attendedDays - $onLeaveDays);
        }
        
        // For attendance percentage, use working days till today for current month, full month for past months
        $workingDaysForPercentage = $targetMonth === $currentMonth ? $workingDaysTillToday : $workingDays;

        // Calculate original expected hours (full month) - without grace time for display
        // Subtract holidays from working days
        $workingDaysMinusHolidays = $workingDays - $holidayDays;
        $expectedHours = $this->calculateExpectedHours($workingDaysMinusHolidays, false);
        
        // Calculate adjusted expected hours (after accounting for approved leaves, holidays, and absent days) - without grace time
        $adjustedWorkingDays = $workingDays - $onLeaveDays - $holidayDays - max(0, $absentDays);
        $expectedHoursAdjusted = $this->calculateExpectedHours($adjustedWorkingDays, false);
        
        // Calculate expected hours till today WITHOUT grace time, excluding holidays and leaves (for "Hours Completed So Far")
        // This should match the logic for "Monthly Expected Hours" but only for days up to today
        $workingDaysTillTodayExcludingHolidaysAndLeaves = 0;
        $currentMonth = Carbon::now()->format('Y-m');
        
        // Load leave dates from processed data for current month calculation
        $onLeaveDates = [];
        if (!empty($processedData) && is_array($processedData)) {
            foreach ($processedData as $record) {
                $date = is_array($record) && isset($record['date']) ? $record['date'] : null;
                if ($date && isset($record['status']) && $record['status'] === 'on_leave') {
                    $onLeaveDates[$date] = true;
                }
            }
        }
        
        if ($targetMonth === $currentMonth) {
            $startOfMonth = Carbon::now()->startOfMonth();
            $today = Carbon::now();
            $current = $startOfMonth->copy();
            
            while ($current->lte($today)) {
                if ($current->isWeekday()) {
                    $dateStr = $current->format('Y-m-d');
                    // Exclude holidays and leave days
                    if (!isset($holidaysMap[$dateStr]) && !isset($onLeaveDates[$dateStr])) {
                        $workingDaysTillTodayExcludingHolidaysAndLeaves++;
                    }
                }
                $current->addDay();
            }
        } else {
            // For past/future months, use the same calculation as expected_hours_adjusted
            // (working days minus holidays and leaves for the entire month)
            $workingDaysTillTodayExcludingHolidaysAndLeaves = $adjustedWorkingDays;
        }
        $expectedHoursTillTodayWithoutGrace = $this->calculateExpectedHours($workingDaysTillTodayExcludingHolidaysAndLeaves, false);
        
        // Calculate expected hours with grace time for full month (for "Monthly Expected Working Hours")
        // This deducts grace time from the expected hours and also subtracts holidays
        $expectedHoursWithGraceTime = $this->calculateExpectedHours($workingDaysMinusHolidays, true);
        
        // Calculate adjusted expected hours with grace time (after accounting for approved leaves, holidays, and absent days)
        // For leaves, holidays, and absent days, deduct (shift duration - grace time) per day from the expected hours with grace time
        // Note: onLeaveDays is now a decimal (0.5 for half-day, 1.0 for full-day), so we can use it directly
        $totalDaysToDeduct = $onLeaveDays + $holidayDays + max(0, $absentDays);
        if ($totalDaysToDeduct > 0 && $this->employeeShift) {
            // Get shift duration in minutes
            $shift = $this->employeeShift;
            $timeFromStr = $shift->time_from;
            $timeToStr = $shift->time_to;
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
            
            $timeToCopy = $timeTo->copy();
            if ($timeFrom->gt($timeTo)) {
                $timeToCopy->addDay();
            }
            
            $shiftMinutes = $timeFrom->diffInMinutes($timeToCopy);
            $graceTimePerDay = $this->getGraceTimePerDay($shift);
            
            // Deduct (shift duration - grace time) per leave/holiday day
            // Since onLeaveDays is now decimal (0.5 for half-day), this will correctly deduct half for half-day leaves
            $deductionPerDay = $shiftMinutes - $graceTimePerDay;
            $totalDeductionMinutes = $totalDaysToDeduct * $deductionPerDay;
            
            // Parse expected hours with grace time and subtract deduction
            $expectedHoursWithGraceTimeParts = explode(':', $expectedHoursWithGraceTime);
            $expectedHoursInt = (int)($expectedHoursWithGraceTimeParts[0] ?? 0);
            $expectedMinsInt = (int)($expectedHoursWithGraceTimeParts[1] ?? 0);
            $expectedTotalMinutes = ($expectedHoursInt * 60) + $expectedMinsInt;
            
            $adjustedTotalMinutes = $expectedTotalMinutes - $totalDeductionMinutes;
            $adjustedHours = floor($adjustedTotalMinutes / 60);
            $adjustedMins = $adjustedTotalMinutes % 60;
            $expectedHoursAdjustedWithGraceTime = sprintf('%d:%02d', $adjustedHours, $adjustedMins);
        } else {
            // No leaves, so adjusted = expected with grace time
            $expectedHoursAdjustedWithGraceTime = $expectedHoursWithGraceTime;
        }

        // Calculate short/excess hours: Total Hours Worked - Monthly Expected Hours (adjusted)
        // Use expected_hours_adjusted for comparison (after leaves and holidays, without grace time)
        $totalHoursWorkedMinutes = ($totalHours * 60) + $remainingMinutes;
        
        // Parse expected_hours_adjusted to get minutes
        $expectedHoursAdjustedParts = explode(':', $expectedHoursAdjusted);
        $expectedHoursAdjustedInt = (int)($expectedHoursAdjustedParts[0] ?? 0);
        $expectedMinsAdjustedInt = (int)($expectedHoursAdjustedParts[1] ?? 0);
        $expectedHoursAdjustedMinutes = ($expectedHoursAdjustedInt * 60) + $expectedMinsAdjustedInt;
        
        // Calculate difference (positive = excess, negative = short)
        $shortExcessMinutes = $totalHoursWorkedMinutes - $expectedHoursAdjustedMinutes;
        $shortExcessHours = floor(abs($shortExcessMinutes) / 60);
        $shortExcessMins = abs($shortExcessMinutes) % 60;
        
        // Format: negative values show with minus sign, positive values show without sign
        $shortExcessHoursFormatted = sprintf('%s%d:%02d', $shortExcessMinutes < 0 ? '-' : '', $shortExcessHours, $shortExcessMins);

        return [
            'working_days' => $workingDaysMinusHolidays, // Working days minus holidays
            'attended_days' => $attendedDays,
            'absent_days' => max(0, $absentDays), // Ensure non-negative
            'on_leave_days' => $onLeaveDays,
            'holiday_days' => $holidayDays,
            'attendance_percentage' => $workingDaysForPercentage > 0 ? round(($attendedDays / $workingDaysForPercentage) * 100, 1) : 0,
            'total_hours' => sprintf('%d:%02d', $totalHours, $remainingMinutes),
            'expected_hours' => $expectedHours, // Original expected hours (full month, without grace time, minus holidays)
            'expected_hours_adjusted' => $expectedHoursAdjusted, // Adjusted expected hours (after leaves and holidays, without grace time)
            'expected_hours_till_today' => $expectedHoursTillToday, // Expected hours till today (including grace time)
            'expected_hours_till_today_without_grace' => $expectedHoursTillTodayWithoutGrace, // Expected hours till today (without grace time, excluding holidays)
            'expected_hours_with_grace_time' => $expectedHoursWithGraceTime, // Full month expected hours with grace time (deducted, minus holidays)
            'expected_hours_adjusted_with_grace_time' => $expectedHoursAdjustedWithGraceTime, // Adjusted expected hours with grace time (after leaves and holidays)
            'late_days' => $lateDays,
            'total_break_time' => sprintf('%d:%02d', floor($totalBreakMinutes / 60), $totalBreakMinutes % 60), // Total break time in HH:MM format
            'total_non_allowed_break_time' => sprintf('%d:%02d', floor($totalNonAllowedBreakMinutes / 60), $totalNonAllowedBreakMinutes % 60), // Total non-allowed break time in HH:MM format
            'short_excess_hours' => $shortExcessHoursFormatted, // Short/excess hours (negative = short, positive = excess)
            'short_excess_minutes' => $shortExcessMinutes, // Raw minutes for conditional styling
        ];
    }

    public function updatedSelectedUserId()
    {
        if (!$this->canSwitchUsers) {
            $this->selectedUserId = null;
            return;
        }

        // Keep the selected month when user changes (don't reset to current month)
        // Reload attendance data when user filter changes (showing all employees)
        $this->loadAllEmployeesAttendance();
    }

    public function updatedSelectedMonth()
    {
        // Reload attendance data when month filter changes
        $this->loadAllEmployeesAttendance();
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
        $this->leaveDuration = 'full_day';
        $this->reason = '';
        
        // Load leave balance and options
        $this->loadLeaveSummary();
        $this->loadLeaveTypeOptions();
        
        // Calculate initial leave days
        $this->recalculateLeaveDays();
        
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
        // Check if balance is depleted
        if ($this->leaveBalanceDepleted) {
            session()->flash('error', __('You do not have sufficient leave balance to apply for leave.'));
            return;
        }

        // Validate the form
        $this->validate([
            'leaveType' => 'required|exists:leave_types,id',
            'leaveDuration' => 'required|string|in:full_day,half_day_morning,half_day_afternoon',
            'leaveFrom' => 'required|date',
            'leaveTo' => 'required|date|after_or_equal:leaveFrom',
            'reason' => 'nullable|string',
        ], [
            'leaveType.required' => __('Please select a leave type.'),
            'leaveType.exists' => __('Selected leave type is invalid.'),
            'leaveDuration.required' => __('Please select leave duration.'),
            'leaveDuration.in' => __('Invalid leave duration selected.'),
            'leaveFrom.required' => __('Leave from date is required.'),
            'leaveTo.required' => __('Leave to date is required.'),
            'leaveTo.after_or_equal' => __('Leave end date must be on or after start date.'),
        ]);

        $user = Auth::user();
        $employee = optional($user)->loadMissing('employee')->employee;

        if (! $employee) {
            throw ValidationException::withMessages([
                'leaveType' => __('No employee record found for this user.'),
            ]);
        }

        $leaveTypeId = (int) $this->leaveType;
        $leaveSetting = LeaveSetting::first();
        $autoApprove = (bool) optional($leaveSetting)->auto_approve_requests;

        $balance = EmployeeLeaveBalance::firstOrCreate(
            [
                'employee_id' => $employee->id,
                'leave_type_id' => $leaveTypeId,
            ],
            [
                'entitled' => 0,
                'carried_forward' => 0,
                'manual_adjustment' => 0,
                'used' => 0,
                'pending' => 0,
                'balance' => 0,
            ]
        );

        $availableBalance = (float) $balance->balance;
        $requestedDays = $this->calculateLeaveDays();

        // Validate calculated days
        if ($requestedDays <= 0) {
            throw ValidationException::withMessages([
                'leaveFrom' => __('Please select valid leave dates.'),
            ]);
        }

        if ($requestedDays > 365) {
            throw ValidationException::withMessages([
                'leaveTo' => __('Leave duration cannot exceed 365 days.'),
            ]);
        }

        if ($availableBalance <= 0 && $requestedDays > $availableBalance) {
            throw ValidationException::withMessages([
                'leaveType' => __('You have no leave balance remaining for this request.'),
            ]);
        }

        if ($requestedDays > $availableBalance) {
            throw ValidationException::withMessages([
                'leaveDays' => __('Requested leave exceeds your available balance (:balance days).', [
                    'balance' => number_format($availableBalance, 1),
                ]),
            ]);
        }

        $status = $autoApprove
            ? LeaveRequestModel::STATUS_APPROVED
            : LeaveRequestModel::STATUS_PENDING;

        DB::transaction(function () use (
            $employee,
            $user,
            $leaveTypeId,
            $requestedDays,
            $status,
            $autoApprove,
            $balance,
            $availableBalance
        ) {
            $request = LeaveRequestModel::create([
                'employee_id' => $employee->id,
                'requested_by' => $user->id,
                'leave_type_id' => $leaveTypeId,
                'start_date' => $this->leaveFrom,
                'end_date' => $this->leaveTo,
                'total_days' => $requestedDays,
                'duration' => $this->leaveDuration,
                'reason' => $this->reason,
                'status' => $status,
                'auto_approved' => $autoApprove,
                'balance_snapshot' => $availableBalance,
            ]);

            LeaveRequestEvent::create([
                'leave_request_id' => $request->id,
                'performed_by' => $user->id,
                'event_type' => 'created',
                'notes' => $this->reason ?: __('No reason provided.'),
                'attachment_path' => null,
            ]);

            if ($status === LeaveRequestModel::STATUS_APPROVED) {
                LeaveRequestEvent::create([
                    'leave_request_id' => $request->id,
                    'performed_by' => $user->id,
                    'event_type' => 'approved',
                    'notes' => __('Automatically approved based on leave settings.'),
                ]);

                $balance->used += $requestedDays;
            } else {
                $balance->pending += $requestedDays;
            }

            $balance->balance -= $requestedDays;
            $balance->save();
        });

        session()->flash('success', __('Leave request submitted successfully for :date', ['date' => $this->selectedDate]));
        
        // Close the modal and reset the form
        $this->closeLeaveRequestModal();
        
        // Reload attendance data and leave summary
        $this->loadUserAttendance();
        $this->loadLeaveSummary();
        
        // Dispatch toast notification
        $this->dispatch('show-toast', [
            'type' => 'success',
            'message' => __('Leave request submitted for :date', ['date' => $this->selectedDate])
        ]);
    }

    protected function calculateLeaveDays(): float
    {
        // For half-day leaves, always return 0.5
        if (in_array($this->leaveDuration, ['half_day_morning', 'half_day_afternoon'], true)) {
            return 0.5;
        }

        // If dates are not set, return 0
        if (! $this->leaveFrom || ! $this->leaveTo) {
            return 0.0;
        }

        try {
            $start = Carbon::parse($this->leaveFrom);
            $end = Carbon::parse($this->leaveTo);

            // Ensure end date is not before start date
            if ($end->lt($start)) {
                return 0.0;
            }

            // Calculate difference in days (inclusive of both start and end dates)
            $days = $start->diffInDays($end) + 1;

            return max(1.0, (float) $days);
        } catch (\Exception $e) {
            return 0.0;
        }
    }

    public function updatedLeaveDuration()
    {
        // Recalculate leave days based on duration and dates
        $this->recalculateLeaveDays();
    }

    public function updatedLeaveFrom()
    {
        $this->recalculateLeaveDays();
    }

    public function updatedLeaveTo()
    {
        $this->recalculateLeaveDays();
    }

    protected function recalculateLeaveDays(): void
    {
        $calculatedDays = $this->calculateLeaveDays();
        $this->leaveDays = number_format($calculatedDays, 1) . ' Working Day' . ($calculatedDays != 1 ? 's' : '');
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

    protected function loadLeaveSummary(): void
    {
        // Use the currently selected employee (from dropdown) or fall back to logged-in user's employee
        $employee = $this->employee;

        if (! $employee) {
            // Fallback to logged-in user's employee if no employee is selected
            $user = Auth::user();
            if ($user && $user->relationLoaded('employee')) {
                $employee = $user->employee;
            } else {
                $user?->loadMissing('employee');
                $employee = $user->employee ?? null;
            }
        }

        if (! $employee) {
            $this->leaveBalances = [];
            $this->leaveSummary = [
                'entitled' => 0.0,
                'used' => 0.0,
                'pending' => 0.0,
                'balance' => 0.0,
            ];
            $this->leaveBalanceDepleted = true;
            return;
        }

        $balances = EmployeeLeaveBalance::query()
            ->with('leaveType:id,name,code')
            ->where('employee_id', $employee->id)
            ->get();

        // Store balances per leave type
        $this->leaveBalances = $balances->map(function ($balance) {
            return [
                'leave_type_id' => $balance->leave_type_id,
                'leave_type_name' => $balance->leaveType?->name ?? __('Unknown'),
                'leave_type_code' => $balance->leaveType?->code,
                'entitled' => (float) $balance->entitled,
                'used' => (float) $balance->used,
                'pending' => (float) $balance->pending,
                'balance' => (float) $balance->balance,
            ];
        })->toArray();

        // Also keep aggregated summary for backward compatibility
        $this->leaveSummary = [
            'entitled' => (float) $balances->sum('entitled'),
            'used' => (float) $balances->sum('used'),
            'pending' => (float) $balances->sum('pending'),
            'balance' => (float) $balances->sum('balance'),
        ];

        $this->leaveBalanceDepleted = ($this->leaveSummary['balance'] ?? 0) <= 0;
    }

    protected function loadLeaveTypeOptions(): void
    {
        $this->leaveTypeOptions = LeaveType::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'code'])
            ->toArray();
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
        $this->missingEntryDateFrom = '';
        $this->missingEntryDateTo = '';
        $this->missingEntryCheckinTime = '';
        $this->missingEntryCheckoutTime = '';
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

        // Validate based on entry type
        if (in_array($this->missingEntryType, ['IN', 'OUT'])) {
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
        } else {
            $this->validate([
                'missingEntryType' => 'required|in:edit_checkin_checkout,edit_checkin_checkout_exclude_breaks',
                'missingEntryDateFrom' => 'required|date',
                'missingEntryDateTo' => 'required|date|after_or_equal:missingEntryDateFrom',
                'missingEntryCheckinTime' => 'required',
                'missingEntryCheckoutTime' => 'required',
                'missingEntryNotes' => 'nullable|string|max:500',
            ], [
                'missingEntryType.required' => 'Entry type is required.',
                'missingEntryType.in' => 'Invalid entry type.',
                'missingEntryDateFrom.required' => 'Date From is required.',
                'missingEntryDateTo.required' => 'Date To is required.',
                'missingEntryDateTo.after_or_equal' => 'Date To must be after or equal to Date From.',
                'missingEntryCheckinTime.required' => 'Checkin Time is required.',
                'missingEntryCheckoutTime.required' => 'Checkout Time is required.',
            ]);
        }

        if (!$this->punchCode) {
            session()->flash('error', 'Punch code not found. Please contact HR.');
            return;
        }

        try {
            if (in_array($this->missingEntryType, ['IN', 'OUT'])) {
                // Original single entry logic
            $dateTime = Carbon::parse($this->missingEntryDate . ' ' . $this->missingEntryTime);

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
                if ($e->getCode() === '23000' || str_contains($e->getMessage(), 'Duplicate entry')) {
                    session()->flash('error', 'An entry already exists for this exact date and time. Please choose a different time.');
                    return;
                }
                    throw $e;
            }

            session()->flash('success', 'Missing entry added successfully.');
            } else {
                // New logic for edit checkin/checkout
                $dateFrom = Carbon::parse($this->missingEntryDateFrom);
                $dateTo = Carbon::parse($this->missingEntryDateTo);
                $checkinTime = Carbon::parse($this->missingEntryCheckinTime);
                $checkoutTime = Carbon::parse($this->missingEntryCheckoutTime);
                
                $entriesCreated = 0;
                $errors = [];

                if ($this->missingEntryType === 'edit_checkin_checkout') {
                    // For each day in the date range, create checkin and checkout entries
                    $current = $dateFrom->copy();
                    while ($current->lte($dateTo)) {
                        $dateStr = $current->format('Y-m-d');
                        
                        // Create checkin entry for this day
                        $checkinDateTime = Carbon::parse($dateStr . ' ' . $checkinTime->format('H:i:s'));
                        try {
                            DeviceAttendance::create([
                                'punch_code' => $this->punchCode,
                                'device_ip' => '0.0.0.0',
                                'device_type' => 'IN',
                                'punch_time' => $checkinDateTime,
                                'punch_type' => 'check_in',
                                'status' => null,
                                'verify_mode' => null,
                                'is_processed' => false,
                                'is_manual_entry' => true,
                                'updated_by' => Auth::id(),
                                'notes' => $this->missingEntryNotes,
                            ]);
                            $entriesCreated++;
                        } catch (\Illuminate\Database\QueryException $e) {
                            if (!($e->getCode() === '23000' || str_contains($e->getMessage(), 'Duplicate entry'))) {
                                $errors[] = "Failed to create checkin entry for {$dateStr}: " . $e->getMessage();
                            }
                        }
                        
                        // Create checkout entry for this day
                        $checkoutDateTime = Carbon::parse($dateStr . ' ' . $checkoutTime->format('H:i:s'));
                        // If checkout time is earlier than checkin time, it's likely next day
                        if ($checkoutDateTime->lt($checkinDateTime)) {
                            $checkoutDateTime->addDay();
                        }
                        
                        try {
                            DeviceAttendance::create([
                                'punch_code' => $this->punchCode,
                                'device_ip' => '0.0.0.0',
                                'device_type' => 'OUT',
                                'punch_time' => $checkoutDateTime,
                                'punch_type' => 'check_out',
                                'status' => null,
                                'verify_mode' => null,
                                'is_processed' => false,
                                'is_manual_entry' => true,
                                'updated_by' => Auth::id(),
                                'notes' => $this->missingEntryNotes,
                            ]);
                            $entriesCreated++;
                        } catch (\Illuminate\Database\QueryException $e) {
                            if (!($e->getCode() === '23000' || str_contains($e->getMessage(), 'Duplicate entry'))) {
                                $errors[] = "Failed to create checkout entry for {$dateStr}: " . $e->getMessage();
                            }
                        }
                        
                        $current->addDay();
                    }
                } else if ($this->missingEntryType === 'edit_checkin_checkout_exclude_breaks') {
                    // Only create checkin for first day and checkout for last day
                    $checkinDateTime = Carbon::parse($dateFrom->format('Y-m-d') . ' ' . $checkinTime->format('H:i:s'));
                    $checkoutDateTime = Carbon::parse($dateTo->format('Y-m-d') . ' ' . $checkoutTime->format('H:i:s'));
                    
                    // If checkout time is earlier than checkin time, it's likely next day
                    if ($checkoutDateTime->lt($checkinDateTime)) {
                        $checkoutDateTime->addDay();
                    }
                    
                    // Create checkin entry for first day
                    try {
                        DeviceAttendance::create([
                            'punch_code' => $this->punchCode,
                            'device_ip' => '0.0.0.0',
                            'device_type' => 'IN',
                            'punch_time' => $checkinDateTime,
                            'punch_type' => 'check_in',
                            'status' => null,
                            'verify_mode' => null,
                            'is_processed' => false,
                            'is_manual_entry' => true,
                            'updated_by' => Auth::id(),
                            'notes' => $this->missingEntryNotes . ' (Exclude Breaks)',
                        ]);
                        $entriesCreated++;
                    } catch (\Illuminate\Database\QueryException $e) {
                        if (!($e->getCode() === '23000' || str_contains($e->getMessage(), 'Duplicate entry'))) {
                            $errors[] = "Failed to create checkin entry: " . $e->getMessage();
                        }
                    }
                    
                    // Create checkout entry for last day
                    try {
                        DeviceAttendance::create([
                            'punch_code' => $this->punchCode,
                            'device_ip' => '0.0.0.0',
                            'device_type' => 'OUT',
                            'punch_time' => $checkoutDateTime,
                            'punch_type' => 'check_out',
                            'status' => null,
                            'verify_mode' => null,
                            'is_processed' => false,
                            'is_manual_entry' => true,
                            'updated_by' => Auth::id(),
                            'notes' => $this->missingEntryNotes . ' (Exclude Breaks)',
                        ]);
                        $entriesCreated++;
                    } catch (\Illuminate\Database\QueryException $e) {
                        if (!($e->getCode() === '23000' || str_contains($e->getMessage(), 'Duplicate entry'))) {
                            $errors[] = "Failed to create checkout entry: " . $e->getMessage();
                        }
                    }
                }

                if (!empty($errors)) {
                    session()->flash('error', 'Some entries could not be created: ' . implode('; ', $errors));
                } else {
                    session()->flash('success', "Successfully created {$entriesCreated} entry/entries.");
                }
            }
            
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
            ->where('is_manual_entry', true)
            ->where(function($q) {
                $q->whereNull('verify_mode')
                  ->orWhere('verify_mode', '!=', 2);
            });
        
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

    /**
     * Format short/excess hours for CSV export (Excel-compatible)
     */
    private function formatShortExcessHoursForCsv($shortExcessHours, $shortExcessMinutes)
    {
        if ($shortExcessMinutes == 0) {
            return '0:00';
        }
        
        // Parse the time format (e.g., "-17:38" or "12:48")
        $isNegative = strpos($shortExcessHours, '-') === 0;
        $timeValue = ltrim($shortExcessHours, '-');
        
        if ($isNegative) {
            return 'Short: ' . $timeValue;
        } else {
            return 'Excess: ' . $timeValue;
        }
    }

    /**
     * Export attendance report to CSV
     */
    public function exportToCsv()
    {
        if (empty($this->employeesStats)) {
            session()->flash('error', 'No data available to export.');
            return;
        }

        // Apply the same filtering as the view
        $filteredEmployees = collect($this->employeesStats);
        if (!empty($this->employeeSearchTerm)) {
            $searchTerm = strtolower($this->employeeSearchTerm);
            $filteredEmployees = $filteredEmployees->filter(function($employeeData) use ($searchTerm) {
                $emp = $employeeData['employee'];
                $fullName = strtolower(trim(($emp->first_name ?? '') . ' ' . ($emp->last_name ?? '')));
                $employeeCode = strtolower($emp->employee_code ?? '');
                return str_contains($fullName, $searchTerm) || str_contains($employeeCode, $searchTerm);
            });
        }

        // Get the month label for filename
        $monthLabel = $this->selectedMonth 
            ? Carbon::createFromFormat('Y-m', $this->selectedMonth)->format('F Y')
            : Carbon::now()->format('F Y');
        
        $filename = 'attendance_report_' . str_replace(' ', '_', strtolower($monthLabel)) . '_' . date('Y-m-d_His') . '.csv';

        // Create CSV content
        $headers = [
            'Employee Code',
            'Employee Name',
            'Working Days',
            'Present Days',
            'Leaves',
            'Absent Days',
            'Late Days',
            'Total Break Time',
            'Total Non-Allowed Break Time',
            'Holidays',
            'Total Hours Worked',
            'Monthly Expected Hours',
            'Short/Excess Hours',
        ];

        $rows = [];
        $rows[] = $headers;

        foreach ($filteredEmployees as $employeeData) {
            $emp = $employeeData['employee'];
            $stats = $employeeData['stats'];
            
            $expectedHours = ($stats['on_leave_days'] ?? 0) > 0 || ($stats['holiday_days'] ?? 0) > 0 || ($stats['absent_days'] ?? 0) > 0
                ? ($stats['expected_hours_adjusted'] ?? '0:00')
                : ($stats['expected_hours'] ?? '0:00');

            $rows[] = [
                $emp->employee_code ?? 'N/A',
                trim(($emp->first_name ?? '') . ' ' . ($emp->last_name ?? '')),
                $stats['working_days'] ?? 0,
                $stats['attended_days'] ?? 0,
                $stats['on_leave_days'] ?? 0,
                $stats['absent_days'] ?? 0,
                $stats['late_days'] ?? 0,
                $stats['total_break_time'] ?? '0:00',
                $stats['total_non_allowed_break_time'] ?? '0:00',
                $stats['holiday_days'] ?? 0,
                $stats['total_hours'] ?? '0:00',
                $expectedHours,
                $this->formatShortExcessHoursForCsv($stats['short_excess_hours'] ?? '0:00', $stats['short_excess_minutes'] ?? 0),
            ];
        }

        // Generate CSV content
        $csvContent = '';
        foreach ($rows as $row) {
            $csvContent .= implode(',', array_map(function($field) {
                // Escape fields that contain commas, quotes, or newlines
                $field = str_replace('"', '""', $field);
                if (strpos($field, ',') !== false || strpos($field, '"') !== false || strpos($field, "\n") !== false) {
                    return '"' . $field . '"';
                }
                return $field;
            }, $row)) . "\n";
        }

        // Return download response
        return response()->streamDownload(function() use ($csvContent) {
            echo $csvContent;
        }, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
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

        return view('livewire.attendance.report', [
            'canViewOtherUsers' => $this->canViewOtherUsers,
            'canSwitchUsers' => $this->canSwitchUsers,
        ])
            ->layout('components.layouts.app');
    }
}