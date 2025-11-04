<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\DeviceAttendance;
use App\Models\Employee;
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
        
        // Get the employee record for this user
        $employee = Employee::where('user_id', $user->id)->first();
        
        if (!$employee || !$employee->punch_code) {
            $this->dailyStats = [];
            return;
        }
        
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
            
            // Check if it's a weekend (off day)
            if ($current->isWeekend()) {
                $dailyData[] = [
                    'date' => $dateKey,
                    'label' => $dayNumber . '-' . $current->format('M'),
                    'present' => 0,
                    'absent' => 0,
                    'off_days' => 1, // 1 for the user's off day
                    'total' => 1,
                    'status' => 'off'
                ];
            } else {
                // It's a weekday - check if user has attendance for this day
                $hasAttendance = DeviceAttendance::where('punch_code', $employee->punch_code)
                    ->whereDate('punch_time', $dateKey)
                    ->exists();
                
                $dailyData[] = [
                    'date' => $dateKey,
                    'label' => $dayNumber . '-' . $current->format('M'),
                    'present' => $hasAttendance ? 1 : 0,
                    'absent' => $hasAttendance ? 0 : 1,
                    'off_days' => 0,
                    'total' => 1,
                    'status' => $hasAttendance ? 'present' : 'absent'
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
