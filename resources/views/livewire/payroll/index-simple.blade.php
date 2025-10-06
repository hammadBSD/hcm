<section class="w-full">
    @include('partials.payroll-heading')

    <x-payroll.layout :heading="__('My Payslip')" :subheading="__('View your salary and payslip information')">
        <div class="space-y-6">
            <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-6">
                <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Test Payroll Page</h3>
                <p class="text-zinc-500 dark:text-zinc-400">This is a simple test page to isolate the issue.</p>
            </div>
        </div>
    </x-payroll.layout>
</section>
