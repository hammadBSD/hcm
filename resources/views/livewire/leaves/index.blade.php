<section class="w-full">
    @include('partials.leaves-heading')

    <x-leaves.layout :heading="__('Leaves')" :subheading="__('View and manage all leave requests')">
        <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-8 text-center">
            <flux:heading size="lg" level="3" class="text-zinc-600 dark:text-zinc-400">
                {{ __('Leaves Dashboard') }}
            </flux:heading>
            <flux:text class="mt-2 text-zinc-500 dark:text-zinc-500">
                {{ __('Welcome to the Leaves module. Use the sidebar to navigate to different leave features.') }}
            </flux:text>
        </div>
    </x-leaves.layout>
</section>
