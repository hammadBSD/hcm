<div class="flex items-start max-md:flex-col">
    <div class="me-10 w-full pb-4 md:w-[220px]">
        <flux:navlist>
            @canany(['tasks.view.self', 'tasks.view.team', 'tasks.view.company', 'tasks.view'])
                <div class="mb-3">
                    <flux:navlist.item 
                        :href="route('tasks.my-tasks')" 
                        wire:navigate
                        :class="request()->routeIs('tasks.my-tasks') || request()->routeIs('tasks.index') ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100' : ''"
                    >
                        <div class="flex items-center">
                            <flux:icon name="clipboard-document-list" class="w-4 h-4 mr-3" />
                            {{ __('Tasks') }}
                        </div>
                    </flux:navlist.item>
                </div>
            @endcanany

            @canany(['daily-logs.view.self', 'daily-logs.create', 'daily-logs.edit'])
                <div class="mb-3">
                    <flux:navlist.item 
                        :href="route('tasks.daily-log')" 
                        wire:navigate
                        :class="request()->routeIs('tasks.daily-log') ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100' : ''"
                    >
                        <div class="flex items-center">
                            <flux:icon name="clipboard-document-check" class="w-4 h-4 mr-3" />
                            {{ __('Daily Logs') }}
                        </div>
                    </flux:navlist.item>
                </div>
            @endcanany

            @can('tasks.manage.settings')
                <div class="mb-3">
                    <flux:navlist.item 
                        :href="route('tasks.settings')" 
                        wire:navigate
                        :class="request()->routeIs('tasks.settings') ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100' : ''"
                    >
                        <div class="flex items-center">
                            <flux:icon name="cog-6-tooth" class="w-4 h-4 mr-3" />
                            {{ __('Tasks Settings') }}
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
