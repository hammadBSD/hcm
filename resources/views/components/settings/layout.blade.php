<div class="flex items-start max-md:flex-col">
    <div class="me-10 w-full pb-4 md:w-[220px]">
        <flux:navlist>
            <div class="mb-3">
                <flux:navlist.item :href="route('settings.profile')" wire:navigate>{{ __('Profile') }}</flux:navlist.item>
            </div>
            <div class="mb-3">
                <flux:navlist.item :href="route('settings.password')" wire:navigate>{{ __('Password') }}</flux:navlist.item>
            </div>
            @if (Laravel\Fortify\Features::canManageTwoFactorAuthentication())
            <div class="mb-3">
                <flux:navlist.item :href="route('two-factor.show')" wire:navigate>{{ __('Two-Factor Auth') }}</flux:navlist.item>
            </div>
            @endif
            <div class="mb-3">
                <flux:navlist.item :href="route('settings.appearance')" wire:navigate>{{ __('Appearance') }}</flux:navlist.item>
            </div>
            <!-- <div class="mb-3">
                <flux:navlist.item :href="route('settings.api-monitor')" wire:navigate>{{ __('API Monitor') }}</flux:navlist.item>
            </div> -->
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
