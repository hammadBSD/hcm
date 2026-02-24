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
            </div>

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
                                            <div class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                                ${{ number_format((float) $request->amount, 2) }}
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
                        </flux:field>
                    </div>

                    <div class="flex justify-end gap-3">
                        <flux:button variant="ghost" wire:click="closeAddAdvanceModal">{{ __('Cancel') }}</flux:button>
                        <flux:button variant="primary" wire:click="addAdvanceSalary">{{ __('Submit Request') }}</flux:button>
                    </div>
                </div>
            </flux:modal>
        @endif
    </x-payroll.layout>
</section>