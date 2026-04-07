<section class="w-full">
    @include('partials.payroll-heading')
    
    <x-payroll.layout :heading="__('Advance Salary')" :subheading="__('Manage advance salary requests')">
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

                <!-- Status Filter -->
                <div class="sm:w-48">
                    <flux:field>
                        <flux:label>{{ __('Status') }}</flux:label>
                        <flux:select wire:model.live="status">
                            <option value="">{{ __('All Status') }}</option>
                            @foreach($statuses as $status)
                                <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                            @endforeach
                        </flux:select>
                    </flux:field>
                </div>

                <!-- Request Period Filter -->
                <div class="sm:w-52">
                    <flux:field>
                        <flux:label>{{ __('Request Period') }}</flux:label>
                        <flux:select wire:model.live="requestPeriod">
                            <option value="current_month">{{ __('Current Month') }}</option>
                            <option value="last_month">{{ __('Last Month') }}</option>
                            <option value="custom">{{ __('Custom') }}</option>
                        </flux:select>
                    </flux:field>
                </div>

                <!-- Confirmed/Unconfirmed -->
                <div class="sm:w-52">
                    <flux:field>
                        <flux:label>{{ __('Confirmation') }}</flux:label>
                        <flux:select wire:model.live="confirmationStatus">
                            <option value="">{{ __('All') }}</option>
                            <option value="confirmed">{{ __('Confirmed') }}</option>
                            <option value="unconfirmed">{{ __('Unconfirmed') }}</option>
                        </flux:select>
                    </flux:field>
                </div>
            </div>

            @if($requestPeriod === 'custom')
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <flux:field>
                        <flux:label>{{ __('From Date') }}</flux:label>
                        <flux:input type="date" wire:model.live="customDateFrom" />
                    </flux:field>
                    <flux:field>
                        <flux:label>{{ __('To Date') }}</flux:label>
                        <flux:input type="date" wire:model.live="customDateTo" />
                    </flux:field>
                </div>
            @endif

            <!-- Action Buttons -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <flux:button variant="primary" icon="plus" wire:click="openAddAdvanceModal">
                        {{ __('Request Advance') }}
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

        <!-- Advance Salary Requests Table -->
        <div class="mt-8">
            @if($advanceRequests->count() > 0)
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
                                        <button wire:click="sort('amount')" class="flex items-center gap-1 hover:text-zinc-700 dark:hover:text-zinc-200">
                                            {{ __('Amount') }}
                                            @if($sortBy === 'amount')
                                                <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="w-4 h-4" />
                                            @endif
                                        </button>
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                        {{ __('Reason') }}
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                        <button wire:click="sort('request_date')" class="flex items-center gap-1 hover:text-zinc-700 dark:hover:text-zinc-200">
                                            {{ __('Request Date') }}
                                            @if($sortBy === 'request_date')
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
                                        {{ __('Approval date') }}
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                        {{ __('Confirmed') }}
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                        {{ __('Payback Received Amount') }}
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                        {{ __('Actions') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-zinc-800 divide-y divide-zinc-200 dark:divide-zinc-700">
                                @foreach ($advanceRequests as $request)
                                    @php
                                        $emp = $request->employee;
                                        $employeeName = $emp ? trim($emp->first_name . ' ' . $emp->last_name) : '—';
                                        $employeeCode = $emp->employee_code ?? '—';
                                        $dept = $emp->department ?? null;
                                        $departmentName = is_object($dept) && isset($dept->title) ? $dept->title : (is_string($dept) && $dept !== '' ? $dept : 'N/A');
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
                                            <div class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                                {{ preg_replace('/\.00$/', '', number_format((float) $request->amount, 2, '.', ',')) }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-zinc-900 dark:text-zinc-100 max-w-xs truncate" title="{{ $request->reason }}">
                                                {{ $request->reason ?: '—' }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-zinc-900 dark:text-zinc-100">
                                                {{ $request->created_at ? $request->created_at->format('M d, Y') : '—' }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @php
                                                $statusColor = match($request->status) {
                    'pending' => 'yellow',
                    'approved' => 'green',
                    'rejected' => 'red',
                    default => 'yellow'
                };
                                            @endphp
                                            <flux:badge color="{{ $statusColor }}" size="sm">
                                                {{ ucfirst($request->status) }}
                                            </flux:badge>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-zinc-900 dark:text-zinc-100">
                                                {{ $request->approved_at ? $request->approved_at->format('M d, Y') : '—' }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-zinc-900 dark:text-zinc-100">
                                                @php
                                                    $isSalaryDeduction = ($request->payback_transaction_type ?? 'deduct_from_salary') === 'deduct_from_salary';
                                                @endphp
                                                {{ $isSalaryDeduction
                                                    ? ($request->confirmed_at ? $request->confirmed_at->format('M d, Y') : '—')
                                                    : ($request->received_at ? $request->received_at->format('M d, Y') : '—') }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-zinc-900 dark:text-zinc-100">
                                                {{ $request->received_amount !== null
                                                    ? preg_replace('/\.00$/', '', number_format((float) $request->received_amount, 2, '.', ','))
                                                    : '—' }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex items-center gap-1">
                                                <flux:dropdown>
                                                    <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                                                    <flux:menu>
                                                        @if($request->status === 'pending')
                                                            <flux:menu.item icon="check" wire:click="approveAdvance({{ $request->id }})">
                                                                {{ __('Approve') }}
                                                            </flux:menu.item>
                                                            <flux:menu.item icon="x-mark" wire:click="rejectAdvance({{ $request->id }})">
                                                                {{ __('Reject') }}
                                                            </flux:menu.item>
                                                        @endif
                                                        @if($request->status === 'approved' && (($request->payback_transaction_type ?? 'deduct_from_salary') === 'deduct_from_salary') && !$request->confirmed_at)
                                                            <flux:menu.item icon="check-badge" wire:click="confirmTransaction({{ $request->id }})">
                                                                {{ __('Confirm Transaction') }}
                                                            </flux:menu.item>
                                                        @endif
                                                        @if($request->status === 'approved' && in_array(($request->payback_transaction_type ?? ''), ['cash', 'account_transfer']) && !$request->received_at)
                                                            <flux:menu.item icon="check-circle" wire:click="confirmReceived({{ $request->id }})" wire:confirm="{{ __('Mark this payback as received?') }}">
                                                                {{ __('Confirm Received') }}
                                                            </flux:menu.item>
                                                        @endif
                                                        @if(!$request->confirmed_at && !$request->received_at)
                                                            <flux:menu.item icon="pencil" wire:click="openEditAdvanceModal({{ $request->id }})">
                                                                {{ __('Edit') }}
                                                            </flux:menu.item>
                                                            @if($request->status !== 'approved')
                                                                <flux:menu.item
                                                                    icon="trash"
                                                                    wire:click="deleteRequest({{ $request->id }})"
                                                                    wire:confirm="{{ __('Are you sure you want to delete this advance request? This action cannot be undone.') }}"
                                                                    class="text-red-600 dark:text-red-400"
                                                                >
                                                                    {{ __('Delete') }}
                                                                </flux:menu.item>
                                                            @endif
                                                        @endif
                                                        <flux:menu.item icon="eye" wire:click="viewRequest({{ $request->id }})">
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
                @if(method_exists($advanceRequests, 'hasPages') && $advanceRequests->hasPages())
                    <div class="mt-6">
                        {{ $advanceRequests->links() }}
                    </div>
                @endif
            @else
                <!-- Empty State -->
                <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                    </svg>
                    <flux:heading size="lg" level="3" class="mt-4 text-zinc-600 dark:text-zinc-400">
                        {{ __('No advance requests found') }}
                    </flux:heading>
                    <flux:text class="mt-2 text-zinc-500 dark:text-zinc-500">
                        {{ __('No advance salary requests match your current search criteria.') }}
                    </flux:text>
                </div>
            @endif
        </div>

        <!-- Request Advance Salary Flyout -->
        @if($showAddAdvanceModal)
            <flux:modal variant="flyout" :open="$showAddAdvanceModal" wire:model="showAddAdvanceModal">
                <div class="space-y-6">
                    <div>
                        <flux:heading size="lg">{{ __('Request Advance Salary') }}</flux:heading>
                    </div>

                    <div class="space-y-4">
                        <flux:field>
                            <flux:label>{{ __('Employee') }}</flux:label>
                            <flux:select wire:model="selectedEmployeeId" placeholder="{{ __('Select employee') }}">
                                <option value="">{{ __('Select employee') }}</option>
                                @foreach($activeEmployees as $emp)
                                    <option value="{{ $emp['id'] }}">{{ $emp['label'] }}</option>
                                @endforeach
                            </flux:select>
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('Amount') }}</flux:label>
                            <flux:input type="number" step="0.01" min="0" placeholder="0.00" wire:model="advanceAmount" />
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('Reason') }}</flux:label>
                            <flux:textarea placeholder="{{ __('Enter reason for advance salary...') }}" wire:model="advanceReason"></flux:textarea>
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('Expected Payback Date') }}</flux:label>
                            <flux:input type="date" wire:model="expectedPaybackDate" />
                            <flux:description>{{ __('Repayment is applied by selected month (date day is ignored).') }}</flux:description>
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('Expected Receiving Date') }}</flux:label>
                            <flux:input type="date" wire:model="expectedReceivingDate" />
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('Advance Salary for How Many Months') }}</flux:label>
                            <flux:select wire:model.live="advanceMonths">
                                @for($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}">{{ $m }}</option>
                                @endfor
                            </flux:select>
                        </flux:field>

                        @if((int) $advanceMonths > 1)
                            <flux:field>
                                <flux:label>{{ __('Payback Mode') }}</flux:label>
                                <flux:select wire:model.live="paybackMode">
                                    <option value="all_at_once">{{ __('Payback all at once') }}</option>
                                    <option value="divide_by_months">{{ __('Divide according to months') }}</option>
                                </flux:select>
                            </flux:field>
                        @endif

                        @if((float) $advanceAmount > 0)
                            <div class="rounded-lg border border-zinc-200 dark:border-zinc-600 bg-zinc-50 dark:bg-zinc-800/50 p-4 space-y-2 text-sm">
                                <flux:heading size="sm" level="4" class="text-zinc-700 dark:text-zinc-300">{{ __('Deduction Preview') }}</flux:heading>
                                <div class="flex justify-between"><span class="text-zinc-600 dark:text-zinc-400">{{ __('Total advance') }}</span><span class="font-medium">{{ preg_replace('/\.00$/', '', number_format((float) $advanceAmount, 2, '.', ',')) }}</span></div>
                                <div class="flex justify-between"><span class="text-zinc-600 dark:text-zinc-400">{{ __('Start month') }}</span><span class="font-medium">{{ $expectedPaybackDate ? \Carbon\Carbon::parse($expectedPaybackDate)->format('M Y') : '—' }}</span></div>
                                <div class="flex justify-between"><span class="text-zinc-600 dark:text-zinc-400">{{ __('Months') }}</span><span class="font-medium">{{ (int) $advanceMonths }}</span></div>
                                <div class="flex justify-between"><span class="text-zinc-600 dark:text-zinc-400">{{ __('Mode') }}</span><span class="font-medium">{{ ((int) $advanceMonths > 1 && $paybackMode === 'divide_by_months') ? __('Divide according to months') : __('Payback all at once') }}</span></div>
                                <div class="flex justify-between"><span class="text-zinc-600 dark:text-zinc-400">{{ __('Deduction per month (Advance column)') }}</span><span class="font-medium">{{ preg_replace('/\.00$/', '', number_format((float) $this->monthlyPaybackAmount, 2, '.', ',')) }}</span></div>
                            </div>
                        @endif
                    </div>

                    <div class="flex justify-end gap-3">
                        <flux:button variant="ghost" wire:click="closeAddAdvanceModal">{{ __('Cancel') }}</flux:button>
                        <flux:button variant="primary" wire:click="addAdvanceSalary">{{ __('Submit Request') }}</flux:button>
                    </div>
                </div>
            </flux:modal>
        @endif

        <!-- Edit Advance Salary Flyout -->
        @if($showEditAdvanceModal)
            <flux:modal variant="flyout" :open="$showEditAdvanceModal" wire:model="showEditAdvanceModal">
                <div class="space-y-6">
                    <div>
                        <flux:heading size="lg">{{ __('Edit Advance Salary Request') }}</flux:heading>
                    </div>

                    <div class="space-y-4">
                        <flux:field>
                            <flux:label>{{ __('Employee') }}</flux:label>
                            <flux:select wire:model="selectedEmployeeId" placeholder="{{ __('Select employee') }}">
                                <option value="">{{ __('Select employee') }}</option>
                                @foreach($activeEmployees as $emp)
                                    <option value="{{ $emp['id'] }}">{{ $emp['label'] }}</option>
                                @endforeach
                            </flux:select>
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('Amount') }}</flux:label>
                            <flux:input type="number" step="0.01" min="0" placeholder="0.00" wire:model="advanceAmount" />
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('Reason') }}</flux:label>
                            <flux:textarea placeholder="{{ __('Enter reason for advance salary...') }}" wire:model="advanceReason"></flux:textarea>
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('Expected Payback Date') }}</flux:label>
                            <flux:input type="date" wire:model="expectedPaybackDate" />
                            <flux:description>{{ __('Repayment is applied by selected month (date day is ignored).') }}</flux:description>
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('Expected Receiving Date') }}</flux:label>
                            <flux:input type="date" wire:model="expectedReceivingDate" />
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('Advance Salary for How Many Months') }}</flux:label>
                            <flux:select wire:model.live="advanceMonths">
                                @for($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}">{{ $m }}</option>
                                @endfor
                            </flux:select>
                        </flux:field>

                        @if((int) $advanceMonths > 1)
                            <flux:field>
                                <flux:label>{{ __('Payback Mode') }}</flux:label>
                                <flux:select wire:model.live="paybackMode">
                                    <option value="all_at_once">{{ __('Payback all at once') }}</option>
                                    <option value="divide_by_months">{{ __('Divide according to months') }}</option>
                                </flux:select>
                            </flux:field>
                        @endif

                        @if((float) $advanceAmount > 0)
                            <div class="rounded-lg border border-zinc-200 dark:border-zinc-600 bg-zinc-50 dark:bg-zinc-800/50 p-4 space-y-2 text-sm">
                                <flux:heading size="sm" level="4" class="text-zinc-700 dark:text-zinc-300">{{ __('Deduction Preview') }}</flux:heading>
                                <div class="flex justify-between"><span class="text-zinc-600 dark:text-zinc-400">{{ __('Total advance') }}</span><span class="font-medium">{{ preg_replace('/\.00$/', '', number_format((float) $advanceAmount, 2, '.', ',')) }}</span></div>
                                <div class="flex justify-between"><span class="text-zinc-600 dark:text-zinc-400">{{ __('Start month') }}</span><span class="font-medium">{{ $expectedPaybackDate ? \Carbon\Carbon::parse($expectedPaybackDate)->format('M Y') : '—' }}</span></div>
                                <div class="flex justify-between"><span class="text-zinc-600 dark:text-zinc-400">{{ __('Months') }}</span><span class="font-medium">{{ (int) $advanceMonths }}</span></div>
                                <div class="flex justify-between"><span class="text-zinc-600 dark:text-zinc-400">{{ __('Mode') }}</span><span class="font-medium">{{ ((int) $advanceMonths > 1 && $paybackMode === 'divide_by_months') ? __('Divide according to months') : __('Payback all at once') }}</span></div>
                                <div class="flex justify-between"><span class="text-zinc-600 dark:text-zinc-400">{{ __('Deduction per month (Advance column)') }}</span><span class="font-medium">{{ preg_replace('/\.00$/', '', number_format((float) $this->monthlyPaybackAmount, 2, '.', ',')) }}</span></div>
                            </div>
                        @endif
                    </div>

                    <div class="flex justify-end gap-3">
                        <flux:button variant="ghost" wire:click="closeEditAdvanceModal">{{ __('Cancel') }}</flux:button>
                        <flux:button variant="primary" wire:click="updateAdvanceSalary">{{ __('Update Request') }}</flux:button>
                    </div>
                </div>
            </flux:modal>
        @endif

        <!-- View Advance Request Flyout -->
        @if($showViewRequestModal && $this->selectedAdvanceRequest)
            @php $req = $this->selectedAdvanceRequest; $emp = $req->employee; @endphp
            <flux:modal variant="flyout" :open="$showViewRequestModal" wire:model="showViewRequestModal" class="w-[32rem] lg:w-[36rem]">
                <div class="space-y-6">
                    <div>
                        <flux:heading size="lg">{{ __('Advance Request Details') }}</flux:heading>
                        <flux:text class="mt-1 text-zinc-500 dark:text-zinc-400">{{ $emp ? trim($emp->first_name . ' ' . $emp->last_name) . ' (' . ($emp->employee_code ?? '') . ')' : '—' }}</flux:text>
                    </div>
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between"><span class="text-zinc-600 dark:text-zinc-400">{{ __('Amount') }}</span><span class="font-medium">{{ preg_replace('/\.00$/', '', number_format((float) $req->amount, 2, '.', ',')) }}</span></div>
                        <div class="flex justify-between"><span class="text-zinc-600 dark:text-zinc-400">{{ __('Reason') }}</span><span class="font-medium">{{ $req->reason ?: '—' }}</span></div>
                        <div class="flex justify-between"><span class="text-zinc-600 dark:text-zinc-400">{{ __('Expected payback date') }}</span><span class="font-medium">{{ $req->expected_payback_date ? $req->expected_payback_date->format('M d, Y') : '—' }}</span></div>
                        <div class="flex justify-between"><span class="text-zinc-600 dark:text-zinc-400">{{ __('Expected receiving date') }}</span><span class="font-medium">{{ $req->expected_receiving_date ? $req->expected_receiving_date->format('M d, Y') : '—' }}</span></div>
                        <div class="flex justify-between"><span class="text-zinc-600 dark:text-zinc-400">{{ __('Advance months') }}</span><span class="font-medium">{{ (int) ($req->payback_months ?? 1) }}</span></div>
                        <div class="flex justify-between"><span class="text-zinc-600 dark:text-zinc-400">{{ __('Payback mode') }}</span><span class="font-medium">{{ ($req->payback_mode ?? 'all_at_once') === 'divide_by_months' ? __('Divide according to months') : __('Payback all at once') }}</span></div>
                        @if(($req->payback_transaction_type ?? 'deduct_from_salary') === 'deduct_from_salary')
                            <div class="flex justify-between"><span class="text-zinc-600 dark:text-zinc-400">{{ __('Deduction per month') }}</span><span class="font-medium">{{ preg_replace('/\.00$/', '', number_format(((float) $req->amount / max(1, (int) (($req->payback_mode === 'divide_by_months') ? ($req->payback_months ?? 1) : 1))), 2, '.', ',')) }}</span></div>
                        @endif
                        <div class="flex justify-between"><span class="text-zinc-600 dark:text-zinc-400">{{ __('Status') }}</span><span class="font-medium">{{ ucfirst($req->status) }}</span></div>
                        <div class="flex justify-between"><span class="text-zinc-600 dark:text-zinc-400">{{ __('Request date') }}</span><span class="font-medium">{{ $req->created_at ? $req->created_at->format('M d, Y') : '—' }}</span></div>
                        <div class="flex justify-between"><span class="text-zinc-600 dark:text-zinc-400">{{ __('Requested by') }}</span><span class="font-medium">{{ $req->requestedByUser?->name ?? '—' }}</span></div>
                        <div class="flex justify-between"><span class="text-zinc-600 dark:text-zinc-400">{{ __('Approval date') }}</span><span class="font-medium">{{ $req->approved_at ? $req->approved_at->format('M d, Y') : '—' }}</span></div>
                        <div class="flex justify-between"><span class="text-zinc-600 dark:text-zinc-400">{{ __('Approved by') }}</span><span class="font-medium">{{ $req->approvedByUser?->name ?? '—' }}</span></div>
                        <div class="flex justify-between"><span class="text-zinc-600 dark:text-zinc-400">{{ __('Confirmed / Received') }}</span><span class="font-medium">{{ ($req->payback_transaction_type ?? 'deduct_from_salary') === 'deduct_from_salary' ? ($req->confirmed_at ? $req->confirmed_at->format('M d, Y') : '—') : ($req->received_at ? $req->received_at->format('M d, Y') : '—') }}</span></div>
                        <div class="flex justify-between"><span class="text-zinc-600 dark:text-zinc-400">{{ __('Payback received amount') }}</span><span class="font-medium">{{ $req->received_amount !== null ? preg_replace('/\.00$/', '', number_format((float) $req->received_amount, 2, '.', ',')) : '—' }}</span></div>
                    </div>
                    <div class="flex justify-end pt-4">
                        <flux:button variant="ghost" wire:click="closeViewRequestModal">{{ __('Close') }}</flux:button>
                    </div>
                </div>
            </flux:modal>
        @endif
    </x-payroll.layout>
</section>