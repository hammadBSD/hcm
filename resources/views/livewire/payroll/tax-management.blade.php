<section class="w-full">
    @include('partials.payroll-heading')
    
    <x-payroll.layout :heading="__('Tax Management')" :subheading="__('Manage employee tax records and calculations')">
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

                <!-- Tax Year Filter -->
                <div class="sm:w-32">
                    <flux:field>
                        <flux:label>{{ __('Tax Year') }}</flux:label>
                        <flux:select wire:model.live="taxYear">
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
                    <flux:button variant="primary" icon="plus" wire:click="openAddTaxModal">
                        {{ __('Add Tax Record') }}
                    </flux:button>
                    
                    <flux:button variant="outline" icon="document-text" wire:click="generateTaxReport">
                        {{ __('Generate Tax Report') }}
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

        <!-- Tax Records Table -->
        <div class="mt-8">
            @if($taxRecords->count() > 0)
                <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 overflow-hidden shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-zinc-50 dark:bg-zinc-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                        <button wire:click="sort('salary_range')" class="flex items-center gap-1 hover:text-zinc-700 dark:hover:text-zinc-200">
                                            {{ __('Salary From - To') }}
                                            @if($sortBy === 'salary_range')
                                                <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="w-4 h-4" />
                                            @endif
                                        </button>
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                        <button wire:click="sort('taxable_income')" class="flex items-center gap-1 hover:text-zinc-700 dark:hover:text-zinc-200">
                                            {{ __('Taxable Income') }}
                                            @if($sortBy === 'taxable_income')
                                                <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="w-4 h-4" />
                                            @endif
                                        </button>
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                        <button wire:click="sort('income_tax')" class="flex items-center gap-1 hover:text-zinc-700 dark:hover:text-zinc-200">
                                            {{ __('Income Tax') }}
                                            @if($sortBy === 'income_tax')
                                                <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="w-4 h-4" />
                                            @endif
                                        </button>
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                        <button wire:click="sort('year')" class="flex items-center gap-1 hover:text-zinc-700 dark:hover:text-zinc-200">
                                            {{ __('Tax Year') }}
                                            @if($sortBy === 'year')
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
                                @foreach ($taxRecords as $record)
                                    <tr class="hover:bg-zinc-100 dark:hover:bg-zinc-600 transition-colors duration-150">
                                        <td class="px-6 py-6 whitespace-nowrap">
                                            <div class="text-sm text-zinc-900 dark:text-zinc-100">
                                                @if(isset($record['salary_from']) && isset($record['salary_to']))
                                                    {{ number_format($record['salary_from'], 0) }} - {{ number_format($record['salary_to'], 0) }}
                                                @else
                                                    {{ number_format($record['annual_salary'] ?? 0, 0) }}
                                                @endif
                                            </div>
                                        </td>
                                        
                                        <td class="px-6 py-6 whitespace-nowrap">
                                            <div class="text-sm text-zinc-900 dark:text-zinc-100">
                                                {{ number_format($record['taxable_income'], 2) }}
                                            </div>
                                        </td>
                                        
                                        <td class="px-6 py-6 whitespace-nowrap">
                                            <div class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                                {{ number_format($record['income_tax'], 2) }}
                                            </div>
                                        </td>
                                        
                                        <td class="px-6 py-6 whitespace-nowrap">
                                            <div class="text-sm text-zinc-900 dark:text-zinc-100">
                                                {{ $record['year'] }}
                                            </div>
                                        </td>
                                        
                                        <td class="px-6 py-6 whitespace-nowrap">
                                            <flux:badge color="green" size="sm">
                                                {{ ucfirst($record['status']) }}
                                            </flux:badge>
                                        </td>
                                        
                                        <td class="px-6 py-6 whitespace-nowrap text-sm font-medium">
                                            <div class="flex items-center gap-1">
                                                <flux:dropdown>
                                                    <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                                                    <flux:menu>
                                                        <flux:menu.item icon="eye" wire:click="viewTaxRecord({{ $record['id'] }})">
                                                            {{ __('View Details') }}
                                                        </flux:menu.item>
                                                        <flux:menu.item icon="arrow-down-tray" wire:click="downloadTaxRecord({{ $record['id'] }})">
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
                @if(method_exists($taxRecords, 'hasPages') && $taxRecords->hasPages())
                    <div class="mt-6">
                        {{ $taxRecords->links() }}
                    </div>
                @endif
            @else
                <!-- Empty State -->
                <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <flux:heading size="lg" level="3" class="mt-4 text-zinc-600 dark:text-zinc-400">
                        {{ __('No tax records found') }}
                    </flux:heading>
                    <flux:text class="mt-2 text-zinc-500 dark:text-zinc-500">
                        {{ __('No tax records match your current search criteria.') }}
                    </flux:text>
                </div>
            @endif
        </div>

        <!-- Add Tax Record Flyout -->
        @if($showAddTaxModal)
            <flux:modal variant="flyout" :open="$showAddTaxModal" wire:model="showAddTaxModal">
                <div class="space-y-6">
                    <div>
                        <flux:heading size="lg">{{ __('Add Tax Record') }}</flux:heading>
                    </div>
                    
                    <div class="space-y-4">
                        <div class="grid grid-cols-5 gap-4">
                            <flux:field>
                                <flux:label>{{ __('Salary From') }}</flux:label>
                                <flux:input type="number" placeholder="1" />
                            </flux:field>

                            <flux:field>
                                <flux:label>{{ __('Salary To') }}</flux:label>
                                <flux:input type="number" placeholder="600000" />
                            </flux:field>

                            <flux:field>
                                <flux:label>{{ __('Tax') }}</flux:label>
                                <flux:input type="number" placeholder="0" suffix="%" />
                            </flux:field>

                            <flux:field>
                                <flux:label>{{ __('Exempted Tax Amount') }}</flux:label>
                                <flux:input type="number" placeholder="0" />
                            </flux:field>

                            <flux:field>
                                <flux:label>{{ __('Additional Tax Amount') }}</flux:label>
                                <flux:input type="number" placeholder="0" />
                            </flux:field>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3">
                        <flux:button variant="ghost" wire:click="closeAddTaxModal">{{ __('Cancel') }}</flux:button>
                        <flux:button variant="primary" wire:click="addTaxRecord">{{ __('Add Record') }}</flux:button>
                    </div>
                </div>
            </flux:modal>
        @endif
    </x-payroll.layout>
</section>