<section class="w-full">
    @include('partials.payroll-heading')
    
    <x-payroll.layout :heading="__('Tax Management')" :subheading="__('Manage employee tax records and calculations')">
        <!-- Filter and Actions -->
        <div class="my-6 w-full space-y-4">
            <div class="flex flex-col sm:flex-row gap-4">
                <!-- Tax Year Filter -->
                <div class="sm:w-48">
                    <flux:field>
                        <flux:label>{{ __('Tax Year') }}</flux:label>
                        <flux:select wire:model.live="taxYear">
                            @foreach($this->taxYearOptions as $opt)
                                <option value="{{ $opt['value'] }}">{{ $opt['label'] }}</option>
                            @endforeach
                        </flux:select>
                    </flux:field>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <flux:button variant="primary" icon="plus" wire:click="openAddTaxModal">
                        {{ __('Add Tax Record') }}
                    </flux:button>
                    
                    <flux:button variant="outline" icon="document-text" wire:click="generateTaxReport">
                        {{ __('Generate Tax Report') }}
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

        <!-- Tax Records Table -->
        <div class="mt-8">
            @if($taxRecords->count() > 0)
                <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 overflow-hidden shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-zinc-50 dark:bg-zinc-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                        <button wire:click="sort('salary_from')" class="flex items-center gap-1 hover:text-zinc-700 dark:hover:text-zinc-200">
                                            {{ __('Salary From - To') }}
                                            @if($sortBy === 'salary_from' || $sortBy === 'salary_to')
                                                <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="w-4 h-4" />
                                            @endif
                                        </button>
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                        <button wire:click="sort('tax')" class="flex items-center gap-1 hover:text-zinc-700 dark:hover:text-zinc-200">
                                            {{ __('Tax') }}
                                            @if($sortBy === 'tax')
                                                <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="w-4 h-4" />
                                            @endif
                                        </button>
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                        <button wire:click="sort('exempted_tax_amount')" class="flex items-center gap-1 hover:text-zinc-700 dark:hover:text-zinc-200">
                                            {{ __('Exempted Tax Amount') }}
                                            @if($sortBy === 'exempted_tax_amount')
                                                <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="w-4 h-4" />
                                            @endif
                                        </button>
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                        <button wire:click="sort('additional_tax_amount')" class="flex items-center gap-1 hover:text-zinc-700 dark:hover:text-zinc-200">
                                            {{ __('Additional Tax Amount') }}
                                            @if($sortBy === 'additional_tax_amount')
                                                <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="w-4 h-4" />
                                            @endif
                                        </button>
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                        <button wire:click="sort('tax_year')" class="flex items-center gap-1 hover:text-zinc-700 dark:hover:text-zinc-200">
                                            {{ __('Tax Year') }}
                                            @if($sortBy === 'tax_year')
                                                <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="w-4 h-4" />
                                            @endif
                                        </button>
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                        {{ __('ACTIONS') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-zinc-800 divide-y divide-zinc-200 dark:divide-zinc-700">
                                @foreach ($taxRecords as $record)
                                    @php
                                        $salaryFrom = (float) ($record->salary_from ?? 0);
                                        $salaryTo = (float) ($record->salary_to ?? 0);
                                        $taxVal = (float) ($record->tax ?? 0);
                                        $exempted = (float) ($record->exempted_tax_amount ?? 0);
                                        $additional = (float) ($record->additional_tax_amount ?? 0);
                                    @endphp
                                    <tr class="hover:bg-zinc-100 dark:hover:bg-zinc-600 transition-colors duration-150">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-zinc-900 dark:text-zinc-100">
                                                {{ number_format($salaryFrom, 0) }} - {{ number_format($salaryTo, 0) }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-zinc-900 dark:text-zinc-100">
                                                {{ $taxVal == 0 ? '0' : preg_replace('/\.00$/', '', number_format($taxVal, 2, '.', ',')) }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-zinc-900 dark:text-zinc-100">
                                                {{ $exempted == 0 ? '0' : preg_replace('/\.00$/', '', number_format($exempted, 2, '.', ',')) }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-zinc-900 dark:text-zinc-100">
                                                {{ $additional == 0 ? '0' : preg_replace('/\.00$/', '', number_format($additional, 2, '.', ',')) }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-zinc-900 dark:text-zinc-100">
                                                {{ $record->tax_year_label }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex items-center gap-1">
                                                <flux:dropdown>
                                                    <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                                                    <flux:menu>
                                                        <flux:menu.item icon="eye" wire:click="viewTaxRecord({{ $record->id }})">
                                                            {{ __('View Details') }}
                                                        </flux:menu.item>
                                                        <flux:menu.item icon="pencil" wire:click="editTaxRecord({{ $record->id }})">
                                                            {{ __('Edit') }}
                                                        </flux:menu.item>
                                                        <flux:menu.item icon="arrow-down-tray" wire:click="downloadTaxRecord({{ $record->id }})">
                                                            {{ __('Download PDF') }}
                                                        </flux:menu.item>
                                                        <flux:menu.item icon="trash" wire:click="deleteTaxRecord({{ $record->id }})" wire:confirm="{{ __('Are you sure you want to delete this tax record? This action cannot be undone.') }}" class="text-red-600 dark:text-red-400">
                                                            {{ __('Delete') }}
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
                @if(method_exists($taxRecords, 'hasPages') && $taxRecords->hasPages())
                    <div class="mt-6">
                        {{ $taxRecords->links() }}
                    </div>
                @endif
            @else
                <!-- Empty State -->
                <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <flux:heading size="lg" level="3" class="mt-4 text-zinc-600 dark:text-zinc-400">
                        {{ __('No tax records found') }}
                    </flux:heading>
                    <flux:text class="mt-2 text-zinc-500 dark:text-zinc-500">
                        {{ __('No tax records match your current search criteria.') }}
                    </flux:text>
                </div>
            @endif
        </div>

        <!-- Add Tax Record Flyout -->
        @if($showAddTaxModal)
            <flux:modal variant="flyout" :open="$showAddTaxModal" wire:model="showAddTaxModal">
                <div class="space-y-6">
                    <div>
                        <flux:heading size="lg">{{ __('Add Tax Record') }}</flux:heading>
                    </div>
                    
                    <div class="space-y-4">
                        <div class="grid grid-cols-4 gap-4">
                            <flux:field>
                                <flux:label>{{ __('Start Year') }}</flux:label>
                                <flux:input type="number" min="2000" max="2100" wire:model="addStartYear" placeholder="2025" />
                            </flux:field>
                            <flux:field>
                                <flux:label>{{ __('Start Month') }}</flux:label>
                                <flux:input type="number" min="1" max="12" wire:model="addStartMonth" placeholder="7" />
                            </flux:field>
                            <flux:field>
                                <flux:label>{{ __('End Year') }}</flux:label>
                                <flux:input type="number" min="2000" max="2100" wire:model="addEndYear" placeholder="2026" />
                            </flux:field>
                            <flux:field>
                                <flux:label>{{ __('End Month') }}</flux:label>
                                <flux:input type="number" min="1" max="12" wire:model="addEndMonth" placeholder="6" />
                            </flux:field>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            <flux:field>
                                <flux:label>{{ __('Salary From') }}</flux:label>
                                <flux:input type="number" min="0" step="0.01" placeholder="1" wire:model="salaryFrom" />
                            </flux:field>

                            <flux:field>
                                <flux:label>{{ __('Salary To') }}</flux:label>
                                <flux:input type="number" min="0" step="0.01" placeholder="600000" wire:model="salaryTo" />
                            </flux:field>

                            <flux:field>
                                <flux:label>{{ __('Tax') }}</flux:label>
                                <flux:input type="number" min="0" step="0.01" placeholder="0" wire:model="tax" />
                            </flux:field>

                            <flux:field>
                                <flux:label>{{ __('Exempted Tax Amount') }}</flux:label>
                                <flux:input type="number" min="0" step="0.01" placeholder="0" wire:model="exemptedTaxAmount" />
                            </flux:field>

                            <flux:field>
                                <flux:label>{{ __('Additional Tax Amount') }}</flux:label>
                                <flux:input type="number" min="0" step="0.01" placeholder="0" wire:model="additionalTaxAmount" />
                            </flux:field>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3">
                        <flux:button variant="ghost" wire:click="closeAddTaxModal">{{ __('Cancel') }}</flux:button>
                        <flux:button variant="primary" wire:click="addTaxRecord">{{ __('Add Record') }}</flux:button>
                    </div>
                </div>
            </flux:modal>
        @endif

        <!-- View Tax Record Flyout -->
        @if($showViewTaxModal && $this->selectedTaxRecord)
            @php
                $rec = $this->selectedTaxRecord;
                $fmt = fn($v) => preg_replace('/\.00$/', '', number_format((float) $v, 2, '.', ','));
                $calc = $this->calculatorResults;
            @endphp
            <flux:modal variant="flyout" :open="$showViewTaxModal" wire:model="showViewTaxModal" class="w-[32rem] lg:w-[40rem]">
                <div class="space-y-6">
                    <div>
                        <flux:heading size="lg">{{ __('Tax Record Details') }}</flux:heading>
                        <flux:text class="mt-1 text-zinc-500 dark:text-zinc-400">{{ __('Salary range') }}: {{ number_format((float) $rec->salary_from, 0) }} - {{ number_format((float) $rec->salary_to, 0) }}</flux:text>
                    </div>
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between"><span class="text-zinc-600 dark:text-zinc-400">{{ __('Tax Year') }}</span><span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $rec->tax_year_label }}</span></div>
                        <div class="flex justify-between"><span class="text-zinc-600 dark:text-zinc-400">{{ __('Salary From') }}</span><span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $fmt($rec->salary_from) }}</span></div>
                        <div class="flex justify-between"><span class="text-zinc-600 dark:text-zinc-400">{{ __('Salary To') }}</span><span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $fmt($rec->salary_to) }}</span></div>
                        <div class="flex justify-between"><span class="text-zinc-600 dark:text-zinc-400">{{ __('Tax') }}</span><span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $fmt($rec->tax) }}</span></div>
                        <div class="flex justify-between"><span class="text-zinc-600 dark:text-zinc-400">{{ __('Exempted Tax Amount') }}</span><span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $fmt($rec->exempted_tax_amount) }}</span></div>
                        <div class="flex justify-between"><span class="text-zinc-600 dark:text-zinc-400">{{ __('Additional Tax Amount') }}</span><span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $fmt($rec->additional_tax_amount) }}</span></div>
                    </div>

                    <div class="rounded-lg border border-zinc-200 dark:border-zinc-600 bg-zinc-50 dark:bg-zinc-800 p-4 space-y-2">
                        <flux:heading size="sm" class="text-zinc-700 dark:text-white font-semibold">{{ __('How this slab works') }}</flux:heading>
                        <p class="text-sm text-zinc-600 dark:text-zinc-300">
                            @if((float) $rec->additional_tax_amount > 0)
                                {{ __('For annual income in this range, tax = base tax (Tax) + (annual income − Exempted Tax Amount) × Additional Tax Amount %. Annual income = monthly salary × 12. Payroll uses this to get monthly tax.') }}
                            @else
                                {{ __('This slab applies a fixed tax amount for any annual income in this range. Annual income = monthly salary × 12; monthly deduction = Tax ÷ 12.') }}
                            @endif
                        </p>
                    </div>

                    <div class="rounded-lg border border-zinc-200 dark:border-zinc-600 bg-zinc-50 dark:bg-zinc-800 p-4 space-y-4">
                        <flux:heading size="sm" class="text-zinc-700 dark:text-white font-semibold">{{ __('Salary tax calculator') }}</flux:heading>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <flux:field>
                                <flux:label class="dark:text-zinc-300">{{ __('Monthly salary') }}</flux:label>
                                <flux:input type="number" min="0" step="0.01" wire:model.live="calculatorSalary" placeholder="Example: 50000" class="dark:bg-zinc-700 dark:border-zinc-600 dark:text-white" />
                            </flux:field>
                            <flux:field>
                                <flux:label class="dark:text-zinc-300">{{ __('Tax year') }}</flux:label>
                                <flux:select wire:model.live="calculatorTaxYear" class="dark:bg-zinc-700 dark:border-zinc-600 dark:text-white">
                                    @foreach($this->taxYearOptions as $opt)
                                        @if($opt['value'] !== '')
                                            <option value="{{ $opt['value'] }}">{{ $opt['label'] }}</option>
                                        @endif
                                    @endforeach
                                </flux:select>
                            </flux:field>
                        </div>
                        <div class="space-y-2 text-sm border-t border-zinc-200 dark:border-zinc-600 pt-3">
                            <div class="flex justify-between"><span class="text-zinc-600 dark:text-zinc-300">{{ __('Monthly Income') }}</span><span class="font-medium text-zinc-900 dark:text-white">{{ $fmt($calc['monthly_income']) }}</span></div>
                            <div class="flex justify-between"><span class="text-zinc-600 dark:text-zinc-300">{{ __('Monthly Tax') }}</span><span class="font-medium text-zinc-900 dark:text-white">{{ $fmt($calc['monthly_tax']) }}</span></div>
                            <div class="flex justify-between"><span class="text-zinc-600 dark:text-zinc-300">{{ __('Salary After Tax') }}</span><span class="font-medium text-zinc-900 dark:text-white">{{ $fmt($calc['salary_after_tax']) }}</span></div>
                            <div class="flex justify-between"><span class="text-zinc-600 dark:text-zinc-300">{{ __('Yearly Income') }}</span><span class="font-medium text-zinc-900 dark:text-white">{{ $fmt($calc['yearly_income']) }}</span></div>
                            <div class="flex justify-between"><span class="text-zinc-600 dark:text-zinc-300">{{ __('Yearly Tax') }}</span><span class="font-medium text-zinc-900 dark:text-white">{{ $fmt($calc['yearly_tax']) }}</span></div>
                            <div class="flex justify-between"><span class="text-zinc-600 dark:text-zinc-300">{{ __('Yearly Income After Tax') }}</span><span class="font-medium text-zinc-900 dark:text-white">{{ $fmt($calc['yearly_after_tax']) }}</span></div>
                        </div>
                    </div>

                    <div class="flex justify-end pt-4">
                        <flux:button variant="ghost" wire:click="closeViewTaxModal">{{ __('Close') }}</flux:button>
                    </div>
                </div>
            </flux:modal>
        @endif

        <!-- Edit Tax Record Flyout -->
        @if($showEditTaxModal)
            <flux:modal variant="flyout" :open="$showEditTaxModal" wire:model="showEditTaxModal" class="w-[32rem] lg:w-[36rem]">
                <div class="space-y-6">
                    <div>
                        <flux:heading size="lg">{{ __('Edit Tax Record') }}</flux:heading>
                        <flux:text class="mt-1 text-zinc-500 dark:text-zinc-400">{{ __('Update tax record details') }}</flux:text>
                    </div>
                    <div class="space-y-4">
                        <div class="grid grid-cols-4 gap-4">
                            <flux:field>
                                <flux:label>{{ __('Start Year') }}</flux:label>
                                <flux:input type="number" min="2000" max="2100" wire:model="editStartYear" />
                            </flux:field>
                            <flux:field>
                                <flux:label>{{ __('Start Moith') }}</flux:label>
                                <flux:input type="number" min="1" max="12" wire:model="editStartMonth" />
                            </flux:field>
                            <flux:field>
                                <flux:label>{{ __('End Year') }}</flux:label>
                                <flux:input type="number" min="2000" max="2100" wire:model="editEndYear" />
                            </flux:field>
                            <flux:field>
                                <flux:label>{{ __('End Month') }}</flux:label>
                                <flux:input type="number" min="1" max="12" wire:model="editEndMonth" />
                            </flux:field>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            <flux:field>
                                <flux:label>{{ __('Salary From') }}</flux:label>
                                <flux:input type="number" min="0" step="0.01" wire:model="editSalaryFrom" />
                            </flux:field>
                            <flux:field>
                                <flux:label>{{ __('Salary To') }}</flux:label>
                                <flux:input type="number" min="0" step="0.01" wire:model="editSalaryTo" />
                            </flux:field>
                            <flux:field>
                                <flux:label>{{ __('Tax') }}</flux:label>
                                <flux:input type="number" min="0" step="0.01" wire:model="editTax" />
                            </flux:field>
                            <flux:field>
                                <flux:label>{{ __('Exempted Tax Amount') }}</flux:label>
                                <flux:input type="number" min="0" step="0.01" wire:model="editExemptedTaxAmount" />
                            </flux:field>
                            <flux:field>
                                <flux:label>{{ __('Additional Tax Amount') }}</flux:label>
                                <flux:input type="number" min="0" step="0.01" wire:model="editAdditionalTaxAmount" />
                            </flux:field>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3">
                        <flux:button variant="ghost" wire:click="closeEditTaxModal">{{ __('Cancel') }}</flux:button>
                        <flux:button variant="primary" wire:click="updateTaxRecord">{{ __('Update Record') }}</flux:button>
                    </div>
                </div>
            </flux:modal>
        @endif
    </x-payroll.layout>
</section>