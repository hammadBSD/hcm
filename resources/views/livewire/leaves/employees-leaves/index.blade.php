@php
    $canApproveLeaves = auth()->user()?->can('leaves.approve.requests');
    $canManageAllLeaves = auth()->user()?->can('leaves.manage.all');
@endphp

<section class="w-full">
    @include('partials.leaves-heading')

    <x-leaves.layout :heading="__('All Leave Requests')" :subheading="__('Manage and approve all submitted leave requests')">
        <div class="space-y-6">
            <!-- Leave Balance -->
            <!-- <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <flux:icon name="calendar-days" class="w-4 h-4 text-zinc-500 dark:text-zinc-400" />
                        <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Leave Balance (Current Leave Quota Year)</span>
                    </div>
                    <div class="flex items-center gap-6 text-sm">
                        <div class="text-center">
                            <div class="text-zinc-500 dark:text-zinc-400">Entitled</div>
                            <div class="font-semibold text-zinc-900 dark:text-zinc-100">3.2</div>
                        </div>
                        <div class="text-center">
                            <div class="text-zinc-500 dark:text-zinc-400">Taken</div>
                            <div class="font-semibold text-zinc-900 dark:text-zinc-100">1</div>
                        </div>
                        <div class="text-center">
                            <div class="text-zinc-500 dark:text-zinc-400">Pending</div>
                            <div class="font-semibold text-zinc-900 dark:text-zinc-100">0</div>
                        </div>
                        <div class="text-center">
                            <div class="text-zinc-500 dark:text-zinc-400">Balance</div>
                            <div class="font-bold text-green-600 dark:text-green-400">2.2</div>
                        </div>
                    </div>
                </div>
            </div> -->

            <!-- Filters -->
            <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-6">
                <div class="flex flex-wrap items-center gap-4">
                    <!-- Left Group: Search + Employee -->
                    <div class="flex items-center gap-4 flex-shrink-0">
                        <div class="w-60">
                            <flux:input 
                                wire:model.live="search" 
                                placeholder="Search by employee name or ID..." 
                                icon="magnifying-glass"
                            />
                        </div>
                        <div class="w-48">
                            <flux:select wire:model.live="employeeFilter" placeholder="{{ __('All Employees') }}">
                                <option value="">{{ __('All Employees') }}</option>
                                @foreach($employeeOptions as $option)
                                    <option value="{{ $option['id'] }}">
                                        {{ $option['label'] }}@if(!empty($option['code'])) ({{ $option['code'] }}) @endif
                                    </option>
                                @endforeach
                            </flux:select>
                        </div>
                    </div>

                    <!-- Right Group: Remaining Filters -->
                    <div class="flex items-center gap-4 ml-auto">
                        <div class="w-32">
                            <flux:select wire:model.live="dateFilter" placeholder="All Dates">
                                <option value="">All Dates</option>
                                <option value="this_month">This Month</option>
                                <option value="last_month">Last Month</option>
                                <option value="this_quarter">This Quarter</option>
                                <option value="this_year">This Year</option>
                                <option value="last_year">Last Year</option>
                            </flux:select>
                        </div>

                        <div class="w-32">
                            <flux:select wire:model.live="statusFilter" placeholder="All Status">
                                <option value="">All Status</option>
                                <option value="pending">Pending</option>
                                <option value="approved">Approved</option>
                                <option value="rejected">Rejected</option>
                                <option value="cancelled">Cancelled</option>
                            </flux:select>
                        </div>

                        <div class="w-32">
                            <flux:select wire:model.live="leaveTypeFilter" placeholder="{{ __('All Types') }}">
                                <option value="">{{ __('All Types') }}</option>
                                @foreach($leaveTypeOptions as $option)
                                    <option value="{{ $option['id'] }}">
                                        {{ $option['name'] }}@if(!empty($option['code'])) ({{ $option['code'] }}) @endif
                                    </option>
                                @endforeach
                            </flux:select>
                        </div>
                    </div>
                </div>

                <div class="mt-4 flex justify-start">
                    <flux:button variant="outline" wire:click="resetFilters">
                        {{ __('Clear Filters') }}
                    </flux:button>
                </div>
            </div>

            {{-- Employee Leave Summary - Commented out from index page
            <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <flux:icon name="calendar-days" class="w-4 h-4 text-zinc-500 dark:text-zinc-400" />
                        <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Leave Balance</span>
                    </div>
                    <div class="flex items-center gap-6 text-sm">
                        <div class="text-center">
                            <div class="text-zinc-500 dark:text-zinc-400">Entitled</div>
                            <div class="font-semibold text-zinc-900 dark:text-zinc-100">3.2</div>
                        </div>
                        <div class="text-center">
                            <div class="text-zinc-500 dark:text-zinc-400">Taken</div>
                            <div class="font-semibold text-zinc-900 dark:text-zinc-100">1</div>
                        </div>
                        <div class="text-center">
                            <div class="text-zinc-500 dark:text-zinc-400">Pending</div>
                            <div class="font-semibold text-zinc-900 dark:text-zinc-100">0</div>
                        </div>
                        <div class="text-center">
                            <div class="text-zinc-500 dark:text-zinc-400">Balance</div>
                            <div class="font-bold text-green-600 dark:text-green-400">2.2</div>
                        </div>
                    </div>
                </div>
            </div>
            --}}

            <!-- Leave Requests Table -->
            <div class="mt-8">
                @if(count($leaveRequests) > 0)
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
                                            <button wire:click="sort('leave_type')" class="flex items-center gap-1 hover:text-zinc-700 dark:hover:text-zinc-200">
                                                {{ __('Leave Type') }}
                                                @if($sortBy === 'leave_type')
                                                    <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="w-4 h-4" />
                                                @endif
                                            </button>
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                            <button wire:click="sort('start_date')" class="flex items-center gap-1 hover:text-zinc-700 dark:hover:text-zinc-200">
                                                {{ __('Duration') }}
                                                @if($sortBy === 'start_date')
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
                                            <button wire:click="sort('created_at')" class="flex items-center gap-1 hover:text-zinc-700 dark:hover:text-zinc-200">
                                                {{ __('Added On') }}
                                                @if($sortBy === 'created_at')
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
                                    @foreach($leaveRequests as $index => $request)
                                        @php
                                            $statusClass = match($request['status']) {
                                                'approved' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-200 border border-emerald-200 dark:border-emerald-700/60',
                                                'rejected' => 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-200 border border-rose-200 dark:border-rose-700/60',
                                                'cancelled' => 'bg-zinc-100 text-zinc-700 dark:bg-zinc-800/60 dark:text-zinc-200 border border-zinc-200 dark:border-zinc-700',
                                                default => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-200 border border-amber-200 dark:border-amber-700/60',
                                            };
                                        @endphp
                                        <tr class="hover:bg-zinc-100 dark:hover:bg-zinc-600 transition-colors duration-150">
                                            <td class="px-6 py-6 whitespace-nowrap">
                                                <div class="flex items-center gap-3">
                                                    <flux:avatar size="sm" initials="{{ $request['employee_initials'] }}" />
                                                    <div>
                                                        <div class="font-medium text-zinc-900 dark:text-zinc-100">
                                                            {{ $request['employee_name'] }}
                                                        </div>
                                                        <div class="text-sm text-zinc-500 dark:text-zinc-400">
                                                            {{ $request['employee_code'] }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            
                                            <td class="px-6 py-6 whitespace-nowrap">
                                                <div class="text-sm text-zinc-900 dark:text-zinc-100">
                                                    {{ $request['department'] ?? __('Not assigned') }}
                                                </div>
                                                <div class="text-sm text-zinc-500 dark:text-zinc-400">
                                                    {{ $request['position'] ?? __('No designation') }}
                                                </div>
                                            </td>
                                            
                                            <td class="px-6 py-6 whitespace-nowrap">
                                                <flux:badge color="blue" size="sm">
                                                    {{ $request['leave_type'] }}
                                                </flux:badge>
                                                <div class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">
                                                    {{ number_format($request['total_days'], 1) }} {{ __('days') }}
                                                </div>
                                            </td>
                                            
                                            <td class="px-6 py-6 whitespace-nowrap">
                                                <div class="text-sm text-zinc-900 dark:text-zinc-100">
                                                    @if($request['start_date'] && $request['end_date'])
                                                        {{ $request['start_date'] }} – {{ $request['end_date'] }}
                                                    @else
                                                        {{ __('N/A') }}
                                                    @endif
                                                </div>
                                            </td>
                                            
                                            <td class="px-6 py-6 whitespace-nowrap">
                                                <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $statusClass }}">
                                                    {{ ucfirst($request['status']) }}
                                                </span>
                                            </td>
                                            
                                            <td class="px-6 py-6 whitespace-nowrap">
                                                <div class="text-sm text-zinc-900 dark:text-zinc-100">
                                                    {{ $request['created_date'] ?? __('N/A') }}
                                                </div>
                                                <div class="text-sm text-zinc-500 dark:text-zinc-400">
                                                    {{ $request['created_time'] ?? '' }}
                                                </div>
                                            </td>
                                            
                                            <td class="px-6 py-6 whitespace-nowrap text-sm font-medium">
                                                <flux:dropdown>
                                                    <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                                                    <flux:menu>
                                                        <flux:menu.item icon="eye" wire:click="viewRequest({{ $request['id'] }})">
                                                            {{ __('View Request') }}
                                                        </flux:menu.item>
                                                        @if($request['status'] === 'pending' && $canApproveLeaves)
                                                            <flux:menu.item icon="check" wire:click="approveRequest({{ $request['id'] }})">
                                                                {{ __('Approve') }}
                                                            </flux:menu.item>
                                                            <flux:menu.item icon="x-mark" wire:click="rejectRequest({{ $request['id'] }})">
                                                                {{ __('Reject') }}
                                                            </flux:menu.item>
                                                        @endif
                                                        @if($canManageAllLeaves)
                                                            <flux:menu.separator />
                                                            <flux:menu.item icon="pencil" wire:click="editRequest({{ $request['id'] }})">
                                                                {{ __('Edit Request') }}
                                                            </flux:menu.item>
                                                        @endif
                                                    </flux:menu>
                                                </flux:dropdown>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @else
                    <div class="text-center py-12">
                        <flux:icon name="document-text" class="mx-auto h-12 w-12 text-zinc-400" />
                        <flux:heading size="sm" class="mt-2 text-zinc-500 dark:text-zinc-400">
                            No leave requests found
                        </flux:heading>
                        <flux:text class="mt-1 text-zinc-500 dark:text-zinc-400">
                            Select criteria and press apply button to view leave requests.
                        </flux:text>
                    </div>
                @endif
            </div>
        </div>
    </x-leaves.layout>
</section>
