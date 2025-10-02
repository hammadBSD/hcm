<section class="w-full">
    @include('partials.system-management-heading')

    <x-system-management.layout :heading="__('Tasks')" :subheading="__('Manage tasks')">
        <div class="space-y-6">
            <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-6">
                <p class="text-zinc-600 dark:text-zinc-400">Tasks management features will be implemented here.</p>
            </div>
        </div>
    </x-system-management.layout>
</section>
