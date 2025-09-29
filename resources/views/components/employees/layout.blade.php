<div class="flex items-start max-md:flex-col">
    <div class="me-10 w-full pb-4 md:w-[220px]">
        <flux:navlist>
            <flux:navlist.item :href="route('employees.list')" wire:navigate>{{ __('Employees') }}</flux:navlist.item>
            <flux:navlist.item :href="route('employees.register')" wire:navigate>{{ __('Create Employee') }}</flux:navlist.item>
            <flux:navlist.item :href="route('employees.role')" wire:navigate>{{ __('Roles & Permissions') }}</flux:navlist.item>
            <flux:navlist.item :href="route('employees.transfer')" wire:navigate>{{ __('Transfer') }}</flux:navlist.item>
            <flux:navlist.item :href="route('employees.delegation-request')" wire:navigate>{{ __('Delegation Requests') }}</flux:navlist.item>
            <flux:navlist.item :href="route('employees.amend-dept')" wire:navigate>{{ __('Amend Employee Dept') }}</flux:navlist.item>
            <flux:navlist.item :href="route('employees.suggestions')" wire:navigate>{{ __('Suggestions') }}</flux:navlist.item>
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
