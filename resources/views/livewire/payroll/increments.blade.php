<section class="w-full">
    @include('partials.payroll-heading')

    <x-payroll.layout :heading="__('Increments')" :subheading="__('View and manage employee increment records')">
        <!-- Search and Filter -->
        <div class="my-6 w-full space-y-4">
            <div class="flex flex-col sm:flex-row gap-4">
                <div class="flex-1">
                    <flux:input
                        wire:model.live.debounce.300ms="search"
                        :label="__('Search')"
                        type="text"
                        placeholder="{{ __('Search by name, employee ID...') }}"
                        icon="magnifying-glass"
                    />
                </div>
                <div class="sm:w-64">
                    <flux:field>
                        <flux:label>{{ __('Department') }}</flux:label>
                        <flux:select wire:model.live="selectedDepartment">
                            <option value="">{{ __('All Departments') }}</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept }}">{{ $dept }}</option>
                            @endforeach
                        </flux:select>
                    </flux:field>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <flux:button variant="primary" icon="plus" wire:click="openAddIncrementModal">
                    {{ __('Add Increment') }}
                </flux:button>
                <flux:button variant="ghost" size="sm" icon="arrow-path" wire:click="$refresh">
                    {{ __('Refresh') }}
                </flux:button>
            </div>
        </div>

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

        <!-- Increments Table -->
        <div class="mt-8">
            @if($increments->count() > 0)
                <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 overflow-hidden shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-zinc-50 dark:bg-zinc-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">{{ __('Employee') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">{{ __('Type') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">{{ __('No. of increments') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">{{ __('Increment due date') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">{{ __('Last increment date') }}</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">{{ __('Gross before') }}</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">{{ __('Basic before') }}</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">{{ __('Increment amount') }}</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">{{ __('Gross after') }}</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">{{ __('Basic after') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">{{ __('Updated by') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">{{ __('Updated when') }}</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-zinc-800 divide-y divide-zinc-200 dark:divide-zinc-700">
                                @foreach($increments as $inc)
                                    @php
                                        $emp = $inc->employee;
                                        $employeeName = $emp ? trim($emp->first_name . ' ' . $emp->last_name) : '—';
                                        $employeeCode = $emp->employee_code ?? '—';
                                        $incAmount = (float) $inc->increment_amount;
                                        $grossAfter = $inc->gross_salary_after !== null ? (float) $inc->gross_salary_after : null;
                                        $basicAfter = $inc->basic_salary_after !== null ? (float) $inc->basic_salary_after : null;
                                        $grossBefore = ($grossAfter !== null && $incAmount >= 0) ? $grossAfter - $incAmount : null;
                                        $basicBefore = ($basicAfter !== null && $incAmount >= 0) ? $basicAfter - $incAmount : null;
                                    @endphp
                                    <tr class="hover:bg-zinc-100 dark:hover:bg-zinc-600 transition-colors duration-150">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div>
                                                <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $employeeName }}</div>
                                                <div class="text-sm text-zinc-500 dark:text-zinc-400">{{ $employeeCode }}</div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($inc->for_history)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300">{{ __('History') }}</span>
                                            @else
                                                <span class="text-zinc-500 dark:text-zinc-400 text-sm">—</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-900 dark:text-zinc-100">
                                            {{ $inc->number_of_increments }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-900 dark:text-zinc-100">
                                            {{ $inc->increment_due_date ? $inc->increment_due_date->format('M d, Y') : '—' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-900 dark:text-zinc-100">
                                            {{ $inc->last_increment_date ? $inc->last_increment_date->format('M d, Y') : '—' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-zinc-900 dark:text-zinc-100">
                                            {{ $grossBefore !== null ? preg_replace('/\.00$/', '', number_format($grossBefore, 2, '.', ',')) : '—' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-zinc-900 dark:text-zinc-100">
                                            {{ $basicBefore !== null ? preg_replace('/\.00$/', '', number_format($basicBefore, 2, '.', ',')) : '—' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-zinc-900 dark:text-zinc-100">
                                            {{ preg_replace('/\.00$/', '', number_format((float) $inc->increment_amount, 2, '.', ',')) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-zinc-900 dark:text-zinc-100">
                                            {{ $inc->gross_salary_after !== null ? preg_replace('/\.00$/', '', number_format((float) $inc->gross_salary_after, 2, '.', ',')) : '—' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-zinc-900 dark:text-zinc-100">
                                            {{ $inc->basic_salary_after !== null ? preg_replace('/\.00$/', '', number_format((float) $inc->basic_salary_after, 2, '.', ',')) : '—' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-900 dark:text-zinc-100">
                                            @if($inc->updatedByUser)
                                                {{ $inc->updatedByUser->name ?? '—' }}
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                                            {{ $inc->updated_at ? $inc->updated_at->format('M d, Y H:i') : '—' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <flux:dropdown>
                                                <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                                                <flux:menu>
                                                    <flux:menu.item icon="eye" wire:click="viewIncrement({{ $inc->id }})">
                                                        {{ __('View') }}
                                                    </flux:menu.item>
                                                    <flux:menu.item icon="pencil" wire:click="editIncrement({{ $inc->id }})">
                                                        {{ __('Edit') }}
                                                    </flux:menu.item>
                                                    <flux:menu.item icon="trash" wire:click="deleteIncrement({{ $inc->id }})" wire:confirm="{{ __('Are you sure you want to delete this increment record? This action cannot be undone.') }}" class="text-red-600 dark:text-red-400">
                                                        {{ __('Delete') }}
                                                    </flux:menu.item>
                                                </flux:menu>
                                            </flux:dropdown>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                @if($increments->hasPages())
                    <div class="mt-6">
                        {{ $increments->links() }}
                    </div>
                @endif
            @else
                <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-12 text-center">
                    <div class="w-12 h-12 mx-auto bg-zinc-100 dark:bg-zinc-700 rounded-full flex items-center justify-center mb-4">
                        <flux:icon name="arrow-trending-up" class="w-6 h-6 text-zinc-400" />
                    </div>
                    <flux:heading size="lg" level="3" class="text-zinc-600 dark:text-zinc-400">
                        {{ __('No increment records found') }}
                    </flux:heading>
                    <flux:text class="mt-2 text-zinc-500 dark:text-zinc-500">
                        {{ __('No employee increment records match your search.') }}
                    </flux:text>
                </div>
            @endif
        </div>

        <!-- Add Increment Flyout -->
        @if($showAddIncrementModal)
            <flux:modal variant="flyout" :open="$showAddIncrementModal" wire:model="showAddIncrementModal" class="w-[32rem] lg:w-[36rem]">
                <div class="space-y-6">
                    <div>
                        <flux:heading size="lg">{{ __('Add Increment') }}</flux:heading>
                        <flux:text class="mt-1 text-zinc-500 dark:text-zinc-400">{{ __('Record a new salary increment for an employee') }}</flux:text>
                    </div>

                    <div class="space-y-4">
                        <flux:field>
                            <flux:label>{{ __('Employee') }}</flux:label>
                            <flux:select wire:model.live="selectedEmployeeId" placeholder="{{ __('Select employee') }}">
                                <option value="">{{ __('Select employee') }}</option>
                                @foreach($activeEmployees as $emp)
                                    <option value="{{ $emp['id'] }}">{{ $emp['label'] }}</option>
                                @endforeach
                            </flux:select>
                        </flux:field>

                        <flux:field>
                            <flux:checkbox wire:model.live="forHistory" :label="__('For history / reporting only')" />
                            <flux:description>{{ __('Check to record a past increment without changing the employee’s current gross or basic salary. Use for maintaining increment history only.') }}</flux:description>
                        </flux:field>

                        @if($selectedEmployeeId)
                            <div class="rounded-lg border border-zinc-200 dark:border-zinc-600 bg-zinc-50 dark:bg-zinc-800/50 p-4 space-y-2">
                                <flux:heading size="sm" level="4" class="text-zinc-700 dark:text-zinc-300">{{ __('Current Salary Info') }}</flux:heading>
                                <div class="grid grid-cols-2 gap-2 text-sm">
                                    <div class="text-zinc-600 dark:text-zinc-400">{{ __('Gross Salary') }}:</div>
                                    <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ preg_replace('/\.00$/', '', number_format((float) $employeeGrossSalary, 2, '.', ',')) }}</div>
                                    <div class="text-zinc-600 dark:text-zinc-400">{{ __('Basic Salary') }}:</div>
                                    <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ preg_replace('/\.00$/', '', number_format((float) $employeeBasicSalary, 2, '.', ',')) }}</div>
                                    <div class="text-zinc-600 dark:text-zinc-400">{{ __('Allowances') }}:</div>
                                    <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ preg_replace('/\.00$/', '', number_format((float) $employeeAllowances, 2, '.', ',')) }}</div>
                                    <div class="text-zinc-600 dark:text-zinc-400">{{ __('Last Increment') }}:</div>
                                    <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $lastIncrementAmount !== '' ? preg_replace('/\.00$/', '', number_format((float) $lastIncrementAmount, 2, '.', ',')) : '—' }}</div>
                                    <div class="text-zinc-600 dark:text-zinc-400">{{ __('Last Increment Date') }}:</div>
                                    <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $lastIncrementDate ? \Carbon\Carbon::parse($lastIncrementDate)->format('M d, Y') : '—' }}</div>
                                    <div class="text-zinc-600 dark:text-zinc-400">{{ __('Time Since Last Increment') }}:</div>
                                    <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $timeSinceLastIncrement ?? '—' }}</div>
                                </div>
                            </div>
                        @endif

                        <flux:field>
                            <flux:label>{{ __('Increment date') }}</flux:label>
                            @if($forHistory)
                                <flux:input type="date" wire:model="incrementEffectiveDate" max="{{ $this->maxIncrementDateForHistory }}" />
                            @else
                                <flux:input type="date" wire:model="incrementEffectiveDate" />
                            @endif
                            <flux:description>
                                @if($forHistory)
                                    {{ __('For history-only, date must be before the current month (max :date).', ['date' => \Carbon\Carbon::parse($this->maxIncrementDateForHistory)->format('M d, Y')]) }}
                                @else
                                    {{ __('From this date onwards the increment will be applied.') }}
                                @endif
                            </flux:description>
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('Increment Amount') }}</flux:label>
                            <flux:input type="number" step="0.01" min="0" placeholder="0.00" wire:model.live="incrementAmount" />
                        </flux:field>

                        @if((float) $incrementAmount > 0 && !$forHistory)
                            <div class="rounded-lg border border-zinc-200 dark:border-zinc-600 bg-zinc-50 dark:bg-zinc-800/50 p-4 space-y-2">
                                <flux:heading size="sm" level="4" class="text-zinc-700 dark:text-zinc-300">{{ __('Projected Salary After Increment') }}</flux:heading>
                                <div class="grid grid-cols-2 gap-2 text-sm">
                                    <div class="text-zinc-600 dark:text-zinc-400">{{ __('New Basic Salary') }}:</div>
                                    <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ preg_replace('/\.00$/', '', number_format($this->calculatedNewBasic, 2, '.', ',')) }}</div>
                                    <div class="text-zinc-600 dark:text-zinc-400">{{ __('New Gross Salary') }}:</div>
                                    <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ preg_replace('/\.00$/', '', number_format($this->calculatedNewGross, 2, '.', ',')) }}</div>
                                    <div class="text-zinc-600 dark:text-zinc-400">{{ __('Tax Amount') }}:</div>
                                    <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ preg_replace('/\.00$/', '', number_format($this->calculatedTaxAmount, 2, '.', ',')) }}</div>
                                    <div class="text-zinc-600 dark:text-zinc-400">{{ __('Net Salary (after tax)') }}:</div>
                                    <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ preg_replace('/\.00$/', '', number_format($this->calculatedNetSalary, 2, '.', ',')) }}</div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="flex justify-end gap-3 pt-4">
                        <flux:button variant="ghost" wire:click="closeAddIncrementModal">{{ __('Cancel') }}</flux:button>
                        <flux:button variant="primary" wire:click="addIncrement" :disabled="!$selectedEmployeeId || (float) $incrementAmount <= 0">
                            {{ __('Save Increment') }}
                        </flux:button>
                    </div>
                </div>
            </flux:modal>
        @endif

        <!-- View Increment Flyout -->
        @if($showViewIncrementModal && $this->selectedIncrement)
            @php $inc = $this->selectedIncrement; $emp = $inc->employee; @endphp
            <flux:modal variant="flyout" :open="$showViewIncrementModal" wire:model="showViewIncrementModal" class="w-[32rem] lg:w-[36rem]">
                <div class="space-y-6">
                    <div>
                        <flux:heading size="lg">{{ __('Increment Details') }}</flux:heading>
                        <flux:text class="mt-1 text-zinc-500 dark:text-zinc-400">{{ $emp ? trim($emp->first_name . ' ' . $emp->last_name) . ' (' . ($emp->employee_code ?? '') . ')' : '—' }}</flux:text>
                    </div>
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between"><span class="text-zinc-600 dark:text-zinc-400">{{ __('No. of increments') }}</span><span class="font-medium">{{ $inc->number_of_increments }}</span></div>
                        <div class="flex justify-between"><span class="text-zinc-600 dark:text-zinc-400">{{ __('Increment due date') }}</span><span class="font-medium">{{ $inc->increment_due_date ? $inc->increment_due_date->format('M d, Y') : '—' }}</span></div>
                        <div class="flex justify-between"><span class="text-zinc-600 dark:text-zinc-400">{{ __('Last increment date') }}</span><span class="font-medium">{{ $inc->last_increment_date ? $inc->last_increment_date->format('M d, Y') : '—' }}</span></div>
                        @php
                            $viewGrossAfter = $inc->gross_salary_after !== null ? (float) $inc->gross_salary_after : null;
                            $viewBasicAfter = $inc->basic_salary_after !== null ? (float) $inc->basic_salary_after : null;
                            $viewIncAmt = (float) $inc->increment_amount;
                            $viewGrossBefore = ($viewGrossAfter !== null && $viewIncAmt >= 0) ? $viewGrossAfter - $viewIncAmt : null;
                            $viewBasicBefore = ($viewBasicAfter !== null && $viewIncAmt >= 0) ? $viewBasicAfter - $viewIncAmt : null;
                        @endphp
                        <div class="flex justify-between"><span class="text-zinc-600 dark:text-zinc-400">{{ __('Gross before') }}</span><span class="font-medium">{{ $viewGrossBefore !== null ? preg_replace('/\.00$/', '', number_format($viewGrossBefore, 2, '.', ',')) : '—' }}</span></div>
                        <div class="flex justify-between"><span class="text-zinc-600 dark:text-zinc-400">{{ __('Basic before') }}</span><span class="font-medium">{{ $viewBasicBefore !== null ? preg_replace('/\.00$/', '', number_format($viewBasicBefore, 2, '.', ',')) : '—' }}</span></div>
                        <div class="flex justify-between"><span class="text-zinc-600 dark:text-zinc-400">{{ __('Increment amount') }}</span><span class="font-medium">{{ preg_replace('/\.00$/', '', number_format((float) $inc->increment_amount, 2, '.', ',')) }}</span></div>
                        <div class="flex justify-between"><span class="text-zinc-600 dark:text-zinc-400">{{ __('Gross after') }}</span><span class="font-medium">{{ $inc->gross_salary_after !== null ? preg_replace('/\.00$/', '', number_format((float) $inc->gross_salary_after, 2, '.', ',')) : '—' }}</span></div>
                        <div class="flex justify-between"><span class="text-zinc-600 dark:text-zinc-400">{{ __('Basic after') }}</span><span class="font-medium">{{ $inc->basic_salary_after !== null ? preg_replace('/\.00$/', '', number_format((float) $inc->basic_salary_after, 2, '.', ',')) : '—' }}</span></div>
                        <div class="flex justify-between"><span class="text-zinc-600 dark:text-zinc-400">{{ __('For history only') }}</span><span class="font-medium">{{ $inc->for_history ? __('Yes') : __('No') }}</span></div>
                        <div class="flex justify-between"><span class="text-zinc-600 dark:text-zinc-400">{{ __('Updated by') }}</span><span class="font-medium">{{ $inc->updatedByUser?->name ?? '—' }}</span></div>
                        <div class="flex justify-between"><span class="text-zinc-600 dark:text-zinc-400">{{ __('Updated when') }}</span><span class="font-medium">{{ $inc->updated_at ? $inc->updated_at->format('M d, Y H:i') : '—' }}</span></div>
                    </div>
                    <div class="flex justify-end pt-4">
                        <flux:button variant="ghost" wire:click="closeViewIncrementModal">{{ __('Close') }}</flux:button>
                    </div>
                </div>
            </flux:modal>
        @endif

        <!-- Edit Increment Flyout -->
        @if($showEditIncrementModal)
            <flux:modal variant="flyout" :open="$showEditIncrementModal" wire:model="showEditIncrementModal" class="w-[32rem] lg:w-[36rem]">
                <div class="space-y-6">
                    <div>
                        <flux:heading size="lg">{{ __('Edit Increment') }}</flux:heading>
                        <flux:text class="mt-1 text-zinc-500 dark:text-zinc-400">{{ __('Update increment record details') }}</flux:text>
                    </div>
                    <div class="space-y-4">
                        <flux:field>
                            <flux:label>{{ __('Employee') }}</flux:label>
                            <div class="rounded-md border border-zinc-200 dark:border-zinc-600 bg-zinc-50 dark:bg-zinc-800/50 px-3 py-2 text-sm text-zinc-900 dark:text-zinc-100">
                                {{ optional(collect($activeEmployees)->firstWhere('id', (int) $selectedEmployeeId))['label'] ?? __('—') }}
                            </div>
                        </flux:field>
                        <flux:field>
                            <flux:checkbox wire:model.live="forHistory" :label="__('For history / reporting only')" />
                            <flux:description>{{ __('When checked, this record does not affect the employee’s current salary.') }}</flux:description>
                        </flux:field>
                        @if($selectedEmployeeId)
                            <div class="rounded-lg border border-zinc-200 dark:border-zinc-600 bg-zinc-50 dark:bg-zinc-800/50 p-4 space-y-2">
                                <flux:heading size="sm" level="4" class="text-zinc-700 dark:text-zinc-300">{{ __('Current Salary Info') }}</flux:heading>
                                <div class="grid grid-cols-2 gap-2 text-sm">
                                    <div class="text-zinc-600 dark:text-zinc-400">{{ __('Gross Salary') }}:</div>
                                    <div class="font-medium">{{ preg_replace('/\.00$/', '', number_format((float) $employeeGrossSalary, 2, '.', ',')) }}</div>
                                    <div class="text-zinc-600 dark:text-zinc-400">{{ __('Basic Salary') }}:</div>
                                    <div class="font-medium">{{ preg_replace('/\.00$/', '', number_format((float) $employeeBasicSalary, 2, '.', ',')) }}</div>
                                    <div class="text-zinc-600 dark:text-zinc-400">{{ __('Allowances') }}:</div>
                                    <div class="font-medium">{{ preg_replace('/\.00$/', '', number_format((float) $employeeAllowances, 2, '.', ',')) }}</div>
                                </div>
                            </div>
                        @endif
                        <flux:field>
                            <flux:label>{{ __('Increment date') }}</flux:label>
                            @if($forHistory)
                                <flux:input type="date" wire:model="incrementEffectiveDate" max="{{ $this->maxIncrementDateForHistory }}" />
                            @else
                                <flux:input type="date" wire:model="incrementEffectiveDate" />
                            @endif
                            <flux:description>
                                @if($forHistory)
                                    {{ __('For history-only, date must be before the current month (max :date).', ['date' => \Carbon\Carbon::parse($this->maxIncrementDateForHistory)->format('M d, Y')]) }}
                                @else
                                    {{ __('From this date onwards the increment will be applied.') }}
                                @endif
                            </flux:description>
                        </flux:field>
                        <flux:field>
                            <flux:label>{{ __('Increment Amount') }}</flux:label>
                            <flux:input type="number" step="0.01" min="0" placeholder="0.00" wire:model.live="incrementAmount" />
                        </flux:field>
                        @if((float) $incrementAmount > 0 && !$forHistory)
                            <div class="rounded-lg border border-zinc-200 dark:border-zinc-600 bg-zinc-50 dark:bg-zinc-800/50 p-4 space-y-2">
                                <flux:heading size="sm" level="4" class="text-zinc-700 dark:text-zinc-300">{{ __('Projected Salary After Increment') }}</flux:heading>
                                <div class="grid grid-cols-2 gap-2 text-sm">
                                    <div class="text-zinc-600 dark:text-zinc-400">{{ __('New Basic Salary') }}:</div>
                                    <div class="font-medium">{{ preg_replace('/\.00$/', '', number_format($this->calculatedNewBasic, 2, '.', ',')) }}</div>
                                    <div class="text-zinc-600 dark:text-zinc-400">{{ __('New Gross Salary') }}:</div>
                                    <div class="font-medium">{{ preg_replace('/\.00$/', '', number_format($this->calculatedNewGross, 2, '.', ',')) }}</div>
                                    <div class="text-zinc-600 dark:text-zinc-400">{{ __('Tax Amount') }}:</div>
                                    <div class="font-medium">{{ preg_replace('/\.00$/', '', number_format($this->calculatedTaxAmount, 2, '.', ',')) }}</div>
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="flex justify-end gap-3 pt-4">
                        <flux:button variant="ghost" wire:click="closeEditIncrementModal">{{ __('Cancel') }}</flux:button>
                        <flux:button variant="primary" wire:click="updateIncrement" :disabled="(float) $incrementAmount <= 0">
                            {{ __('Update Increment') }}
                        </flux:button>
                    </div>
                </div>
            </flux:modal>
        @endif
    </x-payroll.layout>
</section>
