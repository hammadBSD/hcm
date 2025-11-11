<div class="flex items-start max-md:flex-col">
    <div class="me-10 w-full pb-4 md:w-[220px]">
        <flux:navlist>
            @can('attendance.sidebar.my_attendance')
                <div class="mb-3">
                    <flux:navlist.item 
                        :href="route('attendance.index')" 
                        wire:navigate
                        :class="request()->routeIs('attendance.index') ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100' : ''"
                    >
                        <div class="flex items-center">
                            <flux:icon name="clock" class="w-4 h-4 mr-3" />
                            {{ __('Attendance') }}
                        </div>
                    </flux:navlist.item>
                </div>
            @endcan

            @can('attendance.sidebar.requests')
                <div class="mb-3">
                    <flux:navlist.item 
                        :href="route('attendance.request')" 
                        wire:navigate
                        :class="request()->routeIs('attendance.request') ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100' : ''"
                    >
                        <div class="flex items-center">
                            <flux:icon name="clipboard-document" class="w-4 h-4 mr-3" />
                            {{ __('Attendance Request') }}
                        </div>
                    </flux:navlist.item>
                </div>
            @endcan

            @can('attendance.sidebar.exemptions')
                <div class="mb-3">
                    <flux:navlist.item 
                        :href="route('attendance.exemption-request')" 
                        wire:navigate
                        :class="request()->routeIs('attendance.exemption-request') ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100' : ''"
                    >
                        <div class="flex items-center">
                            <flux:icon name="exclamation-triangle" class="w-4 h-4 mr-3" />
                            {{ __('Exemption Request') }}
                        </div>
                    </flux:navlist.item>
                </div>
            @endcan

            @can('attendance.sidebar.approvals')
                <div class="mb-3">
                    <flux:navlist.item 
                        :href="route('attendance.attendance-approval')" 
                        wire:navigate
                        :class="request()->routeIs('attendance.attendance-approval') ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100' : ''"
                    >
                        <div class="flex items-center">
                            <flux:icon name="check-circle" class="w-4 h-4 mr-3" />
                            {{ __('Attendance Approval') }}
                        </div>
                    </flux:navlist.item>
                </div>
            @endcan

            @can('attendance.sidebar.schedule')
                <div class="mb-3">
                    <flux:navlist.item 
                        :href="route('attendance.schedule')" 
                        wire:navigate
                        :class="request()->routeIs('attendance.schedule') ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100' : ''"
                    >
                        <div class="flex items-center">
                            <flux:icon name="calendar-days" class="w-4 h-4 mr-3" />
                            {{ __('Schedule') }}
                        </div>
                    </flux:navlist.item>
                </div>
            @endcan
        </flux:navlist>
    </div>

    <flux:separator class="md:hidden" />

    <div class="flex-1 self-stretch max-md:pt-6">
        <flux:heading>{{ $heading ?? '' }}</flux:heading>
        <flux:subheading>{{ $subheading ?? '' }}</flux:subheading>

        <div class="mt-5 w-full">
            {{ $slot }}
        </div>
    </div>
</div>
