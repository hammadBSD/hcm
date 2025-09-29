<div class="flex items-start max-md:flex-col">
    <div class="me-10 w-full pb-4 md:w-[220px]">
        <flux:navlist>
            <flux:navlist.item href="#" wire:navigate>{{ __('All Employees') }}</flux:navlist.item>
            <flux:navlist.item href="#" wire:navigate>{{ __('New Hires') }}</flux:navlist.item>
            <flux:navlist.item href="#" wire:navigate>{{ __('Terminated') }}</flux:navlist.item>
            <flux:navlist.item href="#" wire:navigate>{{ __('On Leave') }}</flux:navlist.item>
            <flux:navlist.item href="#" wire:navigate>{{ __('Departments') }}</flux:navlist.item>
            <flux:navlist.item href="#" wire:navigate>{{ __('Positions') }}</flux:navlist.item>
            <flux:navlist.item href="#" wire:navigate>{{ __('Two-Factor Auth') }}</flux:navlist.item>
            <flux:navlist.item href="#" wire:navigate>{{ __('Appearance') }}</flux:navlist.item>
        </flux:navlist>
    </div>

    <flux:separator class="md:hidden" />

    <div class="flex-1 self-stretch max-md:pt-6">
        <flux:heading>{{ $heading ?? '' }}</flux:heading>
        <flux:subheading>{{ $subheading ?? '' }}</flux:subheading>

        <div class="mt-5 w-full max-w-lg">
            {{ $slot }}
        </div>
    </div>
</div>
