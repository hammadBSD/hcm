<section class="w-full">
    @include('partials.attendance-heading')

    <x-attendance.layout :heading="__('Attendance')" :subheading="__('View and manage attendance records')">
        <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-8 text-center">
            <flux:heading size="lg" level="3" class="text-zinc-600 dark:text-zinc-400">
                {{ __('Attendance Dashboard') }}
            </flux:heading>
            <flux:text class="mt-2 text-zinc-500 dark:text-zinc-500">
                {{ __('Welcome to the Attendance module. Use the sidebar to navigate to different attendance features.') }}
            </flux:text>
        </div>
    </x-attendance.layout>
</section>
