<section class="w-full">
    @include('partials.employees-heading')
    
    <x-employees.layout :heading="__('Employee List')" :subheading="__('View and manage all employees')">
        @if (session()->has('message'))
            <flux:callout variant="success" icon="check-circle" dismissible class="mb-6">
                {{ session('message') }}
            </flux:callout>
        @endif

        @if (session()->has('error'))
            <flux:callout variant="danger" icon="exclamation-circle" dismissible class="mb-6">
                {{ session('error') }}
            </flux:callout>
        @endif

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
                                        <button wire:click="sort('shift')" class="flex items-center gap-1 hover:text-zinc-700 dark:hover:text-zinc-200">
                                            {{ __('Shift') }}
                                            @if($sortBy === 'shift')
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
                                        <button wire:click="sort('group')" class="flex items-center gap-1 hover:text-zinc-700 dark:hover:text-zinc-200">
                                            {{ __('Group') }}
                                            @if($sortBy === 'group')
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
                                        <td class="px-6 py-6">
                                            <div class="flex items-center gap-3">
                                                <flux:avatar size="sm" :initials="$employee->initials()" />
                                                <div class="space-y-1">
                                                    <div class="font-medium text-zinc-900 dark:text-zinc-100">
                                                        {{ $employee->name }}
                                                    </div>
                                                    <div class="text-sm text-zinc-500 dark:text-zinc-400">
                                                        {{ $employee->email }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        
                                        <td class="px-6 py-6 whitespace-nowrap">
                                            @if($employee->employee && $employee->employee->shift)
                                                <div class="flex items-center gap-2">
                                                    <flux:icon name="clock" class="w-4 h-4 text-zinc-500 dark:text-zinc-400" />
                                                    <span class="text-sm text-zinc-900 dark:text-zinc-100">
                                                        {{ $employee->employee->shift->shift_name }}
                                                    </span>
                                                </div>
                                            @else
                                                <span class="text-sm text-zinc-500 dark:text-zinc-400">N/A</span>
                                            @endif
                                        </td>
                                        
                                        <td class="px-6 py-6 whitespace-nowrap">
                                            @php
                                                $employeeModel = $employee->employee ?? null;
                                                $department = null;
                                                
                                                if ($employeeModel && $employeeModel->department_id) {
                                                    // Check if relationship is loaded and is an object (not the old varchar column)
                                                    if ($employeeModel->relationLoaded('department')) {
                                                        $dept = $employeeModel->getRelation('department');
                                                        if ($dept && is_object($dept)) {
                                                            $department = $dept;
                                                        }
                                                    }
                                                    
                                                    // If not loaded or not an object, fetch it via relationship
                                                    if (!$department) {
                                                        $department = $employeeModel->department()->first();
                                                    }
                                                }
                                            @endphp
                                            
                                            @if($department)
                                                <div class="flex items-center gap-2">
                                                    <flux:icon name="building-office" class="w-4 h-4 text-zinc-500 dark:text-zinc-400" />
                                                    <span class="text-sm text-zinc-900 dark:text-zinc-100">
                                                        {{ $department->title }}
                                                    </span>
                                                </div>
                                            @else
                                                <span class="text-sm text-zinc-500 dark:text-zinc-400">N/A</span>
                                            @endif
                                        </td>
                                        
                                        <td class="px-6 py-6 whitespace-nowrap">
                                            @php
                                                $employeeModel = $employee->employee ?? null;
                                                $group = null;
                                                
                                                if ($employeeModel && $employeeModel->group_id) {
                                                    // Check if relationship is loaded
                                                    if ($employeeModel->relationLoaded('group')) {
                                                        $grp = $employeeModel->getRelation('group');
                                                        if ($grp && is_object($grp)) {
                                                            $group = $grp;
                                                        }
                                                    }
                                                    
                                                    // If not loaded, fetch it via relationship
                                                    if (!$group) {
                                                        $group = $employeeModel->group()->first();
                                                    }
                                                }
                                            @endphp
                                            
                                            @if($group)
                                                <div class="flex items-center gap-2">
                                                    <flux:icon name="user-group" class="w-4 h-4 text-zinc-500 dark:text-zinc-400" />
                                                    <span class="text-sm text-zinc-900 dark:text-zinc-100">
                                                        {{ $group->name }}
                                                    </span>
                                                </div>
                                            @else
                                                <span class="text-sm text-zinc-500 dark:text-zinc-400">N/A</span>
                                            @endif
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
                                                $employeeStatus = $employee->employee->status ?? 'active';
                                                $statusColor = match($employeeStatus) {
                                                    'active' => 'green',
                                                    'inactive' => 'zinc',
                                                    'on-leave' => 'yellow',
                                                    'terminated' => 'red',
                                                    default => 'green'
                                                };
                                            @endphp
                                            <flux:badge color="{{ $statusColor }}" size="sm">
                                                {{ ucfirst($employeeStatus) }}
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
                                                        <flux:menu.item icon="clock" wire:click="openAssignShiftFlyout({{ $employee->id }})">
                                                            {{ __('Assign Shift') }}
                                                        </flux:menu.item>
                                                        <flux:menu.item icon="building-office" wire:click="openAssignDepartmentFlyout({{ $employee->id }})">
                                                            {{ __('Assign Department') }}
                                                        </flux:menu.item>
                                                        <flux:menu.item icon="shield-check" wire:click="openAssignRoleFlyout({{ $employee->id }})">
                                                            {{ __('Assign Role') }}
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

            <!-- Assign Shift Flyout -->
            <flux:modal variant="flyout" :show="$showAssignShiftFlyout" wire:model="showAssignShiftFlyout">
                <form wire:submit="assignShift">
                    <div class="p-6 space-y-6">
                        <div>
                            <flux:heading size="lg" level="3">{{ __('Assign Shift') }}</flux:heading>
                            <flux:subheading>{{ __('Assign or change shift for this employee') }}</flux:subheading>
                        </div>

                        @if (session()->has('message'))
                            <flux:callout variant="success" icon="check-circle" dismissible>
                                {{ session('message') }}
                            </flux:callout>
                        @endif

                        @if (session()->has('error'))
                            <flux:callout variant="danger" icon="exclamation-circle" dismissible>
                                {{ session('error') }}
                            </flux:callout>
                        @endif

                        <flux:field>
                            <flux:label>{{ __('Shift') }}</flux:label>
                            <flux:description>{{ __('Select the shift to assign to this employee') }}</flux:description>
                            <flux:select wire:model="selectedShiftId" required>
                                <option value="">{{ __('Select a shift') }}</option>
                                @foreach($shifts as $shift)
                                    <option value="{{ $shift['value'] }}">{{ $shift['label'] }}</option>
                                @endforeach
                            </flux:select>
                            <flux:error name="selectedShiftId" />
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('Start Date') }}</flux:label>
                            <flux:description>{{ __('The date from which this shift assignment will be effective') }}</flux:description>
                            <flux:input type="date" wire:model="shiftStartDate" required />
                            <flux:error name="shiftStartDate" />
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('Notes') }}</flux:label>
                            <flux:description>{{ __('Optional notes about this shift assignment') }}</flux:description>
                            <flux:textarea wire:model="shiftNotes" rows="3" placeholder="{{ __('Add any notes about this shift assignment...') }}" />
                            <flux:error name="shiftNotes" />
                        </flux:field>

                        @if(!empty($shiftHistory))
                            <div class="pt-4 border-t border-zinc-200 dark:border-zinc-700">
                                <flux:heading size="sm" class="mb-3 text-zinc-700 dark:text-zinc-300">{{ __('Recent Shift Assignments') }}</flux:heading>
                                <div class="space-y-4 max-h-64 overflow-y-auto pr-1">
                                    @foreach($shiftHistory as $index => $history)
                                        <div class="flex gap-3">
                                            {{-- Timeline icon --}}
                                            <div class="flex flex-col items-center">
                                                <div class="w-8 h-8 rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center flex-shrink-0">
                                                    <flux:icon name="clock" class="w-4 h-4 text-zinc-500 dark:text-zinc-400" />
                                                </div>
                                                @if(!$loop->last)
                                                    <div class="w-0.5 h-full min-h-4 bg-zinc-200 dark:bg-zinc-700 mt-2"></div>
                                                @endif
                                            </div>
                                            
                                            {{-- Content --}}
                                            <div class="flex-1 min-w-0 pb-2">
                                                <div class="flex items-center gap-2 mb-1">
                                                    <span class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">{{ $history['shift_name'] }}</span>
                                                    @if($history['end_date'] === 'Current')
                                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400">
                                                            {{ __('(Current)') }}
                                                        </span>
                                                    @endif
                                                </div>
                                                <div class="text-xs text-zinc-600 dark:text-zinc-400 space-y-0.5">
                                                    <div>By - {{ $history['assigned_by'] }} on {{ $history['assigned_date'] }}</div>
                                                    <div class="text-zinc-500 dark:text-zinc-500">
                                                        {{ __('Start') }}: {{ $history['start_date'] }}
                                                        @if($history['end_date'] !== 'Current')
                                                            · {{ __('End') }}: {{ $history['end_date'] }}
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="flex items-center justify-end gap-3 p-6 border-t border-zinc-200 dark:border-zinc-700">
                        <flux:button variant="ghost" wire:click="closeAssignShiftFlyout">
                            {{ __('Cancel') }}
                        </flux:button>
                        <flux:button type="submit">
                            {{ __('Assign Shift') }}
                        </flux:button>
                    </div>
                </form>
            </flux:modal>

            <!-- Assign Department Flyout -->
            <flux:modal variant="flyout" :show="$showAssignDepartmentFlyout" wire:model="showAssignDepartmentFlyout">
                <form wire:submit="assignDepartment">
                    <div class="p-6 space-y-6">
                        <div>
                            <flux:heading size="lg" level="3">{{ __('Assign Department') }}</flux:heading>
                            <flux:subheading>{{ __('Assign or change department for this employee') }}</flux:subheading>
                        </div>

                        @if (session()->has('message'))
                            <flux:callout variant="success" icon="check-circle" dismissible>
                                {{ session('message') }}
                            </flux:callout>
                        @endif

                        @if (session()->has('error'))
                            <flux:callout variant="danger" icon="exclamation-circle" dismissible>
                                {{ session('error') }}
                            </flux:callout>
                        @endif

                        <flux:field>
                            <flux:label>{{ __('Department') }}</flux:label>
                            <flux:description>{{ __('Select the department to assign to this employee') }}</flux:description>
                            <flux:select wire:model="selectedDepartmentId" required>
                                <option value="">{{ __('Select a department') }}</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department['value'] }}">{{ $department['label'] }}</option>
                                @endforeach
                            </flux:select>
                            <flux:error name="selectedDepartmentId" />
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('Effective Date') }}</flux:label>
                            <flux:description>{{ __('The date from which this department assignment will be effective') }}</flux:description>
                            <flux:input type="date" wire:model="departmentStartDate" required />
                            <flux:error name="departmentStartDate" />
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('Reason') }}</flux:label>
                            <flux:description>{{ __('Reason for the department change') }}</flux:description>
                            <flux:select wire:model="departmentReason">
                                <option value="">{{ __('Select a reason (optional)') }}</option>
                                <option value="transfer">{{ __('Transfer') }}</option>
                                <option value="promotion">{{ __('Promotion') }}</option>
                                <option value="reorganization">{{ __('Reorganization') }}</option>
                                <option value="other">{{ __('Other') }}</option>
                            </flux:select>
                            <flux:error name="departmentReason" />
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('Notes') }}</flux:label>
                            <flux:description>{{ __('Optional notes about this department assignment') }}</flux:description>
                            <flux:textarea wire:model="departmentNotes" rows="3" placeholder="{{ __('Add any notes about this department assignment...') }}" />
                            <flux:error name="departmentNotes" />
                        </flux:field>
                    </div>

                    <div class="flex items-center justify-end gap-3 p-6 border-t border-zinc-200 dark:border-zinc-700">
                        <flux:button variant="ghost" wire:click="closeAssignDepartmentFlyout">
                            {{ __('Cancel') }}
                        </flux:button>
                        <flux:button type="submit">
                            {{ __('Assign Department') }}
                        </flux:button>
                    </div>
                </form>
            </flux:modal>

            <!-- Assign Role Flyout -->
            <flux:modal variant="flyout" :show="$showAssignRoleFlyout" wire:model="showAssignRoleFlyout">
                <form wire:submit.prevent="assignRole">
                    <div class="p-6 space-y-6">
                        <div>
                            <flux:heading size="lg" level="3">{{ __('Assign Role') }}</flux:heading>
                            <flux:subheading>
                                {{ __('Assign or update the system role for this employee') }}
                            </flux:subheading>
                        </div>

                        @if (session()->has('message'))
                            <flux:callout variant="success" icon="check-circle" dismissible>
                                {{ session('message') }}
                            </flux:callout>
                        @endif

                        @if (session()->has('error'))
                            <flux:callout variant="danger" icon="exclamation-circle" dismissible>
                                {{ session('error') }}
                            </flux:callout>
                        @endif

                        <flux:field>
                            <flux:label>{{ __('Employee') }}</flux:label>
                            <flux:input type="text" value="{{ $selectedEmployeeName }}" disabled />
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('Role') }}</flux:label>
                            <flux:description>{{ __('Select the role to assign. Choose "No Role" to remove access.') }}</flux:description>
                            <flux:select wire:model="selectedRoleName">
                                <option value="">{{ __('No Role') }}</option>
                                @foreach($availableRoles as $role)
                                    <option value="{{ $role['value'] }}">{{ $role['label'] }}</option>
                                @endforeach
                            </flux:select>
                            <flux:error name="selectedRoleName" />
                        </flux:field>
                    </div>

                    <div class="flex items-center justify-end gap-3 p-6 border-t border-zinc-200 dark:border-zinc-700">
                        <flux:button variant="ghost" wire:click="closeAssignRoleFlyout">
                            {{ __('Cancel') }}
                        </flux:button>
                        <flux:button type="submit">
                            {{ __('Save Role') }}
                        </flux:button>
                    </div>
                </form>
            </flux:modal>
        </div>
    </x-employees.layout>
</section>
