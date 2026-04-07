<section class="w-full">
    @include('partials.payroll-heading')

    <x-payroll.layout :heading="__('EOBI')" :subheading="__('Monthly EOBI deduction amount by effective date range (applied when enabled on the employee)')">
        <div class="my-6 w-full space-y-4">
            <div class="flex flex-col sm:flex-row gap-4">
                <div class="flex-1">
                    <flux:input wire:model.live.debounce.300ms="search" :label="__('Search')" type="text" :placeholder="__('Date from / date to')" icon="magnifying-glass" />
                </div>
                <div class="sm:w-48">
                    <flux:field>
                        <flux:label>{{ __('Date from') }}</flux:label>
                        <flux:input type="date" wire:model.live="selectedFromDate" />
                    </flux:field>
                </div>
            </div>

            <div class="flex items-center justify-between">
                <flux:button variant="primary" icon="plus" wire:click="openAddModal">
                    {{ __('Add EOBI') }}
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
            @if($records->count() > 0)
                <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 overflow-hidden shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-zinc-50 dark:bg-zinc-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                        <button wire:click="sort('date_from')" class="flex items-center gap-1">{{ __('Date from') }}</button>
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                        <button wire:click="sort('date_to')" class="flex items-center gap-1">{{ __('Date to') }}</button>
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                        <button wire:click="sort('monthly_amount')" class="flex items-center gap-1">{{ __('Monthly amount') }}</button>
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                                @foreach($records as $r)
                                    <tr>
                                        <td class="px-6 py-4 text-sm text-zinc-900 dark:text-zinc-100">{{ $r->date_from ? $r->date_from->format('M d, Y') : '—' }}</td>
                                        <td class="px-6 py-4 text-sm text-zinc-700 dark:text-zinc-300">{{ $r->date_to ? $r->date_to->format('M d, Y') : '—' }}</td>
                                        <td class="px-6 py-4 text-sm text-zinc-700 dark:text-zinc-300">{{ preg_replace('/\.00$/', '', number_format((float) $r->monthly_amount, 2, '.', ',')) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <flux:dropdown>
                                                <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                                                <flux:menu>
                                                    <flux:menu.item icon="eye" wire:click="viewRecord({{ $r->id }})">{{ __('View') }}</flux:menu.item>
                                                    <flux:menu.item icon="pencil" wire:click="editRecord({{ $r->id }})">{{ __('Edit') }}</flux:menu.item>
                                                    <flux:menu.item icon="trash" wire:click="deleteRecord({{ $r->id }})" wire:confirm="{{ __('Are you sure you want to delete this EOBI setting?') }}" class="text-red-600 dark:text-red-400">{{ __('Delete') }}</flux:menu.item>
                                                </flux:menu>
                                            </flux:dropdown>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @if(method_exists($records, 'hasPages') && $records->hasPages())
                    <div class="mt-6">{{ $records->links() }}</div>
                @endif
            @else
                <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-12 text-center text-zinc-500 dark:text-zinc-400">
                    {{ __('No EOBI settings found.') }}
                </div>
            @endif
        </div>

        @if($showAddModal)
            <flux:modal variant="flyout" :open="$showAddModal" wire:model="showAddModal">
                <div class="space-y-5">
                    <flux:heading size="lg">{{ __('Add EOBI') }}</flux:heading>
                    <flux:field>
                        <flux:label>{{ __('Date from') }}</flux:label>
                        <flux:input type="date" wire:model="formDateFrom" />
                    </flux:field>
                    <flux:field>
                        <flux:label>{{ __('Date to') }}</flux:label>
                        <flux:input type="date" wire:model="formDateTo" />
                    </flux:field>
                    <flux:field>
                        <flux:label>{{ __('Monthly amount') }}</flux:label>
                        <flux:input type="number" step="0.01" min="0" wire:model="formMonthlyAmount" placeholder="0.00" />
                    </flux:field>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('If date to is blank, this amount continues from date from until a newer setting becomes active.') }}</p>
                    <div class="flex justify-end gap-3">
                        <flux:button variant="ghost" wire:click="closeAddModal">{{ __('Cancel') }}</flux:button>
                        <flux:button variant="primary" wire:click="addRecord">{{ __('Save') }}</flux:button>
                    </div>
                </div>
            </flux:modal>
        @endif

        @if($showViewModal && $this->selectedRecord)
            @php $rec = $this->selectedRecord; @endphp
            <flux:modal variant="flyout" :open="$showViewModal" wire:model="showViewModal">
                <div class="space-y-4">
                    <flux:heading size="lg">{{ __('EOBI details') }}</flux:heading>
                    <div class="text-sm space-y-2">
                        <div class="flex justify-between"><span>{{ __('Date from') }}</span><span class="font-medium">{{ $rec->date_from ? $rec->date_from->format('M d, Y') : '—' }}</span></div>
                        <div class="flex justify-between"><span>{{ __('Date to') }}</span><span class="font-medium">{{ $rec->date_to ? $rec->date_to->format('M d, Y') : '—' }}</span></div>
                        <div class="flex justify-between"><span>{{ __('Monthly amount') }}</span><span class="font-medium">{{ preg_replace('/\.00$/', '', number_format((float) $rec->monthly_amount, 2, '.', ',')) }}</span></div>
                    </div>
                    <div class="flex justify-end">
                        <flux:button variant="ghost" wire:click="closeViewModal">{{ __('Close') }}</flux:button>
                    </div>
                </div>
            </flux:modal>
        @endif

        @if($showEditModal)
            <flux:modal variant="flyout" :open="$showEditModal" wire:model="showEditModal">
                <div class="space-y-5">
                    <flux:heading size="lg">{{ __('Edit EOBI') }}</flux:heading>
                    <flux:field>
                        <flux:label>{{ __('Date from') }}</flux:label>
                        <flux:input type="date" wire:model="editDateFrom" />
                    </flux:field>
                    <flux:field>
                        <flux:label>{{ __('Date to') }}</flux:label>
                        <flux:input type="date" wire:model="editDateTo" />
                    </flux:field>
                    <flux:field>
                        <flux:label>{{ __('Monthly amount') }}</flux:label>
                        <flux:input type="number" step="0.01" min="0" wire:model="editMonthlyAmount" />
                    </flux:field>
                    <div class="flex justify-end gap-3">
                        <flux:button variant="ghost" wire:click="closeEditModal">{{ __('Cancel') }}</flux:button>
                        <flux:button variant="primary" wire:click="updateRecord">{{ __('Update') }}</flux:button>
                    </div>
                </div>
            </flux:modal>
        @endif
    </x-payroll.layout>
</section>
