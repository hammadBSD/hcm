@php
    $user = auth()->user();
    $hasAnyRecruitmentMenu = $user && (
        $user->can('recruitment.view') ||
        $user->can('recruitment.create') ||
        $user->can('recruitment.settings') ||
        $user->can('recruitment.summary')
    );
@endphp

<div class="flex items-start max-md:flex-col">
    @if($user && $hasAnyRecruitmentMenu)
        <div class="me-10 w-full pb-4 md:w-[220px]">
            <flux:navlist>
                @can('recruitment.view')
                    <div class="mb-3">
                        <flux:navlist.item 
                            :href="route('recruitment.index')" 
                            wire:navigate
                            :class="request()->routeIs('recruitment.index') ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100' : ''"
                        >
                            <div class="flex items-center">
                                <flux:icon name="briefcase" class="w-4 h-4 mr-3" />
                                {{ __('Overview') }}
                            </div>
                        </flux:navlist.item>
                    </div>
                @endcan

                @can('recruitment.create')
                    <div class="mb-3">
                        <flux:navlist.item 
                            :href="route('recruitment.jobs.create')" 
                            wire:navigate
                            :class="request()->routeIs('recruitment.jobs.create') ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100' : ''"
                        >
                            <div class="flex items-center">
                                <flux:icon name="plus-circle" class="w-4 h-4 mr-3" />
                                {{ __('Create Job Post') }}
                            </div>
                        </flux:navlist.item>
                    </div>
                @endcan

                @can('recruitment.settings')
                    <div class="mb-3">
                        <flux:navlist.item 
                            :href="route('recruitment.settings')" 
                            wire:navigate
                            :class="request()->routeIs('recruitment.settings') ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100' : ''"
                        >
                            <div class="flex items-center">
                                <flux:icon name="cog-6-tooth" class="w-4 h-4 mr-3" />
                                {{ __('Job Post Settings') }}
                            </div>
                        </flux:navlist.item>
                    </div>
                @endcan

                @can('recruitment.summary')
                    <div class="mb-3">
                        <flux:navlist.item 
                            :href="route('recruitment.summary')" 
                            wire:navigate
                            :class="request()->routeIs('recruitment.summary') ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100' : ''"
                        >
                            <div class="flex items-center">
                                <flux:icon name="chart-bar" class="w-4 h-4 mr-3" />
                                {{ __('Summary/Report') }}
                            </div>
                        </flux:navlist.item>
                    </div>
                @endcan
            </flux:navlist>
        </div>
    @endif

    <div class="flex-1 min-w-0 overflow-x-hidden">
        {{ $slot }}
    </div>
</div>
