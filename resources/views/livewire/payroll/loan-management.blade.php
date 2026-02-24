<section class="w-full">
    @include('partials.payroll-heading')
    
    <x-payroll.layout :heading="__('Loan Management')" :subheading="__('Manage employee loans and installments')">
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

                <!-- Loan Status Filter -->
                <div class="sm:w-48">
                    <flux:field>
                        <flux:label>{{ __('Loan Status') }}</flux:label>
                        <flux:select wire:model.live="loanStatus">
                            <option value="">{{ __('All Status') }}</option>
                            @foreach($statuses as $status)
                                <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                            @endforeach
                        </flux:select>
                    </flux:field>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <flux:button variant="primary" icon="plus" wire:click="openAddLoanModal">
                        {{ __('Add Loan') }}
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
        @if (session()->has('error'))
            <flux:callout variant="danger" icon="x-circle" dismissible>
                {{ session('error') }}
            </flux:callout>
        @endif

        <!-- Loans Table -->
        <div class="mt-8">
            @if($loans->count() > 0)
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
                                        <button wire:click="sort('loan_type')" class="flex items-center gap-1 hover:text-zinc-700 dark:hover:text-zinc-200">
                                            {{ __('Loan Type') }}
                                            @if($sortBy === 'loan_type')
                                                <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="w-4 h-4" />
                                            @endif
                                        </button>
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                        <button wire:click="sort('loan_amount')" class="flex items-center gap-1 hover:text-zinc-700 dark:hover:text-zinc-200">
                                            {{ __('Loan Amount') }}
                                            @if($sortBy === 'loan_amount')
                                                <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="w-4 h-4" />
                                            @endif
                                        </button>
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                        <button wire:click="sort('installment_amount')" class="flex items-center gap-1 hover:text-zinc-700 dark:hover:text-zinc-200">
                                            {{ __('Installment') }}
                                            @if($sortBy === 'installment_amount')
                                                <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="w-4 h-4" />
                                            @endif
                                        </button>
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                        {{ __('Remaining') }}
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
                                @foreach ($loans as $loan)
                                    @php
                                        $emp = $loan->employee;
                                        $employeeName = $emp ? trim($emp->first_name . ' ' . $emp->last_name) : '—';
                                        $employeeCode = $emp->employee_code ?? '—';
                                        $departmentName = $emp && $emp->department ? $emp->department->title : 'N/A';
                                    @endphp
                                    <tr class="hover:bg-zinc-100 dark:hover:bg-zinc-600 transition-colors duration-150">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center gap-3">
                                                <flux:avatar size="sm" :initials="strtoupper(substr($employeeName, 0, 1))" />
                                                <div>
                                                    <div class="font-medium text-zinc-900 dark:text-zinc-100">
                                                        {{ $employeeName }}
                                                    </div>
                                                    <div class="text-sm text-zinc-500 dark:text-zinc-400">
                                                        {{ $employeeCode }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-zinc-900 dark:text-zinc-100">
                                                {{ $departmentName }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-zinc-900 dark:text-zinc-100">
                                                {{ $loan->loan_type }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                                ${{ number_format((float) $loan->loan_amount, 2) }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-zinc-900 dark:text-zinc-100">
                                                ${{ number_format((float) $loan->installment_amount, 2) }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-zinc-900 dark:text-zinc-100">
                                                {{ $loan->remaining_installments }}/{{ $loan->total_installments }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @php
                                                $statusColor = match($loan->status) {
                                                    'pending' => 'yellow',
                                                    'approved' => 'green',
                                                    'completed' => 'blue',
                                                    'rejected' => 'red',
                                                    default => 'yellow'
                                                };
                                            @endphp
                                            <flux:badge color="{{ $statusColor }}" size="sm">
                                                {{ $loan->status === 'approved' ? __('Active') : ucfirst($loan->status) }}
                                            </flux:badge>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex items-center gap-1">
                                                <flux:dropdown>
                                                    <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                                                    <flux:menu>
                                                        @if($loan->status === 'pending')
                                                            <flux:menu.item icon="check" wire:click="approveLoan({{ $loan->id }})">
                                                                {{ __('Approve') }}
                                                            </flux:menu.item>
                                                            <flux:menu.item icon="x-mark" wire:click="rejectLoan({{ $loan->id }})">
                                                                {{ __('Reject') }}
                                                            </flux:menu.item>
                                                        @endif
                                                        <flux:menu.item icon="eye" wire:click="viewLoan({{ $loan->id }})">
                                                            {{ __('View Details') }}
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
                @if(method_exists($loans, 'hasPages') && $loans->hasPages())
                    <div class="mt-6">
                        {{ $loans->links() }}
                    </div>
                @endif
            @else
                <!-- Empty State -->
                <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                    </svg>
                    <flux:heading size="lg" level="3" class="mt-4 text-zinc-600 dark:text-zinc-400">
                        {{ __('No loans found') }}
                    </flux:heading>
                    <flux:text class="mt-2 text-zinc-500 dark:text-zinc-500">
                        {{ __('No loans match your current search criteria.') }}
                    </flux:text>
                </div>
            @endif
        </div>

        <!-- Add Loan Flyout -->
        @if($showAddLoanModal)
            <flux:modal variant="flyout" :open="$showAddLoanModal" wire:model="showAddLoanModal">
                <div class="space-y-6">
                    <div>
                        <flux:heading size="lg">{{ __('Add Loan') }}</flux:heading>
                    </div>

                    <div class="space-y-4">
                        <flux:field>
                            <flux:label>{{ __('Employee') }}</flux:label>
                            <flux:select wire:model="selectedEmployeeId" placeholder="{{ __('Select Employee') }}">
                                <option value="">{{ __('Select Employee') }}</option>
                                @foreach($activeEmployees as $emp)
                                    <option value="{{ $emp['id'] }}">{{ $emp['label'] }}</option>
                                @endforeach
                            </flux:select>
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('Loan Type') }}</flux:label>
                            <flux:select wire:model="loanType">
                                @foreach($loanTypes as $type)
                                    <option value="{{ $type }}">{{ $type }}</option>
                                @endforeach
                            </flux:select>
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('Loan Amount') }}</flux:label>
                            <flux:input type="number" step="0.01" min="0" placeholder="0.00" wire:model="loanAmount" />
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('Number of Installments') }}</flux:label>
                            <flux:input type="number" min="1" placeholder="12" wire:model="totalInstallments" />
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('Description') }}</flux:label>
                            <flux:textarea placeholder="{{ __('Enter loan description...') }}" wire:model="loanDescription"></flux:textarea>
                        </flux:field>
                    </div>

                    <div class="flex justify-end gap-3">
                        <flux:button variant="ghost" wire:click="closeAddLoanModal">{{ __('Cancel') }}</flux:button>
                        <flux:button variant="primary" wire:click="addLoan">{{ __('Add Loan') }}</flux:button>
                    </div>
                </div>
            </flux:modal>
        @endif
    </x-payroll.layout>
</section>