<section class="w-full">
    @include('partials.system-management-heading')

    <x-system-management.layout :heading="__('Departments')" :subheading="__('Manage company departments')">
        <div class="space-y-6">
            <!-- Action Bar -->
            <div class="flex justify-between items-center gap-4">
                <!-- Search Section -->
                <div class="flex items-center gap-3">
                    <flux:input 
                        type="search" 
                        wire:model.live="search" 
                        placeholder="Search departments..." 
                        class="w-80"
                    />
                    <flux:button variant="outline" icon="funnel" />
                </div>
                
                <!-- Action Buttons -->
                <div class="flex items-center gap-3">
                    <flux:button variant="outline" icon="arrow-down-tray">
                        Export
                    </flux:button>
                    <flux:button variant="primary" icon="plus" wire:click="createDepartment">
                        Add Department
                    </flux:button>
                </div>
            </div>

            <!-- Departments Table -->
            <div class="mt-8">
                <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 overflow-hidden shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-zinc-50 dark:bg-zinc-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    <button wire:click="sort('name')" class="flex items-center gap-1 hover:text-zinc-700 dark:hover:text-zinc-200">
                                        {{ __('Department Name') }}
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
                                    <button wire:click="sort('head')" class="flex items-center gap-1 hover:text-zinc-700 dark:hover:text-zinc-200">
                                        {{ __('Head of Department') }}
                                        @if($sortBy === 'head')
                                            <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="w-4 h-4" />
                                        @endif
                                    </button>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    <button wire:click="sort('count')" class="flex items-center gap-1 hover:text-zinc-700 dark:hover:text-zinc-200">
                                        {{ __('Employee Count') }}
                                        @if($sortBy === 'count')
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
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    {{ __('Actions') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-zinc-800 divide-y divide-zinc-200 dark:divide-zinc-700">
                            @forelse($departments as $department)
                                <tr class="hover:bg-zinc-100 dark:hover:bg-zinc-600 transition-colors duration-150">
                                    <td class="px-6 py-6 whitespace-nowrap">
                                        <div class="flex items-center gap-3">
                                            <div class="h-8 w-8 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                                                <flux:icon name="building-office" class="h-4 w-4 text-blue-600 dark:text-blue-400" />
                                            </div>
                                            <div>
                                                <div class="font-medium text-zinc-900 dark:text-zinc-100">
                                                    {{ $department->title }}
                                                </div>
                                                @if($department->code)
                                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                                        {{ $department->code }}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    
                                    <td class="px-6 py-6">
                                        <div class="text-sm text-zinc-900 dark:text-zinc-100">
                                            {{ $department->description ?? '-' }}
                                        </div>
                                    </td>
                                    
                                    <td class="px-6 py-6 whitespace-nowrap">
                                        <div class="text-sm text-zinc-900 dark:text-zinc-100">
                                            @if($department->departmentHead)
                                                {{ $department->departmentHead->user->name ?? ($department->departmentHead->first_name . ' ' . $department->departmentHead->last_name) }}
                                            @else
                                                -
                                            @endif
                                        </div>
                                    </td>
                                    
                                    <td class="px-6 py-6 whitespace-nowrap">
                                        <div class="text-sm text-zinc-900 dark:text-zinc-100">
                                            {{ $department->employees()->count() ?? 0 }}
                                        </div>
                                    </td>
                                    
                                    <td class="px-6 py-6 whitespace-nowrap">
                                        <flux:badge color="{{ $department->status === 'active' ? 'green' : 'red' }}" size="sm">
                                            {{ ucfirst($department->status) }}
                                        </flux:badge>
                                    </td>
                                    
                                    <td class="px-6 py-6 whitespace-nowrap text-sm font-medium">
                                        <div class="flex items-center gap-1">
                                            <flux:dropdown>
                                                <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                                                <flux:menu>
                                                    <flux:menu.item icon="pencil" wire:click="editDepartment({{ $department->id }})">
                                                        {{ __('Edit Department') }}
                                                    </flux:menu.item>
                                                    <flux:menu.item icon="trash" wire:click="deleteDepartment({{ $department->id }})" wire:confirm="Are you sure you want to delete this department?" class="text-red-600">
                                                        {{ __('Delete Department') }}
                                                    </flux:menu.item>
                                                </flux:menu>
                                            </flux:dropdown>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center">
                                        <flux:icon name="inbox" class="mx-auto h-12 w-12 text-zinc-400" />
                                        <flux:heading size="sm" class="mt-2 text-zinc-500 dark:text-zinc-400">
                                            No departments found
                                        </flux:heading>
                                        <flux:text class="mt-1 text-zinc-500 dark:text-zinc-400">
                                            Get started by creating a new department.
                                        </flux:text>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    </div>
                </div>

            <!-- Pagination -->
            @if($departments->hasPages())
                <div class="mt-6">
                    {{ $departments->links() }}
                </div>
            @endif
        </div>
    </x-system-management.layout>

    <!-- Add Department Flyout -->
    <flux:modal variant="flyout" :open="$showAddDepartmentFlyout" wire:model="showAddDepartmentFlyout">
        <div class="p-6">
            <!-- Header -->
            <div class="mb-6">
                <flux:heading size="lg">{{ $editingId ? 'Edit Department' : 'Add Department' }}</flux:heading>
            </div>
            
            <!-- Form -->
            <form wire:submit="submitDepartment" class="space-y-6">
                <!-- First Row: Department Title and Department Head -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Department Title -->
                    <flux:field>
                        <flux:label>Department Title <span class="text-red-500">*</span></flux:label>
                        <flux:input 
                            wire:model="departmentTitle" 
                            placeholder="Title"
                            required
                        />
                        <flux:error name="departmentTitle" />
                    </flux:field>
                    
                    <!-- Department Head -->
                    <flux:field>
                        <flux:label>Head Of Department</flux:label>
                        <flux:select wire:model="departmentHead" placeholder="Select-One">
                            <option value="">Select-One</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee['id'] }}">{{ $employee['name'] }}</option>
                            @endforeach
                        </flux:select>
                        <flux:error name="departmentHead" />
                    </flux:field>
                </div>
                
                <!-- Second Row: Department Code -->
                <div class="grid grid-cols-1 gap-6">
                    <!-- Department Code -->
                    <flux:field>
                        <flux:label>Department Code</flux:label>
                        <flux:input 
                            wire:model="departmentCode" 
                            placeholder="Department Code (optional)"
                        />
                        <flux:error name="departmentCode" />
                    </flux:field>
                </div>
                
                <!-- Third Row: Description -->
                <div class="grid grid-cols-1 gap-6">
                    <!-- Description -->
                    <flux:field>
                        <flux:label>Description</flux:label>
                        <flux:textarea 
                            wire:model="description" 
                            rows="4"
                            placeholder="Enter department description..."
                        ></flux:textarea>
                        <flux:error name="description" />
                    </flux:field>
                </div>
                
                <!-- Fourth Row: Status -->
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
                
                <!-- Submit and Cancel Buttons -->
                <div class="flex justify-end gap-3 pt-4">
                    <flux:button 
                        type="button" 
                        variant="outline" 
                        wire:click="closeAddDepartmentFlyout"
                    >
                        Cancel
                    </flux:button>
                    <flux:button type="submit" variant="primary">
                        {{ $editingId ? 'Update Department' : 'Add Department' }}
                    </flux:button>
                </div>
            </form>
        </div>
    </flux:modal>
</section>
