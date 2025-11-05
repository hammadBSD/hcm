<?php

namespace App\Livewire\SystemManagement\OrganizationSetting\Department;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Group;
use App\Models\Shift;
use App\Models\EmployeeDepartmentChange;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class Index extends Component
{
    use WithPagination;

    public $sortBy = 'title';
    public $sortDirection = 'asc';
    public $search = '';
    
    // Add Department Flyout Properties
    public $showAddDepartmentFlyout = false;
    public $editingId = null;
    public $departmentTitle = '';
    public $departmentHead = '';
    public $departmentCode = '';
    public $description = '';
    public $parentId = '';
    public $groupId = '';
    public $shiftId = null;
    public $status = 'active';

    // Bulk Assignment Flyout Properties
    public $showBulkAssignFlyout = false;
    public $bulkSelectedDepartmentId = null;
    public $bulkSelectedEmployeeIds = [];
    public $bulkDepartmentStartDate = '';
    public $bulkDepartmentNotes = '';
    public $bulkDepartmentReason = null;
    public $employees = [];
    public $employeeSearchTerm = '';

    protected $paginationTheme = 'tailwind';

    public $shifts = [];

    public function mount()
    {
        $this->loadEmployees();
        $this->loadShifts();
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

    public function createDepartment()
    {
        $this->resetForm();
        $this->showAddDepartmentFlyout = true;
    }
    
    public function closeAddDepartmentFlyout()
    {
        $this->showAddDepartmentFlyout = false;
        $this->resetForm();
    }
    
    public function submitDepartment()
    {
        $this->validate([
            'departmentTitle' => 'required|string|max:255',
            'departmentCode' => 'nullable|string|max:50|unique:departments,code,' . ($this->editingId ? $this->editingId : 'NULL'),
            'departmentHead' => 'nullable|exists:employees,id',
            'parentId' => 'nullable|exists:departments,id',
            'groupId' => 'nullable|exists:groups,id',
            'shiftId' => 'nullable|exists:shifts,id',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        $data = [
            'title' => $this->departmentTitle,
            'code' => $this->departmentCode ?: null,
            'description' => $this->description,
            'status' => $this->status,
            'department_head_id' => $this->departmentHead ?: null,
            'parent_id' => $this->parentId ?: null,
            'group_id' => $this->groupId ?: null,
            'shift_id' => $this->shiftId ?: null,
        ];

        if ($this->editingId) {
            $department = Department::findOrFail($this->editingId);
            $department->update($data);
            session()->flash('message', 'Department updated successfully!');
        } else {
            Department::create($data);
            session()->flash('message', 'Department created successfully!');
        }
        
        $this->closeAddDepartmentFlyout();
    }
    
    private function resetForm()
    {
        $this->editingId = null;
        $this->departmentTitle = '';
        $this->departmentHead = '';
        $this->departmentCode = '';
        $this->description = '';
        $this->parentId = '';
        $this->groupId = '';
        $this->shiftId = null;
        $this->status = 'active';
    }

    public function editDepartment($id)
    {
        $department = Department::findOrFail($id);
        $this->editingId = $department->id;
        $this->departmentTitle = $department->title;
        $this->departmentCode = $department->code ?? '';
        $this->departmentHead = $department->department_head_id ?? '';
        $this->parentId = $department->parent_id ?? '';
        $this->groupId = $department->group_id ?? '';
        $this->shiftId = $department->shift_id ?? null;
        $this->description = $department->description ?? '';
        $this->status = $department->status;
        $this->showAddDepartmentFlyout = true;
    }

    public function deleteDepartment($id)
    {
        $department = Department::findOrFail($id);
        $department->delete();
        session()->flash('message', 'Department deleted successfully!');
    }

    public function openBulkAssignFlyout()
    {
        $this->loadEmployees();
        $this->bulkSelectedDepartmentId = null;
        $this->bulkSelectedEmployeeIds = [];
        $this->bulkDepartmentStartDate = Carbon::now()->format('Y-m-d');
        $this->bulkDepartmentNotes = '';
        $this->bulkDepartmentReason = null;
        $this->employeeSearchTerm = '';
        $this->showBulkAssignFlyout = true;
    }

    public function closeBulkAssignFlyout()
    {
        $this->showBulkAssignFlyout = false;
        $this->bulkSelectedDepartmentId = null;
        $this->bulkSelectedEmployeeIds = [];
        $this->bulkDepartmentStartDate = '';
        $this->bulkDepartmentNotes = '';
        $this->bulkDepartmentReason = null;
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

    public function bulkAssignDepartment()
    {
        $this->validate([
            'bulkSelectedDepartmentId' => 'required|exists:departments,id',
            'bulkSelectedEmployeeIds' => 'required|array|min:1',
            'bulkSelectedEmployeeIds.*' => 'exists:employees,id',
            'bulkDepartmentStartDate' => 'required|date',
            'bulkDepartmentNotes' => 'nullable|string|max:500',
            'bulkDepartmentReason' => 'nullable|in:transfer,promotion,reorganization,other',
        ]);

        $assignedCount = 0;
        $skippedCount = 0;

        foreach ($this->bulkSelectedEmployeeIds as $employeeId) {
            $employee = Employee::find($employeeId);
            
            if (!$employee) {
                continue;
            }

            $previousDepartmentId = $employee->department_id;

            // Update the employee's current department
            $employee->department_id = $this->bulkSelectedDepartmentId;
            $employee->save();

            // Create department change history record if department changed
            if ($previousDepartmentId != $this->bulkSelectedDepartmentId) {
                EmployeeDepartmentChange::create([
                    'employee_id' => $employee->id,
                    'old_department_id' => $previousDepartmentId,
                    'new_department_id' => $this->bulkSelectedDepartmentId,
                    'changed_by' => Auth::id(),
                    'changed_at' => Carbon::parse($this->bulkDepartmentStartDate),
                    'notes' => $this->bulkDepartmentNotes,
                    'reason' => $this->bulkDepartmentReason,
                ]);

                $assignedCount++;
            } else {
                $skippedCount++;
            }
        }

        if ($assignedCount > 0) {
            session()->flash('message', "Department assigned successfully to {$assignedCount} employee(s)." . ($skippedCount > 0 ? " {$skippedCount} employee(s) already in this department." : ''));
        } else {
            session()->flash('message', "All selected employees are already in this department.");
        }

        $this->closeBulkAssignFlyout();
    }

    public function render()
    {
        $query = Department::with(['departmentHead.user', 'parent', 'group', 'shift']);

        if (!empty($this->search)) {
            $query->where(function($q) {
                $q->where('title', 'like', '%' . $this->search . '%')
                  ->orWhere('code', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        $query->orderBy($this->sortBy, $this->sortDirection);
        $departments = $query->paginate(10);

        // Load employees for dropdown (for Add Department form)
        $employees = Employee::with('user:id,name')
            ->whereNotNull('user_id')
            ->get()
            ->map(function($employee) {
                return [
                    'id' => $employee->id,
                    'name' => $employee->user->name ?? ($employee->first_name . ' ' . $employee->last_name),
                ];
            })
            ->sortBy('name')
            ->values()
            ->toArray();

        // Load groups for dropdown
        $groups = Group::where('status', 'active')->orderBy('name')->get();
        
        // Load departments for parent dropdown
        $parentDepartments = Department::where('status', 'active')
            ->where('id', '!=', $this->editingId)
            ->orderBy('title')
            ->get();

        // Load all active departments for bulk assign dropdown
        $allDepartments = Department::where('status', 'active')
            ->orderBy('title')
            ->get();

        return view('livewire.system-management.organization-setting.department.index', [
            'departments' => $departments,
            'allDepartments' => $allDepartments,
            'employees' => $employees,
            'groups' => $groups,
            'parentDepartments' => $parentDepartments,
            'filteredEmployees' => $this->filteredEmployees,
            'shifts' => $this->shifts,
        ])
            ->layout('components.layouts.app');
    }
}
