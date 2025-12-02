<section class="w-full">
    @include('partials.system-management-heading')

    <x-system-management.layout :heading="__('Groups')" :subheading="__('Manage employee groups')">
        <div class="space-y-6">
            <!-- Action Bar -->
            <div class="flex justify-between items-center gap-4">
                <!-- Search Section -->
                <div class="flex items-center gap-3">
                    <flux:input 
                        type="search" 
                        wire:model.live="search" 
                        placeholder="Search groups..." 
                        class="w-80"
                    />
                    <flux:button variant="outline" icon="funnel" />
                </div>
                
                <!-- Action Buttons -->
                <div class="flex items-center gap-3">
                    <flux:button variant="outline" icon="arrow-down-tray">
                        Export
                    </flux:button>
                    <flux:button variant="primary" icon="plus" wire:click="createGroup">
                        Add Group
                    </flux:button>
                </div>
            </div>

            <!-- Groups Table -->
            <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 overflow-hidden shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-zinc-50 dark:bg-zinc-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    <button wire:click="sort('name')" class="flex items-center gap-1 hover:text-zinc-700 dark:hover:text-zinc-200">
                                        {{ __('Group Name') }}
                                        @if($sortBy === 'name')
                                            <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="w-4 h-4" />
                                        @endif
                                    </button>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    <button wire:click="sort('description')" class="flex items-center gap-1 hover:text-zinc-700 dark:hover:text-zinc-200">
                                        {{ __('Description') }}
                                        @if($sortBy === 'description')
                                            <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="w-4 h-4" />
                                        @endif
                                    </button>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    <button wire:click="sort('status')" class="flex items-center gap-1 hover:text-zinc-700 dark:hover:text-zinc-200">
                                        {{ __('Status') }}
                                        @if($sortBy === 'status')
                                            <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="w-4 h-4" />
                                        @endif
                                    </button>
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    {{ __('Actions') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-zinc-800 divide-y divide-zinc-200 dark:divide-zinc-700">
                            @forelse($groups as $group)
                                <tr class="hover:bg-zinc-100 dark:hover:bg-zinc-600 transition-colors duration-150">
                                    <td class="px-6 py-6 whitespace-nowrap">
                                        <div class="flex items-center gap-3">
                                            <div class="h-8 w-8 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                                                <flux:icon name="user-group" class="h-4 w-4 text-blue-600 dark:text-blue-400" />
                                            </div>
                                            <div>
                                                <div class="font-medium text-zinc-900 dark:text-zinc-100">
                                                    {{ $group->name }}
                                                </div>
                                                @if($group->code)
                                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                                        {{ $group->code }}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    
                                    <td class="px-6 py-6">
                                        <div class="text-sm text-zinc-900 dark:text-zinc-100">
                                            {{ $group->description ?? '-' }}
                                        </div>
                                    </td>
                                    
                                    <td class="px-6 py-6 whitespace-nowrap">
                                        <flux:badge color="{{ $group->status === 'active' ? 'green' : 'red' }}" size="sm">
                                            {{ ucfirst($group->status) }}
                                        </flux:badge>
                                    </td>
                                    
                                    <td class="px-6 py-6 whitespace-nowrap text-sm font-medium text-right">
                                        <div class="flex items-center justify-end gap-1">
                                            <flux:button 
                                                variant="outline" 
                                                size="sm" 
                                                icon="eye"
                                                wire:click="openViewDetailsFlyout({{ $group->id }})"
                                            >
                                                {{ __('View Details') }}
                                            </flux:button>
                                            <flux:dropdown>
                                                <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                                                <flux:menu>
                                                    <flux:menu.item icon="pencil" wire:click="editGroup({{ $group->id }})">
                                                        {{ __('Edit Group') }}
                                                    </flux:menu.item>
                                                    <flux:menu.item icon="trash" wire:click="deleteGroup({{ $group->id }})" wire:confirm="Are you sure you want to delete this group?" class="text-red-600">
                                                        {{ __('Delete Group') }}
                                                    </flux:menu.item>
                                                </flux:menu>
                                            </flux:dropdown>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-12 text-center">
                                        <flux:icon name="inbox" class="mx-auto h-12 w-12 text-zinc-400" />
                                        <flux:heading size="sm" class="mt-2 text-zinc-500 dark:text-zinc-400">
                                            No groups found
                                        </flux:heading>
                                        <flux:text class="mt-1 text-zinc-500 dark:text-zinc-400">
                                            Get started by creating a new group.
                                        </flux:text>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            @if($groups->hasPages())
                <div class="mt-6">
                    {{ $groups->links() }}
                </div>
            @endif
        </div>
    </x-system-management.layout>

    <!-- Add Group Flyout -->
    <flux:modal variant="flyout" :open="$showAddGroupFlyout" wire:model="showAddGroupFlyout">
        <div class="p-6">
            <!-- Header -->
            <div class="mb-6">
                <flux:heading size="lg">{{ $editingId ? 'Edit Group' : 'Add Group' }}</flux:heading>
            </div>
            
            <!-- Form -->
            <form wire:submit="submitGroup" class="space-y-6">
                <!-- Group Name -->
                <div class="grid grid-cols-1 gap-6">
                    <flux:field>
                        <flux:label>Group Name <span class="text-red-500">*</span></flux:label>
                        <flux:input 
                            wire:model="groupName" 
                            placeholder="Enter group name"
                            required
                        />
                        <flux:error name="groupName" />
                    </flux:field>
                </div>
                
                <!-- Group Code -->
                <div class="grid grid-cols-1 gap-6">
                    <flux:field>
                        <flux:label>Group Code</flux:label>
                        <flux:input 
                            wire:model="groupCode" 
                            placeholder="Enter group code (optional)"
                        />
                        <flux:error name="groupCode" />
                    </flux:field>
                </div>
                
                <!-- Description -->
                <div class="grid grid-cols-1 gap-6">
                    <flux:field>
                        <flux:label>Description</flux:label>
                        <flux:textarea 
                            wire:model="description" 
                            rows="4"
                            placeholder="Optional description"
                        ></flux:textarea>
                        <flux:error name="description" />
                    </flux:field>
                </div>
                
                <!-- Status -->
                <div class="grid grid-cols-1 gap-6">
                    <flux:field>
                        <flux:label>Status</flux:label>
                        <flux:select wire:model="status">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </flux:select>
                        <flux:error name="status" />
                    </flux:field>
                </div>
                
                <!-- Employee Selection -->
                <div class="grid grid-cols-1 gap-6">
                    <flux:field>
                        <flux:label>Select Employees</flux:label>
                        <flux:input 
                            wire:model.live.debounce.300ms="employeeSearchTerm"
                            placeholder="Search employees..."
                            icon="magnifying-glass"
                        />
                        <div class="relative mt-2">
                            <select 
                                wire:model="selectedEmployeeIds" 
                                multiple 
                                class="w-full px-3 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent shadow-sm"
                                size="6"
                                style="min-height: 150px;"
                            >
                                @if(!empty($allEmployees))
                                    @foreach($allEmployees as $employee)
                                        @if(empty($employeeSearchTerm) || stripos($employee['label'], $employeeSearchTerm) !== false)
                                            <option value="{{ $employee['value'] }}">{{ $employee['label'] }}</option>
                                        @endif
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <flux:description>{{ __('Hold Ctrl/Cmd to select multiple employees.') }}</flux:description>
                        <flux:error name="selectedEmployeeIds" />
                    </flux:field>
                </div>
                
                <!-- Submit and Cancel Buttons -->
                <div class="flex justify-end gap-3 pt-4">
                    <flux:button 
                        type="button" 
                        variant="outline" 
                        wire:click="closeAddGroupFlyout"
                    >
                        Cancel
                    </flux:button>
                    <flux:button type="submit" variant="primary">
                        Save
                    </flux:button>
                </div>
            </form>
        </div>
    </flux:modal>

    <!-- View Details Flyout -->
    <flux:modal variant="flyout" :open="$showViewDetailsFlyout" wire:model="showViewDetailsFlyout" class="max-w-4xl">
        <div class="p-6">
            <div class="mb-6">
                <flux:heading size="lg">{{ __('Group Details') }}</flux:heading>
            </div>
            
            <!-- Employees in Group -->
            <div class="mb-6">
                <flux:heading size="md" class="mb-4">{{ __('Employees in Group') }}</flux:heading>
                @if(!empty($groupEmployees))
                    <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 overflow-hidden">
                        <table class="w-full">
                            <thead class="bg-zinc-50 dark:bg-zinc-700">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Employee Name</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Employee Code</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Email</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Designation</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                                @foreach($groupEmployees as $employee)
                                    <tr class="hover:bg-zinc-100 dark:hover:bg-zinc-600">
                                        <td class="px-4 py-3 text-sm text-zinc-900 dark:text-zinc-100">{{ $employee['name'] }}</td>
                                        <td class="px-4 py-3 text-sm text-zinc-900 dark:text-zinc-100">{{ $employee['code'] }}</td>
                                        <td class="px-4 py-3 text-sm text-zinc-900 dark:text-zinc-100">{{ $employee['email'] }}</td>
                                        <td class="px-4 py-3 text-sm text-zinc-900 dark:text-zinc-100">{{ $employee['designation'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-8 text-zinc-500 dark:text-zinc-400">
                        {{ __('No employees assigned to this group.') }}
                    </div>
                @endif
            </div>
            
            <!-- Assignment History -->
            <div class="mb-6">
                <flux:heading size="md" class="mb-4">{{ __('Assignment History') }}</flux:heading>
                @if(!empty($groupHistory))
                    <div class="space-y-3 max-h-96 overflow-y-auto">
                        @foreach($groupHistory as $history)
                            <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-4">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-2">
                                            <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $history['employee_name'] }}</span>
                                            <span class="text-xs text-zinc-500 dark:text-zinc-400">({{ $history['employee_code'] }})</span>
                                            @if($history['end_date'] === 'Current')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400">
                                                    {{ __('Current') }}
                                                </span>
                                            @endif
                                        </div>
                                        <div class="text-sm text-zinc-600 dark:text-zinc-400 space-y-1">
                                            <div>{{ __('Previous Group') }}: {{ $history['previous_group'] }}</div>
                                            <div>{{ __('Assigned By') }}: {{ $history['assigned_by'] }} {{ __('on') }} {{ $history['assigned_date'] }}</div>
                                            @if($history['end_date'] !== 'Current')
                                                <div>{{ __('End Date') }}: {{ $history['end_date'] }}</div>
                                            @endif
                                            @if($history['notes'])
                                                <div>{{ __('Notes') }}: {{ $history['notes'] }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8 text-zinc-500 dark:text-zinc-400">
                        {{ __('No assignment history found.') }}
                    </div>
                @endif
            </div>
            
            <!-- Close Button -->
            <div class="flex justify-end pt-4">
                <flux:button variant="primary" wire:click="closeViewDetailsFlyout">
                    {{ __('Close') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
</section>