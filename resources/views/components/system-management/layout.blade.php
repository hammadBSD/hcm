<div class="flex items-start max-md:flex-col">
    <div class="me-10 w-full pb-4 md:w-[220px]">
        <flux:navlist>
            <flux:navlist.item :href="route('system-management.index')" wire:navigate>{{ __('Dashboard') }}</flux:navlist.item>
            <flux:navlist.item href="#" wire:navigate>{{ __('System Settings') }}</flux:navlist.item>
            <flux:navlist.item href="#" wire:navigate>{{ __('User Management') }}</flux:navlist.item>
            <flux:navlist.item href="#" wire:navigate>{{ __('Database Management') }}</flux:navlist.item>
            <flux:navlist.item href="#" wire:navigate>{{ __('Backup & Restore') }}</flux:navlist.item>
            <flux:navlist.item href="#" wire:navigate>{{ __('Logs & Monitoring') }}</flux:navlist.item>
            <flux:navlist.item href="#" wire:navigate>{{ __('Security Settings') }}</flux:navlist.item>
            <flux:navlist.item href="#" wire:navigate>{{ __('API Management') }}</flux:navlist.item>
        </flux:navlist>
    </div>

    <flux:separator class="md:hidden" />

    <div class="flex-1 self-stretch max-md:pt-6">
        <flux:heading>{{ $heading ?? '' }}</flux:heading>
        <flux:subheading>{{ $subheading ?? '' }}</flux:heading>

        <div class="mt-5 w-full">
            {{ $slot }}
        </div>
    </div>
</div>
