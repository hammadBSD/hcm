<section class="w-full">
    @include('partials.system-management-heading')

    <x-system-management.layout :heading="__('System Management')" :subheading="__('Manage system settings and configurations')">
        <div class="space-y-6">
            <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-6">
                <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100 mb-2">System Management</h2>
                <p class="text-sm text-zinc-600 dark:text-zinc-400 mb-4">Manage system settings and configurations</p>
                <p class="text-zinc-600 dark:text-zinc-400">System management features will be implemented here.</p>
            </div>
        </div>
    </x-system-management.layout>
</section>
