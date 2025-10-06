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
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <flux:field>
                            <flux:label>{{ __('Tax Percentage (%)') }}</flux:label>
                            <flux:input type="number" step="0.1" wire:model.live="settings.tax_percentage" />
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('Provident Fund Percentage (%)') }}</flux:label>
                            <flux:input type="number" step="0.1" wire:model.live="settings.provident_fund_percentage" />
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
