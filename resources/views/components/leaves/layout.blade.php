<div class="flex items-start max-md:flex-col">
    <div class="me-10 w-full pb-4 md:w-[220px]">
        <flux:navlist>
            <div class="mb-3">
                <flux:navlist.item 
                    :href="route('leaves.index')" 
                    wire:navigate
                    :class="request()->routeIs('leaves.index') ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100' : ''"
                >
                    <div class="flex items-center">
                        <flux:icon name="user" class="w-4 h-4 mr-3" />
                        {{ __('My Leaves') }}
                    </div>
                </flux:navlist.item>
            </div>
            <div class="mb-3">
                <flux:navlist.item 
                    :href="route('leaves.employees-leaves')" 
                    wire:navigate
                    :class="request()->routeIs('leaves.employees-leaves') ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100' : ''"
                >
                    <div class="flex items-center">
                        <flux:icon name="users" class="w-4 h-4 mr-3" />
                        {{ __('Leave Requests') }}
                    </div>
                </flux:navlist.item>
            </div>
            <div class="mb-3">
                <flux:navlist.item 
                    :href="route('leaves.leave-request')" 
                    wire:navigate
                    :class="request()->routeIs('leaves.leave-request') ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100' : ''"
                >
                    <div class="flex items-center">
                        <flux:icon name="plus-circle" class="w-4 h-4 mr-3" />
                        {{ __('Leave Request') }}
                    </div>
                </flux:navlist.item>
            </div>
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
