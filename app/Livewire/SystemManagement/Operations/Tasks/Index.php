<?php

namespace App\Livewire\SystemManagement\Operations\Tasks;

use App\Models\TaskTemplate;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = 'active';
    public $sortBy = 'name';
    public $sortDirection = 'asc';
    public $perPage = 10;

    public $showFormFlyout = false;
    public $showAssignFlyout = false;
    public $showDeleteModal = false;

    public $editingId = null;
    public $deletingId = null;

    public $form = [
        'name' => '',
        'description' => '',
        'fields' => [],
        'is_active' => true,
    ];

    public $assignForm = [
        'template_id' => null,
        'assignable_type' => 'employee',
        'assignable_id' => null,
    ];

    public $assignableOptions = [];

    protected $paginationTheme = 'tailwind';

    protected $listeners = [
        'refreshTemplates' => '$refresh',
    ];

    public function mount()
    {
        $user = Auth::user();
        if (!$user || (!$user->can('tasks.manage.templates') && !$user->hasRole('Super Admin'))) {
            abort(403, 'You do not have permission to manage task templates.');
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function sort(string $field): void
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->editingId = null;
        $this->showFormFlyout = true;
    }

    public function openEditModal(int $id): void
    {
        $template = TaskTemplate::findOrFail($id);
        $this->editingId = $id;
        $this->form = [
            'name' => $template->name,
            'description' => $template->description ?? '',
            'fields' => $template->fields ?? [],
            'is_active' => $template->is_active,
        ];
        $this->showFormFlyout = true;
    }

    public function openAssignModal(int $id): void
    {
        $this->assignForm['template_id'] = $id;
        $this->assignForm['assignable_type'] = 'employee';
        $this->assignForm['assignable_id'] = null;
        $this->loadAssignableOptions();
        $this->showAssignFlyout = true;
    }

    public function updatedAssignFormAssignableType(): void
    {
        $this->assignForm['assignable_id'] = null;
        $this->loadAssignableOptions();
    }

    public function loadAssignableOptions(): void
    {
        $type = $this->assignForm['assignable_type'];
        $this->assignableOptions = [];

        switch ($type) {
            case 'employee':
                $this->assignableOptions = \App\Models\Employee::with('user')
                    ->whereHas('user')
                    ->where('status', 'active')
                    ->orderBy('first_name')
                    ->get()
                    ->map(function ($employee) {
                        return [
                            'value' => $employee->id,
                            'label' => ($employee->first_name ?? '') . ' ' . ($employee->last_name ?? '') . ' (' . ($employee->employee_code ?? 'N/A') . ')',
                        ];
                    })
                    ->toArray();
                break;

            case 'department':
                $this->assignableOptions = \App\Models\Department::where('status', 'active')
                    ->orderBy('title')
                    ->get()
                    ->map(function ($department) {
                        return [
                            'value' => $department->id,
                            'label' => $department->title . ($department->code ? ' (' . $department->code . ')' : ''),
                        ];
                    })
                    ->toArray();
                break;

            case 'group':
                $this->assignableOptions = \App\Models\Group::where('status', 'active')
                    ->orderBy('name')
                    ->get()
                    ->map(function ($group) {
                        return [
                            'value' => $group->id,
                            'label' => $group->name . ($group->code ? ' (' . $group->code . ')' : ''),
                        ];
                    })
                    ->toArray();
                break;

            case 'role':
                $this->assignableOptions = \Spatie\Permission\Models\Role::orderBy('name')
                    ->get()
                    ->map(function ($role) {
                        return [
                            'value' => $role->id,
                            'label' => $role->name,
                        ];
                    })
                    ->toArray();
                break;
        }
    }

    public function closeFormFlyout(): void
    {
        $this->showFormFlyout = false;
        $this->resetForm();
    }

    public function closeAssignFlyout(): void
    {
        $this->showAssignFlyout = false;
        $this->assignForm = [
            'template_id' => null,
            'assignable_type' => 'employee',
            'assignable_id' => null,
        ];
    }

    public function resetForm(): void
    {
        $this->form = [
            'name' => '',
            'description' => '',
            'fields' => [],
            'is_active' => true,
        ];
        $this->editingId = null;
    }

    public function addField(): void
    {
        $this->form['fields'][] = [
            'name' => '',
            'label' => '',
            'type' => 'text',
            'required' => false,
            'options' => [],
        ];
    }

    public function removeField(int $index): void
    {
        unset($this->form['fields'][$index]);
        $this->form['fields'] = array_values($this->form['fields']);
    }

    public function saveTemplate(): void
    {
        $this->validate([
            'form.name' => 'required|string|max:255',
            'form.description' => 'nullable|string',
            'form.fields' => 'required|array|min:1',
            'form.fields.*.name' => 'required|string|max:255',
            'form.fields.*.label' => 'required|string|max:255',
            'form.fields.*.type' => 'required|in:text,number,textarea,select,date,time,checkbox',
            'form.fields.*.required' => 'boolean',
            'form.fields.*.options' => 'required_if:form.fields.*.type,select|array',
            'form.is_active' => 'boolean',
        ], [
            'form.fields.required' => 'At least one field is required.',
            'form.fields.*.name.required' => 'Field name is required.',
            'form.fields.*.label.required' => 'Field label is required.',
        ]);

        $data = [
            'name' => $this->form['name'],
            'description' => $this->form['description'],
            'fields' => $this->form['fields'],
            'is_active' => $this->form['is_active'],
            'created_by' => Auth::id(),
        ];

        if ($this->editingId) {
            $template = TaskTemplate::findOrFail($this->editingId);
            $template->update($data);
            session()->flash('success', 'Template updated successfully.');
        } else {
            TaskTemplate::create($data);
            session()->flash('success', 'Template created successfully.');
        }

        $this->closeFormFlyout();
        $this->dispatch('refreshTemplates');
    }

    public function confirmDelete(int $id): void
    {
        $this->deletingId = $id;
        $this->showDeleteModal = true;
    }

    public function deleteTemplate(): void
    {
        if ($this->deletingId) {
            $template = TaskTemplate::findOrFail($this->deletingId);
            $template->delete();
            session()->flash('success', 'Template deleted successfully.');
            $this->showDeleteModal = false;
            $this->deletingId = null;
            $this->dispatch('refreshTemplates');
        }
    }

    public function cancelDelete(): void
    {
        $this->showDeleteModal = false;
        $this->deletingId = null;
    }

    public function saveAssignment(): void
    {
        $this->validate([
            'assignForm.template_id' => 'required|exists:task_templates,id',
            'assignForm.assignable_type' => 'required|in:employee,department,group,role',
            'assignForm.assignable_id' => 'required|integer',
        ]);

        $template = TaskTemplate::findOrFail($this->assignForm['template_id']);
        
        // Map assignable_type to model class
        $modelMap = [
            'employee' => \App\Models\Employee::class,
            'department' => \App\Models\Department::class,
            'group' => \App\Models\Group::class,
            'role' => \Spatie\Permission\Models\Role::class,
        ];

        $assignableType = $modelMap[$this->assignForm['assignable_type']];
        
        // Check if assignment already exists
        $existing = \App\Models\TaskTemplateAssignment::where('task_template_id', $template->id)
            ->where('assignable_type', $assignableType)
            ->where('assignable_id', $this->assignForm['assignable_id'])
            ->first();

        if ($existing) {
            session()->flash('error', 'This template is already assigned to the selected entity.');
            return;
        }

        \App\Models\TaskTemplateAssignment::create([
            'task_template_id' => $template->id,
            'assignable_type' => $assignableType,
            'assignable_id' => $this->assignForm['assignable_id'],
        ]);

        session()->flash('success', 'Template assigned successfully.');
        $this->closeAssignFlyout();
    }

    public function render()
    {
        $query = TaskTemplate::query();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->statusFilter === 'active') {
            $query->where('is_active', true);
        } elseif ($this->statusFilter === 'inactive') {
            $query->where('is_active', false);
        }

        $templates = $query->orderBy($this->sortBy, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.system-management.operations.tasks.index', [
            'templates' => $templates,
        ])->layout('components.layouts.app');
    }
}
