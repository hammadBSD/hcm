<?php

namespace App\Livewire\SystemManagement\LeavesManagement\LeaveTypes;

use App\Models\LeaveType;
use Illuminate\Validation\Rule;
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
    public $showDeleteModal = false;

    public $editingId = null;

    public $form = [
        'name' => '',
        'code' => '',
        'icon' => null,
        'description' => null,
        'requires_approval' => true,
        'is_paid' => true,
        'status' => 'active',
    ];

    protected $listeners = [
        'refreshLeaveTypes' => '$refresh',
    ];

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
        $leaveType = LeaveType::findOrFail($id);
        $this->editingId = $leaveType->id;

        $this->form = [
            'name' => $leaveType->name,
            'code' => $leaveType->code,
            'icon' => $leaveType->icon,
            'description' => $leaveType->description,
            'requires_approval' => (bool) $leaveType->requires_approval,
            'is_paid' => (bool) $leaveType->is_paid,
            'status' => $leaveType->status,
        ];

        $this->showFormFlyout = true;
    }

    public function saveLeaveType(): void
    {
        $validated = $this->validate($this->rules(), $this->messages());
        $payload = $validated['form'];
        $payload['requires_approval'] = (bool) ($payload['requires_approval'] ?? false);
        $payload['is_paid'] = (bool) ($payload['is_paid'] ?? false);

        LeaveType::updateOrCreate(
            ['id' => $this->editingId],
            $payload
        );

        $this->showFormFlyout = false;
        $this->dispatch(
            'notify',
            type: 'success',
            message: $this->editingId
                ? __('Leave type updated successfully.')
                : __('Leave type created successfully.')
        );

        $this->resetForm();
        $this->resetPage();
    }

    public function confirmDelete(int $id): void
    {
        $this->editingId = $id;
        $this->showDeleteModal = true;
    }

    public function deleteLeaveType(): void
    {
        if ($this->editingId) {
            LeaveType::where('id', $this->editingId)->delete();
        }

        $this->showDeleteModal = false;
        $this->dispatch('notify', type: 'success', message: __('Leave type removed.'));

        $this->resetForm();
        $this->resetPage();
    }

    public function toggleStatus(int $id): void
    {
        $leaveType = LeaveType::findOrFail($id);
        $leaveType->status = $leaveType->status === 'active' ? 'inactive' : 'active';
        $leaveType->save();

        $this->dispatch('notify', type: 'success', message: __('Status updated.'));
    }

    protected function rules(): array
    {
        return [
            'form.name' => ['required', 'string', 'max:150'],
            'form.code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('leave_types', 'code')->ignore($this->editingId),
            ],
            'form.icon' => ['nullable', 'string', 'max:100'],
            'form.description' => ['nullable', 'string', 'max:500'],
            'form.requires_approval' => ['boolean'],
            'form.is_paid' => ['boolean'],
            'form.status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }

    protected function messages(): array
    {
        return [
            'form.name.required' => __('Please provide a name for the leave type.'),
            'form.code.required' => __('A unique code is required.'),
            'form.code.unique' => __('This leave code is already in use.'),
        ];
    }

    protected function resetForm(): void
    {
        $this->form = [
            'name' => '',
            'code' => '',
            'icon' => null,
            'description' => null,
            'requires_approval' => true,
            'is_paid' => true,
            'status' => 'active',
        ];

        $this->editingId = null;
    }

    public function getLeaveTypesProperty()
    {
        $query = LeaveType::query()
            ->when($this->search, function ($query) {
                $term = '%' . trim($this->search) . '%';
                $query->where(function ($q) use ($term) {
                    $q->where('name', 'like', $term)
                        ->orWhere('code', 'like', $term)
                        ->orWhere('description', 'like', $term);
                });
            })
            ->when($this->statusFilter && $this->statusFilter !== 'all', function ($query) {
                $query->where('status', $this->statusFilter);
            });

        if (in_array($this->sortBy, ['name', 'code', 'status'])) {
            $query->orderBy($this->sortBy, $this->sortDirection);
        } else {
            $query->orderBy('name', 'asc');
        }

        return $query->paginate($this->perPage);
    }

    public function render()
    {
        return view('livewire.system-management.leaves-management.leave-types.index', [
            'leaveTypes' => $this->leaveTypes,
        ])->layout('components.layouts.app');
    }
}

