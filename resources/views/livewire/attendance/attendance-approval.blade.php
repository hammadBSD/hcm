<section class="w-full">
    @include('partials.attendance-heading')

    <x-attendance.layout :heading="__('Attendance Approval')" :subheading="__('Review and approve attendance records')">
        <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-8 text-center">
            <flux:heading size="lg" level="3" class="text-zinc-600 dark:text-zinc-400">
                {{ __('Attendance Approval') }}
            </flux:heading>
            <flux:text class="mt-2 text-zinc-500 dark:text-zinc-500">
                {{ __('This page will contain attendance approval functionality.') }}
            </flux:text>
        </div>
    </x-attendance.layout>
</section>
