<?php

namespace App\Livewire\SystemManagement\UserManagement\UserRoles;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role as RoleModel;

class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $sortBy = 'name';
    public string $sortDirection = 'asc';
    public int $perPage = 10;

    public bool $showManageUsersModal = false;
    public bool $showRoleFormModal = false;
    public bool $showRoleDetailsModal = false;

    public ?int $manageRoleId = null;
    public array $manageRoleData = [];
    public array $assignedUserIds = [];
    public array $availableUsers = [];
    public string $userSearch = '';

    public array $permissionGroups = [];
    public array $selectedPermissionCategories = [];
    public string $permissionSearch = '';

    public string $roleFormMode = 'create';
    public ?int $roleFormId = null;
    public array $roleForm = [
        'name' => '',
        'description' => '',
        'is_active' => true,
        'permissions' => [],
    ];

    public array $roleDetails = [];

    protected array $roleMeta = [
        'Super Admin' => [
            'icon' => 'shield-check',
            'color_classes' => 'bg-red-100 dark:bg-red-900 text-red-600 dark:text-red-400',
            'description' => 'Full system access and control',
        ],
        'HR Director' => [
            'icon' => 'users',
            'color_classes' => 'bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400',
            'description' => 'Oversees HR policies, approvals, and people operations',
        ],
        'HR Manager' => [
            'icon' => 'user-group',
            'color_classes' => 'bg-indigo-100 dark:bg-indigo-900 text-indigo-600 dark:text-indigo-400',
            'description' => 'Manages HR workflows, timesheets, and leave approvals',
        ],
        'HR Staff' => [
            'icon' => 'user',
            'color_classes' => 'bg-amber-100 dark:bg-amber-900 text-amber-600 dark:text-amber-400',
            'description' => 'Handles daily HR operations and employee updates',
        ],
        'Payroll Admin' => [
            'icon' => 'banknotes',
            'color_classes' => 'bg-emerald-100 dark:bg-emerald-900 text-emerald-600 dark:text-emerald-400',
            'description' => 'Processes payroll runs and salary adjustments',
        ],
        'Department Manager' => [
            'icon' => 'briefcase',
            'color_classes' => 'bg-purple-100 dark:bg-purple-900 text-purple-600 dark:text-purple-400',
            'description' => 'Approves team activity and oversees department staff',
        ],
        'Team Lead' => [
            'icon' => 'flag',
            'color_classes' => 'bg-teal-100 dark:bg-teal-900 text-teal-600 dark:text-teal-400',
            'description' => 'Guides team members and validates attendance records',
        ],
        'Employee' => [
            'icon' => 'user-circle',
            'color_classes' => 'bg-zinc-100 dark:bg-zinc-900 text-zinc-600 dark:text-zinc-300',
            'description' => 'Access to self-service tools and personal records',
        ],
        'Contractor' => [
            'icon' => 'rectangle-stack',
            'color_classes' => 'bg-sky-100 dark:bg-sky-900 text-sky-600 dark:text-sky-400',
            'description' => 'Limited access for contract-based staff',
        ],
        'Intern' => [
            'icon' => 'academic-cap',
            'color_classes' => 'bg-pink-100 dark:bg-pink-900 text-pink-600 dark:text-pink-400',
            'description' => 'Basic platform access for trainees and interns',
        ],
    ];

    protected array $defaultRoleMeta = [
        'icon' => 'shield-check',
        'color_classes' => 'bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400',
        'description' => 'Custom role',
    ];

    protected $queryString = [
        'search' => ['except' => ''],
        'sortBy' => ['except' => 'name'],
        'sortDirection' => ['except' => 'asc'],
        'page' => ['except' => 1],
    ];

    public function mount(): void
    {
        $this->loadPermissionGroups();
        $this->selectedPermissionCategories = array_keys($this->permissionGroups);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingSortBy(): void
    {
        $this->resetPage();
    }

    public function updatingSortDirection(): void
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

    public function openCreateRoleForm(): void
    {
        $this->resetRoleForm();
        $this->roleFormMode = 'create';
        $this->roleFormId = null;
        $this->permissionSearch = '';
        $this->selectedPermissionCategories = array_keys($this->permissionGroups);

        $this->resetValidation();
        $this->showRoleFormModal = true;
    }

    public function editRole(int $id): void
    {
        $role = RoleModel::with('permissions')->findOrFail($id);

        $this->resetRoleForm([
            'name' => $role->name,
            'description' => $role->description,
            'is_active' => (bool) $role->is_active,
            'permissions' => $role->permissions->pluck('name')->toArray(),
        ]);

        $this->roleFormMode = 'edit';
        $this->roleFormId = $role->id;
        $this->permissionSearch = '';
        $this->selectedPermissionCategories = array_keys($this->permissionGroups);

        $this->resetValidation();
        $this->showRoleFormModal = true;
    }

    public function duplicateRole(int $id): void
    {
        $role = RoleModel::with('permissions')->findOrFail($id);

        $this->resetRoleForm([
            'name' => $this->generateCloneName($role->name),
            'description' => $role->description,
            'is_active' => (bool) $role->is_active,
            'permissions' => $role->permissions->pluck('name')->toArray(),
        ]);

        $this->roleFormMode = 'clone';
        $this->roleFormId = null;
        $this->permissionSearch = '';
        $this->selectedPermissionCategories = array_keys($this->permissionGroups);

        $this->resetValidation();
        $this->showRoleFormModal = true;
    }

    public function viewRole(int $id): void
    {
        $role = RoleModel::with(['permissions', 'users:id,name,email'])->findOrFail($id);

        $this->roleDetails = [
            'id' => $role->id,
            'name' => $role->name,
            'description' => $role->description,
            'is_active' => (bool) $role->is_active,
            'users' => $role->users->map(function (User $user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name ?? 'Unnamed User',
                    'email' => $user->email ?? '',
                ];
            })->values()->toArray(),
            'permissions' => $this->groupPermissionsForDisplay($role->permissions->pluck('name')->toArray()),
            'created_at' => optional($role->created_at)->toDayDateTimeString(),
            'updated_at' => optional($role->updated_at)->toDayDateTimeString(),
        ];

        $this->showRoleDetailsModal = true;
    }

    public function closeRoleFormModal(): void
    {
        $this->showRoleFormModal = false;
        $this->roleFormMode = 'create';
        $this->roleFormId = null;
        $this->resetRoleForm();
        $this->permissionSearch = '';
        $this->selectedPermissionCategories = array_keys($this->permissionGroups);
        $this->resetValidation();
    }

    public function closeRoleDetailsModal(): void
    {
        $this->showRoleDetailsModal = false;
        $this->roleDetails = [];
    }

    public function saveRole(): void
    {
        $this->validate($this->roleValidationRules());

        $payload = [
            'name' => $this->roleForm['name'],
            'description' => $this->roleForm['description'] ?: null,
            'is_active' => (bool) $this->roleForm['is_active'],
        ];

        if ($this->roleFormMode === 'edit' && $this->roleFormId) {
            $role = RoleModel::findOrFail($this->roleFormId);
            $role->fill($payload);
            $role->save();
            $message = 'Role updated successfully.';
        } else {
            $role = RoleModel::create(array_merge($payload, [
                'guard_name' => 'web',
            ]));
            $message = $this->roleFormMode === 'clone'
                ? 'Role cloned successfully.'
                : 'Role created successfully.';
        }

        $role->syncPermissions($this->roleForm['permissions']);

        session()->flash('success', $message);

        $this->closeRoleFormModal();
        $this->resetPage();
    }

    public function togglePermissionCategory(string $category): void
    {
        if (! array_key_exists($category, $this->permissionGroups)) {
            return;
        }

        if (in_array($category, $this->selectedPermissionCategories, true)) {
            if (count($this->selectedPermissionCategories) === 1) {
                return;
            }

            $this->selectedPermissionCategories = array_values(array_diff(
                $this->selectedPermissionCategories,
                [$category]
            ));
        } else {
            $this->selectedPermissionCategories[] = $category;
        }
    }

    public function selectAllPermissionCategories(): void
    {
        $this->selectedPermissionCategories = array_keys($this->permissionGroups);
    }

    public function updatedUserSearch(): void
    {
        $this->loadAvailableUsers();
    }

    public function openManageUsersModal(int $roleId): void
    {
        $role = RoleModel::with('users:id')->findOrFail($roleId);

        $this->manageRoleId = $role->id;
        $this->manageRoleData = [
            'id' => $role->id,
            'name' => $role->name,
        ];
        $this->assignedUserIds = $role->users->pluck('id')->map(fn ($id) => (string) $id)->toArray();
        $this->userSearch = '';
        $this->showManageUsersModal = true;

        $this->loadAvailableUsers();
    }

    public function saveRoleAssignments(): void
    {
        if (! $this->manageRoleId) {
            return;
        }

        $role = RoleModel::with('users:id')->findOrFail($this->manageRoleId);

        $desiredIds = collect($this->assignedUserIds)
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $currentIds = $role->users->pluck('id');

        $toAttach = $desiredIds->diff($currentIds)->all();
        $toDetach = $currentIds->diff($desiredIds)->all();

        if (! empty($toAttach)) {
            User::query()
                ->whereIn('id', $toAttach)
                ->each(fn (User $user) => $user->assignRole($role->name));
        }

        if (! empty($toDetach)) {
            User::query()
                ->whereIn('id', $toDetach)
                ->each(fn (User $user) => $user->removeRole($role->name));
        }

        $this->closeManageUsersModal();
    }

    public function closeManageUsersModal(): void
    {
        $this->reset([
            'showManageUsersModal',
            'manageRoleId',
            'manageRoleData',
            'assignedUserIds',
            'availableUsers',
            'userSearch',
        ]);
    }

    protected function loadAvailableUsers(): void
    {
        if (! $this->manageRoleId) {
            $this->availableUsers = [];

            return;
        }

        $assignedIds = collect($this->assignedUserIds)
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->all();

        $query = User::query()
            ->select('id', 'name', 'email')
            ->orderBy('name');

        if ($this->userSearch !== '') {
            $searchTerm = '%' . trim($this->userSearch) . '%';
            $query->where(function ($innerQuery) use ($searchTerm) {
                $innerQuery->where('name', 'like', $searchTerm)
                    ->orWhere('email', 'like', $searchTerm);
            });
        }

        $users = $query->limit(50)->get();

        if (! empty($assignedIds)) {
            $assignedUsers = User::query()
                ->select('id', 'name', 'email')
                ->whereIn('id', $assignedIds)
                ->get();

            $users = $users->concat($assignedUsers);
        }

        $this->availableUsers = $users
            ->unique('id')
            ->sortBy(fn (User $user) => strtolower($user->name ?? ''))
            ->map(function (User $user) {
                return [
                    'id' => (string) $user->id,
                    'name' => $user->name ?? 'Unnamed User',
                    'email' => $user->email ?? '',
                ];
            })
            ->values()
            ->toArray();
    }

    protected function loadPermissionGroups(): void
    {
        $permissions = Permission::orderBy('name')->get();

        $groups = [];

        foreach ($permissions as $permission) {
            $name = $permission->name;
            $categoryKey = $this->permissionCategory($name);

            if (! isset($groups[$categoryKey])) {
                $groups[$categoryKey] = [
                    'label' => $this->titleCase($categoryKey),
                    'permissions' => [],
                ];
            }

            $groups[$categoryKey]['permissions'][] = [
                'name' => $name,
                'label' => $this->permissionLabel($name),
            ];
        }

        ksort($groups);

        $this->permissionGroups = $groups;
    }

    protected function groupPermissionsForDisplay(array $permissions): array
    {
        $grouped = [];

        foreach ($permissions as $permission) {
            $categoryKey = $this->permissionCategory($permission);

            if (! isset($grouped[$categoryKey])) {
                $grouped[$categoryKey] = [
                    'label' => $this->titleCase($categoryKey),
                    'items' => [],
                ];
            }

            $grouped[$categoryKey]['items'][] = $this->permissionLabel($permission);
        }

        ksort($grouped);

        foreach ($grouped as $key => $group) {
            sort($grouped[$key]['items']);
        }

        return $grouped;
    }

    protected function permissionCategory(string $permission): string
    {
        return explode('.', $permission, 2)[0] ?? 'general';
    }

    protected function permissionLabel(string $permission): string
    {
        $parts = explode('.', $permission);

        if (count($parts) === 1) {
            return $this->titleCase($parts[0]);
        }

        return $this->titleCase(implode(' ', array_slice($parts, 1)));
    }

    protected function titleCase(string $value): string
    {
        return Str::title(str_replace(['_', '-'], ' ', $value));
    }

    protected function roleValidationRules(): array
    {
        return [
            'roleForm.name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles', 'name')->ignore($this->roleFormId),
            ],
            'roleForm.description' => ['nullable', 'string', 'max:1000'],
            'roleForm.is_active' => ['boolean'],
            'roleForm.permissions' => ['array'],
            'roleForm.permissions.*' => ['string', Rule::exists('permissions', 'name')],
        ];
    }

    protected function resetRoleForm(array $overrides = []): void
    {
        $defaults = [
            'name' => '',
            'description' => '',
            'is_active' => true,
            'permissions' => [],
        ];

        $this->roleForm = array_merge($defaults, $overrides);
    }

    protected function generateCloneName(string $name): string
    {
        $base = $name . ' Copy';
        $suffix = 1;
        $candidate = $base;

        while (RoleModel::where('name', $candidate)->exists()) {
            $suffix++;
            $candidate = $base . ' ' . $suffix;
        }

        return $candidate;
    }

    protected function getRoleMetaFor(string $roleName): array
    {
        return array_merge($this->defaultRoleMeta, $this->roleMeta[$roleName] ?? []);
    }

    public function getFilteredPermissionGroupsProperty(): array
    {
        $groups = collect($this->permissionGroups);

        if (! empty($this->selectedPermissionCategories)) {
            $groups = $groups->filter(fn ($group, $key) => in_array($key, $this->selectedPermissionCategories, true));
        }

        if ($this->permissionSearch !== '') {
            $search = Str::lower($this->permissionSearch);

            $groups = $groups->map(function ($group) use ($search) {
                $filtered = collect($group['permissions'])
                    ->filter(function ($permission) use ($search) {
                        return Str::contains(Str::lower($permission['label']), $search)
                            || Str::contains(Str::lower($permission['name']), $search);
                    })
                    ->values()
                    ->all();

                return [
                    'label' => $group['label'],
                    'permissions' => $filtered,
                ];
            })->filter(fn ($group) => ! empty($group['permissions']));
        }

        return $groups->toArray();
    }

    public function render(): View
    {
        $query = RoleModel::query()
            ->withCount(['users', 'permissions']);

        if ($this->search !== '') {
            $searchTerm = '%' . trim($this->search) . '%';
            $query->where('name', 'like', $searchTerm);
        }

        $sortColumn = match ($this->sortBy) {
            'users' => 'users_count',
            'permissions' => 'permissions_count',
            default => 'name',
        };

        $roles = $query
            ->orderBy($sortColumn, $this->sortDirection)
            ->paginate($this->perPage);

        $roleMeta = [];
        foreach ($roles as $role) {
            $roleMeta[$role->id] = $this->getRoleMetaFor($role->name);
        }

        return view('livewire.system-management.user-management.user-roles.index', [
            'roles' => $roles,
            'totalPermissions' => Permission::count(),
            'roleMeta' => $roleMeta,
            'defaultRoleMeta' => $this->defaultRoleMeta,
            'filteredPermissionGroups' => $this->filteredPermissionGroups,
        ])->layout('components.layouts.app');
    }
}
