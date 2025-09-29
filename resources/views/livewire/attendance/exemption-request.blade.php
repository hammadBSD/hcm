<section class="w-full">
    @include('partials.attendance-heading')

    <x-attendance.layout :heading="__('Exemption Request')" :subheading="__('Manage exemption requests and approvals')">
        <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-8 text-center">
            <flux:heading size="lg" level="3" class="text-zinc-600 dark:text-zinc-400">
                {{ __('Exemption Request') }}
            </flux:heading>
            <flux:text class="mt-2 text-zinc-500 dark:text-zinc-500">
                {{ __('This page will contain exemption request functionality.') }}
            </flux:text>
        </div>
    </x-attendance.layout>
</section>
