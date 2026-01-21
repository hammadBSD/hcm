@php
    $user = auth()->user();
    // Only Super Admin can access recruitment module
    $hasAnyRecruitmentMenu = $user && $user->hasRole('Super Admin');
@endphp

<div class="flex items-start max-md:flex-col">
    @if($user && $hasAnyRecruitmentMenu)
        <div class="me-10 w-full pb-4 md:w-[220px]">
            <flux:navlist>
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
                <div class="mb-3">
                    <flux:navlist.item 
                        :href="route('recruitment.jobs.settings')" 
                        wire:navigate
                        :class="request()->routeIs('recruitment.jobs.settings') ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100' : ''"
                    >
                        <div class="flex items-center">
                            <flux:icon name="cog-6-tooth" class="w-4 h-4 mr-3" />
                            {{ __('Job Post Settings') }}
                        </div>
                    </flux:navlist.item>
                </div>
            </flux:navlist>
        </div>
    @endif

    <div class="flex-1 min-w-0 overflow-x-hidden">
        {{ $slot }}
    </div>
</div>
