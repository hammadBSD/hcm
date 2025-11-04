<?php

namespace App\Livewire\SystemManagement\OrganizationSetting\Department;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Group;

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
    public $status = 'active';

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

    public function render()
    {
        $query = Department::with(['departmentHead.user', 'parent', 'group']);

        if (!empty($this->search)) {
            $query->where(function($q) {
                $q->where('title', 'like', '%' . $this->search . '%')
                  ->orWhere('code', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        $query->orderBy($this->sortBy, $this->sortDirection);
        $departments = $query->paginate(10);

        // Load employees for dropdown
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
            ->values();

        // Load groups for dropdown
        $groups = Group::where('status', 'active')->orderBy('name')->get();
        
        // Load departments for parent dropdown
        $parentDepartments = Department::where('status', 'active')
            ->where('id', '!=', $this->editingId)
            ->orderBy('title')
            ->get();

        return view('livewire.system-management.organization-setting.department.index', [
            'departments' => $departments,
            'employees' => $employees,
            'groups' => $groups,
            'parentDepartments' => $parentDepartments,
        ])
            ->layout('components.layouts.app');
    }
}
