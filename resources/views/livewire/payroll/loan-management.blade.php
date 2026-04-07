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
                                        <button wire:click="sort('loan_date')" class="flex items-center gap-1 hover:text-zinc-700 dark:hover:text-zinc-200">
                                            {{ __('Loan Issue Date') }}
                                            @if($sortBy === 'loan_date')
                                                <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="w-4 h-4" />
                                            @endif
                                        </button>
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                        <button wire:click="sort('created_at')" class="flex items-center gap-1 hover:text-zinc-700 dark:hover:text-zinc-200">
                                            {{ __('Created Date') }}
                                            @if($sortBy === 'created_at')
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
                                @foreach ($loans as $loan)
                                    @php
                                        $emp = $loan->employee;
                                        $employeeName = $emp ? trim($emp->first_name . ' ' . $emp->last_name) : '—';
                                        $employeeCode = $emp->employee_code ?? '—';
                                        $departmentName = 'N/A';
                                        if ($emp) {
                                            $deptValue = $emp->department ?? null;
                                            if (is_object($deptValue)) {
                                                $departmentName = $deptValue->title ?? 'N/A';
                                            } elseif (is_string($deptValue) && trim($deptValue) !== '') {
                                                // Fallback for legacy/string department values.
                                                $departmentName = $deptValue;
                                            }
                                        }
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
                                                {{ number_format((float) $loan->loan_amount, 2) }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-zinc-900 dark:text-zinc-100">
                                                {{ number_format((float) $loan->installment_amount, 2) }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-zinc-900 dark:text-zinc-100">
                                                {{ $loan->remaining_installments }}/{{ $loan->total_installments }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-zinc-900 dark:text-zinc-100">
                                                {{ $loan->loan_date ? $loan->loan_date->format('M d, Y') : '—' }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-zinc-900 dark:text-zinc-100">
                                                {{ $loan->created_at ? $loan->created_at->format('M d, Y') : '—' }}
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
                            <flux:label>{{ __('Loan Issue Date') }}</flux:label>
                            <flux:input type="date" wire:model="loanDate" />
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

        @if($showApproveLoanModal)
            <flux:modal variant="flyout" :open="$showApproveLoanModal" wire:model="showApproveLoanModal">
                <div class="space-y-6">
                    <div>
                        <flux:heading size="lg">{{ __('Approve Loan') }}</flux:heading>
                        <flux:text class="mt-1 text-zinc-500 dark:text-zinc-400">{{ __('Set approval date and comments.') }}</flux:text>
                    </div>
                    <div class="space-y-4">
                        <flux:field>
                            <flux:label>{{ __('Approval Date') }}</flux:label>
                            <flux:input type="date" wire:model="approvalDate" />
                        </flux:field>
                        <flux:field>
                            <flux:label>{{ __('Comments') }}</flux:label>
                            <flux:textarea wire:model="approvalComments" placeholder="{{ __('Optional approval comments...') }}"></flux:textarea>
                        </flux:field>
                    </div>
                    <div class="flex justify-end gap-3">
                        <flux:button variant="ghost" wire:click="closeApproveLoanModal">{{ __('Cancel') }}</flux:button>
                        <flux:button variant="primary" wire:click="confirmApproveLoan">{{ __('Approve Loan') }}</flux:button>
                    </div>
                </div>
            </flux:modal>
        @endif

        @if($showRejectLoanModal)
            <flux:modal variant="flyout" :open="$showRejectLoanModal" wire:model="showRejectLoanModal">
                <div class="space-y-6">
                    <div>
                        <flux:heading size="lg">{{ __('Reject Loan') }}</flux:heading>
                        <flux:text class="mt-1 text-zinc-500 dark:text-zinc-400">{{ __('Set rejection date and reason/comments.') }}</flux:text>
                    </div>
                    <div class="space-y-4">
                        <flux:field>
                            <flux:label>{{ __('Rejection Date') }}</flux:label>
                            <flux:input type="date" wire:model="rejectDate" />
                        </flux:field>
                        <flux:field>
                            <flux:label>{{ __('Comments') }}</flux:label>
                            <flux:textarea wire:model="rejectComments" placeholder="{{ __('Reason for rejection...') }}"></flux:textarea>
                        </flux:field>
                    </div>
                    <div class="flex justify-end gap-3">
                        <flux:button variant="ghost" wire:click="closeRejectLoanModal">{{ __('Cancel') }}</flux:button>
                        <flux:button variant="danger" wire:click="confirmRejectLoan">{{ __('Reject Loan') }}</flux:button>
                    </div>
                </div>
            </flux:modal>
        @endif

        <!-- View Loan Flyout -->
        @if($showViewLoanModal && $this->selectedLoan)
            @php
                $loan = $this->selectedLoan;
                $emp = $loan->employee;
                $employeeName = $emp ? trim(($emp->first_name ?? '') . ' ' . ($emp->last_name ?? '')) : '—';
                $employeeCode = $emp->employee_code ?? '—';
                $departmentName = 'N/A';
                if ($emp) {
                    $deptValue = $emp->department ?? null;
                    if (is_object($deptValue)) {
                        $departmentName = $deptValue->title ?? 'N/A';
                    } elseif (is_string($deptValue) && trim($deptValue) !== '') {
                        $departmentName = $deptValue;
                    }
                }
                $statusColor = match($loan->status) {
                    'pending' => 'yellow',
                    'approved' => 'green',
                    'completed' => 'blue',
                    'rejected' => 'red',
                    default => 'yellow'
                };
            @endphp
            <flux:modal variant="flyout" :open="$showViewLoanModal" wire:model="showViewLoanModal">
                <div class="space-y-6">
                    <div>
                        <flux:heading size="lg">{{ __('Loan Details') }}</flux:heading>
                        <flux:text class="mt-1 text-zinc-500 dark:text-zinc-400">
                            {{ $employeeName }} ({{ $employeeCode }})
                        </flux:text>
                    </div>

                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between"><span class="text-zinc-600 dark:text-zinc-400">{{ __('Department') }}</span><span class="font-medium">{{ $departmentName }}</span></div>
                        <div class="flex justify-between"><span class="text-zinc-600 dark:text-zinc-400">{{ __('Loan Type') }}</span><span class="font-medium">{{ $loan->loan_type }}</span></div>
                        <div class="flex justify-between"><span class="text-zinc-600 dark:text-zinc-400">{{ __('Loan Amount') }}</span><span class="font-medium">{{ number_format((float) $loan->loan_amount, 2) }}</span></div>
                        <div class="flex justify-between"><span class="text-zinc-600 dark:text-zinc-400">{{ __('Installment Amount') }}</span><span class="font-medium">{{ number_format((float) $loan->installment_amount, 2) }}</span></div>
                        <div class="flex justify-between"><span class="text-zinc-600 dark:text-zinc-400">{{ __('Total Installments') }}</span><span class="font-medium">{{ $loan->total_installments }}</span></div>
                        <div class="flex justify-between"><span class="text-zinc-600 dark:text-zinc-400">{{ __('Remaining Installments') }}</span><span class="font-medium">{{ $loan->remaining_installments }}</span></div>
                        <div class="flex justify-between"><span class="text-zinc-600 dark:text-zinc-400">{{ __('Loan Issue Date') }}</span><span class="font-medium">{{ $loan->loan_date ? $loan->loan_date->format('M d, Y') : '—' }}</span></div>
                        <div class="flex justify-between"><span class="text-zinc-600 dark:text-zinc-400">{{ __('Created Date') }}</span><span class="font-medium">{{ $loan->created_at ? $loan->created_at->format('M d, Y H:i') : '—' }}</span></div>
                        <div class="flex justify-between items-center">
                            <span class="text-zinc-600 dark:text-zinc-400">{{ __('Status') }}</span>
                            <flux:badge color="{{ $statusColor }}" size="sm">{{ ucfirst($loan->status) }}</flux:badge>
                        </div>
                        <div class="flex justify-between"><span class="text-zinc-600 dark:text-zinc-400">{{ __('Requested By') }}</span><span class="font-medium">{{ $loan->requestedByUser?->name ?? '—' }}</span></div>
                        <div class="flex justify-between"><span class="text-zinc-600 dark:text-zinc-400">{{ __('Approved By') }}</span><span class="font-medium">{{ $loan->approvedByUser?->name ?? '—' }}</span></div>
                        <div class="flex justify-between"><span class="text-zinc-600 dark:text-zinc-400">{{ __('Approved At') }}</span><span class="font-medium">{{ $loan->approved_at ? $loan->approved_at->format('M d, Y H:i') : '—' }}</span></div>
                        <div class="pt-2">
                            <div class="text-zinc-600 dark:text-zinc-400 mb-1">{{ __('Decision Comments') }}</div>
                            <div class="rounded-md border border-zinc-200 dark:border-zinc-700 p-3 text-zinc-800 dark:text-zinc-200">
                                {{ trim((string) ($loan->decision_comments ?? '')) !== '' ? $loan->decision_comments : '—' }}
                            </div>
                        </div>
                        <div class="pt-2">
                            <div class="text-zinc-600 dark:text-zinc-400 mb-1">{{ __('Description') }}</div>
                            <div class="rounded-md border border-zinc-200 dark:border-zinc-700 p-3 text-zinc-800 dark:text-zinc-200">
                                {{ trim((string) $loan->description) !== '' ? $loan->description : '—' }}
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end pt-4">
                        <flux:button variant="ghost" wire:click="closeViewLoanModal">{{ __('Close') }}</flux:button>
                    </div>
                </div>
            </flux:modal>
        @endif
    </x-payroll.layout>
</section>