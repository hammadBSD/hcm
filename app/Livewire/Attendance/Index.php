<?php

namespace App\Livewire\Attendance;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\DeviceAttendance;
use App\Models\Employee;
use Carbon\Carbon;

class Index extends Component
{
    public $currentMonth = '';
    public $attendanceData = [];
    public $attendanceStats = [];
    public $punchCode = null;
    public $employee = null;

    public function mount()
    {
        $this->currentMonth = Carbon::now()->format('F Y');
        $this->loadUserAttendance();
    }

    public function loadUserAttendance()
    {
        // Get the logged-in user
        $user = Auth::user();
        
        if (!$user) {
            return;
        }

        // Find the employee record for this user
        $this->employee = Employee::where('user_id', $user->id)->first();
        
        if (!$this->employee) {
            return;
        }

        // Get the punch code
        $this->punchCode = $this->employee->punch_code;
        
        if (!$this->punchCode) {
            return;
        }

        // Get current month attendance data
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        $attendanceRecords = DeviceAttendance::where('punch_code', $this->punchCode)
            ->whereBetween('punch_time', [$startOfMonth, $endOfMonth])
            ->orderBy('punch_time', 'desc')
            ->get();

        // Process attendance data
        $this->attendanceData = $this->processAttendanceData($attendanceRecords);
        
        // Calculate statistics
        $this->attendanceStats = $this->calculateAttendanceStats($attendanceRecords);
    }

    private function processAttendanceData($records)
    {
        $processedData = [];
        
        // Group records by date first
        $groupedRecords = [];
        foreach ($records as $record) {
            $date = Carbon::parse($record->punch_time)->format('Y-m-d');
            $groupedRecords[$date][] = $record;
        }
        
        // Process each day
        foreach ($groupedRecords as $date => $dayRecords) {
            // Sort records by punch_time to get chronological order
            usort($dayRecords, function($a, $b) {
                return Carbon::parse($a->punch_time)->timestamp - Carbon::parse($b->punch_time)->timestamp;
            });
            
            $checkIns = [];
            $checkOuts = [];
            
            // Separate check-ins and check-outs
            foreach ($dayRecords as $record) {
                if ($record->device_type === 'IN' || $record->punch_type === 'IN') {
                    $checkIns[] = Carbon::parse($record->punch_time);
                } elseif ($record->device_type === 'OUT' || $record->punch_type === 'OUT') {
                    $checkOuts[] = Carbon::parse($record->punch_time);
                }
            }
            
            // Get first check-in and last check-out of the day
            $firstCheckIn = !empty($checkIns) ? $checkIns[0] : null;
            $lastCheckOut = !empty($checkOuts) ? end($checkOuts) : null;
            
            $processedData[$date] = [
                'date' => $date,
                'formatted_date' => Carbon::parse($date)->format('M d, Y'),
                'day_name' => Carbon::parse($date)->format('l'),
                'check_in' => $firstCheckIn ? $firstCheckIn->format('h:i A') : null,
                'check_out' => $lastCheckOut ? $lastCheckOut->format('h:i A') : null,
                'total_hours' => null,
                'status' => 'absent'
            ];
            
            // Calculate total hours if both check-in and check-out are available
            if ($firstCheckIn && $lastCheckOut) {
                $totalMinutes = $lastCheckOut->diffInMinutes($firstCheckIn);
                $hours = floor($totalMinutes / 60);
                $minutes = $totalMinutes % 60;
                $processedData[$date]['total_hours'] = sprintf('%d:%02d', $hours, $minutes);
                $processedData[$date]['status'] = 'present';
            } elseif ($firstCheckIn) {
                // Only check-in available (still working or forgot to check out)
                $processedData[$date]['status'] = 'present';
            }
        }
        
        // Sort by date descending (most recent first)
        krsort($processedData);
        
        return array_values($processedData);
    }

    private function calculateAttendanceStats($records)
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();
        
        // Get all working days in the month (excluding weekends)
        $workingDays = 0;
        $current = $startOfMonth->copy();
        
        while ($current->lte($endOfMonth)) {
            if ($current->isWeekday()) {
                $workingDays++;
            }
            $current->addDay();
        }

        // Count unique days with attendance
        $attendedDays = $records->groupBy(function ($record) {
            return Carbon::parse($record->punch_time)->format('Y-m-d');
        })->count();

        // Calculate total working hours using same logic as processAttendanceData
        $totalMinutes = 0;
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
            
            $checkIns = [];
            $checkOuts = [];
            
            // Separate check-ins and check-outs
            foreach ($dayRecords as $record) {
                if ($record->device_type === 'IN' || $record->punch_type === 'IN') {
                    $checkIns[] = Carbon::parse($record->punch_time);
                } elseif ($record->device_type === 'OUT' || $record->punch_type === 'OUT') {
                    $checkOuts[] = Carbon::parse($record->punch_time);
                }
            }
            
            // Get first check-in and last check-out of the day
            $firstCheckIn = !empty($checkIns) ? $checkIns[0] : null;
            $lastCheckOut = !empty($checkOuts) ? end($checkOuts) : null;
            
            if ($firstCheckIn && $lastCheckOut) {
                $totalMinutes += $lastCheckOut->diffInMinutes($firstCheckIn);
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
            'expected_hours' => sprintf('%d:%02d', $workingDays * 8, 0), // Assuming 8 hours per day
        ];
    }

    public function render()
    {
        return view('livewire.attendance.index')
            ->layout('components.layouts.app');
    }
}
