<?php

namespace App\Livewire\SystemManagement\OrganizationSetting\Group;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Group;
use App\Models\Employee;
use App\Models\EmployeeGroupHistory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Index extends Component
{
    use WithPagination;

    public $sortBy = 'name';
    public $sortDirection = 'asc';
    public $search = '';
    
    // Add Group Flyout Properties
    public $showAddGroupFlyout = false;
    public $editingId = null;
    public $groupName = '';
    public $groupCode = '';
    public $description = '';
    public $status = 'active';
    public $selectedEmployeeIds = [];
    public $employeeSearchTerm = '';
    public $allEmployees = [];
    
    // View Details Flyout Properties
    public $showViewDetailsFlyout = false;
    public $selectedGroupId = null;
    public $groupEmployees = [];
    public $groupHistory = [];

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

    public function mount()
    {
        $this->loadEmployees();
    }
    
    public function loadEmployees()
    {
        $this->allEmployees = Employee::select('id', 'first_name', 'last_name', 'employee_code', 'group_id')
            ->where('status', 'active')
            ->orderBy('first_name')
            ->get()
            ->map(function ($employee) {
                return [
                    'value' => $employee->id,
                    'label' => $employee->first_name . ' ' . $employee->last_name . ' (' . ($employee->employee_code ?? 'N/A') . ')',
                    'name' => $employee->first_name . ' ' . $employee->last_name,
                    'code' => $employee->employee_code ?? 'N/A',
                    'current_group_id' => $employee->group_id,
                ];
            })
            ->toArray();
    }
    
    public function getFilteredEmployeesProperty()
    {
        if (empty($this->employeeSearchTerm)) {
            return $this->allEmployees;
        }
        
        return collect($this->allEmployees)->filter(function ($employee) {
            return stripos($employee['label'], $this->employeeSearchTerm) !== false;
        })->values()->toArray();
    }

    public function createGroup()
    {
        $this->resetForm();
        $this->loadEmployees();
        $this->showAddGroupFlyout = true;
    }
    
    public function closeAddGroupFlyout()
    {
        $this->showAddGroupFlyout = false;
        $this->resetForm();
    }
    
    public function submitGroup()
    {
        $this->validate([
            'groupName' => 'required|string|max:255',
            'groupCode' => 'nullable|string|max:50|unique:groups,code,' . $this->editingId,
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
            'selectedEmployeeIds' => 'nullable|array',
            'selectedEmployeeIds.*' => 'exists:employees,id',
        ]);

        DB::transaction(function () {
            if ($this->editingId) {
                $group = Group::findOrFail($this->editingId);
                $group->update([
                    'name' => $this->groupName,
                    'code' => $this->groupCode ?: null,
                    'description' => $this->description,
                    'status' => $this->status,
                ]);
                
                // Handle employee assignments
                $this->assignEmployeesToGroup($group->id);
                
                session()->flash('message', 'Group updated successfully!');
            } else {
                $group = Group::create([
                    'name' => $this->groupName,
                    'code' => $this->groupCode ?: null,
                    'description' => $this->description,
                    'status' => $this->status,
                ]);
                
                // Handle employee assignments
                $this->assignEmployeesToGroup($group->id);
                
                session()->flash('message', 'Group created successfully!');
            }
        });
        
        $this->closeAddGroupFlyout();
    }
    
    private function assignEmployeesToGroup($groupId)
    {
        $assignedDate = Carbon::now()->format('Y-m-d');
        $assignedBy = Auth::id();
        
        // Get current employees in this group
        $currentEmployeeIds = Employee::where('group_id', $groupId)
            ->pluck('id')
            ->toArray();
        
        // Employees to add (in selected but not in current)
        $employeesToAdd = array_diff($this->selectedEmployeeIds, $currentEmployeeIds);
        
        // Employees to remove (in current but not in selected)
        $employeesToRemove = array_diff($currentEmployeeIds, $this->selectedEmployeeIds);
        
        // End previous group assignments for employees being removed
        foreach ($employeesToRemove as $employeeId) {
            // End the current history record
            EmployeeGroupHistory::where('employee_id', $employeeId)
                ->whereNull('end_date')
                ->update(['end_date' => $assignedDate]);
            
            // Remove from group
            Employee::where('id', $employeeId)->update(['group_id' => null]);
        }
        
        // Assign new employees to group and create history
        foreach ($employeesToAdd as $employeeId) {
            $employee = Employee::findOrFail($employeeId);
            $previousGroupId = $employee->group_id;
            
            // End previous group assignment history if exists
            if ($previousGroupId) {
                EmployeeGroupHistory::where('employee_id', $employeeId)
                    ->whereNull('end_date')
                    ->update(['end_date' => $assignedDate]);
            }
            
            // Update employee's group
            $employee->group_id = $groupId;
            $employee->save();
            
            // Create new history record
            EmployeeGroupHistory::create([
                'employee_id' => $employeeId,
                'group_id' => $groupId,
                'previous_group_id' => $previousGroupId,
                'assigned_by' => $assignedBy,
                'assigned_date' => $assignedDate,
                'end_date' => null,
            ]);
        }
    }
    
    private function resetForm()
    {
        $this->editingId = null;
        $this->groupName = '';
        $this->groupCode = '';
        $this->description = '';
        $this->status = 'active';
        $this->selectedEmployeeIds = [];
        $this->employeeSearchTerm = '';
    }

    public function editGroup($id)
    {
        $group = Group::with('employees')->findOrFail($id);
        $this->editingId = $group->id;
        $this->groupName = $group->name;
        $this->groupCode = $group->code ?? '';
        $this->description = $group->description ?? '';
        $this->status = $group->status;
        $this->selectedEmployeeIds = $group->employees->pluck('id')->toArray();
        $this->loadEmployees();
        $this->showAddGroupFlyout = true;
    }
    
    public function openViewDetailsFlyout($groupId)
    {
        $this->selectedGroupId = $groupId;
        $group = Group::with(['employees.user', 'employees.designation'])->findOrFail($groupId);
        
        // Get employees in this group
        $this->groupEmployees = $group->employees->map(function ($employee) {
            return [
                'id' => $employee->id,
                'name' => $employee->first_name . ' ' . $employee->last_name,
                'code' => $employee->employee_code ?? 'N/A',
                'email' => $employee->user->email ?? 'N/A',
                'designation' => $employee->designation->name ?? 'N/A',
            ];
        })->toArray();
        
        // Get group assignment history for all employees in this group
        $employeeIds = $group->employees->pluck('id')->toArray();
        $historyRecords = EmployeeGroupHistory::whereIn('employee_id', $employeeIds)
            ->where('group_id', $groupId)
            ->with(['employee.user', 'previousGroup', 'assignedBy'])
            ->orderBy('assigned_date', 'desc')
            ->get();
        
        $this->groupHistory = $historyRecords->map(function ($history) {
            return [
                'id' => $history->id,
                'employee_name' => $history->employee->first_name . ' ' . $history->employee->last_name,
                'employee_code' => $history->employee->employee_code ?? 'N/A',
                'previous_group' => $history->previousGroup ? $history->previousGroup->name : 'None',
                'assigned_by' => $history->assignedBy ? $history->assignedBy->name : 'N/A',
                'assigned_date' => $history->assigned_date->format('M d, Y'),
                'end_date' => $history->end_date ? $history->end_date->format('M d, Y') : 'Current',
                'notes' => $history->notes,
            ];
        })->toArray();
        
        $this->showViewDetailsFlyout = true;
    }
    
    public function closeViewDetailsFlyout()
    {
        $this->showViewDetailsFlyout = false;
        $this->selectedGroupId = null;
        $this->groupEmployees = [];
        $this->groupHistory = [];
    }

    public function deleteGroup($id)
    {
        $group = Group::findOrFail($id);
        $group->delete();
        session()->flash('message', 'Group deleted successfully!');
    }

    public function render()
    {
        $query = Group::query();

        if (!empty($this->search)) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('code', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        $query->orderBy($this->sortBy, $this->sortDirection);
        $groups = $query->paginate(10);

        return view('livewire.system-management.organization-setting.group.index', [
            'groups' => $groups,
            'allEmployees' => $this->allEmployees,
        ])
            ->layout('components.layouts.app');
    }
}
