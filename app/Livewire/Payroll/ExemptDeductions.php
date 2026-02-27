<?php

namespace App\Livewire\Payroll;

use App\Models\Department;
use App\Models\DeductionExemption;
use App\Models\Employee;
use App\Models\Group;
use Spatie\Permission\Models\Role;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class ExemptDeductions extends Component
{
    use WithPagination;

    public $showCreateFlyout = false;
    public $form = [
        'scope_type' => 'all',
        'department_id' => null,
        'role_id' => null,
        'user_id' => null,
        'group_id' => null,
        'year_month' => '',
        'exemption_type' => 'all',
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
        'form.year_month' => 'required|date_format:Y-m',
        'form.exemption_type' => 'required|in:absent_days,hourly_deduction_short_hours,all',
        'form.notes' => 'nullable|string|max:2000',
    ];

    protected $messages = [
        'form.scope_type.required' => 'Please select a scope type.',
        'form.department_id.required_if' => 'Please select a department.',
        'form.role_id.required_if' => 'Please select a role.',
        'form.user_id.required_if' => 'Please select an employee.',
        'form.group_id.required_if' => 'Please select a group.',
        'form.year_month.required' => 'Please select year and month.',
        'form.exemption_type.required' => 'Please select an exemption type.',
    ];

    public function mount()
    {
        $this->loadOptions();
        if (empty($this->form['year_month'])) {
            $this->form['year_month'] = now()->format('Y-m');
        }
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
        $this->form['year_month'] = now()->format('Y-m');
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
            'year_month' => now()->format('Y-m'),
            'exemption_type' => 'all',
            'notes' => '',
        ];
        $this->resetValidation();
    }

    public function updatedFormScopeType()
    {
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
            DeductionExemption::create([
                'year_month' => $this->form['year_month'],
                'scope_type' => $this->form['scope_type'],
                'department_id' => $this->form['scope_type'] === 'department' ? $this->form['department_id'] : null,
                'role_id' => $this->form['scope_type'] === 'role' ? $this->form['role_id'] : null,
                'user_id' => $this->form['scope_type'] === 'user' ? $this->form['user_id'] : null,
                'group_id' => $this->form['scope_type'] === 'group' ? $this->form['group_id'] : null,
                'exemption_type' => $this->form['exemption_type'],
                'notes' => $this->form['notes'] ?: null,
                'created_by' => Auth::id(),
            ]);
        });

        session()->flash('success', __('Exempt deduction created successfully.'));
        $this->closeCreateFlyout();
        $this->resetPage();
    }

    public function delete($id)
    {
        $exemption = DeductionExemption::findOrFail($id);
        $exemption->delete();

        session()->flash('success', __('Exempt deduction deleted successfully.'));
    }

    public function render()
    {
        $exemptions = DeductionExemption::with(['department', 'role', 'user', 'group', 'creator'])
            ->orderBy('year_month', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('livewire.payroll.exempt-deductions', [
            'exemptions' => $exemptions,
        ]);
    }
}
