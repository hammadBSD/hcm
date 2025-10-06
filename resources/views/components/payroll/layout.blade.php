<div class="flex items-start max-md:flex-col">
    <div class="me-10 w-full pb-4 md:w-[220px]">
        <flux:navlist>
            <div class="mb-3">
                <flux:navlist.item 
                    :href="route('payroll.index')" 
                    wire:navigate
                    :class="request()->routeIs('payroll.index') ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100' : ''"
                >
                    <div class="flex items-center">
                        <flux:icon name="currency-dollar" class="w-4 h-4 mr-3" />
                        {{ __('My Payslip') }}
                    </div>
                </flux:navlist.item>
            </div>
            <div class="mb-3">
                <flux:navlist.item 
                    :href="route('payroll.payroll-processing')" 
                    wire:navigate
                    :class="request()->routeIs('payroll.payroll-processing') ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100' : ''"
                >
                    <div class="flex items-center">
                        <flux:icon name="calculator" class="w-4 h-4 mr-3" />
                        {{ __('Payroll Processing') }}
                    </div>
                </flux:navlist.item>
            </div>
            <div class="mb-3">
                <flux:navlist.item 
                    :href="route('payroll.salary-reports')" 
                    wire:navigate
                    :class="request()->routeIs('payroll.salary-reports') ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100' : ''"
                >
                    <div class="flex items-center">
                        <flux:icon name="chart-bar" class="w-4 h-4 mr-3" />
                        {{ __('Salary Reports') }}
                    </div>
                </flux:navlist.item>
            </div>
            <div class="mb-3">
                <flux:navlist.item 
                    :href="route('payroll.bonus-management')" 
                    wire:navigate
                    :class="request()->routeIs('payroll.bonus-management') ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100' : ''"
                >
                    <div class="flex items-center">
                        <flux:icon name="gift" class="w-4 h-4 mr-3" />
                        {{ __('Bonus Management') }}
                    </div>
                </flux:navlist.item>
            </div>
            <div class="mb-3">
                <flux:navlist.item 
                    :href="route('payroll.advance-salary')" 
                    wire:navigate
                    :class="request()->routeIs('payroll.advance-salary') ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100' : ''"
                >
                    <div class="flex items-center">
                        <flux:icon name="banknotes" class="w-4 h-4 mr-3" />
                        {{ __('Advance Salary') }}
                    </div>
                </flux:navlist.item>
            </div>
            <div class="mb-3">
                <flux:navlist.item 
                    :href="route('payroll.loan-management')" 
                    wire:navigate
                    :class="request()->routeIs('payroll.loan-management') ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100' : ''"
                >
                    <div class="flex items-center">
                        <flux:icon name="credit-card" class="w-4 h-4 mr-3" />
                        {{ __('Loan Management') }}
                    </div>
                </flux:navlist.item>
            </div>
            <div class="mb-3">
                <flux:navlist.item 
                    :href="route('payroll.tax-management')" 
                    wire:navigate
                    :class="request()->routeIs('payroll.tax-management') ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100' : ''"
                >
                    <div class="flex items-center">
                        <flux:icon name="document-text" class="w-4 h-4 mr-3" />
                        {{ __('Tax Management') }}
                    </div>
                </flux:navlist.item>
            </div>
            <div class="mb-3">
                <flux:navlist.item 
                    :href="route('payroll.payroll-settings')" 
                    wire:navigate
                    :class="request()->routeIs('payroll.payroll-settings') ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100' : ''"
                >
                    <div class="flex items-center">
                        <flux:icon name="cog-6-tooth" class="w-4 h-4 mr-3" />
                        {{ __('Payroll Settings') }}
                    </div>
                </flux:navlist.item>
            </div>
        </flux:navlist>
    </div>

    <div class="flex-1">
        {{ $slot }}
    </div>
</div>
