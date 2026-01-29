<section class="w-full">
    @include('partials.system-management-heading')

    <x-system-management.layout :heading="__('Leave Balances')" :subheading="__('View current leave entitlement, usage and manually adjust balances when required')">
        <div class="space-y-6">
            <!-- Filters -->
            <div class="flex justify-between items-center gap-4">
                <div class="flex items-center gap-3">
                    <flux:input
                        type="search"
                        icon="magnifying-glass"
                        wire:model.live.debounce.300ms="search"
                        placeholder="{{ __('Search by employee name or email...') }}"
                        class="w-72"
                    />
                    <flux:select wire:model.live="leaveTypeFilter" class="w-44">
                        <option value="all">{{ __('All Leave Types') }}</option>
                        @foreach($leaveTypes as $type)
                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                        @endforeach
                    </flux:select>
                    <flux:button variant="ghost" icon="funnel" />
                </div>

                <div class="flex items-center gap-3">
                    <flux:button variant="outline" icon="arrow-down-tray">
                        {{ __('Export Balances') }}
                    </flux:button>
                    <flux:button
                        variant="primary"
                        icon="sparkles"
                        wire:click="generateBalances"
                        wire:loading.attr="disabled"
                    >
                        <span wire:loading.remove>{{ __('Generate Balances') }}</span>
                        <span wire:loading>{{ __('Generating...') }}</span>
                    </flux:button>
                </div>
            </div>

            @if($generationSummary)
                <div class="rounded-lg bg-blue-50 dark:bg-zinc-800/70 border border-blue-100 dark:border-zinc-700 px-5 py-4">
                    <p class="text-sm text-blue-700 dark:text-blue-200">
                        {{ __('Generation complete.') }}
                        <span class="font-medium">{{ __('Created: :created, Updated: :updated, Skipped: :skipped', [
                            'created' => $generationSummary['created'] ?? 0,
                            'updated' => $generationSummary['updated'] ?? 0,
                            'skipped' => $generationSummary['skipped'] ?? 0,
                        ]) }}</span>
                    </p>
                    @if(!empty($skipReasons))
                        <ul class="mt-2 space-y-1 text-sm text-blue-700 dark:text-blue-200 list-disc list-outside pl-5">
                            @foreach($skipReasons as $item)
                                <li>
                                    {{ $item['employee_name'] ?? __('Employee #:id', ['id' => $item['employee_id']]) }}
                                    @switch($item['reason'])
                                        @case('still_in_probation')
                                            — {{ __('still in probation (eligible after :date)', ['date' => $item['eligible_after'] ?? '—']) }}
                                            @break
                                        @case('outside_policy_window')
                                            — {{ __('outside the policy date window') }}
                                            @break
                                        @case('not_permanent_employee')
                                            — {{ __('not eligible (permanent employees only)') }}
                                            @break
                                        @case('missing_joining_date')
                                        @default
                                            — {{ __('missing joining details') }}
                                    @endswitch
                                    @if(!empty($item['leave_type_names']))
                                        <strong> ({{ implode(', ', $item['leave_type_names']) }})</strong>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endif

            <!-- Balances Table -->
            <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 overflow-hidden shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-zinc-50 dark:bg-zinc-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    {{ __('Employee') }}
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    {{ __('Leave Type') }}
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    {{ __('Entitled') }}
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    {{ __('Used') }}
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    {{ __('Pending') }}
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    {{ __('Manual Adj.') }}
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    {{ __('Balance') }}
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    {{ __('Actions') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-zinc-800 divide-y divide-zinc-200 dark:divide-zinc-700">
                            @forelse($groupedBalances as $employeeId => $employeeBalances)
                                @php
                                    $firstBalance = $employeeBalances->first();
                                    $employee = $firstBalance->employee;
                                    $rowCount = $employeeBalances->count();
                                @endphp
                                @foreach($employeeBalances as $index => $balance)
                                    <tr class="hover:bg-zinc-100 dark:hover:bg-zinc-600 transition-colors duration-150">
                                        @if($index === 0)
                                            <td class="px-6 py-6" rowspan="{{ $rowCount }}">
                                                <div class="font-medium text-zinc-900 dark:text-zinc-100">
                                                    {{ optional($employee->user)->name ?? __('Unknown Employee') }}
                                                </div>
                                                <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                                    {{ optional($employee->user)->email ?? '—' }}
                                                </div>
                                            </td>
                                        @endif
                                        <td class="px-6 py-6 whitespace-nowrap">
                                            <div class="text-sm text-zinc-900 dark:text-zinc-100">
                                                {{ optional($balance->leaveType)->name ?? __('N/A') }}
                                            </div>
                                            <div class="text-xs text-zinc-500 dark:text-zinc-400 uppercase tracking-wide">
                                                {{ optional($balance->leaveType)->code }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-6 whitespace-nowrap text-sm text-zinc-900 dark:text-zinc-100">
                                            {{ number_format($balance->entitled, 1) }}
                                        </td>
                                        <td class="px-6 py-6 whitespace-nowrap text-sm text-red-500 dark:text-red-400">
                                            {{ number_format($balance->used, 1) }}
                                        </td>
                                        <td class="px-6 py-6 whitespace-nowrap text-sm text-amber-500 dark:text-amber-300">
                                            {{ number_format($balance->pending, 1) }}
                                        </td>
                                        <td class="px-6 py-6 whitespace-nowrap text-sm text-blue-500 dark:text-blue-300">
                                            {{ number_format($balance->manual_adjustment, 1) }}
                                        </td>
                                        <td class="px-6 py-6 whitespace-nowrap text-sm font-semibold {{ $balance->balance >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-500 dark:text-red-300' }}">
                                            {{ number_format($balance->balance, 1) }}
                                        </td>
                                        <td class="px-6 py-6 whitespace-nowrap text-sm font-medium">
                                            <div class="flex items-center justify-center">
                                                <flux:dropdown>
                                                    <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                                                    <flux:menu>
                                                        <flux:menu.item icon="adjustments-horizontal" wire:click="openAdjustmentModal({{ $balance->id }})">
                                                            {{ __('Adjust Balance') }}
                                                        </flux:menu.item>
                                                    </flux:menu>
                                                </flux:dropdown>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-12 text-center">
                                        <flux:icon name="chart-bar" class="mx-auto h-12 w-12 text-zinc-400" />
                                        <flux:heading size="sm" class="mt-2 text-zinc-500 dark:text-zinc-400">
                                            {{ __('No leave balance records found') }}
                                        </flux:heading>
                                        <flux:text class="mt-1 text-zinc-500 dark:text-zinc-400">
                                            {{ __('Adjust your filters or import balances to get started.') }}
                                        </flux:text>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($groupedBalances->hasPages())
                    <div class="px-6 py-4 border-t border-zinc-200 dark:border-zinc-700">
                        {{ $groupedBalances->links() }}
                    </div>
                @endif
            </div>
        </div>
    </x-system-management.layout>

    <!-- Adjustment Flyout -->
    <flux:modal
        wire:model="showAdjustmentModal"
        variant="flyout"
        class="w-[28rem] lg:w-[32rem]"
        :title="__('Manual Balance Adjustment')"
    >
        <div class="space-y-6 pb-6">
            <div class="rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 px-4 py-3">
                <p class="text-sm text-zinc-600 dark:text-zinc-200">
                    {{ __('Add or deduct leave days from the selected employee. A transaction entry will be added to the leave ledger for auditing.') }}
                </p>
            </div>

            @if($selectedBalanceSummary)
                <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-4 py-4 space-y-4">
                    <div>
                        <p class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">
                            {{ $selectedBalanceSummary['employee_name'] ?? __('Unknown Employee') }}
                        </p>
                        @if(!empty($selectedBalanceSummary['employee_email']))
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                {{ $selectedBalanceSummary['employee_email'] }}
                            </p>
                        @endif
                    </div>

                    <div class="grid grid-cols-2 gap-4 text-sm text-zinc-600 dark:text-zinc-300">
                        <div>
                            <p class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">{{ __('Leave Type') }}</p>
                            <p class="font-medium text-zinc-900 dark:text-zinc-100">
                                {{ $selectedBalanceSummary['leave_type_name'] ?? __('N/A') }}
                            </p>
                            @if(!empty($selectedBalanceSummary['leave_type_code']))
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                    {{ $selectedBalanceSummary['leave_type_code'] }}
                                </p>
                            @endif
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">{{ __('Current Balance') }}</p>
                            <p class="font-semibold text-green-600 dark:text-emerald-300">
                                {{ number_format($selectedBalanceSummary['balance'] ?? 0, 1) }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">{{ __('Entitled') }}</p>
                            <p class="font-medium text-zinc-900 dark:text-zinc-100">
                                {{ number_format($selectedBalanceSummary['entitled'] ?? 0, 1) }}
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <flux:input
                type="number"
                step="0.5"
                label="{{ __('Adjustment Amount') }}"
                helper-text="{{ __('Positive values add leave, negative values deduct.') }}"
                wire:model.defer="adjustmentForm.amount"
                required
            />

            <flux:textarea
                rows="3"
                label="{{ __('Notes (optional)') }}"
                placeholder="{{ __('Mention the reason or reference for this adjustment...') }}"
                wire:model.defer="adjustmentForm.notes"
            />
        </div>

        <div class="flex items-center justify-between gap-3 sticky bottom-0 pt-4 pb-2 bg-white/95 dark:bg-zinc-900/95 backdrop-blur border-t border-zinc-200 dark:border-zinc-700">
            <flux:button variant="ghost" wire:click="closeAdjustmentFlyout">
                {{ __('Cancel') }}
            </flux:button>
            <flux:button variant="primary" wire:click="applyAdjustment">
                {{ __('Apply Adjustment') }}
            </flux:button>
        </div>
    </flux:modal>
</section>
