<section class="w-full">
    @include('partials.system-management-heading')

    <x-system-management.layout :heading="__('Employment Type')" :subheading="__('Manage employee type categories')">
        <div class="space-y-6">
            <!-- Action Bar -->
            <div class="flex justify-between items-center gap-4">
                <!-- Search Section -->
                <div class="flex items-center gap-3">
                    <flux:input 
                        type="search" 
                        wire:model.live="search" 
                        placeholder="Search employment types..." 
                        class="w-80"
                    />
                    <flux:button variant="outline" icon="funnel" />
                </div>
                
                <!-- Action Buttons -->
                <div class="flex items-center gap-3">
                    <flux:button variant="outline" icon="arrow-down-tray">
                        Export
                    </flux:button>
                    <flux:button variant="primary" icon="plus" wire:click="createEmploymentType">
                        Add Employment Type
                    </flux:button>
                </div>
            </div>

            <!-- Employment Type Table -->
            <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 overflow-hidden shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-zinc-50 dark:bg-zinc-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    <button wire:click="sort('name')" class="flex items-center gap-1 hover:text-zinc-700 dark:hover:text-zinc-200">
                                        {{ __('Type Name') }}
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
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    {{ __('Actions') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-zinc-800 divide-y divide-zinc-200 dark:divide-zinc-700">
                            @forelse($employmentTypes as $type)
                                <tr class="hover:bg-zinc-100 dark:hover:bg-zinc-600 transition-colors duration-150">
                                    <td class="px-6 py-6 whitespace-nowrap">
                                        <div class="flex items-center gap-3">
                                            <div class="h-8 w-8 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                                                <flux:icon name="users" class="h-4 w-4 text-blue-600 dark:text-blue-400" />
                                            </div>
                                            <div>
                                                <div class="font-medium text-zinc-900 dark:text-zinc-100">
                                                    {{ $type->name }}
                                                </div>
                                                @if($type->code)
                                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                                        {{ $type->code }}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    
                                    <td class="px-6 py-6">
                                        <div class="text-sm text-zinc-900 dark:text-zinc-100">
                                            {{ $type->description ?? '-' }}
                                        </div>
                                    </td>
                                    
                                    <td class="px-6 py-6 whitespace-nowrap">
                                        <flux:badge color="{{ $type->status === 'active' ? 'green' : 'red' }}" size="sm">
                                            {{ ucfirst($type->status) }}
                                        </flux:badge>
                                    </td>
                                    
                                    <td class="px-6 py-6 whitespace-nowrap text-sm font-medium">
                                        <div class="flex items-center gap-1">
                                            <flux:dropdown>
                                                <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                                                <flux:menu>
                                                    <flux:menu.item icon="pencil" wire:click="editEmploymentType({{ $type->id }})">
                                                        {{ __('Edit Employment Type') }}
                                                    </flux:menu.item>
                                                    <flux:menu.item icon="trash" wire:click="deleteEmploymentType({{ $type->id }})" wire:confirm="Are you sure you want to delete this employment type?" class="text-red-600">
                                                        {{ __('Delete Employment Type') }}
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
                                            No employment types found
                                        </flux:heading>
                                        <flux:text class="mt-1 text-zinc-500 dark:text-zinc-400">
                                            Get started by creating a new employment type.
                                        </flux:text>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            @if($employmentTypes->hasPages())
                <div class="mt-6">
                    {{ $employmentTypes->links() }}
                </div>
            @endif
        </div>
    </x-system-management.layout>

    <!-- Add Employment Type Flyout -->
    <flux:modal variant="flyout" :open="$showAddEmploymentTypeFlyout" wire:model="showAddEmploymentTypeFlyout">
        <div class="p-6">
            <!-- Header -->
            <div class="mb-6">
                <flux:heading size="lg">{{ $editingId ? 'Edit Employment Type' : 'Add Employment Type' }}</flux:heading>
            </div>
            
            <!-- Form -->
            <form wire:submit="submitEmploymentType" class="space-y-6">
                <!-- Type Name -->
                <div class="grid grid-cols-1 gap-6">
                    <flux:field>
                        <flux:label>Type Name <span class="text-red-500">*</span></flux:label>
                        <flux:input 
                            wire:model="typeName" 
                            placeholder="Enter type name"
                            required
                        />
                        <flux:error name="typeName" />
                    </flux:field>
                </div>
                
                <!-- Type Code -->
                <div class="grid grid-cols-1 gap-6">
                    <flux:field>
                        <flux:label>Type Code</flux:label>
                        <flux:input 
                            wire:model="typeCode" 
                            placeholder="Enter type code (optional)"
                        />
                        <flux:error name="typeCode" />
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
                
                <!-- Submit and Cancel Buttons -->
                <div class="flex justify-end gap-3 pt-4">
                    <flux:button 
                        type="button" 
                        variant="outline" 
                        wire:click="closeAddEmploymentTypeFlyout"
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
</section>
