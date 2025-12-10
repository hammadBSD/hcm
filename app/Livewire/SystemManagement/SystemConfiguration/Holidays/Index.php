<?php

namespace App\Livewire\SystemManagement\SystemConfiguration\Holidays;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Holiday;
use App\Models\HolidayDay;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Group;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class Index extends Component
{
    use WithPagination;

    public $sortBy = 'name';
    public $sortDirection = 'asc';
    public $search = '';
    
    // Add Holiday Flyout Properties
    public $showAddHolidayFlyout = false;
    public $editingId = null;
    public $holidayName = '';
    public $fromDate = '';
    public $toDate = '';
    public $isSingleDay = false;
    public $scopeType = 'all_employees';
    public $selectedDepartmentIds = [];
    public $selectedRoleIds = [];
    public $selectedGroupIds = [];
    public $selectedEmployeeIds = [];
    public $additionalEmployeeIds = []; // For employees not in selected department/role
    
    // Data properties
    public $departments = [];
    public $roles = [];
    public $groups = [];
    public $employees = [];
    public $additionalEmployees = []; // Employees not in selected department/role
    
    // Search terms
    public $departmentSearchTerm = '';
    public $roleSearchTerm = '';
    public $groupSearchTerm = '';
    public $employeeSearchTerm = '';
    public $additionalEmployeeSearchTerm = '';

    protected $paginationTheme = 'tailwind';

    public function mount()
    {
        $this->loadDepartments();
        $this->loadRoles();
        $this->loadGroups();
        $this->loadEmployees();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatedScopeType()
    {
        // Reset selections when scope type changes
        $this->selectedDepartmentIds = [];
        $this->selectedRoleIds = [];
        $this->selectedGroupIds = [];
        $this->selectedEmployeeIds = [];
        $this->additionalEmployeeIds = [];
        $this->departmentSearchTerm = '';
        $this->roleSearchTerm = '';
        $this->groupSearchTerm = '';
        $this->employeeSearchTerm = '';
        $this->additionalEmployeeSearchTerm = '';
        
        // Reload additional employees if needed
        if (in_array($this->scopeType, ['department', 'role', 'group'])) {
            $this->loadAdditionalEmployees();
        }
    }

    public function updatedSelectedDepartmentIds()
    {
        $this->loadAdditionalEmployees();
    }

    public function updatedSelectedRoleIds()
    {
        $this->loadAdditionalEmployees();
    }

    public function updatedSelectedGroupIds()
    {
        $this->loadAdditionalEmployees();
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

    public function createHoliday()
    {
        $this->resetForm();
        $this->showAddHolidayFlyout = true;
    }
    
    public function closeAddHolidayFlyout()
    {
        $this->showAddHolidayFlyout = false;
        $this->resetForm();
    }
    
    public function submitHoliday()
    {
        $rules = [
            'holidayName' => 'required|string|max:255',
            'fromDate' => 'required|date',
            'toDate' => 'nullable|date|after_or_equal:fromDate',
            'scopeType' => 'required|in:all_employees,department,role,group,employee',
        ];

        if ($this->scopeType === 'department') {
            $rules['selectedDepartmentIds'] = 'required|array|min:1';
            $rules['selectedDepartmentIds.*'] = 'exists:departments,id';
        } elseif ($this->scopeType === 'role') {
            $rules['selectedRoleIds'] = 'required|array|min:1';
            $rules['selectedRoleIds.*'] = 'exists:roles,id';
        } elseif ($this->scopeType === 'group') {
            $rules['selectedGroupIds'] = 'required|array|min:1';
            $rules['selectedGroupIds.*'] = 'exists:groups,id';
        } elseif ($this->scopeType === 'employee') {
            $rules['selectedEmployeeIds'] = 'required|array|min:1';
            $rules['selectedEmployeeIds.*'] = 'exists:employees,id';
        }

        if (in_array($this->scopeType, ['department', 'role', 'group'])) {
            $rules['additionalEmployeeIds'] = 'nullable|array';
            $rules['additionalEmployeeIds.*'] = 'exists:employees,id';
        }

        $this->validate($rules);

        // If toDate is empty, set it to fromDate (single-day holiday)
        if (empty($this->toDate)) {
            $this->toDate = $this->fromDate;
        }

        DB::transaction(function () {
            $data = [
                'name' => $this->holidayName,
                'from_date' => $this->fromDate,
                'to_date' => $this->toDate ?: $this->fromDate,
                'scope_type' => $this->scopeType,
                'created_by' => Auth::id(),
                'status' => 'active',
            ];

            if ($this->editingId) {
                $holiday = Holiday::findOrFail($this->editingId);
                $holiday->update($data);
                
                // Sync relationships
                $holiday->departments()->sync($this->selectedDepartmentIds);
                $holiday->roles()->sync($this->selectedRoleIds);
                $holiday->groups()->sync($this->selectedGroupIds);
                $holiday->employees()->sync(array_merge($this->selectedEmployeeIds, $this->additionalEmployeeIds));
                
                // Delete old holiday days and create new ones
                $holiday->holidayDays()->delete();
            } else {
                $holiday = Holiday::create($data);
                
                // Attach relationships
                if (!empty($this->selectedDepartmentIds)) {
                    $holiday->departments()->attach($this->selectedDepartmentIds);
                }
                if (!empty($this->selectedRoleIds)) {
                    $holiday->roles()->attach($this->selectedRoleIds);
                }
                if (!empty($this->selectedGroupIds)) {
                    $holiday->groups()->attach($this->selectedGroupIds);
                }
                if (!empty($this->selectedEmployeeIds) || !empty($this->additionalEmployeeIds)) {
                    $holiday->employees()->attach(array_merge($this->selectedEmployeeIds, $this->additionalEmployeeIds));
                }
            }

            // Create holiday days for reporting
            $startDate = Carbon::parse($this->fromDate);
            $endDate = Carbon::parse($this->toDate ?: $this->fromDate);
            $current = $startDate->copy();

            while ($current->lte($endDate)) {
                HolidayDay::create([
                    'holiday_id' => $holiday->id,
                    'day' => $current->format('Y-m-d'),
                ]);
                $current->addDay();
            }
        });

        session()->flash('message', $this->editingId ? 'Holiday updated successfully!' : 'Holiday created successfully!');
        $this->closeAddHolidayFlyout();
    }
    
    private function resetForm()
    {
        $this->editingId = null;
        $this->holidayName = '';
        $this->fromDate = '';
        $this->toDate = '';
        $this->isSingleDay = false;
        $this->scopeType = 'all_employees';
        $this->selectedDepartmentIds = [];
        $this->selectedRoleIds = [];
        $this->selectedGroupIds = [];
        $this->selectedEmployeeIds = [];
        $this->additionalEmployeeIds = [];
        $this->departmentSearchTerm = '';
        $this->roleSearchTerm = '';
        $this->groupSearchTerm = '';
        $this->employeeSearchTerm = '';
        $this->additionalEmployeeSearchTerm = '';
    }

    public function editHoliday($id)
    {
        $holiday = Holiday::with(['departments', 'roles', 'groups', 'employees'])->findOrFail($id);
        $this->editingId = $holiday->id;
        $this->holidayName = $holiday->name;
        $this->fromDate = $holiday->from_date->format('Y-m-d');
        $this->toDate = $holiday->to_date ? $holiday->to_date->format('Y-m-d') : '';
        $this->isSingleDay = $holiday->from_date->equalTo($holiday->to_date);
        $this->scopeType = $holiday->scope_type;
        $this->selectedDepartmentIds = $holiday->departments->pluck('id')->toArray();
        $this->selectedRoleIds = $holiday->roles->pluck('id')->toArray();
        $this->selectedGroupIds = $holiday->groups->pluck('id')->toArray();
        $this->selectedEmployeeIds = $holiday->employees->pluck('id')->toArray();
        
        // Load additional employees if needed
        if (in_array($this->scopeType, ['department', 'role', 'group'])) {
            $this->loadAdditionalEmployees();
            // Separate additional employees from selected employees
            $this->additionalEmployeeIds = array_diff($this->selectedEmployeeIds, $this->getEmployeesInScope());
            $this->selectedEmployeeIds = array_intersect($this->selectedEmployeeIds, $this->getEmployeesInScope());
        }
        
        $this->showAddHolidayFlyout = true;
    }

    public function deleteHoliday($id)
    {
        $holiday = Holiday::findOrFail($id);
        $holiday->delete();
        session()->flash('message', 'Holiday deleted successfully!');
    }

    public function loadDepartments()
    {
        $this->departments = Department::where('status', 'active')
            ->orderBy('title')
            ->get()
            ->map(function ($department) {
                return [
                    'value' => $department->id,
                    'label' => $department->title,
                ];
            })
            ->toArray();
    }

    public function loadRoles()
    {
        $this->roles = Role::orderBy('name')
            ->get()
            ->map(function ($role) {
                return [
                    'value' => $role->id,
                    'label' => $role->name,
                ];
            })
            ->toArray();
    }

    public function loadGroups()
    {
        $this->groups = Group::where('status', 'active')
            ->orderBy('name')
            ->get()
            ->map(function ($group) {
                return [
                    'value' => $group->id,
                    'label' => $group->name,
                ];
            })
            ->toArray();
    }

    public function loadEmployees()
    {
        $this->employees = Employee::select('id', 'first_name', 'last_name', 'employee_code', 'department_id')
            ->where('status', 'active')
            ->orderBy('first_name')
            ->get()
            ->map(function ($employee) {
                return [
                    'value' => $employee->id,
                    'label' => $employee->first_name . ' ' . $employee->last_name . ' (' . ($employee->employee_code ?? 'N/A') . ')',
                    'name' => $employee->first_name . ' ' . $employee->last_name,
                    'code' => $employee->employee_code ?? 'N/A',
                    'department_id' => $employee->department_id,
                ];
            })
            ->toArray();
    }

    public function loadAdditionalEmployees()
    {
        // Get employees that are NOT in the selected departments, roles, or groups
        $excludedEmployeeIds = $this->getEmployeesInScope();
        
        $query = Employee::select('id', 'first_name', 'last_name', 'employee_code')
            ->where('status', 'active');
        
        // Only exclude if there are employees to exclude
        if (!empty($excludedEmployeeIds)) {
            $query->whereNotIn('id', $excludedEmployeeIds);
        }
        
        $this->additionalEmployees = $query->orderBy('first_name')
            ->get()
            ->map(function ($employee) {
                return [
                    'value' => $employee->id,
                    'label' => $employee->first_name . ' ' . $employee->last_name . ' (' . ($employee->employee_code ?? 'N/A') . ')',
                    'name' => $employee->first_name . ' ' . $employee->last_name,
                    'code' => $employee->employee_code ?? 'N/A',
                ];
            })
            ->toArray();
    }

    private function getEmployeesInScope()
    {
        $employeeIds = [];
        
        if ($this->scopeType === 'department' && !empty($this->selectedDepartmentIds)) {
            $employeeIds = Employee::whereIn('department_id', $this->selectedDepartmentIds)
                ->where('status', 'active')
                ->pluck('id')
                ->toArray();
        } elseif ($this->scopeType === 'role' && !empty($this->selectedRoleIds)) {
            // Get user IDs with the selected roles
            $userIds = DB::table('model_has_roles')
                ->whereIn('role_id', $this->selectedRoleIds)
                ->where('model_type', 'App\Models\User')
                ->pluck('model_id')
                ->toArray();
            
            // Get employees with those user IDs
            $employeeIds = Employee::whereIn('user_id', $userIds)
                ->where('status', 'active')
                ->pluck('id')
                ->toArray();
        } elseif ($this->scopeType === 'group' && !empty($this->selectedGroupIds)) {
            // Get employees in the selected groups
            $employeeIds = Employee::whereIn('group_id', $this->selectedGroupIds)
                ->where('status', 'active')
                ->pluck('id')
                ->toArray();
        }
        
        return $employeeIds;
    }

    public function getFilteredDepartmentsProperty()
    {
        if (empty($this->departmentSearchTerm)) {
            return $this->departments;
        }
        
        return collect($this->departments)->filter(function ($department) {
            return stripos($department['label'], $this->departmentSearchTerm) !== false;
        })->values()->toArray();
    }

    public function getFilteredRolesProperty()
    {
        if (empty($this->roleSearchTerm)) {
            return $this->roles;
        }
        
        return collect($this->roles)->filter(function ($role) {
            return stripos($role['label'], $this->roleSearchTerm) !== false;
        })->values()->toArray();
    }

    public function getFilteredGroupsProperty()
    {
        if (empty($this->groupSearchTerm)) {
            return $this->groups;
        }
        
        return collect($this->groups)->filter(function ($group) {
            return stripos($group['label'], $this->groupSearchTerm) !== false;
        })->values()->toArray();
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

    public function getFilteredAdditionalEmployeesProperty()
    {
        if (empty($this->additionalEmployeeSearchTerm)) {
            return $this->additionalEmployees;
        }
        
        return collect($this->additionalEmployees)->filter(function ($employee) {
            return stripos($employee['label'], $this->additionalEmployeeSearchTerm) !== false;
        })->values()->toArray();
    }

    public function render()
    {
        $query = Holiday::with(['createdBy', 'departments', 'roles', 'groups', 'employees']);

        if (!empty($this->search)) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('from_date', 'like', '%' . $this->search . '%')
                  ->orWhere('to_date', 'like', '%' . $this->search . '%');
            });
        }

        // Handle sorting
        $query->orderBy($this->sortBy, $this->sortDirection);

        $holidays = $query->paginate(10);

        return view('livewire.system-management.system-configuration.holidays.index', [
            'holidays' => $holidays,
            'filteredDepartments' => $this->filteredDepartments,
            'filteredRoles' => $this->filteredRoles,
            'filteredGroups' => $this->filteredGroups,
            'filteredEmployees' => $this->filteredEmployees,
            'filteredAdditionalEmployees' => $this->filteredAdditionalEmployees,
        ])
            ->layout('components.layouts.app');
    }
}
