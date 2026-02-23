<section class="w-full">
    @include('partials.payroll-heading')
    
    <x-payroll.layout :heading="__('Payroll Processing')" :subheading="__('Process payroll for employees')">
        <!-- Search and Filter Controls -->
        <div class="my-6 w-full space-y-4">
            <div class="flex flex-col sm:flex-row gap-4">
                <!-- Department Filter -->
                <div class="sm:w-64">
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

                <!-- Year Filter -->
                <div class="sm:w-32">
                    <flux:field>
                        <flux:label>{{ __('Year') }}</flux:label>
                        <flux:select wire:model.live="selectedYear">
                            @for($year = now()->year; $year >= now()->year - 5; $year--)
                                <option value="{{ $year }}">{{ $year }}</option>
                            @endfor
                        </flux:select>
                    </flux:field>
                </div>
            </div>

            <!-- Action Buttons (only on listing page, not when viewing a run) -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    @if(!$selectedRun)
                        <flux:button variant="primary" icon="calculator" wire:click="openProcessPayrollModal">
                            {{ __('Process Payroll') }}
                        </flux:button>
                    @endif
                </div>

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

        @if($selectedRun)
            <!-- Run Detail View -->
            <div class="mt-6 bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 shadow-sm overflow-hidden">
                <div class="px-6 py-4 bg-zinc-50 dark:bg-zinc-700/50 border-b border-zinc-200 dark:border-zinc-600 flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <flux:button variant="ghost" size="sm" wire:click="closeRunDetail" icon="arrow-left">
                            {{ __('Back to list') }}
                        </flux:button>
                        <flux:heading size="md">{{ __('Payroll Run') }}: {{ $selectedRun->period_label }}</flux:heading>
                        <flux:badge color="{{ $selectedRun->status === 'approved' ? 'green' : 'yellow' }}" size="sm">
                            {{ $selectedRun->status === 'approved' ? __('Approved') : __('Draft') }}
                        </flux:badge>
                        <span class="text-sm text-zinc-500 dark:text-zinc-400">{{ ucfirst(str_replace('_', ' ', $selectedRun->processing_type)) }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <flux:button variant="outline" size="sm" icon="arrow-down-tray" wire:click="exportRunCsv">
                            {{ __('Export CSV') }}
                        </flux:button>
                        <flux:button variant="outline" size="sm" icon="arrow-down-tray" wire:click="exportRunExcel">
                            {{ __('Export Excel') }}
                        </flux:button>
                        @if($selectedRun->isDraft())
                            <flux:button variant="outline" wire:click="saveLineEdits" wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="saveLineEdits">{{ __('Save changes') }}</span>
                                <span wire:loading wire:target="saveLineEdits">{{ __('Saving...') }}</span>
                            </flux:button>
                            <flux:button variant="primary" wire:click="approveRun" wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="approveRun">{{ __('Approve run') }}</span>
                                <span wire:loading wire:target="approveRun">{{ __('Approving...') }}</span>
                            </flux:button>
                        @endif
                    </div>
                </div>
                @php $inputClass = 'w-full max-w-24 text-right text-sm px-2 py-1.5 rounded border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800'; @endphp
                <div class="overflow-x-auto">
                    <table class="divide-y divide-zinc-200 dark:divide-zinc-700" style="min-width: 1100px;">
                        <thead class="bg-zinc-50 dark:bg-zinc-700">
                            <tr>
                                <th class="sticky left-0 z-20 bg-zinc-50 dark:bg-zinc-700 px-4 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase border-r border-zinc-200 dark:border-zinc-600 whitespace-nowrap" style="width: 180px; min-width: 180px;">{{ __('Employee') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase whitespace-nowrap">{{ __('Department') }}</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase whitespace-nowrap">{{ __('Working days') }}</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase whitespace-nowrap">{{ __('Absent') }}</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase whitespace-nowrap">{{ __('Gross') }}</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase whitespace-nowrap">{{ __('Tax') }}</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase whitespace-nowrap">{{ __('EOBI') }}</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase whitespace-nowrap">{{ __('Advance') }}</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase whitespace-nowrap">{{ __('Loan') }}</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase whitespace-nowrap">{{ __('Deductions') }}</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase whitespace-nowrap">{{ __('Net') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700 bg-white dark:bg-zinc-800">
                            @php $formatDecimal = fn($v) => (float)($v ?? 0) == 0 ? '0' : number_format((float)($v ?? 0), 2); @endphp
                            @foreach($selectedRun->lines as $line)
                                <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-700/50">
                                    <td class="sticky left-0 z-10 bg-white dark:bg-zinc-800 px-4 py-3 text-sm font-medium text-zinc-900 dark:text-zinc-100 border-r border-zinc-200 dark:border-zinc-600 whitespace-nowrap">
                                        {{ $line->employee ? trim((string)($line->employee->first_name ?? '') . ' ' . (string)($line->employee->last_name ?? '')) : '—' }}
                                        @if($line->employee)
                                            <div class="text-xs text-zinc-500">{{ $line->employee->employee_code ?? '' }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm text-zinc-700 dark:text-zinc-300 whitespace-nowrap">{{ $line->department }}</td>
                                    @if($selectedRun->isDraft())
                                        <td class="px-2 py-1.5"><input type="number" min="0" class="{{ $inputClass }}" wire:model.blur="lineEdits.{{ $line->id }}_working_days" /></td>
                                        <td class="px-2 py-1.5"><input type="number" min="0" class="{{ $inputClass }}" wire:model.blur="lineEdits.{{ $line->id }}_absent" /></td>
                                        <td class="px-2 py-1.5"><input type="number" min="0" step="0.01" class="{{ $inputClass }}" wire:model.blur="lineEdits.{{ $line->id }}_gross_salary" /></td>
                                        <td class="px-2 py-1.5"><input type="number" min="0" step="0.01" class="{{ $inputClass }}" wire:model.blur="lineEdits.{{ $line->id }}_tax" /></td>
                                        <td class="px-2 py-1.5"><input type="number" min="0" step="0.01" class="{{ $inputClass }}" wire:model.blur="lineEdits.{{ $line->id }}_eobi" /></td>
                                        <td class="px-2 py-1.5"><input type="number" min="0" step="0.01" class="{{ $inputClass }}" wire:model.blur="lineEdits.{{ $line->id }}_advance" /></td>
                                        <td class="px-2 py-1.5"><input type="number" min="0" step="0.01" class="{{ $inputClass }}" wire:model.blur="lineEdits.{{ $line->id }}_loan" /></td>
                                        <td class="px-2 py-1.5"><input type="number" min="0" step="0.01" class="{{ $inputClass }}" wire:model.blur="lineEdits.{{ $line->id }}_total_deductions" /></td>
                                        <td class="px-2 py-1.5"><input type="number" step="0.01" class="{{ $inputClass }}" wire:model.blur="lineEdits.{{ $line->id }}_net_salary" /></td>
                                    @else
                                        <td class="px-4 py-3 text-sm text-right text-zinc-700 dark:text-zinc-300">{{ $line->working_days ?? 0 }}</td>
                                        <td class="px-4 py-3 text-sm text-right {{ (int)($line->absent ?? 0) > 0 ? 'text-red-600 dark:text-red-400' : 'text-zinc-700 dark:text-zinc-300' }}">{{ $line->absent ?? 0 }}</td>
                                        <td class="px-4 py-3 text-sm text-right text-green-600 dark:text-green-400">{{ $formatDecimal($line->gross_salary) }}</td>
                                        <td class="px-4 py-3 text-sm text-right text-zinc-700 dark:text-zinc-300">{{ $formatDecimal($line->tax) }}</td>
                                        <td class="px-4 py-3 text-sm text-right text-zinc-700 dark:text-zinc-300">{{ $formatDecimal($line->eobi) }}</td>
                                        <td class="px-4 py-3 text-sm text-right text-zinc-700 dark:text-zinc-300">{{ $formatDecimal($line->advance) }}</td>
                                        <td class="px-4 py-3 text-sm text-right text-zinc-700 dark:text-zinc-300">{{ $formatDecimal($line->loan) }}</td>
                                        <td class="px-4 py-3 text-sm text-right text-amber-600 dark:text-amber-400">{{ $formatDecimal($line->total_deductions) }}</td>
                                        <td class="px-4 py-3 text-sm text-right font-medium text-blue-600 dark:text-blue-400">{{ $formatDecimal($line->net_salary) }}</td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-zinc-100 dark:bg-zinc-700 font-semibold">
                            <tr>
                                <td colspan="4" class="sticky left-0 z-10 bg-zinc-100 dark:bg-zinc-700 px-4 py-3 text-sm text-zinc-900 dark:text-zinc-100 border-r border-zinc-200 dark:border-zinc-600">{{ __('Total') }}</td>
                                @php $sumFmt = fn($col) => (float)$selectedRun->lines->sum($col) == 0 ? '0' : number_format((float)$selectedRun->lines->sum($col), 2); @endphp
                                <td class="px-4 py-3 text-sm text-right text-green-600 dark:text-green-400">{{ $sumFmt('gross_salary') }}</td>
                                <td class="px-4 py-3 text-sm text-right text-zinc-700 dark:text-zinc-300">{{ $sumFmt('tax') }}</td>
                                <td class="px-4 py-3 text-sm text-right text-zinc-700 dark:text-zinc-300">{{ $sumFmt('eobi') }}</td>
                                <td class="px-4 py-3 text-sm text-right text-zinc-700 dark:text-zinc-300">{{ $sumFmt('advance') }}</td>
                                <td class="px-4 py-3 text-sm text-right text-zinc-700 dark:text-zinc-300">{{ $sumFmt('loan') }}</td>
                                <td class="px-4 py-3 text-sm text-right text-amber-600 dark:text-amber-400">{{ $sumFmt('total_deductions') }}</td>
                                <td class="px-4 py-3 text-sm text-right text-blue-600 dark:text-blue-400">{{ $sumFmt('net_salary') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        @else
            <!-- Recent Payroll Runs -->
            <div class="mt-6">
                <flux:heading size="md" class="mb-3">{{ __('Recent Payroll Runs') }}</flux:heading>
                @if($payrollRuns && $payrollRuns->count() > 0)
                    <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 overflow-hidden shadow-sm">
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-zinc-50 dark:bg-zinc-700">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">{{ __('Period') }}</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">{{ __('Type') }}</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">{{ __('Status') }}</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">{{ __('Employees') }}</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">{{ __('Created') }}</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                                    @foreach($payrollRuns as $run)
                                        <tr class="hover:bg-zinc-100 dark:hover:bg-zinc-600">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $run->period_label }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-700 dark:text-zinc-300">{{ ucfirst(str_replace('_', ' ', $run->processing_type)) }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <flux:badge color="{{ $run->status === 'approved' ? 'green' : 'yellow' }}" size="sm">{{ $run->status === 'approved' ? __('Approved') : __('Draft') }}</flux:badge>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-700 dark:text-zinc-300">{{ $run->lines_count }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">{{ $run->created_at->format('M d, Y H:i') }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <flux:button variant="ghost" size="sm" href="{{ route('payroll.payroll-processing', ['run' => $run->id]) }}">{{ __('View') }}</flux:button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @else
                    <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-6 text-center text-zinc-500 dark:text-zinc-400">
                        {{ __('No payroll runs yet. Click "Process Payroll" to create a draft run.') }}
                    </div>
                @endif
            </div>
        @endif

        <!-- Month Employees Flyout -->
        <flux:modal variant="flyout" wire:model="showMonthEmployeesFlyout" class="w-[60rem]">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">
                        {{ __('Payroll Employees') }} - {{ $selectedMonthForFlyout ? \Carbon\Carbon::create($selectedYearForFlyout, $selectedMonthForFlyout, 1)->format('F Y') : '' }}
                    </flux:heading>
                </div>

                @if(count($monthEmployees) > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-zinc-50 dark:bg-zinc-700">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                        {{ __('Employee') }}
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                        {{ __('Department') }}
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                        {{ __('Basic Salary') }}
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                        {{ __('Allowances') }}
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                        {{ __('Deductions') }}
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                        {{ __('Net Salary') }}
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                        {{ __('Status') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-zinc-800 divide-y divide-zinc-200 dark:divide-zinc-700">
                                @foreach ($monthEmployees as $employee)
                                    <tr class="hover:bg-zinc-100 dark:hover:bg-zinc-600 transition-colors duration-150">
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <div class="flex items-center gap-3">
                                                <flux:avatar size="sm" :initials="strtoupper(substr($employee['name'], 0, 1))" />
                                                <div>
                                                    <div class="font-medium text-zinc-900 dark:text-zinc-100">
                                                        {{ $employee['name'] }}
                                                    </div>
                                                    <div class="text-sm text-zinc-500 dark:text-zinc-400">
                                                        {{ $employee['employee_code'] }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <div class="text-sm text-zinc-900 dark:text-zinc-100">
                                                {{ $employee['department'] }}
                                            </div>
                                        </td>
                                        
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <div class="text-sm text-zinc-900 dark:text-zinc-100">
                                                ${{ number_format($employee['basic_salary'], 2) }}
                                            </div>
                                        </td>
                                        
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <div class="text-sm text-zinc-900 dark:text-zinc-100">
                                                ${{ number_format($employee['allowances'], 2) }}
                                            </div>
                                        </td>
                                        
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <div class="text-sm text-zinc-900 dark:text-zinc-100">
                                                ${{ number_format($employee['deductions'], 2) }}
                                            </div>
                                        </td>
                                        
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                                ${{ number_format($employee['net_salary'], 2) }}
                                            </div>
                                        </td>
                                        
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <flux:badge color="yellow" size="sm">
                                                {{ __('Pending') }}
                                            </flux:badge>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-8">
                        <flux:icon name="user-group" class="w-12 h-12 mx-auto mb-4 text-zinc-400" />
                        <p class="text-zinc-500 dark:text-zinc-400">{{ __('No employees found for this month.') }}</p>
                    </div>
                @endif

                <div class="flex justify-end gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                    <flux:button variant="ghost" wire:click="closeMonthEmployeesFlyout">
                        {{ __('Close') }}
                    </flux:button>
                </div>
            </div>
        </flux:modal>

        <!-- Process Payroll Modal -->
        <flux:modal wire:model="showProcessPayrollModal" class="max-w-4xl">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">{{ __('Process Payroll') }}</flux:heading>
                    <flux:text class="mt-2 text-zinc-500 dark:text-zinc-400">
                        {{ __('Select a processing method for the selected month') }}
                    </flux:text>
                </div>

                <!-- Month and Year Selection -->
                <div class="grid grid-cols-2 gap-4">
                    <flux:field>
                        <flux:label>{{ __('Month') }}</flux:label>
                        <flux:select wire:model="processMonth">
                            @for($month = 1; $month <= 12; $month++)
                                <option value="{{ $month }}">{{ date('F', mktime(0, 0, 0, $month, 1)) }}</option>
                            @endfor
                        </flux:select>
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('Year') }}</flux:label>
                        <flux:select wire:model="processYear">
                            @for($year = now()->year; $year >= now()->year - 5; $year--)
                                <option value="{{ $year }}">{{ $year }}</option>
                            @endfor
                        </flux:select>
                    </flux:field>
                </div>

                <!-- Processing Options Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">
                    <!-- Monthly Attendance Processing Card -->
                    <div 
                        wire:click="selectProcessingType('monthly_attendance')"
                        class="border-2 rounded-lg p-6 cursor-pointer transition-all duration-200 {{ $selectedProcessingType === 'monthly_attendance' ? 'border-blue-500 dark:border-blue-400 bg-blue-50 dark:bg-blue-900/20 ring-2 ring-blue-500/20' : 'border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 hover:border-blue-500 dark:hover:border-blue-400' }}"
                    >
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                                    <flux:icon name="calendar-days" class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                                </div>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-2">
                                    <flux:heading size="md" level="3">
                                        {{ __('Monthly Attendance Processing') }}
                                    </flux:heading>
                                    @if($selectedProcessingType === 'monthly_attendance')
                                        <flux:icon name="check-circle" class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                                    @endif
                                </div>
                                <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">
                                    {{ __('Process payroll based on monthly attendance records and calculate salary accordingly.') }}
                                </flux:text>
                            </div>
                        </div>
                    </div>

                    <!-- Custom Processing Card -->
                    <div 
                        wire:click="selectProcessingType('custom')"
                        class="border-2 rounded-lg p-6 cursor-pointer transition-all duration-200 {{ $selectedProcessingType === 'custom' ? 'border-purple-500 dark:border-purple-400 bg-purple-50 dark:bg-purple-900/20 ring-2 ring-purple-500/20' : 'border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 hover:border-purple-500 dark:hover:border-purple-400' }}"
                    >
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center">
                                    <flux:icon name="cog-6-tooth" class="w-6 h-6 text-purple-600 dark:text-purple-400" />
                                </div>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-2">
                                    <flux:heading size="md" level="3">
                                        {{ __('Custom Processing') }}
                                    </flux:heading>
                                    @if($selectedProcessingType === 'custom')
                                        <flux:icon name="check-circle" class="w-5 h-5 text-purple-600 dark:text-purple-400" />
                                    @endif
                                </div>
                                <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">
                                    {{ __('Process payroll with custom settings, adjustments, and manual overrides.') }}
                                </flux:text>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                    <flux:button variant="ghost" wire:click="closeProcessPayrollModal">
                        {{ __('Cancel') }}
                    </flux:button>
                    @if($selectedProcessingType)
                        <flux:button variant="primary" wire:click="createPayroll" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="createPayroll">{{ __('Create draft run') }}</span>
                            <span wire:loading wire:target="createPayroll">{{ __('Creating...') }}</span>
                        </flux:button>
                    @endif
                </div>
            </div>
        </flux:modal>
    </x-payroll.layout>
</section>
