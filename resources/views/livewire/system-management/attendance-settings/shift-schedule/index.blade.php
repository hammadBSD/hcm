<section class="w-full">
    @include('partials.system-management-heading')

    <x-system-management.layout :heading="__('Shift Schedule')" :subheading="__('Manage work shifts and schedules')">
        <div class="space-y-6">
            @if (session()->has('message'))
                <flux:callout variant="success" icon="check-circle">
                    <flux:callout.heading>{{ session('message') }}</flux:callout.heading>
                </flux:callout>
            @endif

            <!-- Action Bar -->
            <div class="flex justify-between items-center gap-4">
                <!-- Search Section -->
                <div class="flex items-center gap-3">
                    <flux:input 
                        type="search" 
                        wire:model.live="search" 
                        placeholder="Search shifts..." 
                        class="w-80"
                    />
                    <flux:button variant="outline" icon="funnel" />
                </div>
                
                <!-- Action Buttons -->
                <div class="flex items-center gap-3">
                    <flux:button variant="outline" icon="arrow-down-tray">
                        Export
                    </flux:button>
                    <flux:button variant="primary" icon="plus" wire:click="createShift">
                        Add Shift
                    </flux:button>
                </div>
            </div>

            <!-- Shifts Table -->
            <div class="mt-8">
                <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 overflow-hidden shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-zinc-50 dark:bg-zinc-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    <button wire:click="sort('shift_name')" class="flex items-center gap-1 hover:text-zinc-700 dark:hover:text-zinc-200">
                                        {{ __('Shift Name') }}
                                        @if($sortBy === 'shift_name')
                                            <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="w-4 h-4" />
                                        @endif
                                    </button>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    <button wire:click="sort('time_from')" class="flex items-center gap-1 hover:text-zinc-700 dark:hover:text-zinc-200">
                                        {{ __('Time From') }}
                                        @if($sortBy === 'time_from')
                                            <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="w-4 h-4" />
                                        @endif
                                    </button>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    <button wire:click="sort('time_to')" class="flex items-center gap-1 hover:text-zinc-700 dark:hover:text-zinc-200">
                                        {{ __('Time To') }}
                                        @if($sortBy === 'time_to')
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
                            @forelse($shifts as $shift)
                                <tr class="hover:bg-zinc-100 dark:hover:bg-zinc-600 transition-colors duration-150">
                                    <td class="px-6 py-6 whitespace-nowrap">
                                        <div class="flex items-center gap-3">
                                            <div class="h-8 w-8 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                                                <flux:icon name="clock" class="h-4 w-4 text-blue-600 dark:text-blue-400" />
                                            </div>
                                            <div>
                                                <div class="font-medium text-zinc-900 dark:text-zinc-100">
                                                    {{ $shift->shift_name }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    
                                    <td class="px-6 py-6 whitespace-nowrap">
                                        <div class="text-sm text-zinc-900 dark:text-zinc-100">
                                            {{ date('h:i A', strtotime($shift->time_from)) }}
                                        </div>
                                    </td>
                                    
                                    <td class="px-6 py-6 whitespace-nowrap">
                                        <div class="text-sm text-zinc-900 dark:text-zinc-100">
                                            {{ date('h:i A', strtotime($shift->time_to)) }}
                                        </div>
                                    </td>
                                    
                                    <td class="px-6 py-6 whitespace-nowrap">
                                        <div class="text-sm text-zinc-900 dark:text-zinc-100">
                                            {{ $shift->employees_count ?? 0 }}
                                        </div>
                                    </td>
                                    
                                    <td class="px-6 py-6 whitespace-nowrap">
                                        <flux:badge color="{{ $shift->status === 'active' ? 'green' : 'red' }}" size="sm">
                                            {{ ucfirst($shift->status) }}
                                        </flux:badge>
                                    </td>
                                    
                                    <td class="px-6 py-6 whitespace-nowrap text-sm font-medium">
                                        <div class="flex items-center gap-1">
                                            <flux:dropdown>
                                                <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                                                <flux:menu>
                                                    <flux:menu.item icon="pencil" wire:click="editShift({{ $shift->id }})">
                                                        {{ __('Edit Shift') }}
                                                    </flux:menu.item>
                                                    <flux:menu.item icon="trash" wire:click="deleteShift({{ $shift->id }})" wire:confirm="Are you sure you want to delete this shift?" class="text-red-600">
                                                        {{ __('Delete Shift') }}
                                                    </flux:menu.item>
                                                </flux:menu>
                                            </flux:dropdown>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center">
                                        <flux:icon name="inbox" class="mx-auto h-12 w-12 text-zinc-400" />
                                        <flux:heading size="sm" class="mt-2 text-zinc-500 dark:text-zinc-400">
                                            No shifts found
                                        </flux:heading>
                                        <flux:text class="mt-1 text-zinc-500 dark:text-zinc-400">
                                            Get started by creating a new shift.
                                        </flux:text>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    </div>
                </div>

            <!-- Pagination -->
            @if(method_exists($shifts, 'hasPages') && $shifts->hasPages())
                <div class="mt-6">
                    {{ $shifts->links() }}
                </div>
            @endif
        </div>
    </x-system-management.layout>

    <!-- Add Shift Flyout -->
    <flux:modal variant="flyout" :open="$showAddShiftFlyout" wire:model="showAddShiftFlyout">
        <div class="p-6">
            <!-- Header -->
            <div class="mb-6">
                <flux:heading size="lg">{{ $editingId ? 'Edit Shift' : 'Add Shift' }}</flux:heading>
            </div>
            
            <!-- Form -->
            <form wire:submit="submitShift" class="space-y-6">
                <!-- First Row: Shift Name -->
                <div class="grid grid-cols-1 gap-6">
                    <flux:field>
                        <flux:label>Shift Name <span class="text-red-500">*</span></flux:label>
                        <flux:input 
                            wire:model="shiftName" 
                            placeholder="e.g., Morning Shift, Night Shift"
                            required
                        />
                        <flux:error name="shiftName" />
                    </flux:field>
                </div>
                
                <!-- Second Row: Time From and Time To -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Time From -->
                    <flux:field>
                        <flux:label>Time From <span class="text-red-500">*</span></flux:label>
                        <flux:input 
                            type="time"
                            wire:model="timeFrom" 
                            required
                        />
                        <flux:error name="timeFrom" />
                    </flux:field>
                    
                    <!-- Time To -->
                    <flux:field>
                        <flux:label>Time To <span class="text-red-500">*</span></flux:label>
                        <flux:input 
                            type="time"
                            wire:model="timeTo" 
                            required
                        />
                        <flux:error name="timeTo" />
                    </flux:field>
                </div>
                
                <!-- Third Row: Status -->
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
                        wire:click="closeAddShiftFlyout"
                    >
                        Cancel
                    </flux:button>
                    <flux:button type="submit" variant="primary">
                        {{ $editingId ? 'Update Shift' : 'Add Shift' }}
                    </flux:button>
                </div>
            </form>
        </div>
    </flux:modal>
</section>