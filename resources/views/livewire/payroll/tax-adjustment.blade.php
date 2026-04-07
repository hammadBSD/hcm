<section class="w-full">
    @include('partials.payroll-heading')

    <x-payroll.layout :heading="__('Tax Adjustment')" :subheading="__('Manage employee tax adjustments by effective date')">
        <div class="my-6 w-full space-y-4">
            <div class="flex flex-col sm:flex-row gap-4">
                <div class="flex-1">
                    <flux:input wire:model.live.debounce.300ms="search" :label="__('Search')" type="text" placeholder="Search by name, employee code..." icon="magnifying-glass" />
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

            <div class="flex items-center justify-between">
                <flux:button variant="primary" icon="plus" wire:click="openAddAdjustmentModal">
                    {{ __('Add Adjustment') }}
                </flux:button>
                <flux:button variant="ghost" size="sm" icon="arrow-path" wire:click="$refresh">
                    {{ __('Refresh') }}
                </flux:button>
            </div>
        </div>

        @if (session()->has('message'))
            <flux:callout variant="success" icon="check-circle" dismissible>{{ session('message') }}</flux:callout>
        @endif
        @if (session()->has('error'))
            <flux:callout variant="danger" icon="x-circle" dismissible>{{ session('error') }}</flux:callout>
        @endif

        <div class="mt-8">
            @if($adjustments->count() > 0)
                <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 overflow-hidden shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-zinc-50 dark:bg-zinc-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                        <button wire:click="sort('employee_name')" class="flex items-center gap-1">{{ __('Employee') }}</button>
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">{{ __('Department') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">{{ __('Gross Salary') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                        <button wire:click="sort('adjusted_tax_amount')" class="flex items-center gap-1">{{ __('Adjusted Tax Amount') }}</button>
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                        <button wire:click="sort('effective_from')" class="flex items-center gap-1">{{ __('Effective From') }}</button>
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">{{ __('Notes') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                                @foreach($adjustments as $adj)
                                    @php
                                        $emp = $adj->employee;
                                        $name = $emp ? trim(($emp->first_name ?? '') . ' ' . ($emp->last_name ?? '')) : '—';
                                        $deptValue = $emp ? ($emp->getRelation('department') ?? null) : null;
                                        $dept = is_object($deptValue) && isset($deptValue->title)
                                            ? $deptValue->title
                                            : ((is_string($emp->department ?? null) && trim((string) $emp->department) !== '') ? trim((string) $emp->department) : 'N/A');
                                        $salary = $emp ? ($emp->getRelation('salaryLegalCompliance') ?? null) : null;
                                        $grossSalary = $salary ? (float) ($salary->gross_salary ?? ((float) ($salary->basic_salary ?? 0) + (float) ($salary->allowances ?? 0))) : 0.0;
                                    @endphp
                                    <tr>
                                        <td class="px-6 py-4 text-sm text-zinc-900 dark:text-zinc-100">{{ $name }} ({{ $emp->employee_code ?? '—' }})</td>
                                        <td class="px-6 py-4 text-sm text-zinc-700 dark:text-zinc-300">{{ $dept }}</td>
                                        <td class="px-6 py-4 text-sm text-zinc-700 dark:text-zinc-300">{{ preg_replace('/\.00$/', '', number_format($grossSalary, 2, '.', ',')) }}</td>
                                        <td class="px-6 py-4 text-sm text-zinc-700 dark:text-zinc-300">{{ preg_replace('/\.00$/', '', number_format((float) $adj->adjusted_tax_amount, 2, '.', ',')) }}</td>
                                        <td class="px-6 py-4 text-sm text-zinc-700 dark:text-zinc-300">{{ $adj->effective_from ? $adj->effective_from->format('M d, Y') : '—' }}</td>
                                        <td class="px-6 py-4 text-sm text-zinc-700 dark:text-zinc-300">{{ $adj->notes ?: '—' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <flux:dropdown>
                                                <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                                                <flux:menu>
                                                    <flux:menu.item icon="eye" wire:click="viewAdjustment({{ $adj->id }})">{{ __('View') }}</flux:menu.item>
                                                    <flux:menu.item icon="pencil" wire:click="editAdjustment({{ $adj->id }})">{{ __('Edit') }}</flux:menu.item>
                                                    <flux:menu.item icon="trash" wire:click="deleteAdjustment({{ $adj->id }})" wire:confirm="{{ __('Are you sure you want to delete this tax adjustment?') }}" class="text-red-600 dark:text-red-400">{{ __('Delete') }}</flux:menu.item>
                                                </flux:menu>
                                            </flux:dropdown>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @if(method_exists($adjustments, 'hasPages') && $adjustments->hasPages())
                    <div class="mt-6">{{ $adjustments->links() }}</div>
                @endif
            @else
                <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-12 text-center text-zinc-500 dark:text-zinc-400">
                    {{ __('No tax adjustments found.') }}
                </div>
            @endif
        </div>

        @if($showAddAdjustmentModal)
            <flux:modal variant="flyout" :open="$showAddAdjustmentModal" wire:model="showAddAdjustmentModal">
                <div class="space-y-5">
                    <flux:heading size="lg">{{ __('Add Tax Adjustment') }}</flux:heading>
                    <flux:field>
                        <flux:label>{{ __('Employee') }}</flux:label>
                        <flux:select wire:model="employeeId">
                            <option value="">{{ __('Select employee') }}</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp['id'] }}">{{ $emp['label'] }}</option>
                            @endforeach
                        </flux:select>
                    </flux:field>
                    <flux:field>
                        <flux:label>{{ __('Adjusted Tax Amount') }}</flux:label>
                        <flux:input type="number" step="0.01" wire:model="adjustedTaxAmount" placeholder="0.00" />
                    </flux:field>
                    <flux:field>
                        <flux:label>{{ __('Effective From') }}</flux:label>
                        <flux:input type="date" wire:model="effectiveFrom" />
                    </flux:field>
                    <flux:field>
                        <flux:label>{{ __('Notes') }}</flux:label>
                        <flux:textarea wire:model="notes" placeholder="{{ __('Optional notes') }}"></flux:textarea>
                    </flux:field>
                    <div class="flex justify-end gap-3">
                        <flux:button variant="ghost" wire:click="closeAddAdjustmentModal">{{ __('Cancel') }}</flux:button>
                        <flux:button variant="primary" wire:click="addAdjustment">{{ __('Save') }}</flux:button>
                    </div>
                </div>
            </flux:modal>
        @endif

        @if($showViewAdjustmentModal && $this->selectedAdjustment)
            @php
                $rec = $this->selectedAdjustment;
                $emp = $rec->employee;
                $name = $emp ? trim(($emp->first_name ?? '') . ' ' . ($emp->last_name ?? '')) : '—';
                $deptValue = $emp ? ($emp->getRelation('department') ?? null) : null;
                $dept = is_object($deptValue) && isset($deptValue->title)
                    ? $deptValue->title
                    : ((is_string($emp->department ?? null) && trim((string) $emp->department) !== '') ? trim((string) $emp->department) : 'N/A');
            @endphp
            <flux:modal variant="flyout" :open="$showViewAdjustmentModal" wire:model="showViewAdjustmentModal">
                <div class="space-y-4">
                    <flux:heading size="lg">{{ __('Tax Adjustment Details') }}</flux:heading>
                    <div class="text-sm space-y-2">
                        <div class="flex justify-between"><span>{{ __('Employee') }}</span><span class="font-medium">{{ $name }} ({{ $emp->employee_code ?? '—' }})</span></div>
                        <div class="flex justify-between"><span>{{ __('Department') }}</span><span class="font-medium">{{ $dept }}</span></div>
                        <div class="flex justify-between"><span>{{ __('Adjusted Tax Amount') }}</span><span class="font-medium">{{ preg_replace('/\.00$/', '', number_format((float) $rec->adjusted_tax_amount, 2, '.', ',')) }}</span></div>
                        <div class="flex justify-between"><span>{{ __('Effective From') }}</span><span class="font-medium">{{ $rec->effective_from ? $rec->effective_from->format('M d, Y') : '—' }}</span></div>
                        <div class="flex justify-between"><span>{{ __('Notes') }}</span><span class="font-medium">{{ $rec->notes ?: '—' }}</span></div>
                    </div>
                    <div class="flex justify-end">
                        <flux:button variant="ghost" wire:click="closeViewAdjustmentModal">{{ __('Close') }}</flux:button>
                    </div>
                </div>
            </flux:modal>
        @endif

        @if($showEditAdjustmentModal)
            <flux:modal variant="flyout" :open="$showEditAdjustmentModal" wire:model="showEditAdjustmentModal">
                <div class="space-y-5">
                    <flux:heading size="lg">{{ __('Edit Tax Adjustment') }}</flux:heading>
                    <flux:field>
                        <flux:label>{{ __('Employee') }}</flux:label>
                        <flux:select wire:model="editEmployeeId">
                            <option value="">{{ __('Select employee') }}</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp['id'] }}">{{ $emp['label'] }}</option>
                            @endforeach
                        </flux:select>
                    </flux:field>
                    <flux:field>
                        <flux:label>{{ __('Adjusted Tax Amount') }}</flux:label>
                        <flux:input type="number" step="0.01" wire:model="editAdjustedTaxAmount" />
                    </flux:field>
                    <flux:field>
                        <flux:label>{{ __('Effective From') }}</flux:label>
                        <flux:input type="date" wire:model="editEffectiveFrom" />
                    </flux:field>
                    <flux:field>
                        <flux:label>{{ __('Notes') }}</flux:label>
                        <flux:textarea wire:model="editNotes"></flux:textarea>
                    </flux:field>
                    <div class="flex justify-end gap-3">
                        <flux:button variant="ghost" wire:click="closeEditAdjustmentModal">{{ __('Cancel') }}</flux:button>
                        <flux:button variant="primary" wire:click="updateAdjustment">{{ __('Update') }}</flux:button>
                    </div>
                </div>
            </flux:modal>
        @endif
    </x-payroll.layout>
</section>
