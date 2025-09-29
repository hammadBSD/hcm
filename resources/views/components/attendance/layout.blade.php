<div class="flex items-start max-md:flex-col">
    <div class="me-10 w-full pb-4 md:w-[220px]">
        <flux:navlist>
            <flux:navlist.item :href="route('attendance.index')" wire:navigate>{{ __('Attendance') }}</flux:navlist.item>
            <flux:navlist.item :href="route('attendance.request')" wire:navigate>{{ __('Attendance Request') }}</flux:navlist.item>
            <flux:navlist.item :href="route('attendance.exemption-request')" wire:navigate>{{ __('Exemption Request') }}</flux:navlist.item>
            <flux:navlist.item :href="route('attendance.attendance-approval')" wire:navigate>{{ __('Attendance Approval') }}</flux:navlist.item>
            <flux:navlist.item :href="route('attendance.schedule')" wire:navigate>{{ __('Schedule') }}</flux:navlist.item>
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
