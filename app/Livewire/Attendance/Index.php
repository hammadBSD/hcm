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
    public $selectedMonth = '';
    public $attendanceData = [];
    public $attendanceStats = [];
    public $punchCode = null;
    public $employee = null;
    public $availableMonths = [];
    
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

    public function mount()
    {
        $this->currentMonth = Carbon::now()->format('F Y');
        $this->selectedMonth = ''; // Default to current month
        $this->loadUserAttendance();
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

        // Load available months after getting punch code
        $this->loadAvailableMonths();

        // Determine which month to load
        $targetMonth = $this->selectedMonth ?: Carbon::now()->format('Y-m');
        
        // Get attendance data for the selected month
        $startOfMonth = Carbon::createFromFormat('Y-m', $targetMonth)->startOfMonth();
        $endOfMonth = Carbon::createFromFormat('Y-m', $targetMonth)->endOfMonth();

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
        // Determine which month to process
        $targetMonth = $this->selectedMonth ?: Carbon::now()->format('Y-m');
        $startOfMonth = Carbon::createFromFormat('Y-m', $targetMonth)->startOfMonth();
        $endOfMonth = Carbon::createFromFormat('Y-m', $targetMonth)->endOfMonth();
        
        $processedData = [];
        
        // Group records by date first
        $groupedRecords = [];
        foreach ($records as $record) {
            $date = Carbon::parse($record->punch_time)->format('Y-m-d');
            $groupedRecords[$date][] = $record;
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
            
            // Determine day status
            $status = 'absent'; // Default
            if ($current->isWeekend()) {
                $status = 'off';
            } elseif (!empty($dayRecords)) {
                $status = 'present';
            }
            
            $processedData[$date] = [
                'date' => $date,
                'formatted_date' => $current->format('M d, Y'),
                'day_name' => $current->format('l'),
                'check_in' => null,
                'check_out' => null,
                'total_hours' => null,
                'status' => $status
            ];
            
            // Process attendance records for this day if they exist
            if (!empty($dayRecords)) {
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
                
                $processedData[$date]['check_in'] = $firstCheckIn ? $firstCheckIn->format('h:i A') : null;
                $processedData[$date]['check_out'] = $lastCheckOut ? $lastCheckOut->format('h:i A') : null;
                
                // Calculate total hours if both check-in and check-out are available
                if ($firstCheckIn && $lastCheckOut) {
                    $totalMinutes = $lastCheckOut->diffInMinutes($firstCheckIn);
                    $hours = floor($totalMinutes / 60);
                    $minutes = $totalMinutes % 60;
                    $processedData[$date]['total_hours'] = sprintf('%d:%02d', $hours, $minutes);
                }
            }
            
            // Move to next day
            $current->addDay();
        }
        
        // Apply sorting based on sortBy and sortDirection
        $this->sortAttendanceData($processedData);
        
        return array_values($processedData);
    }

    private function calculateAttendanceStats($records)
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
            case 'status':
                uasort($data, function($a, $b) {
                    $statusOrder = ['present' => 1, 'off' => 2, 'absent' => 3];
                    $aOrder = $statusOrder[$a['status']] ?? 4;
                    $bOrder = $statusOrder[$b['status']] ?? 4;
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

    public function render()
    {
        return view('livewire.attendance.index')
            ->layout('components.layouts.app');
    }
}
