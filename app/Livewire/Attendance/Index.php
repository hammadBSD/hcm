<?php

namespace App\Livewire\Attendance;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\DeviceAttendance;
use App\Models\Employee;
use App\Models\User;
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
    public $selectedUserId = null;
    public $availableUsers = [];
    public $userSearchTerm = '';
    
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
        $this->loadAvailableUsers();
        $this->loadUserAttendance();
    }
    
    public function loadAvailableUsers()
    {
        // Get all employees with punch codes and their associated users
        $employees = Employee::whereNotNull('punch_code')
            ->whereNotNull('user_id')
            ->with('user:id,name,email')
            ->get();
        
        $this->availableUsers = $employees->map(function($employee) {
            $user = $employee->user;
            return [
                'id' => $user->id,
                'name' => $user->name ?? ($employee->first_name . ' ' . $employee->last_name),
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

    public function loadUserAttendance()
    {
        // Determine which user to load attendance for
        // If selectedUserId is null or empty, use current logged-in user
        $userId = !empty($this->selectedUserId) ? $this->selectedUserId : Auth::id();
        
        if (!$userId) {
            return;
        }

        // Find the employee record for this user
        $this->employee = Employee::where('user_id', $userId)->first();
        
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
                'breaks' => '0 (0h 0m total)',
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
                
                // Separate check-ins and check-outs (rely primarily on device_type)
                foreach ($dayRecords as $record) {
                    if ($record->device_type === 'IN') {
                        $checkIns[] = Carbon::parse($record->punch_time);
                    } elseif ($record->device_type === 'OUT') {
                        $checkOuts[] = Carbon::parse($record->punch_time);
                    }
                }
                
                // Get first check-in and last check-out of the day
                $firstCheckIn = !empty($checkIns) ? $checkIns[0] : null;
                $lastCheckOut = !empty($checkOuts) ? end($checkOuts) : null;
                
                $processedData[$date]['check_in'] = $firstCheckIn ? $firstCheckIn->format('h:i A') : null;
                $processedData[$date]['check_out'] = $lastCheckOut ? $lastCheckOut->format('h:i A') : null;
                
                // Calculate total hours and breaks using the correct logic
                $dayTotalMinutes = 0;
                $breaksCount = 0;
                $totalBreakMinutes = 0;
                
                if (!empty($dayRecords)) {
                    $sortedRecords = collect($dayRecords)->sortBy('punch_time');
                    
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
                        $processedData[$date]['total_hours'] = 'N/A';
                    } elseif ($dayTotalMinutes > 0) {
                        $hours = floor($dayTotalMinutes / 60);
                        $minutes = $dayTotalMinutes % 60;
                        $processedData[$date]['total_hours'] = sprintf('%d:%02d', $hours, $minutes);
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

    private function getBreakDetails($sortedRecords)
    {
        $breakDetails = [];
        $lastCheckOut = null;
        
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
                        'start' => $lastCheckOut->format('h:i A'),
                        'end' => '--',
                        'duration' => '--',
                    ];
                }
                // Start a new potential break at this OUT
                $lastCheckOut = $recordTime;
            } elseif ($type === 'IN') {
                if ($lastCheckOut) {
                    // Normal complete pair: OUT → IN
                    $breakDuration = $lastCheckOut->diffInMinutes($recordTime);
                    if ($breakDuration > 0) {
                        $breakDetails[] = [
                            'start' => $lastCheckOut->format('h:i A'),
                            'end' => $recordTime->format('h:i A'),
                            'duration' => $this->formatDuration($breakDuration),
                        ];
                    }
                    $lastCheckOut = null; // Reset for next break
                } else {
                    // IN without prior OUT → missing OUT, show '--' → IN
                    $breakDetails[] = [
                        'start' => '--',
                        'end' => $recordTime->format('h:i A'),
                        'duration' => '--',
                    ];
                }
            }
        }
        
        // If the sequence ended with an OUT and no following IN, show OUT → '--'
        if ($lastCheckOut !== null) {
            $breakDetails[] = [
                'start' => $lastCheckOut->format('h:i A'),
                'end' => '--',
                'duration' => '--',
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
                if ($recordType === $lastType && $timeDiff <= 5) {
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

        // Calculate total working hours by pairing check-ins with check-outs chronologically
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

    public function updatedSelectedUserId()
    {
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

    public function render()
    {
        return view('livewire.attendance.index', [
            'filteredUsers' => $this->filteredUsers,
        ])
            ->layout('components.layouts.app');
    }
}


