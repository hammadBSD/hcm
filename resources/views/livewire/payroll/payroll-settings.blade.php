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
