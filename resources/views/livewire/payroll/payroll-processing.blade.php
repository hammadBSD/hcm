<section class="w-full">
    @include('partials.payroll-heading')
    
    <x-payroll.layout :heading="__('Payroll Processing')" :subheading="__('Process payroll for employees')">
        <!-- Search and Filter Controls -->
        <div class="my-6 w-full space-y-4">
            <div class="flex flex-col sm:flex-row gap-4">
                <!-- Department Filter -->
                <div class="sm:w-64">
                    <flux:field>
                        <flux:label>{{ __('Department') }}</flux:label>
                        <flux:select wire:model.live="selectedDepartment">
                            <option value="">{{ __('All Departments') }}</option>
                            @foreach($departments as $department)
                                <option value="{{ $department }}">{{ $department }}</option>
                            @endforeach
                        </flux:select>
                    </flux:field>
                </div>

                <!-- Year Filter -->
                <div class="sm:w-32">
                    <flux:field>
                        <flux:label>{{ __('Year') }}</flux:label>
                        <flux:select wire:model.live="selectedYear">
                            @for($year = now()->year; $year >= now()->year - 5; $year--)
                                <option value="{{ $year }}">{{ $year }}</option>
                            @endfor
                        </flux:select>
                    </flux:field>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <flux:button variant="primary" icon="calculator" wire:click="openProcessPayrollModal">
                        {{ __('Process Payroll') }}
                    </flux:button>
                </div>
                
                <!-- Additional Actions -->
                <div class="flex items-center gap-2">
                    <flux:button variant="ghost" size="sm" icon="arrow-path" wire:click="$refresh">
                        {{ __('Refresh') }}
                    </flux:button>
                </div>
            </div>
        </div>

        <!-- Flash Messages -->
        @if (session()->has('message'))
            <flux:callout variant="success" icon="check-circle" dismissible>
                {{ session('message') }}
            </flux:callout>
        @endif

        <!-- Months Table -->
        <div class="mt-8">
            @if($months && count($months) > 0)
                <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 overflow-hidden shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-zinc-50 dark:bg-zinc-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                        <button wire:click="sort('month')" class="flex items-center gap-1 hover:text-zinc-700 dark:hover:text-zinc-200">
                                            {{ __('Month') }}
                                            @if($sortBy === 'month')
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
                                @foreach ($months as $monthData)
                                    <tr class="hover:bg-zinc-100 dark:hover:bg-zinc-600 transition-colors duration-150">
                                        <td class="px-6 py-6 whitespace-nowrap">
                                            @if($monthData['status'] === 'pending')
                                                <button 
                                                    wire:click="openMonthEmployeesFlyout({{ $monthData['month'] }}, {{ $monthData['year'] }})"
                                                    class="text-left flex items-center gap-2 text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 hover:underline"
                                                >
                                                    <flux:icon name="calendar" class="w-5 h-5" />
                                                    <span class="font-medium">{{ $monthData['month_name'] }}</span>
                                                </button>
                                            @else
                                                <div class="flex items-center gap-2">
                                                    <flux:icon name="calendar" class="w-5 h-5 text-zinc-400" />
                                                    <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $monthData['month_name'] }}</span>
                                                </div>
                                            @endif
                                        </td>
                                        
                                        <td class="px-6 py-6 whitespace-nowrap">
                                            @if($monthData['status'] === 'pending')
                                            <flux:badge color="yellow" size="sm">
                                                {{ __('Pending') }}
                                            </flux:badge>
                                            @else
                                                <flux:badge color="green" size="sm">
                                                    {{ __('Processed') }}
                                                </flux:badge>
                                            @endif
                                        </td>
                                        
                                        <td class="px-6 py-6 whitespace-nowrap text-sm font-medium">
                                            <div class="flex items-center gap-1">
                                                <flux:dropdown>
                                                    <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                                                    <flux:menu>
                                                        @if($monthData['status'] === 'pending')
                                                            <flux:menu.item icon="calculator" wire:click="openMonthEmployeesFlyout({{ $monthData['month'] }}, {{ $monthData['year'] }})">
                                                                {{ __('Process Payroll') }}
                                                            </flux:menu.item>
                                                        @else
                                                            <flux:menu.item icon="eye" wire:click="openMonthEmployeesFlyout({{ $monthData['month'] }}, {{ $monthData['year'] }})">
                                                                {{ __('View Payroll') }}
                                                        </flux:menu.item>
                                                        @endif
                                                        <flux:menu.item icon="arrow-down-tray">
                                                            {{ __('Export') }}
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
            @else
                <!-- Empty State -->
                <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <flux:heading size="lg" level="3" class="mt-4 text-zinc-600 dark:text-zinc-400">
                        {{ __('No payroll months found') }}
                    </flux:heading>
                    <flux:text class="mt-2 text-zinc-500 dark:text-zinc-500">
                        {{ __('No payroll months match your current search criteria.') }}
                    </flux:text>
                </div>
            @endif
        </div>

        <!-- Month Employees Flyout -->
        <flux:modal variant="flyout" wire:model="showMonthEmployeesFlyout" class="w-[60rem]">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">
                        {{ __('Payroll Employees') }} - {{ $selectedMonthForFlyout ? \Carbon\Carbon::create($selectedYearForFlyout, $selectedMonthForFlyout, 1)->format('F Y') : '' }}
                    </flux:heading>
                </div>

                @if(count($monthEmployees) > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-zinc-50 dark:bg-zinc-700">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                        {{ __('Employee') }}
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                        {{ __('Department') }}
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                        {{ __('Basic Salary') }}
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                        {{ __('Allowances') }}
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                        {{ __('Deductions') }}
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                        {{ __('Net Salary') }}
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                        {{ __('Status') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-zinc-800 divide-y divide-zinc-200 dark:divide-zinc-700">
                                @foreach ($monthEmployees as $employee)
                                    <tr class="hover:bg-zinc-100 dark:hover:bg-zinc-600 transition-colors duration-150">
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <div class="flex items-center gap-3">
                                                <flux:avatar size="sm" :initials="strtoupper(substr($employee['name'], 0, 1))" />
                                                <div>
                                                    <div class="font-medium text-zinc-900 dark:text-zinc-100">
                                                        {{ $employee['name'] }}
                                                    </div>
                                                    <div class="text-sm text-zinc-500 dark:text-zinc-400">
                                                        {{ $employee['employee_code'] }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <div class="text-sm text-zinc-900 dark:text-zinc-100">
                                                {{ $employee['department'] }}
                                            </div>
                                        </td>
                                        
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <div class="text-sm text-zinc-900 dark:text-zinc-100">
                                                ${{ number_format($employee['basic_salary'], 2) }}
                                            </div>
                                        </td>
                                        
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <div class="text-sm text-zinc-900 dark:text-zinc-100">
                                                ${{ number_format($employee['allowances'], 2) }}
                                            </div>
                                        </td>
                                        
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <div class="text-sm text-zinc-900 dark:text-zinc-100">
                                                ${{ number_format($employee['deductions'], 2) }}
                                            </div>
                                        </td>
                                        
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                                ${{ number_format($employee['net_salary'], 2) }}
                                            </div>
                                        </td>
                                        
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <flux:badge color="yellow" size="sm">
                                                {{ __('Pending') }}
                                            </flux:badge>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-8">
                        <flux:icon name="user-group" class="w-12 h-12 mx-auto mb-4 text-zinc-400" />
                        <p class="text-zinc-500 dark:text-zinc-400">{{ __('No employees found for this month.') }}</p>
                    </div>
                @endif

                <div class="flex justify-end gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                    <flux:button variant="ghost" wire:click="closeMonthEmployeesFlyout">
                        {{ __('Close') }}
                    </flux:button>
                </div>
            </div>
        </flux:modal>

        <!-- Process Payroll Modal -->
        <flux:modal wire:model="showProcessPayrollModal" class="max-w-4xl">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">{{ __('Process Payroll') }}</flux:heading>
                    <flux:text class="mt-2 text-zinc-500 dark:text-zinc-400">
                        {{ __('Select a processing method for the selected month') }}
                    </flux:text>
                </div>

                <!-- Month and Year Selection -->
                <div class="grid grid-cols-2 gap-4">
                    <flux:field>
                        <flux:label>{{ __('Month') }}</flux:label>
                        <flux:select wire:model="processMonth">
                            @for($month = 1; $month <= 12; $month++)
                                <option value="{{ $month }}">{{ date('F', mktime(0, 0, 0, $month, 1)) }}</option>
                            @endfor
                        </flux:select>
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('Year') }}</flux:label>
                        <flux:select wire:model="processYear">
                            @for($year = now()->year; $year >= now()->year - 5; $year--)
                                <option value="{{ $year }}">{{ $year }}</option>
                            @endfor
                        </flux:select>
                    </flux:field>
                </div>

                <!-- Processing Options Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">
                    <!-- Monthly Attendance Processing Card -->
                    <div 
                        wire:click="selectProcessingType('monthly_attendance')"
                        class="border-2 rounded-lg p-6 cursor-pointer transition-all duration-200 {{ $selectedProcessingType === 'monthly_attendance' ? 'border-blue-500 dark:border-blue-400 bg-blue-50 dark:bg-blue-900/20 ring-2 ring-blue-500/20' : 'border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 hover:border-blue-500 dark:hover:border-blue-400' }}"
                    >
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                                    <flux:icon name="calendar-days" class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                                </div>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-2">
                                    <flux:heading size="md" level="3">
                                        {{ __('Monthly Attendance Processing') }}
                                    </flux:heading>
                                    @if($selectedProcessingType === 'monthly_attendance')
                                        <flux:icon name="check-circle" class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                                    @endif
                                </div>
                                <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">
                                    {{ __('Process payroll based on monthly attendance records and calculate salary accordingly.') }}
                                </flux:text>
                            </div>
                        </div>
                    </div>

                    <!-- Custom Processing Card -->
                    <div 
                        wire:click="selectProcessingType('custom')"
                        class="border-2 rounded-lg p-6 cursor-pointer transition-all duration-200 {{ $selectedProcessingType === 'custom' ? 'border-purple-500 dark:border-purple-400 bg-purple-50 dark:bg-purple-900/20 ring-2 ring-purple-500/20' : 'border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 hover:border-purple-500 dark:hover:border-purple-400' }}"
                    >
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center">
                                    <flux:icon name="cog-6-tooth" class="w-6 h-6 text-purple-600 dark:text-purple-400" />
                                </div>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-2">
                                    <flux:heading size="md" level="3">
                                        {{ __('Custom Processing') }}
                                    </flux:heading>
                                    @if($selectedProcessingType === 'custom')
                                        <flux:icon name="check-circle" class="w-5 h-5 text-purple-600 dark:text-purple-400" />
                                    @endif
                                </div>
                                <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">
                                    {{ __('Process payroll with custom settings, adjustments, and manual overrides.') }}
                                </flux:text>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                    <flux:button variant="ghost" wire:click="closeProcessPayrollModal">
                        {{ __('Cancel') }}
                    </flux:button>
                    @if($selectedProcessingType)
                        <flux:button variant="primary" wire:click="createPayroll">
                            {{ __('Create') }}
                        </flux:button>
                    @endif
                </div>
            </div>
        </flux:modal>
    </x-payroll.layout>
</section>
