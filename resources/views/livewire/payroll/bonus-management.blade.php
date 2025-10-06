<section class="w-full">
    @include('partials.payroll-heading')
    
    <x-payroll.layout :heading="__('Bonus Management')" :subheading="__('Manage employee bonuses and incentives')">
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

                <!-- Bonus Type Filter -->
                <div class="sm:w-48">
                    <flux:field>
                        <flux:label>{{ __('Bonus Type') }}</flux:label>
                        <flux:select wire:model.live="bonusType">
                            <option value="">{{ __('All Types') }}</option>
                            @foreach($bonusTypes as $type)
                                <option value="{{ $type }}">{{ $type }}</option>
                            @endforeach
                        </flux:select>
                    </flux:field>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <flux:button variant="primary" icon="plus" wire:click="openAddBonusModal">
                        {{ __('Add Bonus') }}
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

        <!-- Employees Table -->
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
                                        <button wire:click="sort('current_bonus')" class="flex items-center gap-1 hover:text-zinc-700 dark:hover:text-zinc-200">
                                            {{ __('Current Bonus') }}
                                            @if($sortBy === 'current_bonus')
                                                <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="w-4 h-4" />
                                            @endif
                                        </button>
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                        <button wire:click="sort('total_earnings')" class="flex items-center gap-1 hover:text-zinc-700 dark:hover:text-zinc-200">
                                            {{ __('Total Earnings') }}
                                            @if($sortBy === 'total_earnings')
                                                <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="w-4 h-4" />
                                            @endif
                                        </button>
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                        <button wire:click="sort('last_bonus_date')" class="flex items-center gap-1 hover:text-zinc-700 dark:hover:text-zinc-200">
                                            {{ __('Last Bonus Date') }}
                                            @if($sortBy === 'last_bonus_date')
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
                                                <flux:avatar size="sm" :initials="strtoupper(substr($employee->first_name, 0, 1) . substr($employee->last_name, 0, 1))" />
                                                <div>
                                                    <div class="font-medium text-zinc-900 dark:text-zinc-100">
                                                        {{ $employee->first_name }} {{ $employee->last_name }}
                                                    </div>
                                                    <div class="text-sm text-zinc-500 dark:text-zinc-400">
                                                        {{ $employee->employee_code ?? 'N/A' }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        
                                        <td class="px-6 py-6 whitespace-nowrap">
                                            <div class="text-sm text-zinc-900 dark:text-zinc-100">
                                                {{ $employee->department ?? 'Not assigned' }}
                                            </div>
                                        </td>
                                        
                                        <td class="px-6 py-6 whitespace-nowrap">
                                            <div class="text-sm text-zinc-900 dark:text-zinc-100">$5,000.00</div>
                                        </td>
                                        
                                        <td class="px-6 py-6 whitespace-nowrap">
                                            <div class="text-sm text-zinc-900 dark:text-zinc-100">$500.00</div>
                                        </td>
                                        
                                        <td class="px-6 py-6 whitespace-nowrap">
                                            <div class="text-sm font-medium text-zinc-900 dark:text-zinc-100">$5,500.00</div>
                                        </td>
                                        
                                        <td class="px-6 py-6 whitespace-nowrap">
                                            <div class="text-sm text-zinc-900 dark:text-zinc-100">2024-01-15</div>
                                        </td>
                                        
                                        <td class="px-6 py-6 whitespace-nowrap text-sm font-medium">
                                            <div class="flex items-center gap-1">
                                                <flux:dropdown>
                                                    <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                                                    <flux:menu>
                                                        <flux:menu.item icon="gift" wire:click="addBonus({{ $employee->id }})">
                                                            {{ __('Add Bonus') }}
                                                        </flux:menu.item>
                                                        <flux:menu.item icon="eye" wire:click="viewBonus({{ $employee->id }})">
                                                            {{ __('View Bonuses') }}
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
                @if(method_exists($employees, 'hasPages') && $employees->hasPages())
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
                        {{ __('No employees match your current search criteria.') }}
                    </flux:text>
                </div>
            @endif
        </div>

        <!-- Add Bonus Modal -->
        @if($showAddBonusModal)
            <flux:modal wire:model="showAddBonusModal" name="add-bonus-modal">
                <div class="space-y-6">
                    <div>
                        <flux:heading size="lg">{{ __('Add Bonus') }}</flux:heading>
                    </div>
                    
                    <div class="space-y-4">
                        <flux:field>
                            <flux:label>{{ __('Employee') }}</flux:label>
                            <flux:select>
                                <option value="">{{ __('Select Employee') }}</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->first_name }} {{ $employee->last_name }}</option>
                                @endforeach
                            </flux:select>
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('Bonus Type') }}</flux:label>
                            <flux:select>
                                @foreach($bonusTypes as $type)
                                    <option value="{{ $type }}">{{ $type }}</option>
                                @endforeach
                            </flux:select>
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('Amount') }}</flux:label>
                            <flux:input type="number" step="0.01" placeholder="0.00" />
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('Description') }}</flux:label>
                            <flux:textarea placeholder="Enter bonus description..."></flux:textarea>
                        </flux:field>
                    </div>

                    <div class="flex justify-end gap-3">
                        <flux:button variant="ghost" wire:click="closeAddBonusModal">{{ __('Cancel') }}</flux:button>
                        <flux:button variant="primary" wire:click="addBonus">{{ __('Add Bonus') }}</flux:button>
                    </div>
                </div>
            </flux:modal>
        @endif
    </x-payroll.layout>
</section>
