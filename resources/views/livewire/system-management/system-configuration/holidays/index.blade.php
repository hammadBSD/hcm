<section class="w-full">
    @include('partials.system-management-heading')

    <x-system-management.layout :heading="__('Holidays')" :subheading="__('Manage holidays')">
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
                        placeholder="Search holidays..." 
                        class="w-80"
                    />
                    <flux:button variant="outline" icon="funnel" />
                </div>
                
                <!-- Action Buttons -->
                <div class="flex items-center gap-3">
                    <flux:button variant="outline" icon="arrow-down-tray">
                        Export
                    </flux:button>
                    <flux:button variant="primary" icon="plus" wire:click="createHoliday">
                        Add Holiday
                    </flux:button>
                </div>
            </div>

            <!-- Holidays Table -->
            <div class="mt-8">
                <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 overflow-hidden shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-zinc-50 dark:bg-zinc-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    <button wire:click="sort('name')" class="flex items-center gap-1 hover:text-zinc-700 dark:hover:text-zinc-200">
                                        {{ __('Holiday Name') }}
                                        @if($sortBy === 'name')
                                            <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="w-4 h-4" />
                                        @endif
                                    </button>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    <button wire:click="sort('from_date')" class="flex items-center gap-1 hover:text-zinc-700 dark:hover:text-zinc-200">
                                        {{ __('From Date') }}
                                        @if($sortBy === 'from_date')
                                            <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="w-4 h-4" />
                                        @endif
                                    </button>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    <button wire:click="sort('to_date')" class="flex items-center gap-1 hover:text-zinc-700 dark:hover:text-zinc-200">
                                        {{ __('To Date') }}
                                        @if($sortBy === 'to_date')
                                            <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="w-4 h-4" />
                                        @endif
                                    </button>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    {{ __('Scope') }}
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    {{ __('Created By') }}
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
                            @forelse($holidays as $holiday)
                                <tr class="hover:bg-zinc-100 dark:hover:bg-zinc-600 transition-colors duration-150">
                                    <td class="px-6 py-6 whitespace-nowrap">
                                        <div class="flex items-center gap-3">
                                            <div class="h-8 w-8 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                                                <flux:icon name="calendar-days" class="h-4 w-4 text-blue-600 dark:text-blue-400" />
                                            </div>
                                            <div>
                                                <div class="font-medium text-zinc-900 dark:text-zinc-100">
                                                    {{ $holiday->name }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    
                                    <td class="px-6 py-6 whitespace-nowrap">
                                        <div class="text-sm text-zinc-900 dark:text-zinc-100">
                                            {{ $holiday->from_date->format('M d, Y') }}
                                        </div>
                                    </td>
                                    
                                    <td class="px-6 py-6 whitespace-nowrap">
                                        <div class="text-sm text-zinc-900 dark:text-zinc-100">
                                            {{ $holiday->to_date ? $holiday->to_date->format('M d, Y') : $holiday->from_date->format('M d, Y') }}
                                        </div>
                                    </td>
                                    
                                    <td class="px-6 py-6">
                                        <div class="text-sm text-zinc-900 dark:text-zinc-100">
                                            @if($holiday->scope_type === 'all_employees')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400">
                                                    All Employees
                                                </span>
                                            @elseif($holiday->scope_type === 'department')
                                                <div class="flex flex-col gap-1">
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400">
                                                        Department
                                                    </span>
                                                    @if($holiday->departments->count() > 0)
                                                        <span class="text-xs text-zinc-500 dark:text-zinc-400">
                                                            {{ $holiday->departments->pluck('title')->join(', ') }}
                                                        </span>
                                                    @endif
                                                </div>
                                            @elseif($holiday->scope_type === 'role')
                                                <div class="flex flex-col gap-1">
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400">
                                                        Role
                                                    </span>
                                                    @if($holiday->roles->count() > 0)
                                                        <span class="text-xs text-zinc-500 dark:text-zinc-400">
                                                            {{ $holiday->roles->pluck('name')->join(', ') }}
                                                        </span>
                                                    @endif
                                                </div>
                                            @elseif($holiday->scope_type === 'group')
                                                <div class="flex flex-col gap-1">
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-400">
                                                        Group
                                                    </span>
                                                    @if($holiday->groups->count() > 0)
                                                        <span class="text-xs text-zinc-500 dark:text-zinc-400">
                                                            {{ $holiday->groups->pluck('name')->join(', ') }}
                                                        </span>
                                                    @endif
                                                </div>
                                            @elseif($holiday->scope_type === 'employee')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-400">
                                                    Employee ({{ $holiday->employees->count() }})
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    
                                    <td class="px-6 py-6 whitespace-nowrap">
                                        <div class="text-sm text-zinc-900 dark:text-zinc-100">
                                            {{ $holiday->createdBy->name ?? 'N/A' }}
                                        </div>
                                    </td>
                                    
                                    <td class="px-6 py-6 whitespace-nowrap">
                                        <flux:badge color="{{ $holiday->status === 'active' ? 'green' : 'red' }}" size="sm">
                                            {{ ucfirst($holiday->status) }}
                                        </flux:badge>
                                    </td>
                                    
                                    <td class="px-6 py-6 whitespace-nowrap text-sm font-medium">
                                        <div class="flex items-center gap-1">
                                            <flux:dropdown>
                                                <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                                                <flux:menu>
                                                    <flux:menu.item icon="pencil" wire:click="editHoliday({{ $holiday->id }})">
                                                        {{ __('Edit Holiday') }}
                                                    </flux:menu.item>
                                                    <flux:menu.item icon="trash" wire:click="deleteHoliday({{ $holiday->id }})" wire:confirm="Are you sure you want to delete this holiday?" class="text-red-600">
                                                        {{ __('Delete Holiday') }}
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
                                            No holidays found
                                        </flux:heading>
                                        <flux:text class="mt-1 text-zinc-500 dark:text-zinc-400">
                                            Get started by creating a new holiday.
                                        </flux:text>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    </div>
                </div>

            <!-- Pagination -->
            @if(method_exists($holidays, 'hasPages') && $holidays->hasPages())
                <div class="mt-6">
                    {{ $holidays->links() }}
                </div>
            @endif
        </div>
    </x-system-management.layout>

    <!-- Add Holiday Flyout -->
    <flux:modal variant="flyout" :open="$showAddHolidayFlyout" wire:model="showAddHolidayFlyout">
        <div class="p-6">
            <!-- Header -->
            <div class="mb-6">
                <flux:heading size="lg">{{ $editingId ? 'Edit Holiday' : 'Add Holiday' }}</flux:heading>
            </div>
            
            <!-- Form -->
            <form wire:submit="submitHoliday" class="space-y-6">
                <!-- Holiday Name -->
                <div class="grid grid-cols-1 gap-6">
                    <flux:field>
                        <flux:label>Holiday Name <span class="text-red-500">*</span></flux:label>
                        <flux:input 
                            wire:model="holidayName" 
                            placeholder="e.g., New Year, Christmas"
                            required
                        />
                        <flux:error name="holidayName" />
                    </flux:field>
                </div>
                
                <!-- Date Range -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- From Date -->
                    <flux:field>
                        <flux:label>From Date <span class="text-red-500">*</span></flux:label>
                        <flux:input 
                            type="date"
                            wire:model.live="fromDate" 
                            required
                        />
                        <flux:error name="fromDate" />
                        <div class="h-5"></div>
                    </flux:field>
                    
                    <!-- To Date -->
                    <flux:field>
                        <flux:label>To Date</flux:label>
                        <flux:input 
                            type="date"
                            wire:model="toDate"
                        />
                        <flux:description>Leave empty for single-day holiday</flux:description>
                        <flux:error name="toDate" />
                    </flux:field>
                </div>
                
                <!-- Scope Type -->
                <div class="grid grid-cols-1 gap-6">
                    <flux:field>
                        <flux:label>Scope <span class="text-red-500">*</span></flux:label>
                        <flux:select wire:model.live="scopeType" required>
                            <option value="all_employees">All Employees</option>
                            <option value="department">Department</option>
                            <option value="role">Role</option>
                            <option value="group">Group</option>
                            <option value="employee">Employee</option>
                        </flux:select>
                        <flux:error name="scopeType" />
                    </flux:field>
                </div>
                
                <!-- Department Selection (if scope is department) -->
                @if($scopeType === 'department')
                    <div class="grid grid-cols-1 gap-6">
                        <flux:field>
                            <flux:label>Select Departments <span class="text-red-500">*</span></flux:label>
                            
                            <!-- Search Input for Departments -->
                            <div class="mb-3">
                                <flux:input 
                                    wire:model.live.debounce.300ms="departmentSearchTerm"
                                    placeholder="Search departments..."
                                    icon="magnifying-glass"
                                />
                            </div>
                            
                            <div class="relative">
                                <select 
                                    wire:model.live="selectedDepartmentIds" 
                                    multiple 
                                    class="w-full px-3 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent shadow-sm"
                                    size="6"
                                    style="min-height: 150px;"
                                >
                                    @foreach($filteredDepartments as $department)
                                        <option value="{{ $department['value'] }}" class="py-2 px-3 hover:bg-zinc-100 dark:hover:bg-zinc-700">
                                            {{ $department['label'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <flux:description>{{ __('Search and select departments. Hold Ctrl/Cmd to select multiple departments.') }}</flux:description>
                            <flux:error name="selectedDepartmentIds" />
                        </flux:field>
                        
                        <!-- Additional Employees (not in selected departments) -->
                        <flux:field>
                            <flux:label>Additional Employees (Optional)</flux:label>
                            <flux:description>{{ __('Select employees from other departments to include in this holiday') }}</flux:description>
                            
                            <!-- Search Input for Additional Employees -->
                            <div class="mb-3">
                                <flux:input 
                                    wire:model.live.debounce.300ms="additionalEmployeeSearchTerm"
                                    placeholder="Search employees..."
                                    icon="magnifying-glass"
                                />
                            </div>
                            
                            <div class="relative">
                                <select 
                                    wire:model.live="additionalEmployeeIds" 
                                    multiple 
                                    class="w-full px-3 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent shadow-sm"
                                    size="6"
                                    style="min-height: 150px;"
                                >
                                    @foreach($filteredAdditionalEmployees as $employee)
                                        <option value="{{ $employee['value'] }}" class="py-2 px-3 hover:bg-zinc-100 dark:hover:bg-zinc-700">
                                            {{ $employee['label'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <flux:description>{{ __('Search and select additional employees. Hold Ctrl/Cmd to select multiple employees.') }}</flux:description>
                            <flux:error name="additionalEmployeeIds" />
                        </flux:field>
                    </div>
                @endif
                
                <!-- Role Selection (if scope is role) -->
                @if($scopeType === 'role')
                    <div class="grid grid-cols-1 gap-6">
                        <flux:field>
                            <flux:label>Select Roles <span class="text-red-500">*</span></flux:label>
                            
                            <!-- Search Input for Roles -->
                            <div class="mb-3">
                                <flux:input 
                                    wire:model.live.debounce.300ms="roleSearchTerm"
                                    placeholder="Search roles..."
                                    icon="magnifying-glass"
                                />
                            </div>
                            
                            <div class="relative">
                                <select 
                                    wire:model.live="selectedRoleIds" 
                                    multiple 
                                    class="w-full px-3 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent shadow-sm"
                                    size="6"
                                    style="min-height: 150px;"
                                >
                                    @foreach($filteredRoles as $role)
                                        <option value="{{ $role['value'] }}" class="py-2 px-3 hover:bg-zinc-100 dark:hover:bg-zinc-700">
                                            {{ $role['label'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <flux:description>{{ __('Search and select roles. Hold Ctrl/Cmd to select multiple roles.') }}</flux:description>
                            <flux:error name="selectedRoleIds" />
                        </flux:field>
                        
                        <!-- Additional Employees (not in selected roles) -->
                        <flux:field>
                            <flux:label>Additional Employees (Optional)</flux:label>
                            <flux:description>{{ __('Select employees with other roles to include in this holiday') }}</flux:description>
                            
                            <!-- Search Input for Additional Employees -->
                            <div class="mb-3">
                                <flux:input 
                                    wire:model.live.debounce.300ms="additionalEmployeeSearchTerm"
                                    placeholder="Search employees..."
                                    icon="magnifying-glass"
                                />
                            </div>
                            
                            <div class="relative">
                                <select 
                                    wire:model.live="additionalEmployeeIds" 
                                    multiple 
                                    class="w-full px-3 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent shadow-sm"
                                    size="6"
                                    style="min-height: 150px;"
                                >
                                    @foreach($filteredAdditionalEmployees as $employee)
                                        <option value="{{ $employee['value'] }}" class="py-2 px-3 hover:bg-zinc-100 dark:hover:bg-zinc-700">
                                            {{ $employee['label'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <flux:description>{{ __('Search and select additional employees. Hold Ctrl/Cmd to select multiple employees.') }}</flux:description>
                            <flux:error name="additionalEmployeeIds" />
                        </flux:field>
                    </div>
                @endif
                
                <!-- Group Selection (if scope is group) -->
                @if($scopeType === 'group')
                    <div class="grid grid-cols-1 gap-6">
                        <flux:field>
                            <flux:label>Select Groups <span class="text-red-500">*</span></flux:label>
                            
                            <!-- Search Input for Groups -->
                            <div class="mb-3">
                                <flux:input 
                                    wire:model.live.debounce.300ms="groupSearchTerm"
                                    placeholder="Search groups..."
                                    icon="magnifying-glass"
                                />
                            </div>
                            
                            <div class="relative">
                                <select 
                                    wire:model.live="selectedGroupIds" 
                                    multiple 
                                    class="w-full px-3 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent shadow-sm"
                                    size="6"
                                    style="min-height: 150px;"
                                >
                                    @foreach($filteredGroups as $group)
                                        <option value="{{ $group['value'] }}" class="py-2 px-3 hover:bg-zinc-100 dark:hover:bg-zinc-700">
                                            {{ $group['label'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <flux:description>{{ __('Search and select groups. Hold Ctrl/Cmd to select multiple groups.') }}</flux:description>
                            <flux:error name="selectedGroupIds" />
                        </flux:field>
                        
                        <!-- Additional Employees (not in selected groups) -->
                        <flux:field>
                            <flux:label>Additional Employees (Optional)</flux:label>
                            <flux:description>{{ __('Select employees from other groups to include in this holiday') }}</flux:description>
                            
                            <!-- Search Input for Additional Employees -->
                            <div class="mb-3">
                                <flux:input 
                                    wire:model.live.debounce.300ms="additionalEmployeeSearchTerm"
                                    placeholder="Search employees..."
                                    icon="magnifying-glass"
                                />
                            </div>
                            
                            <div class="relative">
                                <select 
                                    wire:model.live="additionalEmployeeIds" 
                                    multiple 
                                    class="w-full px-3 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent shadow-sm"
                                    size="6"
                                    style="min-height: 150px;"
                                >
                                    @foreach($filteredAdditionalEmployees as $employee)
                                        <option value="{{ $employee['value'] }}" class="py-2 px-3 hover:bg-zinc-100 dark:hover:bg-zinc-700">
                                            {{ $employee['label'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <flux:description>{{ __('Search and select additional employees. Hold Ctrl/Cmd to select multiple employees.') }}</flux:description>
                            <flux:error name="additionalEmployeeIds" />
                        </flux:field>
                    </div>
                @endif
                
                <!-- Employee Selection (if scope is employee) -->
                @if($scopeType === 'employee')
                    <div class="grid grid-cols-1 gap-6">
                        <flux:field>
                            <flux:label>Select Employees <span class="text-red-500">*</span></flux:label>
                            
                            <!-- Search Input for Employees -->
                            <div class="mb-3">
                                <flux:input 
                                    wire:model.live.debounce.300ms="employeeSearchTerm"
                                    placeholder="Search employees..."
                                    icon="magnifying-glass"
                                />
                            </div>
                            
                            <div class="relative">
                                <select 
                                    wire:model.live="selectedEmployeeIds" 
                                    multiple 
                                    class="w-full px-3 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent shadow-sm"
                                    size="6"
                                    style="min-height: 150px;"
                                >
                                    @foreach($filteredEmployees as $employee)
                                        <option value="{{ $employee['value'] }}" class="py-2 px-3 hover:bg-zinc-100 dark:hover:bg-zinc-700">
                                            {{ $employee['label'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <flux:description>{{ __('Search and select employees. Hold Ctrl/Cmd to select multiple employees.') }}</flux:description>
                            <flux:error name="selectedEmployeeIds" />
                        </flux:field>
                    </div>
                @endif
                
                <!-- Submit and Cancel Buttons -->
                <div class="flex justify-end gap-3 pt-4">
                    <flux:button 
                        type="button" 
                        variant="outline" 
                        wire:click="closeAddHolidayFlyout"
                    >
                        Cancel
                    </flux:button>
                    <flux:button type="submit" variant="primary">
                        {{ $editingId ? 'Update Holiday' : 'Add Holiday' }}
                    </flux:button>
                </div>
            </form>
        </div>
    </flux:modal>
</section>
