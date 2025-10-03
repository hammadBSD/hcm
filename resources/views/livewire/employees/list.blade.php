<section class="w-full">
    @include('partials.employees-heading')
    
    <x-employees.layout :heading="__('Employee List')" :subheading="__('View and manage all employees')">
        <!-- Search and Filter Controls -->
        <div class="my-6 w-full space-y-4">
            <div class="flex flex-col sm:flex-row gap-4">
                <!-- Search Input -->
                <div class="flex-1">
                    <flux:input 
                        wire:model.live.debounce.300ms="search" 
                        :label="__('Search')" 
                        type="text" 
                        placeholder="Search by name, email, or employee ID..." 
                        icon="magnifying-glass"
                    />
                </div>
                
                <!-- Department Filter -->
                <div class="sm:w-64">
                    <flux:field>
                        <flux:label>{{ __('Department') }}</flux:label>
                        <flux:select wire:model.live="filterDepartment">
                            <option value="">{{ __('All Departments') }}</option>
                            <option value="hr">{{ __('Human Resources') }}</option>
                            <option value="it">{{ __('Information Technology') }}</option>
                            <option value="finance">{{ __('Finance') }}</option>
                            <option value="marketing">{{ __('Marketing') }}</option>
                            <option value="sales">{{ __('Sales') }}</option>
                            <option value="operations">{{ __('Operations') }}</option>
                        </flux:select>
                    </flux:field>
                </div>

                <!-- Status Filter -->
                <div class="sm:w-48">
                    <flux:field>
                        <flux:label>{{ __('Status') }}</flux:label>
                        <flux:select wire:model.live="filterStatus">
                            <option value="">{{ __('All Status') }}</option>
                            <option value="active">{{ __('Active') }}</option>
                            <option value="inactive">{{ __('Inactive') }}</option>
                            <option value="on-leave">{{ __('On Leave') }}</option>
                            <option value="terminated">{{ __('Terminated') }}</option>
                        </flux:select>
                    </flux:field>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <flux:button variant="primary" icon="plus" href="{{ route('employees.register') }}">
                        {{ __('Add Employee') }}
                    </flux:button>
                    
                    <flux:button variant="outline" icon="arrow-down-tray" wire:click="export">
                        {{ __('Export') }}
                    </flux:button>

                    <flux:button variant="outline" icon="arrow-up-tray" wire:click="import">
                        {{ __('Import') }}
                    </flux:button>
                </div>
                
                <!-- Additional Filters -->
                <div class="flex items-center gap-2">
                    <flux:button variant="ghost" size="sm" icon="funnel" wire:click="toggleAdvancedFilters">
                        {{ __('Advanced Filters') }}
                    </flux:button>
                    
                    <flux:button variant="ghost" size="sm" icon="arrow-path" wire:click="refresh">
                        {{ __('Refresh') }}
                    </flux:button>
                </div>
            </div>

            <!-- Advanced Filters (Toggle) -->
            @if($showAdvancedFilters)
                <div class="bg-zinc-50 dark:bg-zinc-800 rounded-lg p-4 border border-zinc-200 dark:border-zinc-700">
                    <!-- Row 1 -->
                    <div class="flex flex-wrap -mx-2 mb-4">
                        <div class="w-full md:w-1/2 lg:w-1/4 px-2 mb-4">
                            <flux:field>
                                <flux:label>{{ __('Country') }}</flux:label>
                                <flux:select wire:model.live="filterCountry">
                                    <option value="">{{ __('--ALL--') }}</option>
                                    <option value="pakistan">{{ __('Pakistan') }}</option>
                                    <option value="uae">{{ __('UAE') }}</option>
                                    <option value="usa">{{ __('USA') }}</option>
                                </flux:select>
                            </flux:field>
                        </div>

                        <div class="w-full md:w-1/2 lg:w-1/4 px-2 mb-4">
                            <flux:field>
                                <flux:label>{{ __('Province') }}</flux:label>
                                <flux:select wire:model.live="filterProvince">
                                    <option value="">{{ __('--ALL--') }}</option>
                                    <option value="punjab">{{ __('Punjab') }}</option>
                                    <option value="sindh">{{ __('Sindh') }}</option>
                                    <option value="kpk">{{ __('KPK') }}</option>
                                </flux:select>
                            </flux:field>
                        </div>

                        <div class="w-full md:w-1/2 lg:w-1/4 px-2 mb-4">
                            <flux:field>
                                <flux:label>{{ __('City') }}</flux:label>
                                <flux:select wire:model.live="filterCity">
                                    <option value="">{{ __('--ALL--') }}</option>
                                    <option value="karachi">{{ __('Karachi') }}</option>
                                    <option value="lahore">{{ __('Lahore') }}</option>
                                    <option value="islamabad">{{ __('Islamabad') }}</option>
                                </flux:select>
                            </flux:field>
                        </div>

                        <div class="w-full md:w-1/2 lg:w-1/4 px-2 mb-4">
                            <flux:field>
                                <flux:label>{{ __('Area') }}</flux:label>
                                <flux:select wire:model.live="filterArea">
                                    <option value="">{{ __('--ALL--') }}</option>
                                    <option value="dha">{{ __('DHA') }}</option>
                                    <option value="gulshan">{{ __('Gulshan') }}</option>
                                    <option value="clifton">{{ __('Clifton') }}</option>
                                </flux:select>
                            </flux:field>
                        </div>
                    </div>

                    <!-- Row 2 -->
                    <div class="flex flex-wrap -mx-2 mb-4">
                        <div class="w-full md:w-1/2 lg:w-1/4 px-2 mb-4">
                            <flux:field>
                                <flux:label>{{ __('Vendor') }}</flux:label>
                                <flux:select wire:model.live="filterVendor">
                                    <option value="">{{ __('--ALL--') }}</option>
                                    <option value="vendor1">{{ __('Vendor 1') }}</option>
                                    <option value="vendor2">{{ __('Vendor 2') }}</option>
                                </flux:select>
                            </flux:field>
                        </div>

                        <div class="w-full md:w-1/2 lg:w-1/4 px-2 mb-4">
                            <flux:field>
                                <flux:label>{{ __('Station') }}</flux:label>
                                <flux:select wire:model.live="filterStation">
                                    <option value="">{{ __('--ALL--') }}</option>
                                    <option value="station1">{{ __('Station 1') }}</option>
                                    <option value="station2">{{ __('Station 2') }}</option>
                                </flux:select>
                            </flux:field>
                        </div>

                        <div class="w-full md:w-1/2 lg:w-1/4 px-2 mb-4">
                            <flux:field>
                                <flux:label>{{ __('Department') }}</flux:label>
                                <flux:select wire:model.live="filterDepartment">
                                    <option value="">{{ __('-- Select --') }}</option>
                                    <option value="hr">{{ __('Human Resources') }}</option>
                                    <option value="it">{{ __('Information Technology') }}</option>
                                    <option value="finance">{{ __('Finance') }}</option>
                                    <option value="marketing">{{ __('Marketing') }}</option>
                                    <option value="sales">{{ __('Sales') }}</option>
                                    <option value="operations">{{ __('Operations') }}</option>
                                </flux:select>
                            </flux:field>
                        </div>

                        <div class="w-full md:w-1/2 lg:w-1/4 px-2 mb-4">
                            <flux:field>
                                <flux:label>{{ __('Sub Department') }}</flux:label>
                                <flux:select wire:model.live="filterSubDepartment">
                                    <option value="">{{ __('--ALL--') }}</option>
                                    <option value="subdept1">{{ __('Sub Department 1') }}</option>
                                    <option value="subdept2">{{ __('Sub Department 2') }}</option>
                                </flux:select>
                            </flux:field>
                        </div>
                    </div>

                    <!-- Row 3 -->
                    <div class="flex flex-wrap -mx-2 mb-4">
                        <div class="w-full md:w-1/2 lg:w-1/4 px-2 mb-4">
                            <flux:field>
                                <flux:label>{{ __('Employee Group') }}</flux:label>
                                <flux:select wire:model.live="filterEmployeeGroup">
                                    <option value="">{{ __('--ALL--') }}</option>
                                    <option value="group1">{{ __('Group 1') }}</option>
                                    <option value="group2">{{ __('Group 2') }}</option>
                                </flux:select>
                            </flux:field>
                        </div>

                        <div class="w-full md:w-1/2 lg:w-1/4 px-2 mb-4">
                            <flux:field>
                                <flux:label>{{ __('Designation') }}</flux:label>
                                <flux:select wire:model.live="filterDesignation">
                                    <option value="">{{ __('-- Select --') }}</option>
                                    <option value="ceo">{{ __('CEO') }}</option>
                                    <option value="manager">{{ __('Manager') }}</option>
                                    <option value="senior">{{ __('Senior') }}</option>
                                    <option value="junior">{{ __('Junior') }}</option>
                                </flux:select>
                            </flux:field>
                        </div>

                        <div class="w-full md:w-1/2 lg:w-1/4 px-2 mb-4">
                            <flux:field>
                                <flux:label>{{ __('Division') }}</flux:label>
                                <flux:select wire:model.live="filterDivision">
                                    <option value="">{{ __('--ALL--') }}</option>
                                    <option value="division1">{{ __('Division 1') }}</option>
                                    <option value="division2">{{ __('Division 2') }}</option>
                                </flux:select>
                            </flux:field>
                        </div>

                        <div class="w-full md:w-1/2 lg:w-1/4 px-2 mb-4">
                            <flux:field>
                                <flux:label>{{ __('Employee Code') }}</flux:label>
                                <flux:input wire:model.live="filterEmployeeCode" placeholder="Type something" />
                            </flux:field>
                        </div>
                    </div>

                    <!-- Row 4 -->
                    <div class="flex flex-wrap -mx-2 mb-4">
                        <div class="w-full md:w-1/2 lg:w-1/4 px-2 mb-4">
                            <flux:field>
                                <flux:label>{{ __('Employee Name') }}</flux:label>
                                <flux:select wire:model.live="filterEmployeeName">
                                    <option value="">{{ __('-- Select Employee --') }}</option>
                                    <option value="john">{{ __('John Doe') }}</option>
                                    <option value="jane">{{ __('Jane Smith') }}</option>
                                    <option value="bob">{{ __('Bob Johnson') }}</option>
                                </flux:select>
                            </flux:field>
                        </div>

                        <div class="w-full md:w-1/2 lg:w-1/4 px-2 mb-4">
                            <flux:field>
                                <flux:label>{{ __('Status') }}</flux:label>
                                <flux:select wire:model.live="filterStatus">
                                    <option value="">{{ __('All Status') }}</option>
                                    <option value="active">{{ __('Active') }}</option>
                                    <option value="inactive">{{ __('Inactive') }}</option>
                                    <option value="on-leave">{{ __('On Leave') }}</option>
                                    <option value="terminated">{{ __('Terminated') }}</option>
                                </flux:select>
                            </flux:field>
                        </div>

                        <div class="w-full md:w-1/2 lg:w-1/4 px-2 mb-4">
                            <flux:field>
                                <flux:label>{{ __('Employee Status') }}</flux:label>
                                <flux:select wire:model.live="filterEmployeeStatus">
                                    <option value="">{{ __('ALL') }}</option>
                                    <option value="permanent">{{ __('Permanent') }}</option>
                                    <option value="contract">{{ __('Contract') }}</option>
                                    <option value="temporary">{{ __('Temporary') }}</option>
                                </flux:select>
                            </flux:field>
                        </div>

                        <div class="w-full md:w-1/2 lg:w-1/4 px-2 mb-4">
                            <flux:field>
                                <flux:label>{{ __('Documents Attached') }}</flux:label>
                                <flux:select wire:model.live="filterDocumentsAttached">
                                    <option value="">{{ __('ALL') }}</option>
                                    <option value="yes">{{ __('Yes') }}</option>
                                    <option value="no">{{ __('No') }}</option>
                                </flux:select>
                            </flux:field>
                        </div>
                    </div>

                    <!-- Row 5 -->
                    <div class="flex flex-wrap -mx-2 mb-4">
                        <div class="w-full md:w-1/2 lg:w-1/4 px-2 mb-4">
                            <flux:field>
                                <flux:label>{{ __('Roles Template') }}</flux:label>
                                <flux:select wire:model.live="filterRolesTemplate">
                                    <option value="">{{ __('-- Select --') }}</option>
                                    <option value="template1">{{ __('Template 1') }}</option>
                                    <option value="template2">{{ __('Template 2') }}</option>
                                </flux:select>
                            </flux:field>
                        </div>

                        <div class="w-full md:w-1/2 lg:w-1/4 px-2 mb-4">
                            <flux:field>
                                <flux:label>{{ __('Emirates ID / CNIC #') }}</flux:label>
                                <flux:input wire:model.live="filterEmiratesId" placeholder="Enter ID number" />
                            </flux:field>
                        </div>

                        <div class="w-full md:w-1/2 lg:w-1/4 px-2 mb-4">
                            <flux:field>
                                <flux:label>{{ __('Flag') }}</flux:label>
                                <flux:select wire:model.live="filterFlag">
                                    <option value="">{{ __('--ALL--') }}</option>
                                    <option value="flag1">{{ __('Flag 1') }}</option>
                                    <option value="flag2">{{ __('Flag 2') }}</option>
                                </flux:select>
                            </flux:field>
                        </div>

                        <div class="w-full md:w-1/2 lg:w-1/4 px-2 mb-4">
                            <flux:field>
                                <flux:label>{{ __('Employee Reports To') }}</flux:label>
                                <flux:select wire:model.live="filterReportsTo">
                                    <option value="">{{ __('-- Select --') }}</option>
                                    <option value="manager1">{{ __('Manager 1') }}</option>
                                    <option value="manager2">{{ __('Manager 2') }}</option>
                                </flux:select>
                            </flux:field>
                        </div>
                    </div>

                    <!-- Row 6 -->
                    <div class="flex flex-wrap -mx-2 mb-4">
                        <div class="w-full md:w-1/2 lg:w-1/4 px-2 mb-4">
                            <flux:field>
                                <flux:label>{{ __('Blacklist/Whitelist') }}</flux:label>
                                <flux:select wire:model.live="filterBlacklistWhitelist">
                                    <option value="">{{ __('Whitelist') }}</option>
                                    <option value="blacklist">{{ __('Blacklist') }}</option>
                                    <option value="whitelist">{{ __('Whitelist') }}</option>
                                </flux:select>
                            </flux:field>
                        </div>

                        <div class="w-full md:w-1/2 lg:w-1/4 px-2 mb-4">
                            <flux:field>
                                <flux:label>{{ __('Position Code') }}</flux:label>
                                <flux:input wire:model.live="filterPositionCode" placeholder="Enter position code" />
                            </flux:field>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Employee Table -->
        <div class="mt-8">
            @if($employees->count() > 0)
                <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 overflow-hidden shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-zinc-50 dark:bg-zinc-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                        <button wire:click="sort('name')" class="flex items-center gap-1 hover:text-zinc-700 dark:hover:text-zinc-200">
                                            {{ __('Employee') }}
                                            @if($sortBy === 'name')
                                                <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="w-4 h-4" />
                                            @endif
                                        </button>
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                        <button wire:click="sort('email')" class="flex items-center gap-1 hover:text-zinc-700 dark:hover:text-zinc-200">
                                            {{ __('Email') }}
                                            @if($sortBy === 'email')
                                                <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="w-4 h-4" />
                                            @endif
                                        </button>
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                        <button wire:click="sort('department')" class="flex items-center gap-1 hover:text-zinc-700 dark:hover:text-zinc-200">
                                            {{ __('Department') }}
                                            @if($sortBy === 'department')
                                                <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="w-4 h-4" />
                                            @endif
                                        </button>
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                        <button wire:click="sort('role')" class="flex items-center gap-1 hover:text-zinc-700 dark:hover:text-zinc-200">
                                            {{ __('Role') }}
                                            @if($sortBy === 'role')
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
                                @foreach ($employees as $employee)
                                    <tr class="hover:bg-zinc-100 dark:hover:bg-zinc-600 transition-colors duration-150">
                                        <td class="px-6 py-6 whitespace-nowrap">
                                            <div class="flex items-center gap-3">
                                                <flux:avatar size="sm" :initials="$employee->initials()" />
                                                <div>
                                                    <div class="font-medium text-zinc-900 dark:text-zinc-100">
                                                        {{ $employee->name }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        
                                        <td class="px-6 py-6 whitespace-nowrap">
                                            <div class="text-sm text-zinc-900 dark:text-zinc-100">
                                                {{ $employee->email }}
                                            </div>
                                        </td>
                                        
                                        <td class="px-6 py-6 whitespace-nowrap">
                                            <div class="text-sm text-zinc-900 dark:text-zinc-100">
                                                {{ $employee->department ?? 'Not assigned' }}
                                            </div>
                                        </td>
                                        
                                        <td class="px-6 py-6 whitespace-nowrap">
                                            @if($employee->roles->count() > 0)
                                                <flux:badge color="blue" size="sm">
                                                    {{ $employee->roles->first()->name }}
                                                </flux:badge>
                                            @else
                                                <span class="text-sm text-zinc-500 dark:text-zinc-400">No role</span>
                                            @endif
                                        </td>
                                        
                                        <td class="px-6 py-6 whitespace-nowrap">
                                            @php
                                                $statusColor = match($employee->status ?? 'active') {
                                                    'active' => 'green',
                                                    'inactive' => 'zinc',
                                                    'on-leave' => 'yellow',
                                                    'terminated' => 'red',
                                                    default => 'green'
                                                };
                                            @endphp
                                            <flux:badge color="{{ $statusColor }}" size="sm">
                                                {{ ucfirst($employee->status ?? 'Active') }}
                                            </flux:badge>
                                        </td>
                                        
                                        <td class="px-6 py-6 whitespace-nowrap text-sm font-medium">
                                            <div class="flex items-center gap-1">
                                                <!-- <flux:button variant="ghost" size="sm" icon="eye" href="{{ route('employees.show', $employee->id) }}" /> -->
                                                <!-- <flux:button variant="ghost" size="sm" icon="pencil" href="{{ route('employees.edit', $employee->id) }}" /> -->
                                                <flux:dropdown>
                                                    <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                                                    <flux:menu>
                                                        <flux:menu.item icon="user" href="{{ route('employees.show', $employee->id) }}">
                                                            {{ __('View Profile') }}
                                                        </flux:menu.item>
                                                        <flux:menu.item icon="pencil" href="{{ route('employees.edit', $employee->id) }}">
                                                            {{ __('Edit Details') }}
                                                        </flux:menu.item>
                                                        <!-- <flux:menu.item icon="key" wire:click="resetPassword({{ $employee->id }})">
                                                            {{ __('Reset Password') }}
                                                        </flux:menu.item> -->
                                                        <flux:menu.separator />
                                                        <flux:menu.item icon="trash" color="red" wire:click="deactivate({{ $employee->id }})">
                                                            {{ __('Deactivate') }}
                                                        </flux:menu.item>
                                                    </flux:menu>
                                                </flux:dropdown>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Pagination -->
                @if($employees->hasPages())
                    <div class="mt-6">
                        {{ $employees->links() }}
                    </div>
                @endif
            @else
                <!-- Empty State -->
                <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                    </svg>
                    <flux:heading size="lg" level="3" class="mt-4 text-zinc-600 dark:text-zinc-400">
                        {{ __('No employees found') }}
                    </flux:heading>
                    <flux:text class="mt-2 text-zinc-500 dark:text-zinc-500">
                        {{ __('Get started by adding your first employee.') }}
                    </flux:text>
                    <flux:button href="{{ route('employees.register') }}" icon="plus" class="mt-4">
                        {{ __('Add Employee') }}
                    </flux:button>
                </div>
            @endif
        </div>
    </x-employees.layout>
</section>
