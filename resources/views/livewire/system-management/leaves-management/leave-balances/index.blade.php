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
                </div>
            </div>

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
                            @forelse($balances as $balance)
                                <tr class="hover:bg-zinc-100 dark:hover:bg-zinc-600 transition-colors duration-150">
                                    <td class="px-6 py-6">
                                        <div class="font-medium text-zinc-900 dark:text-zinc-100">
                                            {{ optional($balance->employee->user)->name ?? __('Unknown Employee') }}
                                        </div>
                                        <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                            {{ optional($balance->employee->user)->email ?? '—' }}
                                        </div>
                                    </td>
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

                @if($balances->hasPages())
                    <div class="px-6 py-4 border-t border-zinc-200 dark:border-zinc-700">
                        {{ $balances->links() }}
                    </div>
                @endif
            </div>
        </div>
    </x-system-management.layout>

    <!-- Adjustment Modal -->
    <flux:modal wire:model="showAdjustmentModal" icon="adjustments-horizontal" :title="__('Manual Balance Adjustment')" size="xl">
        <div class="space-y-6">
            <div class="rounded-lg bg-zinc-50 dark:bg-zinc-800/60 border border-zinc-200 dark:border-zinc-700 px-4 py-3">
                <p class="text-sm text-zinc-600 dark:text-zinc-300">
                    {{ __('Add or deduct leave days from the selected employee. A transaction entry will be added to the leave ledger for auditing.') }}
                </p>
            </div>

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

        <x-slot name="footer">
            <div class="flex items-center justify-between w-full">
                <flux:button variant="ghost" wire:click="$set('showAdjustmentModal', false)">
                    {{ __('Cancel') }}
                </flux:button>
                <flux:button variant="primary" wire:click="applyAdjustment">
                    {{ __('Apply Adjustment') }}
                </flux:button>
            </div>
        </x-slot>
    </flux:modal>
</section>
