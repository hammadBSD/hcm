<div class="flex items-start max-md:flex-col">
    <div class="me-10 w-full pb-4 md:w-[220px]">
        <flux:navlist>
            <div class="mb-3">
                <flux:navlist.item 
                    :href="route('employees.index')" 
                    wire:navigate
                    :class="request()->routeIs('employees.index') ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100' : ''"
                >
                    <div class="flex items-center">
                        <flux:icon name="user-circle" class="w-4 h-4 mr-3" />
                        {{ __('My Profile') }}
                    </div>
                </flux:navlist.item>
            </div>
            @can('employees.sidebar.directory')
                <div class="mb-3">
                    <flux:navlist.item 
                        :href="route('employees.list')" 
                        wire:navigate
                        :class="request()->routeIs('employees.list') ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100' : ''"
                    >
                        <div class="flex items-center">
                            <flux:icon name="users" class="w-4 h-4 mr-3" />
                            {{ __('Employees') }}
                        </div>
                    </flux:navlist.item>
                </div>
            @endcan

            @can('employees.sidebar.create')
                <div class="mb-3">
                    <flux:navlist.item 
                        :href="route('employees.register')" 
                        wire:navigate
                        :class="request()->routeIs('employees.register') ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100' : ''"
                    >
                        <div class="flex items-center">
                            <flux:icon name="user-plus" class="w-4 h-4 mr-3" />
                            {{ __('Create Employee') }}
                        </div>
                    </flux:navlist.item>
                </div>
            @endcan

            @can('employees.sidebar.import')
                <div class="mb-3">
                    <flux:navlist.item 
                        :href="route('employees.import')" 
                        wire:navigate
                        :class="request()->routeIs('employees.import') ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100' : ''"
                    >
                        <div class="flex items-center">
                            <flux:icon name="document-plus" class="w-4 h-4 mr-3" />
                            {{ __('Import Employees') }}
                        </div>
                    </flux:navlist.item>
                </div>
            @endcan

            @can('employees.sidebar.transfer')
                <div class="mb-3">
                    <flux:navlist.item 
                        :href="route('employees.transfer')" 
                        wire:navigate
                        :class="request()->routeIs('employees.transfer') ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100' : ''"
                    >
                        <div class="flex items-center">
                            <flux:icon name="arrow-path" class="w-4 h-4 mr-3" />
                            {{ __('Transfer') }}
                        </div>
                    </flux:navlist.item>
                </div>
            @endcan

            @can('employees.sidebar.delegation')
                <div class="mb-3">
                    <flux:navlist.item 
                        :href="route('employees.delegation-request')" 
                        wire:navigate
                        :class="request()->routeIs('employees.delegation-request') ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100' : ''"
                    >
                        <div class="flex items-center">
                            <flux:icon name="hand-thumb-up" class="w-4 h-4 mr-3" />
                            {{ __('Delegation Requests') }}
                        </div>
                    </flux:navlist.item>
                </div>
            @endcan

            @can('employees.sidebar.amend_department')
                <div class="mb-3">
                    <flux:navlist.item 
                        :href="route('employees.amend-dept')" 
                        wire:navigate
                        :class="request()->routeIs('employees.amend-dept') ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100' : ''"
                    >
                        <div class="flex items-center">
                            <flux:icon name="pencil-square" class="w-4 h-4 mr-3" />
                            {{ __('Amend Employee Dept') }}
                        </div>
                    </flux:navlist.item>
                </div>
            @endcan

            @can('employees.sidebar.suggestions')
                <div class="mb-3">
                    <flux:navlist.item 
                        :href="route('employees.suggestions')" 
                        wire:navigate
                        :class="request()->routeIs('employees.suggestions') ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100' : ''"
                    >
                        <div class="flex items-center">
                            <flux:icon name="light-bulb" class="w-4 h-4 mr-3" />
                            {{ __('Suggestions') }}
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
