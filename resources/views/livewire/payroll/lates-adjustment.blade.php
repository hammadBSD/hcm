<section class="w-full">
    @include('partials.payroll-heading')

    <x-payroll.layout :heading="__('Lates Adjustment')" :subheading="__('Reduce late salary-day deductions for employees who exceeded the allowed lates')">
        <div class="my-6 w-full space-y-4">
            <div class="flex flex-col sm:flex-row gap-4">
                <div class="flex-1">
                    <flux:input wire:model.live.debounce.300ms="search" :label="__('Search')" type="text" placeholder="{{ __('Search by name, employee code...') }}" icon="magnifying-glass" />
                </div>
                <div class="sm:w-56">
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
                <div class="sm:w-48">
                    <flux:field>
                        <flux:label>{{ __('Month') }}</flux:label>
                        <flux:input type="month" wire:model.live="selectedMonth" />
                    </flux:field>
                </div>
            </div>

            @if($ruleSummary)
                <flux:callout variant="secondary" icon="information-circle">
                    <strong>{{ $monthLabel }}:</strong> {{ $ruleSummary }}
                </flux:callout>
            @endif

            @if($isMonthLocked)
                <flux:callout variant="warning" icon="lock-closed">
                    {{ __('This month is locked. Late adjustments are read-only.') }}
                </flux:callout>
            @endif
        </div>

        @if (session()->has('message'))
            <flux:callout variant="success" icon="check-circle" dismissible>{{ session('message') }}</flux:callout>
        @endif
        @if (session()->has('error'))
            <flux:callout variant="danger" icon="x-circle" dismissible>{{ session('error') }}</flux:callout>
        @endif

        <div class="mt-4">
            @if(count($rows) > 0)
                <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 overflow-hidden shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-zinc-50 dark:bg-zinc-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">{{ __('Employee') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">{{ __('Department') }}</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">{{ __('Total Lates') }}</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">{{ __('Calculated Days') }}</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">{{ __('Waived Days') }}</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">{{ __('Days to Deduct') }}</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">{{ __('Deduction Amount') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                                @foreach($rows as $row)
                                    <tr class="{{ $row['has_adjustment'] ? 'bg-amber-50/60 dark:bg-amber-950/20' : '' }}">
                                        <td class="px-6 py-4 text-sm text-zinc-900 dark:text-zinc-100">
                                            {{ $row['employee_name'] }}
                                            <span class="text-zinc-500 dark:text-zinc-400">({{ $row['employee_code'] }})</span>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-zinc-700 dark:text-zinc-300">{{ $row['department'] }}</td>
                                        <td class="px-6 py-4 text-sm text-center text-amber-600 dark:text-amber-400 font-medium">{{ $row['late_days'] }}</td>
                                        <td class="px-6 py-4 text-sm text-center text-zinc-700 dark:text-zinc-300">{{ $row['calculated_deduction_late_days'] }}</td>
                                        <td class="px-6 py-4 text-sm text-center font-semibold {{ $row['has_adjustment'] ? 'text-blue-600 dark:text-blue-400' : 'text-zinc-700 dark:text-zinc-300' }}">
                                            {{ $row['waived_deduction_late_days'] }}
                                            @if($row['has_adjustment'])
                                                <span class="block text-xs font-normal text-zinc-500 dark:text-zinc-400">{{ __('waived') }}</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-sm text-center text-red-600 dark:text-red-400 font-medium">{{ $row['final_deduction_late_days'] }}</td>
                                        <td class="px-6 py-4 text-sm text-right text-zinc-700 dark:text-zinc-300">
                                            {{ number_format($row['final_deduction_late_amount'], 2) }}
                                            @if($row['has_adjustment'] && $row['final_deduction_late_amount'] < $row['calculated_deduction_late_amount'])
                                                <span class="block text-xs text-zinc-500 dark:text-zinc-400 line-through">{{ number_format($row['calculated_deduction_late_amount'], 2) }}</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <flux:dropdown>
                                                <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                                                <flux:menu>
                                                    <flux:menu.item icon="pencil" wire:click="openEditFlyout({{ $row['employee_id'] }})" :disabled="$isMonthLocked">
                                                        {{ __('Adjust') }}
                                                    </flux:menu.item>
                                                    @if($row['has_adjustment'])
                                                        <flux:menu.item
                                                            icon="arrow-path"
                                                            wire:click="removeAdjustment({{ $row['employee_id'] }})"
                                                            wire:confirm="{{ __('Remove this adjustment and use the calculated deduction days?') }}"
                                                            :disabled="$isMonthLocked"
                                                            class="text-amber-600 dark:text-amber-400"
                                                        >
                                                            {{ __('Reset to calculated') }}
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
                <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-12 text-center text-zinc-500 dark:text-zinc-400">
                    {{ __('No employees with late salary deductions for this month.') }}
                    <p class="mt-2 text-sm">{{ __('Only employees at or above the late deduction threshold who are not exempt from lates appear here.') }}</p>
                </div>
            @endif
        </div>

        @if($showEditFlyout && $this->editingRow)
            @php $row = $this->editingRow; @endphp
            <flux:modal variant="flyout" wire:model="showEditFlyout" class="w-[32rem] lg:w-[36rem]">
                <div class="space-y-6">
                    <div>
                        <flux:heading size="lg">{{ __('Adjust Late Deduction') }}</flux:heading>
                        <flux:text class="mt-1 text-zinc-500 dark:text-zinc-400">
                            {{ $row['employee_name'] }} ({{ $row['employee_code'] }}) · {{ $monthLabel }}
                        </flux:text>
                    </div>

                    <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/70 p-4 text-sm space-y-2">
                        <div class="flex justify-between">
                            <span class="text-zinc-500 dark:text-zinc-400">{{ __('Total lates this month') }}</span>
                            <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $row['late_days'] }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-zinc-500 dark:text-zinc-400">{{ __('Calculated salary days to deduct') }}</span>
                            <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $row['calculated_deduction_late_days'] }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-zinc-500 dark:text-zinc-400">{{ __('Calculated deduction amount') }}</span>
                            <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ number_format($row['calculated_deduction_late_amount'], 2) }}</span>
                        </div>
                    </div>

                    <flux:field>
                        <flux:label>{{ __('Salary days to waive') }}</flux:label>
                        <flux:input type="number" min="0" max="{{ $editCalculatedDays }}" step="1" wire:model="editWaivedDays" />
                        <flux:description>
                            {{ __('Enter days to waive from the calculated deduction (0 to :max). Example: waive 2 of :max calculated days to deduct only :remaining.', [
                                'max' => $editCalculatedDays,
                                'remaining' => max(0, (int) $editCalculatedDays - (int) ($editWaivedDays !== '' ? $editWaivedDays : 0)),
                            ]) }}
                        </flux:description>
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('Notes') }}</flux:label>
                        <flux:textarea wire:model="editNotes" placeholder="{{ __('Reason for adjustment (optional)') }}"></flux:textarea>
                    </flux:field>

                    <div class="flex justify-end gap-3">
                        <flux:button variant="ghost" wire:click="closeEditFlyout" kbd="esc">{{ __('Cancel') }}</flux:button>
                        <flux:button variant="primary" wire:click="saveAdjustment">{{ __('Save') }}</flux:button>
                    </div>
                </div>
            </flux:modal>
        @endif
    </x-payroll.layout>
</section>
