<section class="w-full">
@include('partials.employees-heading')
    <x-employees.layout :heading="__('Employee Transfer')" :subheading="__('Manage employee transfers between departments')">
        <div class="space-y-6">
            <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-6">
                <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100 mb-2">Employee Transfers</h2>
                <p class="text-sm text-zinc-600 dark:text-zinc-400 mb-4">Manage employee transfers between departments</p>
                <p class="text-zinc-600 dark:text-zinc-400">Employee transfer management functionality will be implemented here.</p>
            </div>
        </div>
    </x-employees.layout>
</section>
