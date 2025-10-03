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
                            <!-- Sample Data -->
                            <tr class="hover:bg-zinc-100 dark:hover:bg-zinc-600 transition-colors duration-150">
                                <td class="px-6 py-6 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <flux:avatar size="sm" initials="IT" />
                                        <div>
                                            <div class="font-medium text-zinc-900 dark:text-zinc-100">
                                                Human Resources
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                
                                <td class="px-6 py-6 whitespace-nowrap">
                                    <div class="text-sm text-zinc-900 dark:text-zinc-100">
                                        Manages employee relations and HR processes
                                    </div>
                                </td>
                                
                                <td class="px-6 py-6 whitespace-nowrap">
                                    <div class="text-sm text-zinc-900 dark:text-zinc-100">
                                        Sarah Johnson
                                    </div>
                                </td>
                                
                                <td class="px-6 py-6 whitespace-nowrap">
                                    <div class="text-sm text-zinc-900 dark:text-zinc-100">
                                        25
                                    </div>
                                </td>
                                
                                <td class="px-6 py-6 whitespace-nowrap">
                                    <flux:badge color="green" size="sm">
                                        Active
                                    </flux:badge>
                                </td>
                                
                                <td class="px-6 py-6 whitespace-nowrap text-sm font-medium">
                                    <div class="flex items-center gap-1">
                                        <flux:dropdown>
                                            <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                                            <flux:menu>
                                                <flux:menu.item icon="pencil" wire:click="editDepartment(1)">
                                                    {{ __('Edit Department') }}
                                                </flux:menu.item>
                                                <flux:menu.item icon="eye" wire:click="viewDepartment(1)">
                                                    {{ __('View Details') }}
                                                </flux:menu.item>
                                                <flux:menu.item icon="trash" wire:click="deleteDepartment(1)" class="text-red-600">
                                                    {{ __('Delete Department') }}
                                                </flux:menu.item>
                                            </flux:menu>
                                        </flux:dropdown>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    </div>
                </div>

                <!-- Pagination -->
                <div class="mt-6">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-zinc-700 dark:text-zinc-300">
                        Showing 1 to 1 of 1 results
                    </div>
                    <div class="flex space-x-1">
                        <flux:button variant="outline" size="sm" disabled>
                            Previous
                        </flux:button>
                        <flux:button variant="outline" size="sm">
                            1
                        </flux:button>
                        <flux:button variant="outline" size="sm" disabled>
                            Next
                        </flux:button>
                    </div>
                </div>
            </div>
        </div>
    </x-system-management.layout>

    <!-- Add Department Flyout -->
    <flux:modal variant="flyout" :open="$showAddDepartmentFlyout" wire:model="showAddDepartmentFlyout">
        <div class="p-6">
            <!-- Header -->
            <div class="mb-6">
                <flux:heading size="lg">Add Department</flux:heading>
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
                            <option value="sarah-johnson">Sarah Johnson</option>
                            <option value="mike-wilson">Mike Wilson</option>
                            <option value="jane-doe">Jane Doe</option>
                            <option value="john-smith">John Smith</option>
                        </flux:select>
                        <flux:error name="departmentHead" />
                    </flux:field>
                </div>
                
                <!-- Second Row: Department Code -->
                <div class="grid grid-cols-1 gap-6">
                    <!-- Department Code -->
                    <flux:field>
                        <flux:label>Department Code <span class="text-red-500">*</span></flux:label>
                        <flux:input 
                            wire:model="departmentCode" 
                            placeholder="Department Code"
                            required
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
                
                <!-- Fourth Row: Active/Inactive Radio -->
                <div class="grid grid-cols-1 gap-6">
                    <flux:field>
                        <flux:label>Status</flux:label>
                        <div class="flex items-center gap-6">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input 
                                    type="radio" 
                                    wire:model.live="isActive" 
                                    value="true" 
                                    class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                                />
                                <span class="text-sm font-medium text-gray-900 dark:text-gray-300">Active</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input 
                                    type="radio" 
                                    wire:model.live="isActive" 
                                    value="false" 
                                    class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                                />
                                <span class="text-sm font-medium text-gray-900 dark:text-gray-300">Inactive</span>
                            </label>
                        </div>
                        <flux:error name="isActive" />
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
                        Add Department
                    </flux:button>
                </div>
            </form>
        </div>
    </flux:modal>
</section>
