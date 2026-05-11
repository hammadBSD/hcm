<?php

namespace App\Livewire\SystemManagement\SystemConfiguration\Announcements;

use App\Models\Announcement;
use App\Models\Department;
use App\Models\Group;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

class Index extends Component
{
    use WithPagination;

    public string $sortBy = 'start_date';

    public string $sortDirection = 'desc';

    public string $search = '';

    public bool $showFlyout = false;

    public ?int $editingId = null;

    public string $title = '';

    public string $content = '';

    public string $type = 'info';

    public string $startDate = '';

    public string $endDate = '';

    public bool $isPinned = false;

    public string $status = 'active';

    public string $scopeType = Announcement::SCOPE_ALL;

    /** @var array<int, int|string> */
    public array $selectedDepartmentIds = [];

    /** @var array<int, int|string> */
    public array $selectedRoleIds = [];

    /** @var array<int, int|string> */
    public array $selectedGroupIds = [];

    /** @var array<int, array{value:int,label:string}> */
    public array $departments = [];

    /** @var array<int, array{value:int,label:string}> */
    public array $roles = [];

    /** @var array<int, array{value:int,label:string}> */
    public array $groups = [];

    public string $departmentSearchTerm = '';

    public string $roleSearchTerm = '';

    public string $groupSearchTerm = '';

    protected string $paginationTheme = 'tailwind';

    public function mount(): void
    {
        $this->loadDepartments();
        $this->loadRoles();
        $this->loadGroups();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatedScopeType(): void
    {
        $this->selectedDepartmentIds = [];
        $this->selectedRoleIds = [];
        $this->selectedGroupIds = [];
        $this->departmentSearchTerm = '';
        $this->roleSearchTerm = '';
        $this->groupSearchTerm = '';
    }

    public function sort(string $field): void
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    public function createAnnouncement(): void
    {
        $this->resetForm();
        $this->showFlyout = true;
    }

    public function closeFlyout(): void
    {
        $this->showFlyout = false;
        $this->resetForm();
    }

    public function submitAnnouncement(): void
    {
        $rules = [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'required|in:info,warning,success,error',
            'startDate' => 'required|date',
            'endDate' => 'required|date|after_or_equal:startDate',
            'isPinned' => 'boolean',
            'status' => 'required|in:active,inactive',
            'scopeType' => 'required|in:'.Announcement::SCOPE_ALL.','.Announcement::SCOPE_DEPARTMENT.','.Announcement::SCOPE_ROLE.','.Announcement::SCOPE_GROUP,
        ];

        if ($this->scopeType === Announcement::SCOPE_DEPARTMENT) {
            $rules['selectedDepartmentIds'] = 'required|array|min:1';
            $rules['selectedDepartmentIds.*'] = 'exists:departments,id';
        } elseif ($this->scopeType === Announcement::SCOPE_ROLE) {
            $rules['selectedRoleIds'] = 'required|array|min:1';
            $rules['selectedRoleIds.*'] = 'exists:roles,id';
        } elseif ($this->scopeType === Announcement::SCOPE_GROUP) {
            $rules['selectedGroupIds'] = 'required|array|min:1';
            $rules['selectedGroupIds.*'] = 'exists:groups,id';
        }

        $this->validate($rules);

        $deptIds = array_map('intval', $this->selectedDepartmentIds);
        $roleIds = array_map('intval', $this->selectedRoleIds);
        $groupIds = array_map('intval', $this->selectedGroupIds);

        DB::transaction(function () use ($deptIds, $roleIds, $groupIds) {
            $data = [
                'title' => $this->title,
                'content' => $this->content,
                'type' => $this->type,
                'scope_type' => $this->scopeType,
                'start_date' => $this->startDate,
                'end_date' => $this->endDate,
                'is_pinned' => $this->isPinned,
                'status' => $this->status,
                'created_by' => Auth::id(),
            ];

            if ($this->editingId) {
                $announcement = Announcement::findOrFail($this->editingId);
                unset($data['created_by']);
                $announcement->update($data);
            } else {
                $announcement = Announcement::create($data);
            }

            $announcement->departments()->sync(
                $this->scopeType === Announcement::SCOPE_DEPARTMENT ? $deptIds : []
            );
            $announcement->roles()->sync(
                $this->scopeType === Announcement::SCOPE_ROLE ? $roleIds : []
            );
            $announcement->groups()->sync(
                $this->scopeType === Announcement::SCOPE_GROUP ? $groupIds : []
            );
        });

        session()->flash('message', $this->editingId ? __('Announcement updated successfully.') : __('Announcement created successfully.'));
        $this->closeFlyout();
    }

    public function editAnnouncement(int $id): void
    {
        $row = Announcement::with(['departments', 'roles', 'groups'])->findOrFail($id);
        $this->editingId = $row->id;
        $this->title = $row->title;
        $this->content = $row->content;
        $this->type = $row->type;
        $this->startDate = $row->start_date->format('Y-m-d');
        $this->endDate = $row->end_date->format('Y-m-d');
        $this->isPinned = (bool) $row->is_pinned;
        $this->status = $row->status;
        $this->scopeType = $row->scope_type ?? Announcement::SCOPE_ALL;
        $this->selectedDepartmentIds = $row->departments->pluck('id')->map(fn ($id) => (int) $id)->all();
        $this->selectedRoleIds = $row->roles->pluck('id')->map(fn ($id) => (int) $id)->all();
        $this->selectedGroupIds = $row->groups->pluck('id')->map(fn ($id) => (int) $id)->all();
        $this->showFlyout = true;
    }

    public function deleteAnnouncement(int $id): void
    {
        Announcement::findOrFail($id)->delete();
        session()->flash('message', __('Announcement deleted successfully.'));
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->title = '';
        $this->content = '';
        $this->type = 'info';
        $this->startDate = '';
        $this->endDate = '';
        $this->isPinned = false;
        $this->status = 'active';
        $this->scopeType = Announcement::SCOPE_ALL;
        $this->selectedDepartmentIds = [];
        $this->selectedRoleIds = [];
        $this->selectedGroupIds = [];
        $this->departmentSearchTerm = '';
        $this->roleSearchTerm = '';
        $this->groupSearchTerm = '';
    }

    private function loadDepartments(): void
    {
        $this->departments = Department::where('status', 'active')
            ->orderBy('title')
            ->get()
            ->map(fn ($d) => ['value' => $d->id, 'label' => $d->title])
            ->all();
    }

    private function loadRoles(): void
    {
        $this->roles = Role::query()->orderBy('name')
            ->get()
            ->map(fn ($r) => ['value' => $r->id, 'label' => $r->name])
            ->all();
    }

    private function loadGroups(): void
    {
        $this->groups = Group::where('status', 'active')
            ->orderBy('name')
            ->get()
            ->map(fn ($g) => ['value' => $g->id, 'label' => $g->name])
            ->all();
    }

    public function getFilteredDepartmentsProperty(): array
    {
        if ($this->departmentSearchTerm === '') {
            return $this->departments;
        }

        return collect($this->departments)
            ->filter(fn ($d) => stripos($d['label'], $this->departmentSearchTerm) !== false)
            ->values()
            ->all();
    }

    public function getFilteredRolesProperty(): array
    {
        if ($this->roleSearchTerm === '') {
            return $this->roles;
        }

        return collect($this->roles)
            ->filter(fn ($r) => stripos($r['label'], $this->roleSearchTerm) !== false)
            ->values()
            ->all();
    }

    public function getFilteredGroupsProperty(): array
    {
        if ($this->groupSearchTerm === '') {
            return $this->groups;
        }

        return collect($this->groups)
            ->filter(fn ($g) => stripos($g['label'], $this->groupSearchTerm) !== false)
            ->values()
            ->all();
    }

    public function render()
    {
        $query = Announcement::query()->with(['creator', 'departments', 'roles', 'groups']);

        if ($this->search !== '') {
            $term = '%'.$this->search.'%';
            $query->where(function ($q) use ($term) {
                $q->where('title', 'like', $term)
                    ->orWhere('content', 'like', $term);
            });
        }

        $query->orderBy($this->sortBy, $this->sortDirection);

        return view('livewire.system-management.system-configuration.announcements.index', [
            'announcements' => $query->paginate(10),
            'filteredDepartments' => $this->filteredDepartments,
            'filteredRoles' => $this->filteredRoles,
            'filteredGroups' => $this->filteredGroups,
        ])->layout('components.layouts.app');
    }
}
