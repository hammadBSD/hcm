<section class="w-full">
    @include('partials.payroll-heading')

    <x-payroll.layout :heading="__('My Payslip')" :subheading="__('View your salary and payslip information')">
        <div class="space-y-6">
            @if($employee)
                <!-- Employee Info Header -->
                <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-6">
                    <div class="flex items-center gap-6">
                        <div class="relative">
                            @if($employee->profile_picture)
                                <img src="{{ asset('storage/' . $employee->profile_picture) }}" 
                                     alt="{{ $employee->first_name }} {{ $employee->last_name }}" 
                                     class="w-24 h-24 rounded-full object-cover border-4 border-zinc-200 dark:border-zinc-700">
                            @else
                                <div class="w-24 h-24 rounded-full bg-zinc-100 dark:bg-zinc-700 flex items-center justify-center border-4 border-zinc-200 dark:border-zinc-700">
                                    <span class="text-2xl font-semibold text-zinc-600 dark:text-zinc-400">
                                        {{ strtoupper(substr($employee->first_name, 0, 1)) }}{{ strtoupper(substr($employee->last_name, 0, 1)) }}
                                    </span>
                                </div>
                            @endif
                        </div>
                        <div class="flex-1">
                            <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">
                                {{ $employee->first_name }} {{ $employee->last_name }}
                            </h1>
                            <p class="text-lg text-zinc-600 dark:text-zinc-400">
                                {{ $employee->designation ?? 'Not Assigned' }}
                            </p>
                            <div class="flex items-center gap-4 mt-2">
                                <span class="text-sm text-zinc-500 dark:text-zinc-400">
                                    Employee ID: <span class="font-medium">{{ $employee->employee_code ?? 'Not Assigned' }}</span>
                                </span>
                                <span class="text-sm text-zinc-500 dark:text-zinc-400">
                                    Department: <span class="font-medium">{{ $employee->department ?? 'Not Assigned' }}</span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payslips Section -->
                <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700">
                    <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Payslips') }}</h3>
                            <div class="flex items-center gap-4">
                                <div class="w-32">
                                    <!-- <flux:label class="text-sm">{{ __('Year') }}</flux:label> -->
                                    <flux:select wire:model.live="selectedYear" placeholder="{{ __('Year') }}" size="sm">
                                        @for($year = now()->year; $year >= now()->year - 5; $year--)
                                            <option value="{{ $year }}">{{ $year }}</option>
                                        @endfor
                                    </flux:select>
                                </div>
                                <div class="w-32">
                                    <!-- <flux:label class="text-sm">{{ __('Month') }}</flux:label> -->
                                    <flux:select wire:model.live="selectedMonth" placeholder="{{ __('Month') }}" size="sm">
                                        @for($month = 1; $month <= 12; $month++)
                                            <option value="{{ $month }}">{{ date('F', mktime(0, 0, 0, $month, 1)) }}</option>
                                        @endfor
                                    </flux:select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="p-6">
                        @if(count($payslips) > 0)
                            <div class="space-y-4">
                                @foreach($payslips as $payslip)
                                    <div class="border border-zinc-200 dark:border-zinc-700 rounded-lg p-4 hover:bg-zinc-50 dark:hover:bg-zinc-700/50 transition-colors">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-4">
                                                <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center">
                                                    <flux:icon name="currency-dollar" class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                                                </div>
                                                <div>
                                                    <h4 class="font-semibold text-zinc-900 dark:text-zinc-100">
                                                        Payslip - {{ date('F Y', strtotime($payslip['month'] . '-01')) }}
                                                    </h4>
                                                    <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                                        Basic: ${{ number_format($payslip['basic_salary'], 2) }} | 
                                                        Allowances: ${{ number_format($payslip['allowances'], 2) }} | 
                                                        Deductions: ${{ number_format($payslip['deductions'], 2) }}
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <div class="text-lg font-bold text-zinc-900 dark:text-zinc-100">
                                                    ${{ number_format($payslip['net_salary'], 2) }}
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $payslip['status'] === 'paid' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' }}">
                                                        {{ ucfirst($payslip['status']) }}
                                                    </span>
                                                    <flux:button variant="ghost" size="sm">
                                                        <flux:icon name="eye" class="w-4 h-4" />
                                                    </flux:button>
                                                    <flux:button variant="ghost" size="sm">
                                                        <flux:icon name="arrow-down-tray" class="w-4 h-4" />
                                                    </flux:button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-12">
                                <flux:icon name="document-text" class="w-16 h-16 text-zinc-400 dark:text-zinc-500 mx-auto mb-4" />
                                <h3 class="text-lg font-medium text-zinc-900 dark:text-zinc-100 mb-2">{{ __('No Payslips Found') }}</h3>
                                <p class="text-zinc-500 dark:text-zinc-400">{{ __('No payslips available for the selected period.') }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            @else
                <!-- No Employee Record -->
                <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-12">
                    <div class="text-center">
                        <flux:icon name="exclamation-triangle" class="w-16 h-16 text-yellow-500 mx-auto mb-4" />
                        <h3 class="text-lg font-medium text-zinc-900 dark:text-zinc-100 mb-2">{{ __('No Employee Record Found') }}</h3>
                        <p class="text-zinc-500 dark:text-zinc-400">{{ __('Your employee record could not be found. Please contact HR for assistance.') }}</p>
                    </div>
                </div>
            @endif
        </div>
    </x-payroll.layout>
</section>
