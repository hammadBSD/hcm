<?php

namespace App\Livewire\Attendance;

use App\Models\Department;
use App\Models\Employee;
use App\Models\ExemptionDay;
use App\Models\Group;
use Spatie\Permission\Models\Role;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class ExemptionDays extends Component
{
    use WithPagination;

    public $showCreateFlyout = false;
    public $form = [
        'scope_type' => 'all',
        'department_id' => null,
        'role_id' => null,
        'user_id' => null,
        'group_id' => null,
        'from_date' => '',
        'to_date' => '',
        'notes' => '',
    ];

    public $departmentOptions = [];
    public $roleOptions = [];
    public $employeeOptions = [];
    public $groupOptions = [];

    protected $rules = [
        'form.scope_type' => 'required|in:all,department,role,user,group',
        'form.department_id' => 'required_if:form.scope_type,department|nullable|exists:departments,id',
        'form.role_id' => 'required_if:form.scope_type,role|nullable|exists:roles,id',
        'form.user_id' => 'required_if:form.scope_type,user|nullable|exists:users,id',
        'form.group_id' => 'required_if:form.scope_type,group|nullable|exists:groups,id',
        'form.from_date' => 'required|date',
        'form.to_date' => 'required|date|after_or_equal:form.from_date',
        'form.notes' => 'nullable|string|max:2000',
    ];

    protected $messages = [
        'form.scope_type.required' => 'Please select a scope type.',
        'form.department_id.required_if' => 'Please select a department.',
        'form.role_id.required_if' => 'Please select a role.',
        'form.user_id.required_if' => 'Please select an employee.',
        'form.group_id.required_if' => 'Please select a group.',
        'form.from_date.required' => 'Please select a start date.',
        'form.to_date.required' => 'Please select an end date.',
        'form.to_date.after_or_equal' => 'The end date must be after or equal to the start date.',
    ];

    public function mount()
    {
        $this->loadOptions();
    }

    public function loadOptions()
    {
        $this->departmentOptions = Department::orderBy('title')->get()->map(function ($dept) {
            return ['id' => $dept->id, 'name' => $dept->title];
        })->toArray();

        $this->roleOptions = Role::orderBy('name')->get()->map(function ($role) {
            return ['id' => $role->id, 'name' => $role->name];
        })->toArray();

        $this->groupOptions = Group::where('status', 'active')
            ->orderBy('name')
            ->get()
            ->map(function ($group) {
                return ['id' => $group->id, 'name' => $group->name];
            })
            ->toArray();

        $this->employeeOptions = Employee::with('user')
            ->where('status', 'active')
            ->orderBy('first_name')
            ->get()
            ->map(function ($employee) {
                return [
                    'id' => $employee->user_id,
                    'name' => trim($employee->first_name . ' ' . $employee->last_name) ?: ($employee->user->name ?? 'Unknown'),
                ];
            })
            ->toArray();
    }

    public function openCreateFlyout()
    {
        $this->resetForm();
        $this->showCreateFlyout = true;
    }

    public function closeCreateFlyout()
    {
        $this->showCreateFlyout = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->form = [
            'scope_type' => 'all',
            'department_id' => null,
            'role_id' => null,
            'user_id' => null,
            'group_id' => null,
            'from_date' => '',
            'to_date' => '',
            'notes' => '',
        ];
        $this->resetValidation();
    }

    public function updatedFormScopeType()
    {
        // Reset related fields when scope type changes
        $this->form['department_id'] = null;
        $this->form['role_id'] = null;
        $this->form['user_id'] = null;
        $this->form['group_id'] = null;
        $this->resetValidation(['form.department_id', 'form.role_id', 'form.user_id', 'form.group_id']);
    }

    public function submit()
    {
        $this->validate();

        DB::transaction(function () {
            ExemptionDay::create([
                'scope_type' => $this->form['scope_type'],
                'department_id' => $this->form['scope_type'] === 'department' ? $this->form['department_id'] : null,
                'role_id' => $this->form['scope_type'] === 'role' ? $this->form['role_id'] : null,
                'user_id' => $this->form['scope_type'] === 'user' ? $this->form['user_id'] : null,
                'group_id' => $this->form['scope_type'] === 'group' ? $this->form['group_id'] : null,
                'from_date' => $this->form['from_date'],
                'to_date' => $this->form['to_date'],
                'notes' => $this->form['notes'] ?: null,
                'created_by' => Auth::id(),
            ]);
        });

        session()->flash('success', __('Exemption days created successfully.'));
        $this->closeCreateFlyout();
        $this->resetPage();
    }

    public function delete($id)
    {
        $exemption = ExemptionDay::findOrFail($id);
        $exemption->delete();

        session()->flash('success', __('Exemption days deleted successfully.'));
    }

    public function render()
    {
        $exemptions = ExemptionDay::with(['department', 'role', 'user', 'group', 'creator'])
            ->orderBy('from_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('livewire.attendance.exemption-days', [
            'exemptions' => $exemptions,
        ]);
    }
}
