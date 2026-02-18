<section class="w-full">
    @include('partials.payroll-heading')

    <x-payroll.layout :heading="__('Gross Salary Report')" :subheading="$subheading">
        <div class="space-y-6 w-full" style="max-width: 100%; overflow-x: hidden;">
            <flux:heading size="lg" class="mb-1">{{ __('Gross Salary Report') }}</flux:heading>
            <flux:subheading class="mb-4 text-zinc-500 dark:text-zinc-400">{{ $subheading }}</flux:subheading>
            @if(session('success'))
                <flux:callout variant="success" icon="check-circle">
                    {{ session('success') }}
                </flux:callout>
            @endif

            @if(session('error'))
                <flux:callout variant="danger" icon="exclamation-circle">
                    {{ session('error') }}
                </flux:callout>
            @endif

            @if($hasData)
                <!-- Filters (same layout as Attendance Report) -->
                <div class="flex items-center justify-between gap-3 mb-4">
                    <div class="flex-1 max-w-md">
                        <flux:input
                            wire:model.live.debounce.300ms="employeeSearchTerm"
                            type="text"
                            placeholder="{{ __('Search employees...') }}"
                            class="w-full"
                        />
                    </div>
                    <div class="flex items-center gap-3">
                        <flux:button
                            wire:click="exportToExcel"
                            variant="primary"
                            icon="arrow-down-tray"
                            wire:loading.attr="disabled"
                            wire:target="exportToExcel"
                        >
                            <span wire:loading.remove wire:target="exportToExcel">{{ __('Export Excel') }}</span>
                            <span wire:loading wire:target="exportToExcel">{{ __('Exporting...') }}</span>
                        </flux:button>
                        <flux:button
                            wire:click="exportToCsv"
                            variant="outline"
                            icon="arrow-down-tray"
                            wire:loading.attr="disabled"
                            wire:target="exportToCsv"
                        >
                            <span wire:loading.remove wire:target="exportToCsv">{{ __('Export CSV') }}</span>
                            <span wire:loading wire:target="exportToCsv">{{ __('Exporting...') }}</span>
                        </flux:button>
                        <div
                            class="text-zinc-400 dark:text-zinc-500 hidden md:flex items-center justify-center"
                            wire:loading.flex
                            wire:target="selectedMonth"
                        >
                            <flux:icon name="arrow-path" class="w-5 h-5 animate-spin" />
                        </div>
                        <flux:select
                            wire:model.live="selectedMonth"
                            placeholder="{{ $currentMonth }}"
                            class="w-40"
                            wire:loading.attr="disabled"
                            wire:target="selectedMonth"
                        >
                            <option value="">{{ \Carbon\Carbon::parse($currentMonth . '-01')->format('F Y') }} ({{ __('Current') }})</option>
                            @foreach($availableMonths as $month)
                                <option value="{{ $month['value'] }}">{{ $month['label'] }}</option>
                            @endforeach
                        </flux:select>
                    </div>
                </div>

                <!-- Department-wise sections -->
                @foreach($groupedData as $group)
                    <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 shadow-sm overflow-hidden mb-6">
                        <!-- Department header with totals -->
                        <div class="px-6 py-4 bg-zinc-50 dark:bg-zinc-700/50 border-b border-zinc-200 dark:border-zinc-600">
                            <flux:heading size="md" class="mb-3 text-zinc-900 dark:text-zinc-100">{{ $group['department'] }}</flux:heading>
                            <div class="flex flex-wrap gap-6 text-sm">
                                <span class="font-medium text-zinc-700 dark:text-zinc-300">
                                    {{ __('Total Gross Salary') }}: <span class="text-green-600 dark:text-green-400">{{ number_format($group['total_gross'], 2) }}</span>
                                </span>
                                <span class="font-medium text-zinc-700 dark:text-zinc-300">
                                    {{ __('Total Salary (without tax)') }}: <span class="text-blue-600 dark:text-blue-400">{{ number_format($group['total_gross'], 2) }}</span>
                                </span>
                                <span class="font-medium text-zinc-700 dark:text-zinc-300">
                                    {{ __('Total Tax') }}: <span class="text-amber-600 dark:text-amber-400">{{ number_format($group['total_tax'] ?? 0, 2) }}</span>
                                </span>
                                <span class="font-medium text-zinc-700 dark:text-zinc-300">
                                    {{ __('No. of employees') }}: <span class="text-zinc-900 dark:text-zinc-100">{{ $group['count'] }}</span>
                                </span>
                            </div>
                        </div>

                        <!-- Employee table for this department -->
                        <div class="overflow-x-auto">
                            <table class="divide-y divide-zinc-200 dark:divide-zinc-700 min-w-full">
                                <thead class="bg-zinc-50 dark:bg-zinc-700">
                                    <tr>
                                        <th class="sticky left-0 z-10 bg-zinc-50 dark:bg-zinc-700 px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider border-r border-zinc-200 dark:border-zinc-600">
                                            {{ __('Employee') }}
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                            {{ __('Department') }}
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                            {{ __('Basic Salary') }}
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                            {{ __('Allowances') }}
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                            {{ __('Gross Salary') }}
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                            {{ __('Tax') }}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-zinc-800 divide-y divide-zinc-200 dark:divide-zinc-700">
                                    @foreach($group['employees'] as $row)
                                        @php $emp = $row['employee']; @endphp
                                        <tr class="hover:bg-zinc-100 dark:hover:bg-zinc-600 transition-colors duration-150 group">
                                            <td class="sticky left-0 z-10 bg-white dark:bg-zinc-800 group-hover:bg-zinc-100 dark:group-hover:bg-zinc-600 px-6 py-4 whitespace-nowrap border-r border-zinc-200 dark:border-zinc-700">
                                                <div class="flex items-center gap-3">
                                                    <flux:avatar size="sm" :initials="strtoupper(substr($emp->first_name ?? '', 0, 1) . substr($emp->last_name ?? '', 0, 1))" />
                                                    <div>
                                                        <div class="font-medium text-zinc-900 dark:text-zinc-100">
                                                            {{ trim(($emp->first_name ?? '') . ' ' . ($emp->last_name ?? '')) }}
                                                        </div>
                                                        <div class="text-sm text-zinc-500 dark:text-zinc-400">
                                                            {{ $emp->employee_code ?? 'N/A' }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                                {{ $row['department'] }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                                {{ number_format($row['basic_salary'], 2) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-cyan-600 dark:text-cyan-400">
                                                {{ number_format($row['allowances'], 2) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-green-600 dark:text-green-400">
                                                {{ number_format($row['gross_salary'], 2) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-amber-600 dark:text-amber-400">
                                                {{ number_format($row['tax'] ?? 0, 2) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 shadow-sm p-6">
                    <div class="flex items-center justify-between gap-3 mb-4">
                        <div class="flex-1 max-w-md">
                            <flux:input
                                wire:model.live.debounce.300ms="employeeSearchTerm"
                                type="text"
                                placeholder="{{ __('Search employees...') }}"
                                class="w-full"
                            />
                        </div>
                        <div class="flex items-center gap-3">
                            <flux:select
                                wire:model.live="selectedMonth"
                                placeholder="{{ $currentMonth }}"
                                class="w-40"
                            >
                                <option value="">{{ \Carbon\Carbon::parse($currentMonth . '-01')->format('F Y') }} ({{ __('Current') }})</option>
                                @foreach($availableMonths as $month)
                                    <option value="{{ $month['value'] }}">{{ $month['label'] }}</option>
                                @endforeach
                            </flux:select>
                        </div>
                    </div>
                    <div class="text-center py-12 text-zinc-500 dark:text-zinc-400">
                        {{ __('No salary data found for the selected criteria.') }}
                    </div>
                </div>
            @endif
        </div>
    </x-payroll.layout>
</section>
