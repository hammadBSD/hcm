<section class="w-full">
    @include('partials.system-management-heading')

    <x-system-management.layout :heading="__('User Roles')" :subheading="__('Manage user access roles')">
        <div class="space-y-6">
            <!-- Action Bar -->
            <div class="flex justify-between items-center gap-4">
                <!-- Search Section -->
                <div class="flex items-center gap-3">
                    <flux:input 
                        type="search" 
                        wire:model.live="search" 
                        placeholder="Search roles..." 
                        class="w-80"
                    />
                    <flux:button variant="outline" icon="funnel" />
                </div>
                
                <!-- Action Buttons -->
                <div class="flex items-center gap-3">
                    <flux:button variant="outline" icon="arrow-down-tray">
                        Export
                    </flux:button>
                    <flux:button variant="primary" icon="plus" wire:click="openCreateRoleForm">
                        Add Role
                    </flux:button>
                </div>
            </div>

            <!-- Roles Table -->
            <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 overflow-hidden shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-zinc-50 dark:bg-zinc-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    <button wire:click="sort('name')" class="flex items-center gap-1 hover:text-zinc-700 dark:hover:text-zinc-200">
                                        {{ __('Role Name') }}
                                        @if($sortBy === 'name')
                                            <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="w-4 h-4" />
                                        @endif
                                    </button>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    <button wire:click="sort('users')" class="flex items-center gap-1 hover:text-zinc-700 dark:hover:text-zinc-200">
                                        {{ __('Users') }}
                                        @if($sortBy === 'users')
                                            <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="w-4 h-4" />
                                        @endif
                                    </button>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    <button wire:click="sort('permissions')" class="flex items-center gap-1 hover:text-zinc-700 dark:hover:text-zinc-200">
                                        {{ __('Permissions') }}
                                        @if($sortBy === 'permissions')
                                            <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="w-4 h-4" />
                                        @endif
                                    </button>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    {{ __('Status') }}
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    {{ __('Actions') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-zinc-800 divide-y divide-zinc-200 dark:divide-zinc-700">
                            @forelse($roles as $role)
                                @php
                                    $meta = $roleMeta[$role->id] ?? $defaultRoleMeta;
                                    $description = $role->description ?? ($meta['description'] ?? $defaultRoleMeta['description']);
                                    $permissionsLabel = $role->permissions_count === $totalPermissions
                                        ? __('All Permissions')
                                        : $role->permissions_count . ' ' . __('Permissions');
                                @endphp
                                <tr class="hover:bg-zinc-100 dark:hover:bg-zinc-600 transition-colors duration-150">
                                    <td class="px-6 py-6 whitespace-nowrap">
                                        <div class="flex items-center gap-3">
                                            <div class="h-9 w-9 rounded-lg flex items-center justify-center {{ $meta['color_classes'] }}">
                                                <flux:icon :name="$meta['icon']" class="h-4 w-4" />
                                            </div>
                                            <div>
                                                <div class="font-medium text-zinc-900 dark:text-zinc-100">
                                                    {{ $role->name }}
                                                </div>
                                                <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                                    {{ $description }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="px-6 py-6 whitespace-nowrap">
                                        <div class="text-sm text-zinc-900 dark:text-zinc-100">
                                            {{ $role->users_count }}
                                        </div>
                                    </td>

                                    <td class="px-6 py-6 whitespace-nowrap">
                                        <div class="text-sm text-zinc-900 dark:text-zinc-100">
                                            {{ $permissionsLabel }}
                                        </div>
                                    </td>

                                    <td class="px-6 py-6 whitespace-nowrap">
                                        <flux:badge :color="$role->is_active ? 'green' : 'zinc'" size="sm">
                                            {{ $role->is_active ? __('Active') : __('Inactive') }}
                                        </flux:badge>
                                    </td>

                                    <td class="px-6 py-6 whitespace-nowrap text-sm font-medium">
                                        <div class="flex items-center gap-1">
                                            <flux:dropdown>
                                                <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                                                <flux:menu>
                                                    <flux:menu.item icon="pencil" wire:click="editRole({{ $role->id }})">
                                                        {{ __('Edit Role') }}
                                                    </flux:menu.item>
                                                    <flux:menu.item icon="document-duplicate" wire:click="duplicateRole({{ $role->id }})">
                                                        {{ __('Clone Role') }}
                                                    </flux:menu.item>
                                                    <flux:menu.item icon="user-plus" wire:click="openManageUsersModal({{ $role->id }})">
                                                        {{ __('Manage Users') }}
                                                    </flux:menu.item>
                                                    <flux:menu.item icon="eye" wire:click="viewRole({{ $role->id }})">
                                                        {{ __('View Details') }}
                                                    </flux:menu.item>
                                                </flux:menu>
                                            </flux:dropdown>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-sm text-zinc-500 dark:text-zinc-400">
                                        <div class="flex flex-col items-center gap-3">
                                            <flux:icon name="shield-exclamation" class="w-10 h-10 text-zinc-400 dark:text-zinc-600" />
                                            <div>{{ __('No roles found. Try adjusting your search.') }}</div>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            <div class="mt-6">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-zinc-700 dark:text-zinc-300">
                        @if($roles->total())
                            {{ __('Showing :from to :to of :total results', ['from' => $roles->firstItem(), 'to' => $roles->lastItem(), 'total' => $roles->total()]) }}
                        @else
                            {{ __('Showing 0 results') }}
                        @endif
                    </div>
                    <div>
                        {{ $roles->onEachSide(1)->links() }}
                    </div>
                </div>
            </div>
        </div>
    </x-system-management.layout>

    <!-- Manage Users Modal -->
    <flux:modal wire:model="showManageUsersModal" size="xl" :title="__('Manage Users')">
        <div class="space-y-6">
            <div>
                <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">
                    {{ $manageRoleData['name'] ?? __('Selected Role') }}
                </h3>
                <p class="text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('Choose which system users should have this role.') }}
                </p>
            </div>

            <div>
                <flux:input
                    type="search"
                    wire:model.live="userSearch"
                    placeholder="{{ __('Search users by name or email...') }}"
                    class="w-full"
                    icon="magnifying-glass"
                />
            </div>

            <div class="max-h-80 overflow-y-auto border border-zinc-200 dark:border-zinc-700 rounded-lg divide-y divide-zinc-200 dark:divide-zinc-700">
                @forelse($availableUsers as $user)
                    <label class="flex items-start gap-3 px-4 py-3 hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors" wire:key="available-user-{{ $user['id'] }}">
                        <input
                            type="checkbox"
                            value="{{ $user['id'] }}"
                            wire:model="assignedUserIds"
                            class="mt-1 h-4 w-4 rounded border-zinc-300 text-zinc-900 dark:border-zinc-600 dark:bg-zinc-800 focus:ring-2 focus:ring-zinc-500"
                        />
                        <div>
                            <div class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                {{ $user['name'] }}
                            </div>
                            <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                {{ $user['email'] ?: __('No email provided') }}
                            </div>
                        </div>
                    </label>
                @empty
                    <div class="px-4 py-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
                        {{ __('No users match your search.') }}
                    </div>
                @endforelse
            </div>

            <div class="flex items-center justify-between">
                <div class="text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('Selected users: :count', ['count' => count($assignedUserIds)]) }}
                </div>
                <div class="flex items-center gap-3">
                    <flux:button variant="ghost" wire:click="closeManageUsersModal">
                        {{ __('Cancel') }}
                    </flux:button>
                    <flux:button
                        variant="primary"
                        wire:click="saveRoleAssignments"
                        wire:loading.attr="disabled"
                        wire:target="saveRoleAssignments"
                    >
                        <span wire:loading.remove wire:target="saveRoleAssignments">{{ __('Save Changes') }}</span>
                        <span wire:loading wire:target="saveRoleAssignments">{{ __('Saving...') }}</span>
                    </flux:button>
                </div>
            </div>
        </div>
    </flux:modal>

    <!-- Role Form Modal -->
    <flux:modal wire:model="showRoleFormModal" size="3xl" :title="$roleFormMode === 'edit' ? __('Edit Role') : ($roleFormMode === 'clone' ? __('Clone Role') : __('Add Role'))">
        <form class="space-y-6" wire:submit.prevent="saveRole">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <flux:field label="{{ __('Role Name') }}" required>
                    <flux:input wire:model.defer="roleForm.name" placeholder="{{ __('Enter role name') }}" />
                    <flux:error for="roleForm.name" />
                </flux:field>

                <flux:field label="{{ __('Status') }}">
                    <div class="flex items-center gap-3">
                        <flux:switch wire:model.defer="roleForm.is_active" />
                        <span class="text-sm text-zinc-600 dark:text-zinc-400">
                            {{ $roleForm['is_active'] ? __('Active') : __('Inactive') }}
                        </span>
                    </div>
                </flux:field>

                <flux:field class="md:col-span-2" label="{{ __('Description') }}">
                    <flux:textarea rows="3" wire:model.defer="roleForm.description" placeholder="{{ __('Short summary of this role') }}" />
                    <flux:error for="roleForm.description" />
                </flux:field>
            </div>

            <div class="space-y-4">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                    <div>
                        <flux:heading size="sm">{{ __('Permissions') }}</flux:heading>
                        <flux:text class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">
                            {{ __('Select the permissions this role should have access to.') }}
                        </flux:text>
                    </div>
                    <div class="flex items-center gap-2">
                        <flux:input
                            type="search"
                            wire:model.live="permissionSearch"
                            placeholder="{{ __('Search permissions...') }}"
                            class="w-56"
                            icon="magnifying-glass"
                        />
                        <flux:button type="button" variant="ghost" size="sm" wire:click="selectAllPermissionCategories">
                            {{ __('Show All') }}
                        </flux:button>
                    </div>
                </div>

                <div class="flex flex-wrap gap-2">
                    @foreach($permissionGroups as $categoryKey => $group)
                        <button
                            type="button"
                            class="px-3 py-1.5 rounded-full border text-xs font-medium transition-colors
                                {{ in_array($categoryKey, $selectedPermissionCategories, true)
                                    ? 'border-zinc-900 bg-zinc-900 text-white dark:border-zinc-100 dark:bg-zinc-100 dark:text-zinc-900'
                                    : 'border-zinc-300 bg-white text-zinc-600 hover:bg-zinc-100 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700' }}"
                            wire:click="togglePermissionCategory('{{ $categoryKey }}')"
                        >
                            {{ $group['label'] }}
                        </button>
                    @endforeach
                </div>

                <div class="space-y-4 max-h-[28rem] overflow-y-auto pr-1">
                    @forelse($filteredPermissionGroups as $categoryKey => $group)
                        <div class="border border-zinc-200 dark:border-zinc-700 rounded-lg p-4 space-y-3" wire:key="permission-group-{{ $categoryKey }}">
                            <div class="flex items-center justify-between">
                                <flux:heading size="sm">{{ $group['label'] }}</flux:heading>
                                <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">
                                    {{ count($group['permissions']) }} {{ __('permissions') }}
                                </flux:text>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                @foreach($group['permissions'] as $permission)
                                    <label class="flex items-start gap-3 text-sm text-zinc-700 dark:text-zinc-300" wire:key="permission-{{ $permission['name'] }}">
                                        <input
                                            type="checkbox"
                                            value="{{ $permission['name'] }}"
                                            wire:model="roleForm.permissions"
                                            class="mt-1 h-4 w-4 rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-800"
                                        >
                                        <div>
                                            <div class="font-medium">{{ $permission['label'] }}</div>
                                            <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ $permission['name'] }}</div>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <div class="text-sm text-zinc-500 dark:text-zinc-400">
                            {{ __('No permissions match your current filters.') }}
                        </div>
                    @endforelse
                </div>
                <flux:error for="roleForm.permissions" />
            </div>

            <div class="flex items-center justify-end gap-3 pt-2 border-t border-zinc-200 dark:border-zinc-700">
                <flux:button type="button" variant="ghost" wire:click="closeRoleFormModal">
                    {{ __('Cancel') }}
                </flux:button>
                <flux:button type="submit" variant="primary" wire:loading.attr="disabled" wire:target="saveRole">
                    <span wire:loading.remove wire:target="saveRole">
                        {{ $roleFormMode === 'edit' ? __('Save Changes') : ($roleFormMode === 'clone' ? __('Clone Role') : __('Create Role')) }}
                    </span>
                    <span wire:loading wire:target="saveRole">{{ __('Saving...') }}</span>
                </flux:button>
            </div>
        </form>
    </flux:modal>

    <!-- Role Details Modal -->
    <flux:modal wire:model="showRoleDetailsModal" size="3xl" :title="__('Role Details')">
        @if($roleDetails)
            <div class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Role Name') }}</flux:text>
                        <flux:heading size="md">{{ $roleDetails['name'] }}</flux:heading>
                    </div>
                    <div class="space-y-1">
                        <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Status') }}</flux:text>
                        <flux:badge :color="$roleDetails['is_active'] ? 'green' : 'zinc'" size="sm">
                            {{ $roleDetails['is_active'] ? __('Active') : __('Inactive') }}
                        </flux:badge>
                    </div>
                    <div class="md:col-span-2 space-y-1">
                        <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Description') }}</flux:text>
                        <flux:text class="text-sm text-zinc-700 dark:text-zinc-300">
                            {{ $roleDetails['description'] ?? __('No description provided.') }}
                        </flux:text>
                    </div>
                </div>

                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <flux:heading size="sm">{{ __('Assigned Users') }}</flux:heading>
                        <flux:badge color="blue" size="sm">{{ count($roleDetails['users']) }}</flux:badge>
                    </div>
                    @if(count($roleDetails['users']) > 0)
                        <div class="space-y-2 max-h-40 overflow-y-auto">
                            @foreach($roleDetails['users'] as $user)
                                <div class="flex items-center justify-between text-sm text-zinc-700 dark:text-zinc-300" wire:key="role-detail-user-{{ $user['id'] }}">
                                    <span>{{ $user['name'] }}</span>
                                    <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ $user['email'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                            {{ __('No users are currently assigned to this role.') }}
                        </flux:text>
                    @endif
                </div>

                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <flux:heading size="sm">{{ __('Permissions') }}</flux:heading>
                    </div>
                    <div class="space-y-3 max-h-56 overflow-y-auto pr-1">
                        @foreach($roleDetails['permissions'] as $category => $group)
                            <div class="border border-zinc-200 dark:border-zinc-700 rounded-lg p-4 space-y-2" wire:key="role-detail-group-{{ $category }}">
                                <flux:heading size="xs">{{ $group['label'] }}</flux:heading>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($group['items'] as $permissionLabel)
                                        <flux:badge color="zinc" size="xs">{{ $permissionLabel }}</flux:badge>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs text-zinc-500 dark:text-zinc-400">
                    <div>
                        <span class="font-medium">{{ __('Created At') }}:</span>
                        <span class="ml-1">{{ $roleDetails['created_at'] ?? '—' }}</span>
                    </div>
                    <div>
                        <span class="font-medium">{{ __('Last Updated') }}:</span>
                        <span class="ml-1">{{ $roleDetails['updated_at'] ?? '—' }}</span>
                    </div>
                </div>
            </div>
        @endif
    </flux:modal>
</section>
