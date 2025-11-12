<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\DeviceAttendance;
use App\Models\Employee;
use App\Models\Shift;
use App\Models\Constant;
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
            ->with(['shift', 'department.shift'])
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
        
        // For current month, only count days up to today
        $endDate = ($targetMonth === $today->format('Y-m')) ? $today->copy() : $endOfMonth->copy();
        
        // Get all attendance records for this month
        $records = DeviceAttendance::where('punch_code', $employee->punch_code)
            ->whereBetween('punch_time', [
                $startOfMonth->format('Y-m-d 00:00:00'),
                $endDate->format('Y-m-d 23:59:59')
            ])
            ->orderBy('punch_time')
            ->get();
        
        // Group records by date
        $groupedRecords = [];
        foreach ($records as $record) {
            $date = Carbon::parse($record->punch_time)->format('Y-m-d');
            $groupedRecords[$date][] = $record;
        }
        
        $dailyData = [];
        $current = $startOfMonth->copy();
        
        while ($current->lte($endDate)) {
            $dateKey = $current->format('Y-m-d');
            $dayNumber = $current->format('d');
            $dayRecords = $groupedRecords[$dateKey] ?? [];
            
            // Determine shift characteristics
            $isOvernight = false;
            $shiftStartsInPM = false;
            $timeFrom = null;
            $timeTo = null;
            
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
            }
            
            // Initialize day data
            $checkIn = null;
            $checkOut = null;
            $totalHours = null;
            $hoursDecimal = 0; // For bar height
            $isLate = false;
            $status = 'absent';
            
            // Check if it's a weekend
            if ($current->isWeekend()) {
                $status = 'off';
                $dailyData[] = [
                    'date' => $dateKey,
                    'label' => $dayNumber . '-' . $current->format('M'),
                    'present' => 0,
                    'absent' => 0,
                    'off_days' => 0,
                    'late' => 0,
                    'hours' => 0,
                    'status' => 'off',
                    'check_in' => null,
                    'check_out' => null,
                    'total_hours' => null,
                    'is_late' => false,
                ];
                $current->addDay();
                continue;
            }
            
            // Process attendance records
            if (!empty($dayRecords)) {
                // Sort records chronologically
                usort($dayRecords, function($a, $b) {
                    return Carbon::parse($a->punch_time)->timestamp - Carbon::parse($b->punch_time)->timestamp;
                });
                
                // Get first check-in and last check-out
                $checkIns = [];
                $checkOuts = [];
                
                    foreach ($dayRecords as $record) {
                    $recordTime = Carbon::parse($record->punch_time);
                    if ($record->device_type === 'IN') {
                        // For PM-start shifts, only count PM check-ins
                        if ($employeeShift && $isOvernight && $shiftStartsInPM) {
                            if ($recordTime->hour >= 12) {
                                $checkIns[] = $recordTime;
                            }
                        } else {
                            $checkIns[] = $recordTime;
                        }
                    } elseif ($record->device_type === 'OUT') {
                        // For PM-start shifts, exclude AM check-outs on current day (they belong to previous shift)
                        if ($employeeShift && $isOvernight && $shiftStartsInPM) {
                            if ($recordTime->hour >= 12) {
                                $checkOuts[] = $recordTime; // Only PM check-outs on current day
                            }
                        } else {
                            $checkOuts[] = $recordTime;
                        }
                    }
                }
                
                // For PM-start overnight shifts, include next day's AM check-outs
                if ($employeeShift && $isOvernight && $shiftStartsInPM && !empty($checkIns)) {
                    $nextDate = $current->copy()->addDay()->format('Y-m-d');
                    $nextDayRecords = $groupedRecords[$nextDate] ?? [];
                    foreach ($nextDayRecords as $record) {
                        if ($record->device_type === 'OUT') {
                            $checkOutTime = Carbon::parse($record->punch_time);
                            // Include all AM check-outs (before 12 PM) - they belong to this shift
                            if ($checkOutTime->hour < 12) {
                                $checkOuts[] = $checkOutTime;
                            }
                        }
                    }
                }
                
                if (!empty($checkIns)) {
                    $checkIn = $checkIns[0];
                }
                if (!empty($checkOuts)) {
                    usort($checkOuts, function($a, $b) {
                        return $b->timestamp - $a->timestamp;
                    });
                    $checkOut = $checkOuts[0];
                }
                
                // Calculate total hours if both check-in and check-out exist
                if ($checkIn && $checkOut) {
                    $totalMinutes = $checkIn->diffInMinutes($checkOut);
                    $hours = floor($totalMinutes / 60);
                    $minutes = $totalMinutes % 60;
                    $totalHours = sprintf('%d:%02d', $hours, $minutes);
                    $hoursDecimal = $hours + ($minutes / 60);
                } else {
                    $totalHours = 'N/A';
                }
                
                // Check if late or early (if shift exists)
                if ($employeeShift && $checkIn && $timeFrom) {
                    // For PM-start overnight shifts, expected check-in is on the same day
                    $expectedCheckIn = Carbon::parse($dateKey)->setTime(
                        $timeFrom->hour,
                        $timeFrom->minute,
                        $timeFrom->second
                    );
                    
                    // For PM-start shifts, if check-in is in AM, it belongs to previous day
                    // So we need to compare with the actual check-in time on the correct date
                    $gracePeriod = $this->getEffectiveGracePeriodLateIn($employeeShift);
                    $gracePeriodTime = $expectedCheckIn->copy()->addMinutes($gracePeriod);
                    
                    $isLate = false;
                    $isEarly = false;
                    
                    // Check if late - compare checkIn time with expected time + grace period
                    if ($checkIn->gt($gracePeriodTime)) {
                        $isLate = true;
                    }
                    
                    // Check if early (if check-out exists)
                    if ($checkOut && $timeTo) {
                        // Determine expected check-out time
                        if ($isOvernight) {
                            // For overnight shifts, check-out is on next day
                            $expectedCheckOut = Carbon::parse($dateKey)->addDay()->setTime(
                                $timeTo->hour,
                                $timeTo->minute,
                                $timeTo->second
                            );
                        } else {
                            // For regular shifts, check-out is on same day
                            $expectedCheckOut = Carbon::parse($dateKey)->setTime(
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
                    
                    // Set status based on late/early
                    if ($isLate && $isEarly) {
                        $status = 'present_late_early';
                    } elseif ($isLate) {
                        $status = 'present_late';
                    } elseif ($isEarly) {
                        $status = 'present_early';
                    } else {
                        $status = 'present';
                    }
                } else {
                    $status = 'present';
                }
            }
            
            $dailyData[] = [
                'date' => $dateKey,
                'label' => $dayNumber . '-' . $current->format('M'),
                'hours' => $hoursDecimal, // For bar height
                'status' => $status,
                'check_in' => $checkIn ? $checkIn->format('h:i A') : null,
                'check_out' => $checkOut ? $checkOut->format('h:i A') : null,
                'total_hours' => $totalHours,
                'is_late' => $isLate,
                'is_early' => isset($isEarly) ? $isEarly : false,
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
