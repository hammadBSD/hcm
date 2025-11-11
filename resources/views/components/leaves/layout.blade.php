@php
    $user = auth()->user();
    $canViewSelfLeaves = $user?->can('leaves.view.self');
    $canViewAllLeaves = $user?->can('leaves.view.all') || $user?->can('leaves.manage.all');
    $canManageAllLeaves = $user?->can('leaves.manage.all');
    $canSubmitLeaveRequest = $user?->can('leaves.request.submit');

    $showMyLeaves = $user?->can('leaves.sidebar.my_leaves') && $canViewSelfLeaves;
    $showAllLeaves = $user?->can('leaves.sidebar.all_leaves') && $canViewAllLeaves;
    $showLeaveRequestForm = $user?->can('leaves.sidebar.request_form') && $canSubmitLeaveRequest;

    $hasAnyLeavesMenu = $showMyLeaves || $showAllLeaves || $showLeaveRequestForm;
@endphp

<div class="flex items-start max-md:flex-col">
    @if($hasAnyLeavesMenu)
        <div class="me-10 w-full pb-4 md:w-[220px]">
            <flux:navlist>
                @if($showMyLeaves)
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
                @endif

                @if($showAllLeaves)
                    <div class="mb-3">
                        <flux:navlist.item 
                            :href="route('leaves.employees-leaves')" 
                            wire:navigate
                            :class="request()->routeIs('leaves.employees-leaves') ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100' : ''"
                        >
                            <div class="flex items-center">
                                <flux:icon name="users" class="w-4 h-4 mr-3" />
                                {{ __('All Leave Requests') }}
                            </div>
                        </flux:navlist.item>
                    </div>
                @endif

                @if($showLeaveRequestForm)
                    <div class="mb-3">
                        <flux:navlist.item 
                            :href="route('leaves.leave-request')" 
                            wire:navigate
                            :class="request()->routeIs('leaves.leave-request') ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100' : ''"
                        >
                            <div class="flex items-center">
                                <flux:icon name="plus-circle" class="w-4 h-4 mr-3" />
                                {{ __('Submit Leave Request') }}
                            </div>
                        </flux:navlist.item>
                    </div>
                @endif
            </flux:navlist>
        </div>
    @endif

    <flux:separator class="md:hidden" />

    <div class="flex-1 self-stretch max-md:pt-6">
        <flux:heading>{{ $heading ?? '' }}</flux:heading>
        <flux:subheading>{{ $subheading ?? '' }}</flux:subheading>

        <div class="mt-5 w-full">
            {{ $slot }}
        </div>
    </div>
</div>
