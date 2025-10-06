<section class="w-full">
    @include('partials.payroll-heading')
    
    <x-payroll.layout :heading="__('Salary Reports')" :subheading="__('Generate and view salary reports')">
        <!-- Search and Filter Controls -->
        <div class="my-6 w-full space-y-4">
            <div class="flex flex-col sm:flex-row gap-4">
                <!-- Search Input -->
                <div class="flex-1">
                    <flux:input 
                        wire:model.live.debounce.300ms="search" 
                        :label="__('Search')" 
                        type="text" 
                        placeholder="Search by name, employee ID..." 
                        icon="magnifying-glass"
                    />
                </div>
                
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

                <!-- Report Type Filter -->
                <div class="sm:w-48">
                    <flux:field>
                        <flux:label>{{ __('Report Type') }}</flux:label>
                        <flux:select wire:model.live="reportType">
                            <option value="monthly">{{ __('Monthly') }}</option>
                            <option value="quarterly">{{ __('Quarterly') }}</option>
                            <option value="yearly">{{ __('Yearly') }}</option>
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
                    <flux:button variant="primary" icon="chart-bar" wire:click="generateReport">
                        {{ __('Generate Report') }}
                    </flux:button>
                    
                    <flux:button variant="outline" icon="arrow-down-tray" wire:click="exportReport">
                        {{ __('Export Report') }}
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

        <!-- Salary Reports Table -->
        <div class="mt-8">
            @if($salaryData->count() > 0)
                <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 overflow-hidden shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-zinc-50 dark:bg-zinc-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                        <button wire:click="sort('employee_name')" class="flex items-center gap-1 hover:text-zinc-700 dark:hover:text-zinc-200">
                                            {{ __('Employee') }}
                                            @if($sortBy === 'employee_name')
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
                                        <button wire:click="sort('basic_salary')" class="flex items-center gap-1 hover:text-zinc-700 dark:hover:text-zinc-200">
                                            {{ __('Basic Salary') }}
                                            @if($sortBy === 'basic_salary')
                                                <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="w-4 h-4" />
                                            @endif
                                        </button>
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                        <button wire:click="sort('total_allowances')" class="flex items-center gap-1 hover:text-zinc-700 dark:hover:text-zinc-200">
                                            {{ __('Total Allowances') }}
                                            @if($sortBy === 'total_allowances')
                                                <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="w-4 h-4" />
                                            @endif
                                        </button>
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                        <button wire:click="sort('total_deductions')" class="flex items-center gap-1 hover:text-zinc-700 dark:hover:text-zinc-200">
                                            {{ __('Total Deductions') }}
                                            @if($sortBy === 'total_deductions')
                                                <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="w-4 h-4" />
                                            @endif
                                        </button>
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                        <button wire:click="sort('net_salary')" class="flex items-center gap-1 hover:text-zinc-700 dark:hover:text-zinc-200">
                                            {{ __('Net Salary') }}
                                            @if($sortBy === 'net_salary')
                                                <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="w-4 h-4" />
                                            @endif
                                        </button>
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                        <button wire:click="sort('month')" class="flex items-center gap-1 hover:text-zinc-700 dark:hover:text-zinc-200">
                                            {{ __('Month') }}
                                            @if($sortBy === 'month')
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
                                @foreach ($salaryData as $record)
                                    <tr class="hover:bg-zinc-100 dark:hover:bg-zinc-600 transition-colors duration-150">
                                        <td class="px-6 py-6 whitespace-nowrap">
                                            <div class="flex items-center gap-3">
                                                <flux:avatar size="sm" :initials="strtoupper(substr($record['employee_name'], 0, 1))" />
                                                <div>
                                                    <div class="font-medium text-zinc-900 dark:text-zinc-100">
                                                        {{ $record['employee_name'] }}
                                                    </div>
                                                    <div class="text-sm text-zinc-500 dark:text-zinc-400">
                                                        {{ $record['employee_code'] }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        
                                        <td class="px-6 py-6 whitespace-nowrap">
                                            <div class="text-sm text-zinc-900 dark:text-zinc-100">
                                                {{ $record['department'] }}
                                            </div>
                                        </td>
                                        
                                        <td class="px-6 py-6 whitespace-nowrap">
                                            <div class="text-sm text-zinc-900 dark:text-zinc-100">
                                                ${{ number_format($record['basic_salary'], 2) }}
                                            </div>
                                        </td>
                                        
                                        <td class="px-6 py-6 whitespace-nowrap">
                                            <div class="text-sm text-zinc-900 dark:text-zinc-100">
                                                ${{ number_format($record['total_allowances'], 2) }}
                                            </div>
                                        </td>
                                        
                                        <td class="px-6 py-6 whitespace-nowrap">
                                            <div class="text-sm text-zinc-900 dark:text-zinc-100">
                                                ${{ number_format($record['total_deductions'], 2) }}
                                            </div>
                                        </td>
                                        
                                        <td class="px-6 py-6 whitespace-nowrap">
                                            <div class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                                ${{ number_format($record['net_salary'], 2) }}
                                            </div>
                                        </td>
                                        
                                        <td class="px-6 py-6 whitespace-nowrap">
                                            <div class="text-sm text-zinc-900 dark:text-zinc-100">
                                                {{ date('F Y', strtotime($record['month'] . '-01')) }}
                                            </div>
                                        </td>
                                        
                                        <td class="px-6 py-6 whitespace-nowrap text-sm font-medium">
                                            <div class="flex items-center gap-1">
                                                <flux:dropdown>
                                                    <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                                                    <flux:menu>
                                                        <flux:menu.item icon="eye" wire:click="viewReport({{ $record['id'] }})">
                                                            {{ __('View Report') }}
                                                        </flux:menu.item>
                                                        <flux:menu.item icon="arrow-down-tray" wire:click="downloadReport({{ $record['id'] }})">
                                                            {{ __('Download PDF') }}
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
                @if(method_exists($salaryData, 'hasPages') && $salaryData->hasPages())
                    <div class="mt-6">
                        {{ $salaryData->links() }}
                    </div>
                @endif
            @else
                <!-- Empty State -->
                <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    <flux:heading size="lg" level="3" class="mt-4 text-zinc-600 dark:text-zinc-400">
                        {{ __('No salary data found') }}
                    </flux:heading>
                    <flux:text class="mt-2 text-zinc-500 dark:text-zinc-500">
                        {{ __('No salary records match your current search criteria.') }}
                    </flux:text>
                </div>
            @endif
        </div>
    </x-payroll.layout>
</section>
