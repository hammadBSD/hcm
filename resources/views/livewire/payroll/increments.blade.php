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
                <flux:button variant="ghost" size="sm" icon="arrow-path" wire:click="$refresh">
                    {{ __('Refresh') }}
                </flux:button>
            </div>
        </div>

        <!-- Increments Table -->
        <div class="mt-8">
            @if($increments->count() > 0)
                <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 overflow-hidden shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-zinc-50 dark:bg-zinc-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">{{ __('Employee') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">{{ __('No. of increments') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">{{ __('Increment due date') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">{{ __('Last increment date') }}</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">{{ __('Increment amount') }}</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">{{ __('Gross after') }}</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">{{ __('Basic after') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">{{ __('Updated by') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">{{ __('Updated when') }}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-zinc-800 divide-y divide-zinc-200 dark:divide-zinc-700">
                                @foreach($increments as $inc)
                                    @php
                                        $emp = $inc->employee;
                                        $employeeName = $emp ? trim($emp->first_name . ' ' . $emp->last_name) : '—';
                                        $employeeCode = $emp->employee_code ?? '—';
                                    @endphp
                                    <tr class="hover:bg-zinc-100 dark:hover:bg-zinc-600 transition-colors duration-150">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div>
                                                <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $employeeName }}</div>
                                                <div class="text-sm text-zinc-500 dark:text-zinc-400">{{ $employeeCode }}</div>
                                            </div>
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
                                            ${{ number_format((float) $inc->increment_amount, 2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-zinc-900 dark:text-zinc-100">
                                            {{ $inc->gross_salary_after !== null ? '$' . number_format((float) $inc->gross_salary_after, 2) : '—' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-zinc-900 dark:text-zinc-100">
                                            {{ $inc->basic_salary_after !== null ? '$' . number_format((float) $inc->basic_salary_after, 2) : '—' }}
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
    </x-payroll.layout>
</section>
