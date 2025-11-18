@php
    $canApproveLeaves = auth()->user()?->can('leaves.approve.requests');
@endphp

<section class="w-full">
    @include('partials.leaves-heading')

    <x-leaves.layout :heading="__('My Leaves')" :subheading="__('View and manage your leave requests')">
        <div class="space-y-6">
            @if (session()->has('success'))
                <flux:callout variant="success" class="mb-6">
                    {{ session('success') }}
                </flux:callout>
            @endif

            <!-- Leave Balance -->
            <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <flux:icon name="calendar-days" class="w-4 h-4 text-zinc-500 dark:text-zinc-400" />
                        <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Leave Balance (Current Leave Quota Year)</span>
                    </div>
                    <div class="flex items-center gap-6 text-sm">
                        <div class="text-center">
                            <div class="text-zinc-500 dark:text-zinc-400">{{ __('Entitled') }}</div>
                            <div class="font-semibold text-zinc-900 dark:text-zinc-100">
                                {{ number_format($summary['entitled'] ?? 0, 1) }}
                            </div>
                        </div>
                        <div class="text-center">
                            <div class="text-zinc-500 dark:text-zinc-400">{{ __('Taken') }}</div>
                            <div class="font-semibold text-zinc-900 dark:text-zinc-100">
                                {{ number_format($summary['used'] ?? 0, 1) }}
                            </div>
                        </div>
                        @php
                            $pendingValue = $summary['pending'] ?? 0;
                            $pendingTextClasses = $pendingValue > 0
                                ? 'text-amber-600 dark:text-amber-300'
                                : 'text-zinc-900 dark:text-zinc-100';
                        @endphp
                        <div class="text-center">
                            <div class="text-zinc-500 dark:text-zinc-400">{{ __('Pending') }}</div>
                            <div class="font-semibold {{ $pendingTextClasses }}">
                                {{ number_format($pendingValue, 1) }}
                            </div>
                        </div>
                        @php
                            $balanceValue = $summary['balance'] ?? 0;
                        @endphp
                        <div class="text-center">
                            <div class="text-zinc-500 dark:text-zinc-400">{{ __('Balance') }}</div>
                            <div class="font-bold {{ $balanceValue >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                {{ number_format($balanceValue, 1) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-6">
                <div class="flex flex-wrap items-center justify-end gap-4">
                    <!-- Date Filter -->
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
                    
                    <!-- Status Filter -->
                    <div class="w-32">
                        <flux:select wire:model.live="statusFilter" placeholder="All Status">
                            <option value="">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                            <option value="cancelled">Cancelled</option>
                        </flux:select>
                    </div>
                    
                    <!-- Leave Type Filter -->
                    <div class="w-32">
                        <flux:select wire:model.live="leaveTypeFilter" placeholder="All Types">
                            <option value="">{{ __('All Types') }}</option>
                            @foreach($leaveTypeOptions as $option)
                                <option value="{{ $option['id'] }}">
                                    {{ $option['name'] }}@if(!empty($option['code'])) ({{ $option['code'] }}) @endif
                                </option>
                            @endforeach
                        </flux:select>
                    </div>
                    
                    <!-- Clear Filters Button -->
                    <flux:button variant="outline" wire:click="resetFilters">
                        Clear Filters
                    </flux:button>
                    
                    <!-- Request Leave Button (for self) -->
                    @can('leaves.request.submit')
                        <flux:button variant="primary" :href="route('leaves.leave-request')" wire:navigate>
                            <flux:icon name="plus" class="w-4 h-4 mr-2" />
                            Request Leave
                        </flux:button>
                    @endcan
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
                                            $startDate = $request['start_date'] ? \Carbon\Carbon::parse($request['start_date']) : null;
                                            $endDate = $request['end_date'] ? \Carbon\Carbon::parse($request['end_date']) : null;
                                            $createdAt = $request['created_at'] ? \Carbon\Carbon::parse($request['created_at']) : null;
                                            $statusClass = match ($request['status']) {
                                                'approved' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-200 border border-emerald-200 dark:border-emerald-700/60',
                                                'rejected' => 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-200 border border-rose-200 dark:border-rose-700/60',
                                                'cancelled' => 'bg-zinc-100 text-zinc-700 dark:bg-zinc-800/60 dark:text-zinc-200 border border-zinc-200 dark:border-zinc-700',
                                                default => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-200 border border-amber-200 dark:border-amber-700/60',
                                            };
                                        @endphp
                                        <tr class="hover:bg-zinc-100 dark:hover:bg-zinc-600 transition-colors duration-150">
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
                                                    @if($startDate && $endDate)
                                                        {{ $startDate->format('Y-m-d') }} – {{ $endDate->format('Y-m-d') }}
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
                                                    {{ $createdAt ? $createdAt->format('M d, Y') : __('N/A') }}
                                                </div>
                                                <div class="text-sm text-zinc-500 dark:text-zinc-400">
                                                    {{ $createdAt ? $createdAt->format('h:i A') : '' }}
                                                </div>
                                            </td>

                                            <td class="px-6 py-6 whitespace-nowrap text-sm font-medium">
                                                <div class="flex items-center gap-1">
                                                    <flux:dropdown>
                                                        <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                                                        <flux:menu>
                                                            <flux:menu.item icon="eye" wire:click.prevent="viewRequest({{ $request['id'] }})">
                                                                {{ __('View details') }}
                                                            </flux:menu.item>
                                                            @if($request['status'] === 'pending' && $canApproveLeaves)
                                                                <flux:menu.item icon="check" wire:click.prevent="approveRequest({{ $request['id'] }})">
                                                                    {{ __('Approve') }}
                                                                </flux:menu.item>
                                                                <flux:menu.item icon="x-mark" wire:click.prevent="rejectRequest({{ $request['id'] }})">
                                                                    {{ __('Reject') }}
                                                                </flux:menu.item>
                                                            @endif
                                                            <flux:menu.separator />
                                                            <flux:menu.item icon="pencil" wire:click.prevent="editRequest({{ $request['id'] }})">
                                                                {{ __('Edit request') }}
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
