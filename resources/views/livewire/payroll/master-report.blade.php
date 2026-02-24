<section class="w-full">
    @include('partials.payroll-heading')

    <x-payroll.layout :heading="__('Master Report')" :subheading="$subheading">
        <div class="space-y-6 w-full" style="max-width: 100%; overflow-x: hidden;">
            <flux:heading size="lg" class="mb-1">{{ __('Master Report') }}</flux:heading>
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
                @php
                    $fmtNum = function($v) { $v = (float)($v ?? 0); $s = number_format($v, 2, '.', ','); return preg_replace('/\.00$/', '', $s); };
                @endphp
                <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 shadow-sm overflow-hidden mb-6">
                    <div class="master-report-grand-total px-6 py-4 bg-zinc-100 dark:bg-[#424242] border-b border-zinc-200 dark:border-zinc-600">
                        <flux:heading size="md" class="mb-3 text-zinc-900 dark:text-zinc-100">{{ __('Grand Total') }}</flux:heading>
                        <div class="flex flex-wrap gap-6 text-sm">
                            <span class="font-medium text-zinc-700 dark:text-zinc-300">
                                {{ __('Total Gross Salary') }}: <span class="text-green-600 dark:text-green-400">{{ $fmtNum($grandTotals['total_gross']) }}</span>
                            </span>
                            <span class="font-medium text-zinc-700 dark:text-zinc-300">
                                {{ __('Total Deductions') }}: <span class="text-amber-600 dark:text-amber-400">{{ $fmtNum($grandTotals['total_deductions']) }}</span>
                            </span>
                            <span class="font-medium text-zinc-700 dark:text-zinc-300">
                                {{ __('Total Net Salary') }}: <span class="text-blue-600 dark:text-blue-400">{{ $fmtNum($grandTotals['total_net_salary']) }}</span>
                            </span>
                            <span class="font-medium text-zinc-700 dark:text-zinc-300">
                                {{ __('No. of employees') }}: <span class="text-zinc-900 dark:text-zinc-100">{{ $grandTotals['count'] }}</span>
                            </span>
                        </div>
                    </div>
                </div>

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

                @foreach($groupedData as $group)
                    <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 shadow-sm overflow-hidden mb-6">
                        <div class="px-6 py-4 bg-zinc-50 dark:bg-zinc-700/50 border-b border-zinc-200 dark:border-zinc-600">
                            <flux:heading size="md" class="mb-3 text-zinc-900 dark:text-zinc-100">{{ $group['department'] }}</flux:heading>
                            <div class="flex flex-wrap gap-6 text-sm">
                                <span class="font-medium text-zinc-700 dark:text-zinc-300">
                                    {{ __('Total Gross Salary') }}: <span class="text-green-600 dark:text-green-400">{{ $fmtNum($group['total_gross']) }}</span>
                                </span>
                                <span class="font-medium text-zinc-700 dark:text-zinc-300">
                                    {{ __('Total Deductions') }}: <span class="text-amber-600 dark:text-amber-400">{{ $fmtNum($group['total_deductions'] ?? 0) }}</span>
                                </span>
                                <span class="font-medium text-zinc-700 dark:text-zinc-300">
                                    {{ __('Total Net Salary') }}: <span class="text-blue-600 dark:text-blue-400">{{ $fmtNum($group['total_net_salary'] ?? 0) }}</span>
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
                                        <th rowspan="2" class="sticky left-0 z-20 bg-zinc-50 dark:bg-zinc-700 px-3 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider border-r border-zinc-200 dark:border-zinc-600" style="width: 56px; min-width: 56px; max-width: 56px;">{{ __('SR NO') }}</th>
                                        <th rowspan="2" class="sticky z-20 bg-zinc-50 dark:bg-zinc-700 px-3 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider border-r border-zinc-200 dark:border-zinc-600" style="left: 56px; width: 96px; min-width: 96px; max-width: 96px;">{{ __('EMP CODE') }}</th>
                                        <th rowspan="2" class="sticky z-20 bg-zinc-50 dark:bg-zinc-700 px-3 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider border-r border-zinc-200 dark:border-zinc-600" style="left: 152px; width: 180px; min-width: 180px; max-width: 180px;">{{ __('EMPLOYEE NAME') }}</th>
                                        <th rowspan="2" class="px-3 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">{{ __('DEPT') }}</th>
                                        <th rowspan="2" class="px-3 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">{{ __('DSG') }}</th>
                                        <th rowspan="2" class="px-3 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">{{ __('DOJ') }}</th>
                                        <th rowspan="2" class="px-3 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">{{ __('CURRENT STATUS') }}</th>
                                        <th rowspan="2" class="px-3 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">{{ __('REPORTING MANAGER') }}</th>
                                        <th rowspan="2" class="px-3 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">{{ __('MCS') }}</th>
                                        <th rowspan="2" class="px-3 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">{{ __('Brands') }}</th>
                                        <th rowspan="2" class="px-3 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">{{ __('Employment Status') }}</th>
                                        <th rowspan="2" class="px-3 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">{{ __('CNIC') }}</th>
                                        <th rowspan="2" class="px-3 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">{{ __('DATE OF LAST INCREMENT') }}</th>
                                        <th rowspan="2" class="px-3 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">{{ __('INCREMENT AMOUNT') }}</th>
                                        <th rowspan="2" class="px-3 py-3 text-center text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">{{ __('# MONTHS SINCE LAST INCREMENT') }}</th>
                                        <th rowspan="2" class="px-3 py-3 text-center text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">{{ __('WORKING DAYS') }}</th>
                                        <th rowspan="2" class="px-3 py-3 text-center text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">{{ __('PRESENT DAYS') }}</th>
                                        <th rowspan="2" class="px-3 py-3 text-center text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">{{ __('Extra Days') }}</th>
                                        <th rowspan="2" class="px-3 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">{{ __('Amount of extra days') }}</th>
                                        <th rowspan="2" class="px-3 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">{{ __('Hourly Rate') }}</th>
                                        <th rowspan="2" class="px-3 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">{{ __('Hourly Deduction Amount') }}</th>
                                        <th rowspan="2" class="px-3 py-3 text-center text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">{{ __('Leaves (approved)') }}</th>
                                        <th colspan="3" class="px-3 py-3 text-center text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider border-r border-zinc-200 dark:border-zinc-600">{{ __('LEAVES') }}</th>
                                        <th rowspan="2" class="px-3 py-3 text-center text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">{{ __('ABSENT DAYS') }}</th>
                                        <th rowspan="2" class="px-3 py-3 text-center text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">{{ __('LATE DAYS') }}</th>
                                        <th rowspan="2" class="px-3 py-3 text-center text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">{{ __('TOTAL BREAK TIME') }}</th>
                                        <th rowspan="2" class="px-3 py-3 text-center text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">{{ __('HOLIDAYS') }}</th>
                                        <th rowspan="2" class="px-3 py-3 text-center text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">{{ __('TOTAL HOURS WORKED') }}</th>
                                        <th rowspan="2" class="px-3 py-3 text-center text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">{{ __('MONTHLY EXPECTED HOURS') }}</th>
                                        <th rowspan="2" class="px-3 py-3 text-center text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">{{ __('SHORT/EXCESS HOURS') }}</th>
                                        <th rowspan="2" class="px-3 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">{{ __('SALARY TYPE') }}</th>
                                        <th rowspan="2" class="px-3 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">{{ __('BASIC SALARY') }}</th>
                                        <th rowspan="2" class="px-3 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">{{ __('ALLOWANCES') }}</th>
                                        <th rowspan="2" class="px-3 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">{{ __('OT HRS') }}</th>
                                        <th rowspan="2" class="px-3 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">{{ __('OT AMT') }}</th>
                                        <th rowspan="2" class="px-3 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">{{ __('GROSS SALARY') }}</th>
                                        <th rowspan="2" class="px-3 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">{{ __('BONUS') }}</th>
                                        <th colspan="2" class="px-3 py-3 text-center text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider border-r border-zinc-200 dark:border-zinc-600">{{ __('EPF') }}</th>
                                        <th colspan="2" class="px-3 py-3 text-center text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider border-r border-zinc-200 dark:border-zinc-600">{{ __('ESIC') }}</th>
                                        <th rowspan="2" class="px-3 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">{{ __('TAX') }}</th>
                                        <th rowspan="2" class="px-3 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">{{ __('PROF TAX') }}</th>
                                        <th rowspan="2" class="px-3 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">{{ __('EOBI') }}</th>
                                        <th rowspan="2" class="px-3 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">{{ __('ADVANCE') }}</th>
                                        <th rowspan="2" class="px-3 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">{{ __('LOAN') }}</th>
                                        <th rowspan="2" class="px-3 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">{{ __('OTHER DEDUCTIONS') }}</th>
                                        <th rowspan="2" class="px-3 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">{{ __('TOTAL DEDUCTIONS') }}</th>
                                        <th rowspan="2" class="px-3 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">{{ __('NET SALARY') }}</th>
                                        <th rowspan="2" class="px-3 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">{{ __('BANK NAME') }}</th>
                                        <th rowspan="2" class="px-3 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">{{ __('ACCOUNT TITLE') }}</th>
                                        <th rowspan="2" class="px-3 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">{{ __('BANK ACCOUNT') }}</th>
                                    </tr>
                                    <tr>
                                        <th class="px-3 py-2 text-center text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">{{ __('PAID') }}</th>
                                        <th class="px-3 py-2 text-center text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">{{ __('UNPAID') }}</th>
                                        <th class="px-3 py-2 text-center text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider border-r border-zinc-200 dark:border-zinc-600">{{ __('LWP') }}</th>
                                        <th class="px-3 py-2 text-center text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">{{ __('EE') }}</th>
                                        <th class="px-3 py-2 text-center text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider border-r border-zinc-200 dark:border-zinc-600">{{ __('ER') }}</th>
                                        <th class="px-3 py-2 text-center text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">{{ __('EE') }}</th>
                                        <th class="px-3 py-2 text-center text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider border-r border-zinc-200 dark:border-zinc-600">{{ __('ER') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-zinc-800 divide-y divide-zinc-200 dark:divide-zinc-700">
                                    @foreach($group['employees'] as $row)
                                        @php $emp = $row['employee']; @endphp
                                        <tr class="hover:bg-zinc-100 dark:hover:bg-zinc-600 transition-colors duration-150 group">
                                            <td class="sticky left-0 z-10 bg-white dark:bg-zinc-800 group-hover:bg-zinc-100 dark:group-hover:bg-zinc-600 px-3 py-3 whitespace-nowrap border-r border-zinc-200 dark:border-zinc-700 text-sm text-zinc-700 dark:text-zinc-300" style="width: 56px; min-width: 56px; max-width: 56px;">{{ $row['sr_no'] ?? '' }}</td>
                                            <td class="sticky z-10 bg-white dark:bg-zinc-800 group-hover:bg-zinc-100 dark:group-hover:bg-zinc-600 px-3 py-3 whitespace-nowrap border-r border-zinc-200 dark:border-zinc-700 text-sm font-medium text-zinc-900 dark:text-zinc-100" style="left: 56px; width: 96px; min-width: 96px; max-width: 96px;">{{ $emp->employee_code ?? 'N/A' }}</td>
                                            <td class="sticky z-10 bg-white dark:bg-zinc-800 group-hover:bg-zinc-100 dark:group-hover:bg-zinc-600 px-3 py-3 whitespace-nowrap border-r border-zinc-200 dark:border-zinc-700 text-sm font-medium text-zinc-900 dark:text-zinc-100" style="left: 152px; width: 180px; min-width: 180px; max-width: 180px;">{{ trim(($emp->first_name ?? '') . ' ' . ($emp->last_name ?? '')) }}</td>
                                            <td class="px-3 py-3 whitespace-nowrap text-sm text-zinc-900 dark:text-zinc-100">{{ $row['department'] }}</td>
                                            <td class="px-3 py-3 whitespace-nowrap text-sm text-zinc-900 dark:text-zinc-100">{{ $row['designation'] }}</td>
                                            <td class="px-3 py-3 whitespace-nowrap text-sm text-zinc-700 dark:text-zinc-300">{{ $row['doj'] ?? '—' }}</td>
                                            <td class="px-3 py-3 whitespace-nowrap text-sm text-zinc-700 dark:text-zinc-300">{{ $row['current_status'] ?? '—' }}</td>
                                            <td class="px-3 py-3 whitespace-nowrap text-sm text-zinc-700 dark:text-zinc-300">{{ $row['reporting_manager'] ?? '—' }}</td>
                                            <td class="px-3 py-3 whitespace-nowrap text-sm text-zinc-700 dark:text-zinc-300">{{ $row['mcs'] ?? '—' }}</td>
                                            <td class="px-3 py-3 whitespace-nowrap text-sm text-zinc-700 dark:text-zinc-300">{{ $row['brands'] ?? '—' }}</td>
                                            <td class="px-3 py-3 whitespace-nowrap text-sm text-zinc-700 dark:text-zinc-300">{{ $row['employment_status'] ?? '—' }}</td>
                                            <td class="px-3 py-3 whitespace-nowrap text-sm text-zinc-700 dark:text-zinc-300">{{ $row['cnic'] ?? '—' }}</td>
                                            <td class="px-3 py-3 whitespace-nowrap text-sm text-zinc-700 dark:text-zinc-300">{{ $row['last_increment_date'] ?? '—' }}</td>
                                            <td class="px-3 py-3 whitespace-nowrap text-sm text-right text-zinc-700 dark:text-zinc-300">{{ $fmtNum($row['last_increment_amount'] ?? 0) }}</td>
                                            <td class="px-3 py-3 whitespace-nowrap text-sm text-center text-zinc-700 dark:text-zinc-300">{{ $row['months_since_increment'] ?? 0 }}</td>
                                            <td class="px-3 py-3 whitespace-nowrap text-sm text-center text-zinc-700 dark:text-zinc-300">{{ $row['working_days'] ?? 0 }}</td>
                                            <td class="px-3 py-3 whitespace-nowrap text-sm text-center text-zinc-700 dark:text-zinc-300">{{ $row['days_present'] ?? 0 }}</td>
                                            <td class="px-3 py-3 whitespace-nowrap text-sm text-center text-zinc-700 dark:text-zinc-300">{{ $row['extra_days'] ?? 0 }}</td>
                                            <td class="px-3 py-3 whitespace-nowrap text-sm text-right text-zinc-700 dark:text-zinc-300">{{ $fmtNum($row['amount_extra_days'] ?? 0) }}</td>
                                            <td class="px-3 py-3 whitespace-nowrap text-sm text-right text-zinc-700 dark:text-zinc-300">{{ $fmtNum($row['hourly_rate'] ?? 0) }}</td>
                                            <td class="px-3 py-3 whitespace-nowrap text-sm text-right text-zinc-700 dark:text-zinc-300">{{ $fmtNum($row['hourly_deduction_amount'] ?? 0) }}</td>
                                            <td class="px-3 py-3 whitespace-nowrap text-sm text-center text-zinc-700 dark:text-zinc-300">{{ $row['leaves_approved'] ?? 0 }}</td>
                                            <td class="px-3 py-3 whitespace-nowrap text-sm text-center text-zinc-700 dark:text-zinc-300">{{ $row['leave_paid'] ?? 0 }}</td>
                                            <td class="px-3 py-3 whitespace-nowrap text-sm text-center text-zinc-700 dark:text-zinc-300">{{ $row['leave_unpaid'] ?? 0 }}</td>
                                            <td class="px-3 py-3 whitespace-nowrap text-sm text-center text-zinc-700 dark:text-zinc-300">{{ $row['leave_lwp'] ?? 0 }}</td>
                                            <td class="px-3 py-3 whitespace-nowrap text-sm text-center {{ ($row['absent'] ?? 0) > 0 ? 'text-red-600 dark:text-red-400' : 'text-zinc-700 dark:text-zinc-300' }}">{{ $row['absent'] ?? 0 }}</td>
                                            <td class="px-3 py-3 whitespace-nowrap text-sm text-center {{ ($row['late_days'] ?? 0) > 0 ? 'text-red-600 dark:text-red-400' : 'text-zinc-700 dark:text-zinc-300' }}">{{ $row['late_days'] ?? 0 }}</td>
                                            <td class="px-3 py-3 whitespace-nowrap text-sm text-center {{ ($row['total_break_time'] ?? '0:00') !== '0:00' ? 'text-amber-600 dark:text-amber-400' : 'text-zinc-700 dark:text-zinc-300' }}">{{ $row['total_break_time'] ?? '0:00' }}</td>
                                            <td class="px-3 py-3 whitespace-nowrap text-sm text-center text-zinc-700 dark:text-zinc-300">{{ $row['holiday'] ?? 0 }}</td>
                                            <td class="px-3 py-3 whitespace-nowrap text-sm text-center text-zinc-700 dark:text-zinc-300">{{ $row['total_hours_worked'] ?? '0:00' }}</td>
                                            <td class="px-3 py-3 whitespace-nowrap text-sm text-center text-zinc-700 dark:text-zinc-300">{{ $row['monthly_expected_hours'] ?? '0:00' }}</td>
                                            <td class="px-3 py-3 whitespace-nowrap text-sm text-center {{ str_starts_with((string)($row['short_excess_hours'] ?? '0:00'), '-') ? 'text-red-600 dark:text-red-400' : 'text-zinc-700 dark:text-zinc-300' }}">{{ $row['short_excess_hours'] ?? '0:00' }}</td>
                                            <td class="px-3 py-3 whitespace-nowrap text-sm text-zinc-700 dark:text-zinc-300">{{ $row['salary_type'] ?? '—' }}</td>
                                            <td class="px-3 py-3 whitespace-nowrap text-sm text-right text-zinc-900 dark:text-zinc-100">{{ $fmtNum($row['basic_salary']) }}</td>
                                            <td class="px-3 py-3 whitespace-nowrap text-sm text-right text-zinc-700 dark:text-zinc-300">{{ $fmtNum($row['allowances'] ?? 0) }}</td>
                                            <td class="px-3 py-3 whitespace-nowrap text-sm text-right text-zinc-700 dark:text-zinc-300">{{ $row['ot_hrs'] ?? 0 }}</td>
                                            <td class="px-3 py-3 whitespace-nowrap text-sm text-right text-zinc-700 dark:text-zinc-300">{{ $fmtNum($row['ot_amt'] ?? 0) }}</td>
                                            <td class="px-3 py-3 whitespace-nowrap text-sm text-right text-green-600 dark:text-green-400">{{ $fmtNum($row['gross_salary']) }}</td>
                                            <td class="px-3 py-3 whitespace-nowrap text-sm text-right text-zinc-700 dark:text-zinc-300">{{ $fmtNum($row['bonus'] ?? 0) }}</td>
                                            <td class="px-3 py-3 whitespace-nowrap text-sm text-right text-zinc-700 dark:text-zinc-300">{{ $fmtNum($row['epf_ee'] ?? 0) }}</td>
                                            <td class="px-3 py-3 whitespace-nowrap text-sm text-right text-zinc-700 dark:text-zinc-300">{{ $fmtNum($row['epf_er'] ?? 0) }}</td>
                                            <td class="px-3 py-3 whitespace-nowrap text-sm text-right text-zinc-700 dark:text-zinc-300">{{ $fmtNum($row['esic_ee'] ?? 0) }}</td>
                                            <td class="px-3 py-3 whitespace-nowrap text-sm text-right text-zinc-700 dark:text-zinc-300">{{ $fmtNum($row['esic_er'] ?? 0) }}</td>
                                            <td class="px-3 py-3 whitespace-nowrap text-sm text-right text-amber-600 dark:text-amber-400">{{ $fmtNum($row['tax'] ?? 0) }}</td>
                                            <td class="px-3 py-3 whitespace-nowrap text-sm text-right text-zinc-700 dark:text-zinc-300">{{ $fmtNum($row['prof_tax'] ?? 0) }}</td>
                                            <td class="px-3 py-3 whitespace-nowrap text-sm text-right text-zinc-700 dark:text-zinc-300">{{ $fmtNum($row['eobi'] ?? 0) }}</td>
                                            <td class="px-3 py-3 whitespace-nowrap text-sm text-right text-zinc-700 dark:text-zinc-300">{{ $fmtNum($row['advance'] ?? 0) }}</td>
                                            <td class="px-3 py-3 whitespace-nowrap text-sm text-right text-zinc-700 dark:text-zinc-300">{{ $fmtNum($row['loan'] ?? 0) }}</td>
                                            <td class="px-3 py-3 whitespace-nowrap text-sm text-right text-zinc-700 dark:text-zinc-300">{{ $fmtNum($row['other_deductions'] ?? 0) }}</td>
                                            <td class="px-3 py-3 whitespace-nowrap text-sm text-right text-amber-600 dark:text-amber-400">{{ $fmtNum($row['total_deductions'] ?? 0) }}</td>
                                            <td class="px-3 py-3 whitespace-nowrap text-sm text-right font-medium text-blue-600 dark:text-blue-400">{{ $fmtNum($row['net_salary'] ?? 0) }}</td>
                                            <td class="px-3 py-3 whitespace-nowrap text-sm text-zinc-700 dark:text-zinc-300">{{ $row['bank_name'] ?? '—' }}</td>
                                            <td class="px-3 py-3 whitespace-nowrap text-sm text-zinc-700 dark:text-zinc-300">{{ $row['account_title'] ?? '—' }}</td>
                                            <td class="px-3 py-3 whitespace-nowrap text-sm text-zinc-700 dark:text-zinc-300">{{ $row['bank_account'] ?? '—' }}</td>
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
                        {{ __('No data found for the selected criteria.') }}
                    </div>
                </div>
            @endif
        </div>
    </x-payroll.layout>
</section>
