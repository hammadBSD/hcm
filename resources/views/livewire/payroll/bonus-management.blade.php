<section class="w-full">
    @include('partials.payroll-heading')

    <x-payroll.layout :heading="__('Bonus/Reimburse')" :subheading="__('Manage employee bonuses and reimbursements by month')">
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
                        <flux:label>{{ __('Bonus Type') }}</flux:label>
                        <flux:select wire:model.live="bonusType">
                            <option value="">{{ __('All Types') }}</option>
                            @foreach($bonusTypes as $type)
                                <option value="{{ $type }}">{{ $type }}</option>
                            @endforeach
                        </flux:select>
                    </flux:field>
                </div>

                <div class="sm:w-48">
                    <flux:field>
                        <flux:label>{{ __('Month') }}</flux:label>
                        <flux:select wire:model.live="selectedMonth">
                            <option value="">{{ __('All Months') }}</option>
                            @foreach($availableMonths as $month)
                                <option value="{{ $month['value'] }}">{{ $month['label'] }}</option>
                            @endforeach
                        </flux:select>
                    </flux:field>
                </div>
            </div>

            <div class="flex items-center justify-between">
                <flux:button variant="primary" icon="plus" wire:click="openAddBonusModal">
                    {{ __('Add Bonus') }}
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

        <div class="mt-8">
            @if($bonuses->count() > 0)
                <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 overflow-hidden shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-zinc-50 dark:bg-zinc-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                        <button wire:click="sort('employee_name')" class="flex items-center gap-1 hover:text-zinc-700 dark:hover:text-zinc-200">
                                            {{ __('Employee') }}
                                        </button>
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">{{ __('Department') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                        <button wire:click="sort('bonus_type')" class="flex items-center gap-1 hover:text-zinc-700 dark:hover:text-zinc-200">
                                            {{ __('Type') }}
                                        </button>
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                        <button wire:click="sort('amount')" class="flex items-center gap-1 hover:text-zinc-700 dark:hover:text-zinc-200">
                                            {{ __('Amount') }}
                                        </button>
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                        <button wire:click="sort('year_month')" class="flex items-center gap-1 hover:text-zinc-700 dark:hover:text-zinc-200">
                                            {{ __('Month') }}
                                        </button>
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">{{ __('Description') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                        <button wire:click="sort('created_at')" class="flex items-center gap-1 hover:text-zinc-700 dark:hover:text-zinc-200">
                                            {{ __('Added On') }}
                                        </button>
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-zinc-800 divide-y divide-zinc-200 dark:divide-zinc-700">
                                @foreach($bonuses as $bonus)
                                    @php
                                        $emp = $bonus->employee;
                                        $name = $emp ? trim(($emp->first_name ?? '') . ' ' . ($emp->last_name ?? '')) : '—';
                                        $deptRel = $emp ? ($emp->getRelation('department') ?? null) : null;
                                        $dept = is_object($deptRel) && isset($deptRel->title)
                                            ? $deptRel->title
                                            : ((is_string($emp->department ?? null) && trim((string) $emp->department) !== '') ? trim((string) $emp->department) : 'N/A');
                                        $fmt = fn ($v) => preg_replace('/\.00$/', '', number_format((float) $v, 2, '.', ','));
                                    @endphp
                                    <tr class="hover:bg-zinc-100 dark:hover:bg-zinc-600 transition-colors duration-150">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $name }}</div>
                                            <div class="text-sm text-zinc-500 dark:text-zinc-400">{{ $emp->employee_code ?? 'N/A' }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-700 dark:text-zinc-300">{{ $dept }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-700 dark:text-zinc-300">{{ $bonus->bonus_type }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $fmt($bonus->amount) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-700 dark:text-zinc-300">
                                            {{ \Carbon\Carbon::createFromFormat('Y-m', $bonus->year_month)->format('F Y') }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-zinc-700 dark:text-zinc-300">{{ $bonus->description ?: '—' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-700 dark:text-zinc-300">
                                            {{ $bonus->created_at?->format('M d, Y') ?? '—' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <flux:dropdown>
                                                <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                                                <flux:menu>
                                                    <flux:menu.item
                                                        icon="trash"
                                                        wire:click="deleteBonus({{ $bonus->id }})"
                                                        wire:confirm="{{ __('Are you sure you want to delete this bonus?') }}"
                                                        class="text-red-600 dark:text-red-400"
                                                    >
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

                @if(method_exists($bonuses, 'hasPages') && $bonuses->hasPages())
                    <div class="mt-6">
                        {{ $bonuses->links() }}
                    </div>
                @endif
            @else
                <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-12 text-center">
                    <flux:icon name="gift" class="mx-auto h-12 w-12 text-zinc-400" />
                    <flux:heading size="lg" level="3" class="mt-4 text-zinc-600 dark:text-zinc-400">
                        {{ __('No bonuses found') }}
                    </flux:heading>
                    <flux:text class="mt-2 text-zinc-500 dark:text-zinc-500">
                        {{ __('No bonuses match the selected filters. Try another month or add a new bonus.') }}
                    </flux:text>
                </div>
            @endif
        </div>

        @if($showAddBonusModal)
            <flux:modal variant="flyout" :open="$showAddBonusModal" wire:model="showAddBonusModal" name="add-bonus-modal">
                <div class="space-y-6">
                    <div>
                        <flux:heading size="lg">{{ __('Add Bonus') }}</flux:heading>
                    </div>

                    <div class="space-y-4">
                        <flux:field>
                            <flux:label>{{ __('Month') }} <span class="text-red-500">*</span></flux:label>
                            <flux:input type="month" wire:model="formYearMonth" required />
                            <flux:description>{{ __('Bonus will appear in the Master Report for this month.') }}</flux:description>
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('Employee') }} <span class="text-red-500">*</span></flux:label>
                            <flux:select wire:model="employeeId">
                                <option value="">{{ __('Select Employee') }}</option>
                                @foreach($employeeOptions as $employee)
                                    <option value="{{ $employee->id }}">
                                        {{ $employee->first_name }} {{ $employee->last_name }}
                                        @if($employee->employee_code)
                                            ({{ $employee->employee_code }})
                                        @endif
                                        @if($employee->status !== 'active')
                                            — {{ __('Inactive') }}
                                        @endif
                                    </option>
                                @endforeach
                            </flux:select>
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('Bonus Type') }} <span class="text-red-500">*</span></flux:label>
                            <flux:select wire:model="formBonusType">
                                @foreach($bonusTypes as $type)
                                    <option value="{{ $type }}">{{ $type }}</option>
                                @endforeach
                            </flux:select>
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('Amount') }} <span class="text-red-500">*</span></flux:label>
                            <flux:input type="number" step="0.01" min="0" wire:model="amount" placeholder="0.00" />
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('Description') }}</flux:label>
                            <flux:textarea wire:model="description" placeholder="{{ __('Enter bonus description...') }}"></flux:textarea>
                        </flux:field>
                    </div>

                    <div class="flex justify-end gap-3">
                        <flux:button variant="ghost" wire:click="closeAddBonusModal">{{ __('Cancel') }}</flux:button>
                        <flux:button variant="primary" wire:click="addBonus">{{ __('Add Bonus') }}</flux:button>
                    </div>
                </div>
            </flux:modal>
        @endif
    </x-payroll.layout>
</section>
