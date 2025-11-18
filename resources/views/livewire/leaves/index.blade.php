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

    <!-- View Details Flyout -->
    <flux:modal variant="flyout" wire:model="showViewFlyout">
    <div class="flex flex-col h-full">
        <div class="px-6 pt-6 pb-4 border-b border-zinc-200 dark:border-zinc-700">
    <flux:heading size="lg">{{ __('Leave Request Details') }}</flux:heading>
    <flux:text class="mt-1 text-zinc-500 dark:text-zinc-400">
        {{ __('Review the full context, history, and metadata for this leave request.') }}
    </flux:text>
        </div>

        <div class="flex-1 overflow-y-auto px-6 py-6 space-y-6">
    @if($activeRequest)
        <div class="grid grid-cols-1 gap-4">
            <div class="border border-zinc-200 dark:border-zinc-700 rounded-lg p-4 space-y-3">
                <flux:heading size="sm" class="text-zinc-900 dark:text-zinc-100">{{ __('Request Summary') }}</flux:heading>
                <div class="space-y-2 text-sm">
                    <div class="flex items-start gap-2">
                        <span class="text-zinc-500 dark:text-zinc-400 min-w-[8rem]">{{ __('Leave Type') }}</span>
                        <span class="font-medium text-zinc-900 dark:text-zinc-100">
                            {{ $activeRequest['leave_type'] }}
                            @if(!empty($activeRequest['leave_type_code']))
                                <span class="text-xs text-zinc-500 dark:text-zinc-400">({{ $activeRequest['leave_type_code'] }})</span>
                            @endif
                        </span>
                    </div>
                    <div class="flex items-start gap-2">
                        <span class="text-zinc-500 dark:text-zinc-400 min-w-[8rem]">{{ __('Duration') }}</span>
                        <span class="text-zinc-900 dark:text-zinc-100">
                            {{ $activeRequest['duration']['start'] }} – {{ $activeRequest['duration']['end'] }}
                        </span>
                    </div>
                    <div class="flex items-start gap-2">
                        <span class="text-zinc-500 dark:text-zinc-400 min-w-[8rem]">{{ __('Total Days') }}</span>
                        <span class="text-zinc-900 dark:text-zinc-100">{{ number_format($activeRequest['total_days'], 1) }}</span>
                    </div>
                    <div class="flex items-start gap-2">
                        <span class="text-zinc-500 dark:text-zinc-400 min-w-[8rem]">{{ __('Status') }}</span>
                        <span class="text-zinc-900 dark:text-zinc-100 capitalize">{{ $activeRequest['status'] }}</span>
                    </div>
                    <div class="flex items-start gap-2">
                        <span class="text-zinc-500 dark:text-zinc-400 min-w-[8rem]">{{ __('Requested At') }}</span>
                        <span class="text-zinc-900 dark:text-zinc-100">{{ $activeRequest['requested_at'] }}</span>
                    </div>
                </div>
                @if(!empty($activeRequest['reason']))
                    <div class="mt-4 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                        <flux:heading size="xs" class="text-zinc-500 dark:text-zinc-400 mb-2 uppercase tracking-wide">
                            {{ __('Reason') }}
                        </flux:heading>
                        <div class="text-sm text-zinc-900 dark:text-zinc-100 whitespace-pre-line">
                            {{ $activeRequest['reason'] }}
                        </div>
                    </div>
                @endif
            </div>

            <div class="border border-zinc-200 dark:border-zinc-700 rounded-lg p-4 space-y-3">
                <flux:heading size="sm" class="text-zinc-900 dark:text-zinc-100">{{ __('History & Timeline') }}</flux:heading>
                <div class="space-y-3">
                    @forelse($activeEvents as $event)
                        <div class="flex items-start gap-3">
                            <flux:icon name="clock" class="w-5 h-5 text-zinc-400 dark:text-zinc-500 mt-1 flex-shrink-0" />
                            <div class="flex-1">
                                <div class="text-sm font-medium text-zinc-900 dark:text-zinc-100 capitalize">
                                    {{ str_replace('_', ' ', $event['type']) }}
                                </div>
                                <div class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">
                                    {{ $event['performed_by'] }} · {{ $event['created_at'] }}
                                </div>
                                @if(!empty($event['notes']))
                                    <div class="mt-2 text-sm text-zinc-900 dark:text-zinc-100 whitespace-pre-line">
                                        {{ $event['notes'] }}
                                    </div>
                                @else
                                    <div class="mt-2 text-sm text-zinc-500 dark:text-zinc-400 italic">
                                        {{ __('No reason provided.') }}
                                    </div>
                                @endif
                                @if(!empty($event['attachment_path']))
                                    <div class="mt-2">
                                        <a
                                            href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($event['attachment_path']) }}"
                                            target="_blank"
                                            class="inline-flex items-center gap-2 text-sm text-blue-600 dark:text-blue-300 hover:underline"
                                        >
                                            <flux:icon name="paper-clip" class="w-4 h-4" />
                                            {{ __('Download attachment') }}
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-sm text-zinc-500 dark:text-zinc-400">
                            {{ __('No history available yet.') }}
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    @else
        <div class="text-sm text-zinc-500 dark:text-zinc-400">
            {{ __('Select a leave request to view its details.') }}
        </div>
    @endif
        </div>

        <div class="px-6 py-4 border-t border-zinc-200 dark:border-zinc-700 flex justify-end gap-3">
    <flux:button variant="outline" wire:click="closeFlyouts">{{ __('Close') }}</flux:button>
        </div>
    </div>
    </flux:modal>

    <!-- Approve Flyout -->
    <flux:modal variant="flyout" wire:model="showApproveFlyout">
    <form class="flex flex-col h-full" wire:submit.prevent="submitApproval">
        <div class="px-6 pt-6 pb-4 border-b border-zinc-200 dark:border-zinc-700">
            <flux:heading size="lg">{{ __('Approve Leave Request') }}</flux:heading>
            <flux:text class="mt-1 text-zinc-500 dark:text-zinc-400">
                {{ __('Confirm the approval and optionally include guidance or supporting documents.') }}
            </flux:text>
        </div>

        <div class="flex-1 overflow-y-auto px-6 py-6 space-y-6">
            @if($activeRequest)
                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 text-sm text-blue-900 dark:text-blue-100">
                    <div class="font-medium">{{ $activeRequest['employee_name'] ?? __('Your Leave Request') }}</div>
                    <div class="mt-1 text-blue-700 dark:text-blue-200">
                        {{ $activeRequest['leave_type'] }} · {{ $activeRequest['duration']['start'] }} – {{ $activeRequest['duration']['end'] }} ({{ number_format($activeRequest['total_days'], 1) }} {{ __('days') }})
                    </div>
                </div>
            @endif

            <div class="space-y-4">
                <div class="space-y-2">
                    <flux:label>{{ __('Approval Notes') }}</flux:label>
                    <flux:textarea
                        rows="6"
                        class="dark:bg-transparent!"
                        placeholder="{{ __('Share any context, conditions, or next steps for the employee.') }}"
                        wire:model.defer="approveForm.notes"
                    ></flux:textarea>
                    <flux:error name="approveForm.notes" />
                    <div class="text-xs text-zinc-500 dark:text-zinc-400">
                        {{ __('Employees will see these notes alongside the approval entry.') }}
                    </div>
                </div>

                <div class="space-y-2">
                    <flux:label>{{ __('Attach Supporting File') }}</flux:label>
                    <input type="file" wire:model="approveAttachment" class="block w-full text-sm text-zinc-500 dark:text-zinc-300" />
                    <flux:error name="approveAttachment" />
                    <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Optional. Max 5MB.') }}</div>
                </div>
            </div>
        </div>

        <div class="px-6 py-4 border-t border-zinc-200 dark:border-zinc-700 flex justify-end gap-3">
            <flux:button type="button" variant="outline" wire:click="closeFlyouts" kbd="esc">{{ __('Cancel') }}</flux:button>
            <flux:button type="submit" wire:loading.attr="disabled" icon="check">
                {{ __('Approve Request') }}
            </flux:button>
        </div>
    </form>
    </flux:modal>

    <!-- Reject Flyout -->
    <flux:modal variant="flyout" wire:model="showRejectFlyout">
    <form class="flex flex-col h-full" wire:submit.prevent="submitRejection">
        <div class="px-6 pt-6 pb-4 border-b border-zinc-200 dark:border-zinc-700">
            <flux:heading size="lg">{{ __('Reject Leave Request') }}</flux:heading>
            <flux:text class="mt-1 text-zinc-500 dark:text-zinc-400">
                {{ __('Capture the reasoning and provide alternate guidance for the employee.') }}
            </flux:text>
        </div>

        <div class="flex-1 overflow-y-auto px-6 py-6 space-y-6">
            @if($activeRequest)
                <div class="bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800 rounded-lg p-4 text-sm text-rose-900 dark:text-rose-100">
                    <div class="font-medium">{{ $activeRequest['employee_name'] ?? __('Your Leave Request') }}</div>
                    <div class="mt-1 text-rose-700 dark:text-rose-200">
                        {{ $activeRequest['leave_type'] }} · {{ $activeRequest['duration']['start'] }} – {{ $activeRequest['duration']['end'] }} ({{ number_format($activeRequest['total_days'], 1) }} {{ __('days') }})
                    </div>
                </div>
            @endif

            <div class="space-y-4">
                <div class="space-y-2">
                    <flux:label>{{ __('Rejection Notes') }}</flux:label>
                    <flux:textarea
                        rows="6"
                        class="dark:bg-transparent!"
                        placeholder="{{ __('Explain why the leave cannot be approved and offer recommendations if possible.') }}"
                        wire:model.defer="rejectForm.notes"
                    ></flux:textarea>
                    <flux:error name="rejectForm.notes" />
                    <div class="text-xs text-zinc-500 dark:text-zinc-400">
                        {{ __('A clear reason helps employees understand the decision and next steps.') }}
                    </div>
                </div>

                <div class="space-y-2">
                    <flux:label>{{ __('Attach Reference File') }}</flux:label>
                    <input type="file" wire:model="rejectAttachment" class="block w-full text-sm text-zinc-500 dark:text-zinc-300" />
                    <flux:error name="rejectAttachment" />
                    <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Optional. Max 5MB.') }}</div>
                </div>
            </div>
        </div>

        <div class="px-6 py-4 border-t border-zinc-200 dark:border-zinc-700 flex justify-end gap-3">
            <flux:button type="button" variant="outline" wire:click="closeFlyouts" kbd="esc">{{ __('Cancel') }}</flux:button>
            <flux:button type="submit" variant="danger" wire:loading.attr="disabled" icon="x-mark">
                {{ __('Reject Request') }}
            </flux:button>
        </div>
    </form>
    </flux:modal>

    <!-- Edit Flyout -->
    <flux:modal variant="flyout" wire:model="showEditFlyout">
    <form class="flex flex-col h-full" wire:submit.prevent="submitEdit">
        <div class="px-6 pt-6 pb-4 border-b border-zinc-200 dark:border-zinc-700">
            <flux:heading size="lg">{{ __('Edit Leave Request') }}</flux:heading>
            <flux:text class="mt-1 text-zinc-500 dark:text-zinc-400">
                {{ __('Update the leave request details. Only pending requests can be edited.') }}
            </flux:text>
        </div>

        <div class="flex-1 overflow-y-auto px-6 py-6 space-y-6">
            <div class="space-y-4">
                <!-- Leave Type -->
                <flux:field>
                    <flux:label>{{ __('Leave Type') }} <span class="text-red-500">*</span></flux:label>
                    <flux:select wire:model="editForm.leave_type_id" placeholder="{{ __('Select Leave Type') }}">
                        <option value="">{{ __('Select Leave Type') }}</option>
                        @foreach($leaveTypeOptions as $option)
                            <option value="{{ $option['id'] }}">
                                {{ $option['name'] }}@if(!empty($option['code'])) ({{ $option['code'] }}) @endif
                            </option>
                        @endforeach
                    </flux:select>
                    <flux:error name="editForm.leave_type_id" />
                </flux:field>

                <!-- Leave Duration -->
                <flux:field>
                    <flux:label>{{ __('Leave Duration') }} <span class="text-red-500">*</span></flux:label>
                    <flux:select wire:model="editForm.duration" placeholder="{{ __('Full Day') }}">
                        <option value="full_day">{{ __('Full Day') }}</option>
                        <option value="half_day_morning">{{ __('Half Day (Morning)') }}</option>
                        <option value="half_day_afternoon">{{ __('Half Day (Afternoon)') }}</option>
                    </flux:select>
                    <flux:error name="editForm.duration" />
                </flux:field>

                <!-- Leave Days -->
                <flux:field>
                    <flux:label>{{ __('Leave Days') }} <span class="text-red-500">*</span></flux:label>
                    <flux:input wire:model="editForm.total_days" type="text" placeholder="1.0" pattern="[0-9.]+" inputmode="decimal" />
                    <flux:error name="editForm.total_days" />
                </flux:field>

                <!-- Leave From and To -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <flux:field>
                        <flux:label>{{ __('Leave From') }} <span class="text-red-500">*</span></flux:label>
                        <flux:input wire:model="editForm.start_date" type="date" />
                        <flux:error name="editForm.start_date" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('Leave To') }} <span class="text-red-500">*</span></flux:label>
                        <flux:input wire:model="editForm.end_date" type="date" />
                        <flux:error name="editForm.end_date" />
                    </flux:field>
                </div>

                <!-- Reason -->
                <flux:field>
                    <flux:label>{{ __('Reason') }} <span class="text-red-500">*</span></flux:label>
                    <flux:textarea
                        wire:model="editForm.reason"
                        rows="6"
                        class="dark:bg-transparent!"
                        placeholder="{{ __('Please provide a detailed reason for your leave request...') }}"
                    ></flux:textarea>
                    <flux:error name="editForm.reason" />
                </flux:field>
            </div>
        </div>

        <div class="px-6 py-4 border-t border-zinc-200 dark:border-zinc-700 flex justify-end gap-3">
            <flux:button type="button" variant="outline" wire:click="closeFlyouts" kbd="esc">{{ __('Cancel') }}</flux:button>
            <flux:button type="submit" wire:loading.attr="disabled" icon="pencil">
                {{ __('Update Request') }}
            </flux:button>
        </div>
    </form>
    </flux:modal>
</section>
