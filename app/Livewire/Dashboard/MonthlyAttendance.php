<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\DeviceAttendance;
use App\Models\Employee;
use App\Models\Shift;
use Carbon\Carbon;

class MonthlyAttendance extends Component
{
    public $dailyStats = [];
    public $currentMonth = '';
    public $selectedMonth = '';
    public $availableMonths = [];

    public function mount()
    {
        $this->currentMonth = Carbon::now()->format('F Y');
        $this->selectedMonth = Carbon::now()->format('Y-m'); // Default to current month
        $this->loadAvailableMonths();
        $this->calculateDailyAttendance();
    }

    public function loadAvailableMonths()
    {
        // Get current logged-in user
        $user = Auth::user();
        
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

        // Get all months that have attendance data (need to load employee first)
        $employee = Employee::where('user_id', $user->id)->first();
        if (!$employee || !$employee->punch_code) {
            $this->availableMonths = [];
            return;
        }
        
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
    }

    public function updatedSelectedMonth()
    {
        $this->calculateDailyAttendance();
        $this->dispatch('monthly-attendance-updated');
    }

    private function calculateDailyAttendance()
    {
        // Get current logged-in user
        $user = Auth::user();
        
        if (!$user) {
            $this->dailyStats = [];
            return;
        }
        
        // Get the employee record for this user with shift
        $employee = Employee::where('user_id', $user->id)->with('shift')->first();
        
        if (!$employee || !$employee->punch_code) {
            $this->dailyStats = [];
            return;
        }
        
        // Get employee's shift for shift-based logic
        $employeeShift = $employee->shift;
        
        // Determine which month to load
        $targetMonth = $this->selectedMonth ?: Carbon::now()->format('Y-m');
        $startOfMonth = Carbon::createFromFormat('Y-m', $targetMonth)->startOfMonth();
        $endOfMonth = Carbon::createFromFormat('Y-m', $targetMonth)->endOfMonth();
        $today = Carbon::now();
        
        // For current month, only count days up to today
        // For previous months, show all days
        $endDate = ($targetMonth === $today->format('Y-m')) ? $today->copy() : $endOfMonth->copy();
        
        $dailyData = [];
        
        // Iterate through each day of the month (up to today)
        $current = $startOfMonth->copy();
        
        while ($current->lte($endDate)) {
            $dateKey = $current->format('Y-m-d');
            $dayNumber = $current->format('d'); // 01, 02, etc.
            
            // Determine shift characteristics
            $isOvernight = false;
            $shiftStartsInPM = false;
            
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
            
            // Check if it's a weekend (off day)
            if ($current->isWeekend()) {
                // For off days with PM-start shifts, check if there are AM check-outs from previous shift
                // If only AM check-outs exist, don't count them as attendance for this off day
                $hasValidAttendance = false;
                if ($employeeShift && $isOvernight && $shiftStartsInPM) {
                    // Check if there's a PM check-in on this off day (shouldn't happen, but check anyway)
                    $pmCheckIn = DeviceAttendance::where('punch_code', $employee->punch_code)
                        ->whereDate('punch_time', $dateKey)
                        ->where('device_type', 'IN')
                        ->whereRaw('HOUR(punch_time) >= 12')
                        ->exists();
                    $hasValidAttendance = $pmCheckIn;
                }
                
                $dailyData[] = [
                    'date' => $dateKey,
                    'label' => $dayNumber . '-' . $current->format('M'),
                    'present' => 0,
                    'absent' => 0,
                    'off_days' => 1,
                    'total' => 1,
                    'status' => 'off'
                ];
            } else {
                // It's a weekday - use shift-based logic to determine attendance
                $hasValidAttendance = false;
                
                if ($employeeShift && $isOvernight && $shiftStartsInPM) {
                    // For PM-start overnight shifts, only count PM check-ins as valid attendance
                    $pmCheckIn = DeviceAttendance::where('punch_code', $employee->punch_code)
                        ->whereDate('punch_time', $dateKey)
                        ->where('device_type', 'IN')
                        ->whereRaw('HOUR(punch_time) >= 12')
                        ->exists();
                    $hasValidAttendance = $pmCheckIn;
                } else {
                    // For regular shifts or AM-start overnight shifts, any attendance record counts
                    $hasAttendance = DeviceAttendance::where('punch_code', $employee->punch_code)
                        ->whereDate('punch_time', $dateKey)
                        ->exists();
                    $hasValidAttendance = $hasAttendance;
                }
                
                $dailyData[] = [
                    'date' => $dateKey,
                    'label' => $dayNumber . '-' . $current->format('M'),
                    'present' => $hasValidAttendance ? 1 : 0,
                    'absent' => $hasValidAttendance ? 0 : 1,
                    'off_days' => 0,
                    'total' => 1,
                    'status' => $hasValidAttendance ? 'present' : 'absent'
                ];
            }
            
            $current->addDay();
        }
        
        $this->dailyStats = $dailyData;
    }

    public function render()
    {
        return view('livewire.dashboard.monthly-attendance');
    }
}
