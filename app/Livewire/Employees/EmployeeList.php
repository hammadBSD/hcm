<?php

namespace App\Livewire\Employees;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
use App\Models\Employee;
use App\Models\Shift;
use App\Models\EmployeeShift;
use App\Models\Department;
use App\Models\EmployeeDepartmentChange;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class EmployeeList extends Component
{
    use WithPagination;

        // Search and filter properties
        public $search = '';
        public $filterDepartment = '';
        public $filterStatus = '';
        public $filterRole = '';
        public $hireDateFrom = '';
        public $hireDateTo = '';
        public $showAdvancedFilters = false;

        // Advanced filter properties
        public $filterCountry = '';
        public $filterProvince = '';
        public $filterCity = '';
        public $filterArea = '';
        public $filterVendor = '';
        public $filterStation = '';
        public $filterSubDepartment = '';
        public $filterEmployeeGroup = '';
        public $filterDesignation = '';
        public $filterDivision = '';
        public $filterEmployeeCode = '';
        public $filterEmployeeName = '';
        public $filterEmployeeStatus = '';
        public $filterDocumentsAttached = '';
        public $filterRolesTemplate = '';
        public $filterEmiratesId = '';
        public $filterFlag = '';
        public $filterReportsTo = '';
        public $filterBlacklistWhitelist = '';
        public $filterPositionCode = '';

    // Sorting properties
    public $sortBy = 'name';
    public $sortDirection = 'asc';

    // Pagination
    protected $paginationTheme = 'tailwind';

    // Shift Assignment Flyout Properties
    public $showAssignShiftFlyout = false;
    public $selectedEmployeeId = null;
    public $selectedShiftId = null;
    public $shiftStartDate = '';
    public $shiftNotes = '';
    public $shifts = [];

    // Department Assignment Flyout Properties
    public $showAssignDepartmentFlyout = false;
    public $selectedDepartmentId = null;
    public $departmentStartDate = '';
    public $departmentNotes = '';
    public $departmentReason = null;
    public $departments = [];

    public function mount()
    {
        $this->loadShifts();
        $this->loadDepartments();
    }

    public function loadShifts()
    {
        $this->shifts = Shift::where('status', 'active')
            ->orderBy('shift_name')
            ->get()
            ->map(function ($shift) {
                return [
                    'value' => $shift->id,
                    'label' => $shift->shift_name . ' (' . date('h:i A', strtotime($shift->time_from)) . ' - ' . date('h:i A', strtotime($shift->time_to)) . ')'
                ];
            })
            ->toArray();
    }

    public function loadDepartments()
    {
        $this->departments = Department::where('status', 'active')
            ->orderBy('title')
            ->get()
            ->map(function ($department) {
                return [
                    'value' => $department->id,
                    'label' => $department->title . ($department->code ? ' (' . $department->code . ')' : '')
                ];
            })
            ->toArray();
    }

    public function openAssignShiftFlyout($userId)
    {
        $this->selectedEmployeeId = $userId;
        $employee = Employee::where('user_id', $userId)->first();
        $this->selectedShiftId = $employee ? $employee->shift_id : null;
        $this->shiftStartDate = Carbon::now()->format('Y-m-d');
        $this->shiftNotes = '';
        $this->showAssignShiftFlyout = true;
    }

    public function closeAssignShiftFlyout()
    {
        $this->showAssignShiftFlyout = false;
        $this->selectedEmployeeId = null;
        $this->selectedShiftId = null;
        $this->shiftStartDate = '';
        $this->shiftNotes = '';
    }

    public function assignShift()
    {
        $this->validate([
            'selectedShiftId' => 'required|exists:shifts,id',
            'shiftStartDate' => 'required|date',
            'shiftNotes' => 'nullable|string|max:500',
        ]);

        $employee = Employee::where('user_id', $this->selectedEmployeeId)->first();
        
        if (!$employee) {
            session()->flash('error', 'Employee not found!');
            return;
        }

        // Get the previous shift for history tracking
        $previousShiftId = $employee->shift_id;

        // Update the employee's current shift
        $employee->shift_id = $this->selectedShiftId;
        $employee->save();

        // Create shift history record if shift changed
        if ($previousShiftId != $this->selectedShiftId) {
            // End the previous shift history record if exists
            $previousShiftHistory = EmployeeShift::where('employee_id', $employee->id)
                ->whereNull('end_date')
                ->latest()
                ->first();

            if ($previousShiftHistory) {
                $previousShiftHistory->end_date = Carbon::parse($this->shiftStartDate)->subDay()->format('Y-m-d');
                $previousShiftHistory->save();
            }

            // Create new shift history record
            EmployeeShift::create([
                'employee_id' => $employee->id,
                'shift_id' => $this->selectedShiftId,
                'start_date' => $this->shiftStartDate,
                'end_date' => null, // Current shift
                'changed_by' => Auth::id(),
                'notes' => $this->shiftNotes,
            ]);

            session()->flash('message', 'Shift assigned successfully!');
        } else {
            session()->flash('message', 'Employee already has this shift assigned.');
        }

        $this->closeAssignShiftFlyout();
    }

    public function openAssignDepartmentFlyout($userId)
    {
        $this->selectedEmployeeId = $userId;
        $employee = Employee::where('user_id', $userId)->first();
        $this->selectedDepartmentId = $employee ? $employee->department_id : null;
        $this->departmentStartDate = Carbon::now()->format('Y-m-d');
        $this->departmentNotes = '';
        $this->departmentReason = null;
        $this->showAssignDepartmentFlyout = true;
    }

    public function closeAssignDepartmentFlyout()
    {
        $this->showAssignDepartmentFlyout = false;
        $this->selectedEmployeeId = null;
        $this->selectedDepartmentId = null;
        $this->departmentStartDate = '';
        $this->departmentNotes = '';
        $this->departmentReason = null;
    }

    public function assignDepartment()
    {
        $this->validate([
            'selectedDepartmentId' => 'required|exists:departments,id',
            'departmentStartDate' => 'required|date',
            'departmentNotes' => 'nullable|string|max:500',
            'departmentReason' => 'nullable|in:transfer,promotion,reorganization,other',
        ]);

        $employee = Employee::where('user_id', $this->selectedEmployeeId)->first();
        
        if (!$employee) {
            session()->flash('error', 'Employee not found!');
            return;
        }

        // Get the previous department for history tracking
        $previousDepartmentId = $employee->department_id;

        // Update the employee's current department
        $employee->department_id = $this->selectedDepartmentId;
        $employee->save();

        // Create department change history record if department changed
        if ($previousDepartmentId != $this->selectedDepartmentId) {
            EmployeeDepartmentChange::create([
                'employee_id' => $employee->id,
                'old_department_id' => $previousDepartmentId,
                'new_department_id' => $this->selectedDepartmentId,
                'changed_by' => Auth::id(),
                'changed_at' => Carbon::parse($this->departmentStartDate),
                'notes' => $this->departmentNotes,
                'reason' => $this->departmentReason,
            ]);

            session()->flash('message', 'Department assigned successfully!');
        } else {
            session()->flash('message', 'Employee already in this department.');
        }

        $this->closeAssignDepartmentFlyout();
    }

    public function render()
    {
        return view('livewire.employees.list', [
            'employees' => $this->getEmployees()
        ])->layout('components.layouts.app');
    }

    public function getEmployees()
    {
        // Start with base query - join employees table directly for proper sorting
        $query = User::select('users.*')
            ->join('employees', 'users.id', '=', 'employees.user_id')
            ->with(['employee.shift', 'employee.department']);

        // Apply search filter
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('users.name', 'like', '%' . $this->search . '%')
                  ->orWhere('users.email', 'like', '%' . $this->search . '%')
                  ->orWhere('employees.employee_code', 'like', '%' . $this->search . '%')
                  ->orWhere('employees.first_name', 'like', '%' . $this->search . '%')
                  ->orWhere('employees.last_name', 'like', '%' . $this->search . '%');
            });
        }

        // Apply department filter
        if ($this->filterDepartment) {
            $query->where('users.department', $this->filterDepartment);
        }

        // Apply status filter - use employees table status
        if ($this->filterStatus) {
            $query->where('employees.status', $this->filterStatus);
        }

        // Apply role filter
        if ($this->filterRole) {
            $query->whereHas('roles', function ($q) {
                $q->where('name', $this->filterRole);
            });
        }

        // Apply date filters
        if ($this->hireDateFrom) {
            $query->whereDate('users.created_at', '>=', $this->hireDateFrom);
        }

        if ($this->hireDateTo) {
            $query->whereDate('users.created_at', '<=', $this->hireDateTo);
        }

        // Group by to avoid duplicates from joins (must be before sorting)
        $query->groupBy('users.id');

        // Apply sorting
        if ($this->sortBy) {
            if ($this->sortBy === 'shift') {
                // Sort by shift name using subquery to avoid GROUP BY issues
                $query->orderByRaw('(
                    SELECT shifts.shift_name 
                    FROM shifts 
                    WHERE shifts.id = employees.shift_id 
                    LIMIT 1
                ) ' . ($this->sortDirection === 'asc' ? 'ASC' : 'DESC'));
            } elseif ($this->sortBy === 'status') {
                // Sort by employees table status - active first using subquery to avoid GROUP BY issues
                $query->orderByRaw('(
                    SELECT CASE 
                        WHEN LOWER(employees.status) = "active" THEN 0 
                        ELSE 1 
                    END
                    FROM employees 
                    WHERE employees.user_id = users.id 
                    LIMIT 1
                ) ASC')
                ->orderByRaw('(
                    SELECT employees.status
                    FROM employees 
                    WHERE employees.user_id = users.id 
                    LIMIT 1
                ) ' . ($this->sortDirection === 'asc' ? 'ASC' : 'DESC'));
            } else {
                $query->orderBy('users.' . $this->sortBy, $this->sortDirection);
            }
        } else {
            // Default sorting: Active employees first (from employees table), then inactive, then by name
            // Use subquery to avoid GROUP BY issues
            $query->orderByRaw('(
                SELECT CASE 
                    WHEN LOWER(employees.status) = "active" THEN 0 
                    ELSE 1 
                END
                FROM employees 
                WHERE employees.user_id = users.id 
                LIMIT 1
            ) ASC')
            ->orderBy('users.name', 'asc');
        }

        return $query->paginate(10);
    }

    public function sort($column)
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
    }

    public function toggleAdvancedFilters()
    {
        $this->showAdvancedFilters = !$this->showAdvancedFilters;
    }

    public function refresh()
    {
        $this->resetPage();
    }

    public function export()
    {
        // Export functionality
        $this->dispatch('export-employees');
    }

    public function import()
    {
        // Redirect to import page
        return redirect()->route('employees.import');
    }

    public function resetPassword($employeeId)
    {
        // Reset password functionality
        $this->dispatch('reset-password', $employeeId);
    }

    public function deactivate($userId)
    {
        // Find the employee record for this user
        $employee = Employee::where('user_id', $userId)->first();
        
        if (!$employee) {
            session()->flash('error', 'Employee not found!');
            return;
        }
        
        // Update employee status to inactive
        $employee->status = 'inactive';
        $employee->save();
        
        session()->flash('message', 'Employee deactivated successfully!');
    }
}
