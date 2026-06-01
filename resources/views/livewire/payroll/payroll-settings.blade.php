<section class="w-full">
    @include('partials.payroll-heading')
    
    <x-payroll.layout :heading="__('Payroll Settings')" :subheading="__('Configure payroll settings and preferences')">
        <!-- Flash Messages -->
        @if (session()->has('message'))
            <flux:callout variant="success" icon="check-circle" dismissible>
                {{ session('message') }}
            </flux:callout>
        @endif

        <div class="space-y-6">
            <!-- General Settings -->
            <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700">
                <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ __('General Settings') }}</h3>
                </div>
                <div class="p-6 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <flux:field>
                            <flux:label>{{ __('Payroll Frequency') }}</flux:label>
                            <flux:select wire:model.live="settings.payroll_frequency">
                                <option value="weekly">{{ __('Weekly') }}</option>
                                <option value="bi-weekly">{{ __('Bi-weekly') }}</option>
                                <option value="monthly">{{ __('Monthly') }}</option>
                            </flux:select>
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('Payroll Day') }}</flux:label>
                            <flux:input type="number" min="1" max="31" wire:model.live="settings.payroll_day" />
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('Overtime Rate (Multiplier)') }}</flux:label>
                            <flux:input type="number" step="0.1" wire:model.live="settings.overtime_rate" />
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('Allowance Percentage (%)') }}</flux:label>
                            <flux:input type="number" step="0.1" wire:model.live="settings.allowance_percentage" />
                        </flux:field>
                    </div>
                </div>
            </div>

            <!-- Tax Settings -->
            <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700">
                <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Tax Settings') }}</h3>
                </div>
                <div class="p-6 space-y-6">
                    <flux:field>
                        <flux:label>{{ __('Tax calculation method') }}</flux:label>
                        <flux:radio.group wire:model.live="settings.tax_calculation_method">
                            <flux:radio value="percentage" :label="__('Use tax percentage (below)')" />
                            <flux:radio value="tax_slabs" :label="__('Use Tax Management slabs')" />
                        </flux:radio.group>
                        <flux:description>{{ __('When using Tax Management slabs, the percentage below is ignored for payroll and reports.') }}</flux:description>
                    </flux:field>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <flux:field>
                            <flux:label>{{ __('Tax Percentage (%)') }}</flux:label>
                            <flux:input type="number" step="0.1" wire:model.live="settings.tax_percentage" />
                            <flux:description>{{ __('Used only when "Use tax percentage" is selected.') }}</flux:description>
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('Tax Exempt Percentage from Salary (%)') }}</flux:label>
                            <flux:input type="number" min="0" max="100" step="0.1" wire:model.live="settings.tax_exempt_percentage_from_salary" placeholder="0" />
                            <flux:description>{{ __('If set, this percentage of salary is exempt before tax is calculated. E.g. 10 means tax is calculated on 90% of salary.') }}</flux:description>
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('Provident Fund Percentage (%)') }}</flux:label>
                            <flux:input type="number" step="0.1" wire:model.live="settings.provident_fund_percentage" />
                        </flux:field>
                    </div>
                </div>
            </div>

            <!-- Deduction Settings -->
            <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700">
                <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Deduction Settings') }}</h3>
                </div>
                <div class="p-6 space-y-6">
                    <flux:field>
                        <flux:label>{{ __('Per-day deduction for absent') }}</flux:label>
                        <flux:radio.group wire:model.live="settings.absent_deduction_use_formula">
                            <flux:radio :value="true" :label="__('Use formula: gross salary ÷ working days (per-day salary deducted per absent day)')" />
                            <flux:radio :value="false" :label="__('Use fixed amount per absent day')" />
                        </flux:radio.group>
                        <flux:description>{{ __('When formula is enabled, deduction = (gross salary ÷ working days) × absent days. Working days come from attendance (e.g. 20 for the month).') }}</flux:description>
                    </flux:field>
                    @if(empty($settings['absent_deduction_use_formula']))
                    <flux:field>
                        <flux:label>{{ __('Fixed amount per absent day') }}</flux:label>
                        <flux:input type="number" step="0.01" min="0" wire:model.live="settings.per_day_absent_deduction" />
                    </flux:field>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <flux:field>
                            <flux:label>{{ __('Short hours threshold (hours)') }}</flux:label>
                            <flux:input type="number" step="0.5" min="0" wire:model.live="settings.short_hours_threshold" />
                            <flux:description>{{ __('Deduction for short hours starts only when short hours exceed this (e.g. 9).') }}</flux:description>
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('When short hours exceed threshold') }}</flux:label>
                            <flux:select
                                wire:model.live="settings.short_hours_deduction_policy"
                                :disabled="(float)($settings['short_hours_threshold'] ?? 0) <= 0"
                            >
                                <option value="excess_only">{{ __('Deduct only hours over threshold') }}</option>
                                <option value="full_when_over_threshold">{{ __('Deduct full short hours when over threshold') }}</option>
                            </flux:select>
                            <flux:description>{{ __('When threshold is 0, every short hour is deducted. When threshold &gt; 0: choose whether to deduct only the excess over threshold or the full short hours once exceeded.') }}</flux:description>
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('Hours per day (for short-hours formula)') }}</flux:label>
                            <flux:input type="number" step="0.5" min="0" wire:model.live="settings.hours_per_day" />
                            <flux:description>{{ __('Hourly rate = gross salary ÷ working days ÷ this value (e.g. 9). Deduction = hourly rate × excess short hours; minutes are included.') }}</flux:description>
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('Short hours deduction per hour (optional override)') }}</flux:label>
                            <flux:input type="number" step="0.01" min="0" wire:model.live="settings.short_hours_deduction_per_hour" placeholder="{{ __('Leave empty to use formula above') }}" />
                            <flux:description>{{ __('When set, this fixed amount is deducted per hour for excess short hours instead of the formula.') }}</flux:description>
                        </flux:field>
                    </div>

                    <div class="border-t border-zinc-200 dark:border-zinc-700 pt-6 space-y-4">
                        <h4 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Late deduction') }}</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <flux:field>
                                <flux:label>{{ __('Lates per 1 day salary deduction') }}</flux:label>
                                <flux:input type="number" step="1" min="1" wire:model="late_deduction_lates_per_day" />
                                <flux:description>
                                    @if(!empty($late_deduction_lates_per_day) && (int) $late_deduction_lates_per_day > 0)
                                        {{ __('On the :nth late, 1 day of salary is deducted (e.g. :n lates = :days day(s); :allowed lates allowed with no deduction).', [
                                            'nth' => (int) $late_deduction_lates_per_day,
                                            'n' => (int) $late_deduction_lates_per_day * 2,
                                            'days' => 2,
                                            'allowed' => (int) $late_deduction_lates_per_day - 1,
                                        ]) }}
                                    @else
                                        {{ __('Enter how many lates trigger 1 day of salary deduction (e.g. 5 → 10 lates deduct 2 days).') }}
                                    @endif
                                </flux:description>
                                <flux:error name="late_deduction_lates_per_day" />
                            </flux:field>
                            <flux:field>
                                <flux:label>{{ __('Effective from') }}</flux:label>
                                <flux:input type="date" wire:model="late_deduction_effective_from" />
                                <flux:description>{{ __('Payroll months before this date use the previous rule. Changing this creates a new history entry.') }}</flux:description>
                                <flux:error name="late_deduction_effective_from" />
                            </flux:field>
                        </div>
                        <div class="flex justify-end">
                            <flux:button type="button" wire:click="saveLateDeductionSetting" variant="primary">
                                {{ __('Save late deduction rule') }}
                            </flux:button>
                        </div>
                        @if(isset($lateDeductionHistory) && $lateDeductionHistory->isNotEmpty())
                            <div class="mt-4">
                                <h5 class="text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 mb-2">{{ __('Rule history') }}</h5>
                                <div class="overflow-x-auto rounded-lg border border-zinc-200 dark:border-zinc-700">
                                    <table class="min-w-full text-sm divide-y divide-zinc-200 dark:divide-zinc-700">
                                        <thead class="bg-zinc-50 dark:bg-zinc-900/50">
                                            <tr>
                                                <th class="px-3 py-2 text-left font-medium text-zinc-600 dark:text-zinc-400">{{ __('Lates per day deducted') }}</th>
                                                <th class="px-3 py-2 text-left font-medium text-zinc-600 dark:text-zinc-400">{{ __('Effective from') }}</th>
                                                <th class="px-3 py-2 text-left font-medium text-zinc-600 dark:text-zinc-400">{{ __('Saved at') }}</th>
                                                <th class="px-3 py-2 text-left font-medium text-zinc-600 dark:text-zinc-400">{{ __('By') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700 bg-white dark:bg-zinc-800">
                                            @foreach($lateDeductionHistory as $entry)
                                                <tr>
                                                    <td class="px-3 py-2 text-zinc-900 dark:text-zinc-100">{{ $entry->lates_per_day_deduction }}</td>
                                                    <td class="px-3 py-2 text-zinc-700 dark:text-zinc-300">{{ $entry->effective_from->format('Y-m-d') }}</td>
                                                    <td class="px-3 py-2 text-zinc-700 dark:text-zinc-300">{{ $entry->created_at?->format('Y-m-d H:i') }}</td>
                                                    <td class="px-3 py-2 text-zinc-700 dark:text-zinc-300">{{ $entry->creator?->name ?? '—' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Automation Settings -->
            <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700">
                <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Automation Settings') }}</h3>
                </div>
                <div class="p-6 space-y-6">
                    <div class="space-y-4">
                        <flux:switch 
                            wire:model.live="settings.auto_process" 
                            label="{{ __('Auto Process Payroll') }}" 
                            description="{{ __('Automatically process payroll on scheduled dates') }}" 
                        />
                        <flux:separator variant="subtle" />
                        
                        <flux:switch 
                            wire:model.live="settings.email_payslips" 
                            label="{{ __('Email Payslips') }}" 
                            description="{{ __('Automatically send payslips to employees via email') }}" 
                        />
                        <flux:separator variant="subtle" />
                        
                        <flux:switch 
                            wire:model.live="settings.backup_payroll" 
                            label="{{ __('Backup Payroll Data') }}" 
                            description="{{ __('Automatically backup payroll data for security and compliance') }}" 
                        />
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end">
                <div class="flex items-center gap-3">
                    <flux:button variant="outline" wire:click="resetToDefaults">
                        {{ __('Reset to Defaults') }}
                    </flux:button>
                    
                    <flux:button variant="primary" wire:click="saveAllSettings">
                        {{ __('Save All Settings') }}
                    </flux:button>
                </div>
            </div>
        </div>
    </x-payroll.layout>
</section>
