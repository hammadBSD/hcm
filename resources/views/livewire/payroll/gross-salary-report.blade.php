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

            @if($hasAnyEmployees)
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

                @if($hasFilteredData)
                    <!-- Dept-wise summary table (aligned with Payroll → Dept-wise Summary) -->
                    <div class="space-y-3">
                        <flux:heading size="md" class="text-zinc-900 dark:text-zinc-100">{{ __('Dept-wise Summary Table') }}</flux:heading>
                        <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 shadow-sm overflow-hidden">
                            <div class="overflow-x-auto">
                                <table class="divide-y divide-zinc-200 dark:divide-zinc-700 min-w-full">
                                    <thead class="bg-zinc-50 dark:bg-zinc-700">
                                        <tr>
                                            <th class="px-3 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider bg-sky-100 dark:bg-sky-900/30">{{ __('Department') }}</th>
                                            <th class="px-3 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider bg-sky-100 dark:bg-sky-900/30">{{ __('No. of Emp.') }}</th>
                                            <th class="px-3 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider bg-sky-100 dark:bg-sky-900/30">{{ __('MCS') }}</th>
                                            <th class="px-3 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider bg-sky-100 dark:bg-sky-900/30">{{ __('Brand') }}</th>
                                            <th class="px-3 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider bg-sky-100 dark:bg-sky-900/30">{{ __('Gross Salary Before Vehicle Allowance') }}</th>
                                            <th class="px-3 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider bg-sky-100 dark:bg-sky-900/30">{{ __('Gross Salary') }}</th>
                                            <th class="px-3 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider bg-sky-100 dark:bg-sky-900/30">{{ __('Deduction Absent Days') }}</th>
                                            <th class="px-3 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider bg-sky-100 dark:bg-sky-900/30">{{ __('Ded Amt Hrs') }}</th>
                                            <th class="px-3 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider bg-sky-100 dark:bg-sky-900/30">{{ __('Net Gross') }}</th>
                                            <th class="px-3 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider bg-sky-100 dark:bg-sky-900/30">{{ __('Tax') }}</th>
                                            <th class="px-3 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider bg-sky-100 dark:bg-sky-900/30">{{ __('Eobi') }}</th>
                                            <th class="px-3 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider bg-sky-100 dark:bg-sky-900/30">{{ __('Advance / Rentals') }}</th>
                                            <th class="px-3 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider bg-sky-100 dark:bg-sky-900/30">{{ __('Loan') }}</th>
                                            <th class="px-3 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider bg-emerald-100 dark:bg-emerald-900/30">{{ __('Net Pay') }}</th>
                                            <th class="px-3 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider bg-emerald-100 dark:bg-emerald-900/30">{{ __('HBL to HBL') }}</th>
                                            <th class="px-3 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider bg-emerald-100 dark:bg-emerald-900/30">{{ __('Cheque') }}</th>
                                            <th class="px-3 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider bg-emerald-100 dark:bg-emerald-900/30">{{ __('IBFT') }}</th>
                                            <th class="px-3 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider bg-emerald-100 dark:bg-emerald-900/30">{{ __('Cash') }}</th>
                                            <th class="px-3 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider bg-emerald-100 dark:bg-emerald-900/30">{{ __('To be Disbursed') }}</th>
                                            <th class="px-3 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider bg-emerald-100 dark:bg-emerald-900/30">{{ __('Hold') }}</th>
                                            <th class="px-3 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider bg-emerald-100 dark:bg-emerald-900/30 border-r-2 border-zinc-300 dark:border-zinc-600">{{ __('Total') }}</th>
                                            <th class="px-3 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider bg-zinc-200 dark:bg-zinc-600">{{ __('Already Paid') }}</th>
                                            <th class="px-3 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider bg-zinc-200 dark:bg-zinc-600">{{ __('Balance') }}</th>
                                            <th class="px-3 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider bg-zinc-200 dark:bg-zinc-600">{{ __('EOBI Contribution (Employer)') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-zinc-800 divide-y divide-zinc-200 dark:divide-zinc-700">
                                        @foreach($summaryRows as $row)
                                            <tr class="hover:bg-zinc-100 dark:hover:bg-zinc-600 transition-colors duration-150">
                                                <td class="px-3 py-3 whitespace-nowrap text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $row['department'] }}</td>
                                                <td class="px-3 py-3 whitespace-nowrap text-sm text-right text-zinc-700 dark:text-zinc-300">{{ $row['no_of_emp'] }}</td>
                                                <td class="px-3 py-3 whitespace-nowrap text-sm text-right text-zinc-700 dark:text-zinc-300">{{ $row['mcs'] ?? '—' }}</td>
                                                <td class="px-3 py-3 whitespace-nowrap text-sm text-right text-zinc-700 dark:text-zinc-300">{{ $row['brand'] ?? '—' }}</td>
                                                <td class="px-3 py-3 whitespace-nowrap text-sm text-right text-zinc-700 dark:text-zinc-300">{{ number_format($row['total_basic'], 2) }}</td>
                                                <td class="px-3 py-3 whitespace-nowrap text-sm text-right text-green-600 dark:text-green-400">{{ number_format($row['total_gross'], 2) }}</td>
                                                <td class="px-3 py-3 whitespace-nowrap text-sm text-right {{ $row['total_absent'] > 0 ? 'text-red-600 dark:text-red-400' : 'text-zinc-700 dark:text-zinc-300' }}">{{ $row['total_absent'] }}</td>
                                                <td class="px-3 py-3 whitespace-nowrap text-sm text-right text-zinc-700 dark:text-zinc-300">{{ number_format($row['ded_amt_hrs'], 2) }}</td>
                                                <td class="px-3 py-3 whitespace-nowrap text-sm text-right text-zinc-700 dark:text-zinc-300">{{ number_format($row['net_gross'], 2) }}</td>
                                                <td class="px-3 py-3 whitespace-nowrap text-sm text-right text-zinc-700 dark:text-zinc-300">{{ number_format($row['total_tax'], 2) }}</td>
                                                <td class="px-3 py-3 whitespace-nowrap text-sm text-right text-zinc-700 dark:text-zinc-300">{{ number_format($row['total_eobi'], 2) }}</td>
                                                <td class="px-3 py-3 whitespace-nowrap text-sm text-right text-zinc-700 dark:text-zinc-300">{{ number_format($row['total_advance'], 2) }}</td>
                                                <td class="px-3 py-3 whitespace-nowrap text-sm text-right text-zinc-700 dark:text-zinc-300">{{ number_format($row['total_loan'], 2) }}</td>
                                                <td class="px-3 py-3 whitespace-nowrap text-sm text-right font-medium text-blue-600 dark:text-blue-400">{{ number_format($row['total_net_salary'], 2) }}</td>
                                                <td class="px-3 py-3 whitespace-nowrap text-sm text-right text-zinc-700 dark:text-zinc-300">{{ number_format($row['hbl_to_hbl'], 2) }}</td>
                                                <td class="px-3 py-3 whitespace-nowrap text-sm text-right text-zinc-700 dark:text-zinc-300">{{ number_format($row['cheque'], 2) }}</td>
                                                <td class="px-3 py-3 whitespace-nowrap text-sm text-right text-zinc-700 dark:text-zinc-300">{{ number_format($row['ibft'], 2) }}</td>
                                                <td class="px-3 py-3 whitespace-nowrap text-sm text-right text-zinc-700 dark:text-zinc-300">{{ number_format($row['cash'], 2) }}</td>
                                                <td class="px-3 py-3 whitespace-nowrap text-sm text-right font-medium text-blue-600 dark:text-blue-400">{{ number_format($row['to_be_disbursed'], 2) }}</td>
                                                <td class="px-3 py-3 whitespace-nowrap text-sm text-right text-zinc-700 dark:text-zinc-300">{{ number_format($row['hold'], 2) }}</td>
                                                <td class="px-3 py-3 whitespace-nowrap text-sm text-right text-zinc-700 dark:text-zinc-300 border-r-2 border-zinc-200 dark:border-zinc-600">{{ number_format($row['total'], 2) }}</td>
                                                <td class="px-3 py-3 whitespace-nowrap text-sm text-right text-zinc-700 dark:text-zinc-300">{{ number_format($row['already_paid'], 2) }}</td>
                                                <td class="px-3 py-3 whitespace-nowrap text-sm text-right text-zinc-700 dark:text-zinc-300">{{ number_format($row['balance'], 2) }}</td>
                                                <td class="px-3 py-3 whitespace-nowrap text-sm text-right text-zinc-700 dark:text-zinc-300">{{ number_format($row['eobi_employer'], 2) }}</td>
                                            </tr>
                                        @endforeach
                                        <tr class="bg-zinc-100 dark:bg-zinc-700 font-bold">
                                            <td class="px-3 py-3 whitespace-nowrap text-sm text-zinc-900 dark:text-zinc-100">{{ __('GRAND TOTAL') }}</td>
                                            <td class="px-3 py-3 whitespace-nowrap text-sm text-right text-zinc-900 dark:text-zinc-100">{{ $grandTotal['no_of_emp'] }}</td>
                                            <td class="px-3 py-3 whitespace-nowrap text-sm text-right text-zinc-900 dark:text-zinc-100">—</td>
                                            <td class="px-3 py-3 whitespace-nowrap text-sm text-right text-zinc-900 dark:text-zinc-100">—</td>
                                            <td class="px-3 py-3 whitespace-nowrap text-sm text-right text-zinc-900 dark:text-zinc-100">{{ number_format($grandTotal['total_basic'], 2) }}</td>
                                            <td class="px-3 py-3 whitespace-nowrap text-sm text-right text-green-600 dark:text-green-400">{{ number_format($grandTotal['total_gross'], 2) }}</td>
                                            <td class="px-3 py-3 whitespace-nowrap text-sm text-right {{ $grandTotal['total_absent'] > 0 ? 'text-red-600 dark:text-red-400' : 'text-zinc-900 dark:text-zinc-100' }}">{{ $grandTotal['total_absent'] }}</td>
                                            <td class="px-3 py-3 whitespace-nowrap text-sm text-right text-zinc-900 dark:text-zinc-100">{{ number_format($grandTotal['ded_amt_hrs'], 2) }}</td>
                                            <td class="px-3 py-3 whitespace-nowrap text-sm text-right text-zinc-900 dark:text-zinc-100">{{ number_format($grandTotal['net_gross'], 2) }}</td>
                                            <td class="px-3 py-3 whitespace-nowrap text-sm text-right text-zinc-900 dark:text-zinc-100">{{ number_format($grandTotal['total_tax'], 2) }}</td>
                                            <td class="px-3 py-3 whitespace-nowrap text-sm text-right text-zinc-900 dark:text-zinc-100">{{ number_format($grandTotal['total_eobi'], 2) }}</td>
                                            <td class="px-3 py-3 whitespace-nowrap text-sm text-right text-zinc-900 dark:text-zinc-100">{{ number_format($grandTotal['total_advance'], 2) }}</td>
                                            <td class="px-3 py-3 whitespace-nowrap text-sm text-right text-zinc-900 dark:text-zinc-100">{{ number_format($grandTotal['total_loan'], 2) }}</td>
                                            <td class="px-3 py-3 whitespace-nowrap text-sm text-right text-blue-600 dark:text-blue-400">{{ number_format($grandTotal['total_net_salary'], 2) }}</td>
                                            <td class="px-3 py-3 whitespace-nowrap text-sm text-right text-zinc-900 dark:text-zinc-100">{{ number_format($grandTotal['hbl_to_hbl'], 2) }}</td>
                                            <td class="px-3 py-3 whitespace-nowrap text-sm text-right text-zinc-900 dark:text-zinc-100">{{ number_format($grandTotal['cheque'], 2) }}</td>
                                            <td class="px-3 py-3 whitespace-nowrap text-sm text-right text-zinc-900 dark:text-zinc-100">{{ number_format($grandTotal['ibft'], 2) }}</td>
                                            <td class="px-3 py-3 whitespace-nowrap text-sm text-right text-zinc-900 dark:text-zinc-100">{{ number_format($grandTotal['cash'], 2) }}</td>
                                            <td class="px-3 py-3 whitespace-nowrap text-sm text-right text-blue-600 dark:text-blue-400">{{ number_format($grandTotal['to_be_disbursed'], 2) }}</td>
                                            <td class="px-3 py-3 whitespace-nowrap text-sm text-right text-zinc-900 dark:text-zinc-100">{{ number_format($grandTotal['hold'], 2) }}</td>
                                            <td class="px-3 py-3 whitespace-nowrap text-sm text-right text-zinc-900 dark:text-zinc-100 border-r-2 border-zinc-300 dark:border-zinc-600">{{ number_format($grandTotal['total'], 2) }}</td>
                                            <td class="px-3 py-3 whitespace-nowrap text-sm text-right text-zinc-900 dark:text-zinc-100">{{ number_format($grandTotal['already_paid'], 2) }}</td>
                                            <td class="px-3 py-3 whitespace-nowrap text-sm text-right text-zinc-900 dark:text-zinc-100">{{ number_format($grandTotal['balance'], 2) }}</td>
                                            <td class="px-3 py-3 whitespace-nowrap text-sm text-right text-zinc-900 dark:text-zinc-100">{{ number_format($grandTotal['eobi_employer'], 2) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Department-wise employee detail -->
                    @foreach($groupedData as $group)
                        <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 shadow-sm overflow-hidden mb-6">
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
                    <flux:callout variant="warning" icon="magnifying-glass">
                        {{ __('No employees match your search. Clear the search box to see all departments and the summary table.') }}
                    </flux:callout>
                @endif
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
