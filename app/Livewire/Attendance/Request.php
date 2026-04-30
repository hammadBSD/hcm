<?php

namespace App\Livewire\Attendance;

use App\Models\Employee;
use App\Models\EmployeeSuggestion;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Request extends Component
{
    public $employeeId = '';
    public $employees = [];

    // Form fields
    public $attendanceDate = '';
    public $inDate = '';
    public $inTime = '';
    public $outDate = '';
    public $outTime = '';
    public $attendanceType = '';
    public $reason = '';

    public bool $employeeSelectDisabled = false;

    public function mount(): void
    {
        $user = Auth::user();

        // Permission meaning: can create requests for all employees.
        // If not granted -> only allow the current user's own employee.
        $canRequestAll = $user && $user->can('attendance.request');

        $employeesQuery = Employee::query()
            ->where('status', 'active')
            ->select(['id', 'employee_code', 'first_name', 'last_name']);

        if ($canRequestAll) {
            $this->employees = $employeesQuery
                ->orderBy('first_name')
                ->orderBy('last_name')
                ->get()
                ->map(fn ($e) => [
                    'id' => (int) $e->id,
                    'employee_code' => $e->employee_code,
                    'first_name' => $e->first_name,
                    'last_name' => $e->last_name,
                ])
                ->toArray();

            $this->employeeSelectDisabled = false;
            $this->employeeId = '';
            return;
        }

        // Self-only fallback
        $myEmployee = $user ? Employee::where('user_id', $user->id)->first() : null;
        if ($myEmployee) {
            $this->employees = [[
                'id' => (int) $myEmployee->id,
                'employee_code' => $myEmployee->employee_code,
                'first_name' => $myEmployee->first_name,
                'last_name' => $myEmployee->last_name,
            ]];
            $this->employeeId = (string) $myEmployee->id;
        } else {
            $this->employees = [];
            $this->employeeId = '';
        }

        $this->employeeSelectDisabled = true;
    }

    public function submitAttendanceRequest(): void
    {
        $user = Auth::user();
        if (!$user) {
            abort(403, 'Unauthorized access.');
        }

        // Access control:
        // - `attendance.request` => can submit for all employees
        // - otherwise => employee can only submit their own request
        if (!$user->can('attendance.sidebar.requests') && !$user->can('attendance.request')) {
            abort(403, 'Unauthorized access.');
        }

        $canRequestAll = $user->can('attendance.request');

        $this->validate([
            'employeeId' => 'required|integer|exists:employees,id',
            'attendanceDate' => 'required|date',
            'inDate' => 'required|date',
            'inTime' => 'required|date_format:H:i',
            'outDate' => 'required|date',
            'outTime' => 'required|date_format:H:i',
            'attendanceType' => 'nullable|string|max:50',
            'reason' => 'required|string|min:3|max:5000',
        ]);

        $employeeId = (int) $this->employeeId;

        // Enforce self-only submission if user can't submit for all employees.
        if (!$canRequestAll) {
            $myEmployee = Employee::where('user_id', $user->id)->first();
            if (!$myEmployee) {
                session()->flash('error', __('Employee record not found. Please contact administrator.'));
                return;
            }

            $employeeId = (int) $myEmployee->id;
            $this->employeeId = (string) $employeeId;
        }

        $targetEmployee = Employee::findOrFail($employeeId);

        $attendanceTypeLabel = match ($this->attendanceType) {
            'late_arrival' => __('Late Arrival'),
            'early_departure' => __('Early Departure'),
            'missed_checkin' => __('Missed Check-in'),
            'missed_checkout' => __('Missed Check-out'),
            'system_error' => __('System Error'),
            'device_malfunction' => __('Device Malfunction'),
            'emergency' => __('Emergency'),
            default => __('Other'),
        };

        $message = __('Attendance correction request') . PHP_EOL .
            __('Attendance Date') . ': ' . $this->attendanceDate . PHP_EOL .
            __('In') . ': ' . $this->inDate . ' ' . $this->inTime . PHP_EOL .
            __('Out') . ': ' . $this->outDate . ' ' . $this->outTime . PHP_EOL .
            __('Attendance Type') . ': ' . $attendanceTypeLabel . PHP_EOL .
            __('Reason') . ': ' . $this->reason;

        // Store as a complaint entry in the shared employee_suggestions system.
        EmployeeSuggestion::create([
            'employee_id' => $targetEmployee->id,
            'type' => 'complaint',
            'complaint_type' => 'attendance',
            'priority' => 'medium',
            'message' => $message,
            'status' => 'pending',
        ]);

        session()->flash('success', __('Attendance request submitted successfully.'));
        $this->dispatch('notify', type: 'success', message: __('Attendance request submitted successfully.'));

        // Reset only fields that should be cleared after submission.
        $this->attendanceDate = '';
        $this->inDate = '';
        $this->inTime = '';
        $this->outDate = '';
        $this->outTime = '';
        $this->attendanceType = '';
        $this->reason = '';
    }

    public function render()
    {
        return view('livewire.attendance.request')
            ->layout('components.layouts.app');
    }
}
