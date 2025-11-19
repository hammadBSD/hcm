<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Employee;
use App\Models\DeviceAttendance;

class YourStatusCard extends Component
{
    public $status = 'Absent';
    public $checkInTime = null;
    public $isPresent = false;

    public function mount()
    {
        $this->loadAttendanceData();
    }

    public function loadAttendanceData()
    {
        $user = Auth::user();
        $today = Carbon::today();
        
        // Get employee record for the logged-in user
        $employee = Employee::where('user_id', $user->id)->first();
        
        if (!$employee) {
            // If no employee record, set default values
            $this->status = 'Not Found';
            $this->isPresent = false;
            $this->checkInTime = null;
            return;
        }

        // Get today's first check-in from device attendance
        $firstCheckIn = DeviceAttendance::where('punch_code', $employee->punch_code)
            ->whereDate('punch_time', $today)
            ->where('device_type', 'IN')
            ->orderBy('punch_time', 'asc')
            ->first();

        if ($firstCheckIn) {
            $this->status = 'Present';
            $this->checkInTime = Carbon::parse($firstCheckIn->punch_time)->format('g:i A');
            $this->isPresent = true;
        } else {
            // Check if there's any attendance record for today
            $anyAttendance = DeviceAttendance::where('punch_code', $employee->punch_code)
                ->whereDate('punch_time', $today)
                ->whereIn('device_type', ['IN', 'OUT'])
                ->exists();

            if ($anyAttendance) {
                $this->status = 'Partial';
                $this->isPresent = false;
            } else {
                $this->status = 'Absent';
                $this->isPresent = false;
            }
        }
    }

    public function getStatusColor()
    {
        return match($this->status) {
            'Present' => 'bg-green-100 dark:bg-green-900/20',
            'Partial' => 'bg-yellow-100 dark:bg-yellow-900/20',
            'Not Found' => 'bg-gray-100 dark:bg-gray-900/20',
            default => 'bg-red-100 dark:bg-red-900/20'
        };
    }

    public function getStatusIcon()
    {
        return match($this->status) {
            'Present' => 'check-circle',
            'Partial' => 'exclamation-triangle',
            'Not Found' => 'question-mark-circle',
            default => 'x-circle'
        };
    }

    public function getStatusIconColor()
    {
        return match($this->status) {
            'Present' => 'text-green-600 dark:text-green-400',
            'Partial' => 'text-yellow-600 dark:text-yellow-400',
            'Not Found' => 'text-gray-600 dark:text-gray-400',
            default => 'text-red-600 dark:text-red-400'
        };
    }

    public function refresh()
    {
        $this->loadAttendanceData();
    }

    public function render()
    {
        return view('livewire.dashboard.your-status-card');
    }
}