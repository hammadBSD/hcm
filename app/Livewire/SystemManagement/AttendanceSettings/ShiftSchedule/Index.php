<?php

namespace App\Livewire\SystemManagement\AttendanceSettings\ShiftSchedule;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Shift;
use App\Models\Employee;
use App\Models\EmployeeShift;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class Index extends Component
{
    use WithPagination;

    public $sortBy = 'shift_name';
    public $sortDirection = 'asc';
    public $search = '';
    
    // Add Shift Flyout Properties
    public $showAddShiftFlyout = false;
    public $editingId = null;
    public $shiftName = '';
    public $timeFrom = '';
    public $timeTo = '';
    public $status = 'active';
    public $gracePeriodLateIn = null;
    public $gracePeriodEarlyOut = null;
    public $disableGracePeriod = false;

    // Bulk Assignment Flyout Properties
    public $showBulkAssignFlyout = false;
    public $bulkSelectedShiftId = null;
    public $bulkSelectedEmployeeIds = [];
    public $bulkShiftStartDate = '';
    public $bulkShiftNotes = '';
    public $employees = [];
    public $employeeSearchTerm = '';

    protected $paginationTheme = 'tailwind';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function sort($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    public function createShift()
    {
        $this->resetForm();
        $this->showAddShiftFlyout = true;
    }
    
    public function closeAddShiftFlyout()
    {
        $this->showAddShiftFlyout = false;
        $this->resetForm();
    }
    
    public function submitShift()
    {
        $this->validate([
            'shiftName' => 'required|string|max:255',
            'timeFrom' => 'required|date_format:H:i',
            'timeTo' => 'required|date_format:H:i',
            'status' => 'required|in:active,inactive',
            'gracePeriodLateIn' => 'nullable|integer|min:0',
            'gracePeriodEarlyOut' => 'nullable|integer|min:0',
            'disableGracePeriod' => 'boolean',
        ]);

        $data = [
            'shift_name' => $this->shiftName,
            'time_from' => $this->timeFrom,
            'time_to' => $this->timeTo,
            'status' => $this->status,
            'grace_period_late_in' => $this->gracePeriodLateIn ?: null,
            'grace_period_early_out' => $this->gracePeriodEarlyOut ?: null,
            'disable_grace_period' => $this->disableGracePeriod,
        ];

        if ($this->editingId) {
            $shift = Shift::findOrFail($this->editingId);
            $shift->update($data);
            session()->flash('message', 'Shift updated successfully!');
        } else {
            Shift::create($data);
            session()->flash('message', 'Shift created successfully!');
        }
        
        $this->closeAddShiftFlyout();
    }
    
    private function resetForm()
    {
        $this->editingId = null;
        $this->shiftName = '';
        $this->timeFrom = '';
        $this->timeTo = '';
        $this->status = 'active';
        $this->gracePeriodLateIn = null;
        $this->gracePeriodEarlyOut = null;
        $this->disableGracePeriod = false;
    }

    public function editShift($id)
    {
        $shift = Shift::findOrFail($id);
        $this->editingId = $shift->id;
        $this->shiftName = $shift->shift_name;
        $this->timeFrom = $shift->time_from;
        $this->timeTo = $shift->time_to;
        $this->status = $shift->status;
        $this->gracePeriodLateIn = $shift->grace_period_late_in;
        $this->gracePeriodEarlyOut = $shift->grace_period_early_out;
        $this->disableGracePeriod = $shift->disable_grace_period ?? false;
        $this->showAddShiftFlyout = true;
    }

    public function deleteShift($id)
    {
        $shift = Shift::findOrFail($id);
        $shift->delete();
        session()->flash('message', 'Shift deleted successfully!');
    }

    public function openBulkAssignFlyout()
    {
        $this->loadEmployees();
        $this->bulkSelectedShiftId = null;
        $this->bulkSelectedEmployeeIds = [];
        $this->bulkShiftStartDate = Carbon::now()->format('Y-m-d');
        $this->bulkShiftNotes = '';
        $this->employeeSearchTerm = '';
        $this->showBulkAssignFlyout = true;
    }

    public function closeBulkAssignFlyout()
    {
        $this->showBulkAssignFlyout = false;
        $this->bulkSelectedShiftId = null;
        $this->bulkSelectedEmployeeIds = [];
        $this->bulkShiftStartDate = '';
        $this->bulkShiftNotes = '';
        $this->employeeSearchTerm = '';
    }

    public function loadEmployees()
    {
        $this->employees = Employee::select('id', 'first_name', 'last_name', 'employee_code')
            ->where('status', 'active')
            ->orderBy('first_name')
            ->get()
            ->map(function ($employee) {
                return [
                    'value' => $employee->id,
                    'label' => $employee->first_name . ' ' . $employee->last_name . ' (' . ($employee->employee_code ?? 'N/A') . ')',
                    'name' => $employee->first_name . ' ' . $employee->last_name,
                    'code' => $employee->employee_code ?? 'N/A'
                ];
            })
            ->toArray();
    }

    public function getFilteredEmployeesProperty()
    {
        if (empty($this->employeeSearchTerm)) {
            return $this->employees;
        }
        
        return collect($this->employees)->filter(function ($employee) {
            return stripos($employee['label'], $this->employeeSearchTerm) !== false;
        })->values()->toArray();
    }

    public function toggleEmployeeSelection($employeeId)
    {
        if (in_array($employeeId, $this->bulkSelectedEmployeeIds)) {
            $this->bulkSelectedEmployeeIds = array_values(array_diff($this->bulkSelectedEmployeeIds, [$employeeId]));
        } else {
            $this->bulkSelectedEmployeeIds[] = $employeeId;
        }
    }

    public function removeEmployeeSelection($employeeId)
    {
        $this->bulkSelectedEmployeeIds = array_values(array_diff($this->bulkSelectedEmployeeIds, [$employeeId]));
    }

    public function bulkAssignShift()
    {
        $this->validate([
            'bulkSelectedShiftId' => 'required|exists:shifts,id',
            'bulkSelectedEmployeeIds' => 'required|array|min:1',
            'bulkSelectedEmployeeIds.*' => 'exists:employees,id',
            'bulkShiftStartDate' => 'required|date',
            'bulkShiftNotes' => 'nullable|string|max:500',
        ]);

        $assignedCount = 0;
        $skippedCount = 0;

        foreach ($this->bulkSelectedEmployeeIds as $employeeId) {
            $employee = Employee::find($employeeId);
            
            if (!$employee) {
                continue;
            }

            $previousShiftId = $employee->shift_id;

            // Update the employee's current shift
            $employee->shift_id = $this->bulkSelectedShiftId;
            $employee->save();

            // Create shift history record if shift changed
            if ($previousShiftId != $this->bulkSelectedShiftId) {
                // End the previous shift history record if exists
                $previousShiftHistory = EmployeeShift::where('employee_id', $employee->id)
                    ->whereNull('end_date')
                    ->latest()
                    ->first();

                if ($previousShiftHistory) {
                    $previousShiftHistory->end_date = Carbon::parse($this->bulkShiftStartDate)->subDay()->format('Y-m-d');
                    $previousShiftHistory->save();
                }

                // Create new shift history record
                EmployeeShift::create([
                    'employee_id' => $employee->id,
                    'shift_id' => $this->bulkSelectedShiftId,
                    'start_date' => $this->bulkShiftStartDate,
                    'end_date' => null, // Current shift
                    'changed_by' => Auth::id(),
                    'notes' => $this->bulkShiftNotes,
                ]);

                $assignedCount++;
            } else {
                $skippedCount++;
            }
        }

        if ($assignedCount > 0) {
            session()->flash('message', "Shift assigned successfully to {$assignedCount} employee(s)." . ($skippedCount > 0 ? " {$skippedCount} employee(s) already had this shift assigned." : ''));
        } else {
            session()->flash('message', "All selected employees already have this shift assigned.");
        }

        $this->closeBulkAssignFlyout();
    }

    public function render()
    {
        $query = Shift::withCount('employees');

        if (!empty($this->search)) {
            $query->where(function($q) {
                $q->where('shift_name', 'like', '%' . $this->search . '%')
                  ->orWhere('time_from', 'like', '%' . $this->search . '%')
                  ->orWhere('time_to', 'like', '%' . $this->search . '%');
            });
        }

        // Handle sorting
        $sortField = $this->sortBy;
        if ($sortField === 'count') {
            $query->orderBy('employees_count', $this->sortDirection);
        } else {
            $query->orderBy($sortField, $this->sortDirection);
        }

        $shifts = $query->paginate(10);

        return view('livewire.system-management.attendance-settings.shift-schedule.index', [
            'shifts' => $shifts,
            'filteredEmployees' => $this->filteredEmployees,
        ])
            ->layout('components.layouts.app');
    }
}

