<section class="w-full">
    @include('partials.system-management-heading')

    <x-system-management.layout :heading="__('Leave Types')" :subheading="__('Define the different leave entitlements available in your organisation')">
        <div class="space-y-6">
            <!-- Action Bar -->
            <div class="flex justify-between items-center gap-4">
                <div class="flex items-center gap-3">
                    <flux:input
                        type="search"
                        icon="magnifying-glass"
                        wire:model.live.debounce.300ms="search"
                        placeholder="{{ __('Search leave types...') }}"
                        class="w-72"
                    />
                    <flux:button variant="ghost" icon="funnel" />
                    <!-- <flux:select wire:model.live="statusFilter" class="w-36">
                        <option value="active">{{ __('Active') }}</option>
                        <option value="inactive">{{ __('Inactive') }}</option>
                        <option value="all">{{ __('All Status') }}</option>
                    </flux:select> -->
                </div>

                <div class="flex items-center gap-3">
                    <flux:button variant="outline" icon="arrow-down-tray">
                        {{ __('Export') }}
                    </flux:button>
                    <flux:button variant="primary" icon="plus" wire:click="openCreateModal">
                        {{ __('Add Leave Type') }}
                    </flux:button>
                </div>
            </div>

            <!-- Leave Types Table -->
            <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 overflow-hidden shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-zinc-50 dark:bg-zinc-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    <button wire:click="sort('name')" class="flex items-center gap-1 hover:text-zinc-700 dark:hover:text-zinc-200">
                                        {{ __('Leave Type') }}
                                        @if($sortBy === 'name')
                                            <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="w-4 h-4" />
                                        @endif
                                    </button>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    <button wire:click="sort('code')" class="flex items-center gap-1 hover:text-zinc-700 dark:hover:text-zinc-200">
                                        {{ __('Code') }}
                                        @if($sortBy === 'code')
                                            <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="w-4 h-4" />
                                        @endif
                                    </button>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    {{ __('Approval') }}
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    {{ __('Paid') }}
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    <button wire:click="sort('status')" class="flex items-center gap-1 hover:text-zinc-700 dark:hover:text-zinc-200">
                                        {{ __('Status') }}
                                        @if($sortBy === 'status')
                                            <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="w-4 h-4" />
                                        @endif
                                    </button>
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    {{ __('Actions') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-zinc-800 divide-y divide-zinc-200 dark:divide-zinc-700">
                            @forelse($leaveTypes as $leaveType)
                                <tr class="hover:bg-zinc-100 dark:hover:bg-zinc-600 transition-colors duration-150">
                                    <td class="px-6 py-6">
                                        <div class="flex items-start gap-3">
                                            <div class="h-10 w-10 rounded-lg bg-zinc-100 dark:bg-zinc-700 flex items-center justify-center">
                                                <flux:icon :name="$leaveType->icon ?? 'document-text'" class="w-5 h-5 text-zinc-600 dark:text-zinc-300" />
                                            </div>
                                            <div>
                                                <div class="font-medium text-zinc-900 dark:text-zinc-100">
                                                    {{ $leaveType->name }}
                                                </div>
                                                @if($leaveType->description)
                                                    <div class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">
                                                        {{ $leaveType->description }}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-6 whitespace-nowrap text-sm text-zinc-700 dark:text-zinc-200 font-mono">
                                        {{ strtoupper($leaveType->code) }}
                                    </td>
                                    <td class="px-6 py-6 whitespace-nowrap">
                                        <flux:badge :color="$leaveType->requires_approval ? 'zinc' : 'green'" size="sm">
                                            {{ $leaveType->requires_approval ? __('Yes') : __('Auto') }}
                                        </flux:badge>
                                    </td>
                                    <td class="px-6 py-6 whitespace-nowrap">
                                        <flux:badge :color="$leaveType->is_paid ? 'green' : 'amber'" size="sm">
                                            {{ $leaveType->is_paid ? __('Paid') : __('Unpaid') }}
                                        </flux:badge>
                                    </td>
                                    <td class="px-6 py-6 whitespace-nowrap">
                                        <flux:badge :color="$leaveType->status === 'active' ? 'green' : 'zinc'" size="sm">
                                            {{ ucfirst($leaveType->status) }}
                                        </flux:badge>
                                    </td>
                                    <td class="px-6 py-6 whitespace-nowrap text-sm font-medium">
                                        <div class="flex items-center justify-center">
                                            <flux:dropdown>
                                                <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                                            <flux:menu>
                                                <flux:menu.item icon="pencil" wire:click="openEditModal({{ $leaveType->id }})">
                                                    {{ __('Edit Leave Type') }}
                                                </flux:menu.item>
                                                <flux:menu.item icon="adjustments-horizontal" wire:click="toggleStatus({{ $leaveType->id }})">
                                                    {{ $leaveType->status === 'active' ? __('Mark as Inactive') : __('Mark as Active') }}
                                                </flux:menu.item>
                                                <flux:menu.item icon="trash" wire:click="confirmDelete({{ $leaveType->id }})" class="text-red-600">
                                                    {{ __('Delete Leave Type') }}
                                                </flux:menu.item>
                                            </flux:menu>
                                            </flux:dropdown>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center">
                                        <flux:icon name="inbox" class="mx-auto h-12 w-12 text-zinc-400" />
                                        <flux:heading size="sm" class="mt-2 text-zinc-500 dark:text-zinc-400">
                                            {{ __('No leave types found') }}
                                        </flux:heading>
                                        <flux:text class="mt-1 text-zinc-500 dark:text-zinc-400">
                                            {{ __('Get started by creating a new leave type.') }}
                                        </flux:text>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($leaveTypes->hasPages())
                    <div class="px-6 py-4 border-t border-zinc-200 dark:border-zinc-700">
                        {{ $leaveTypes->links() }}
                    </div>
                @endif
            </div>
        </div>
    </x-system-management.layout>

    <!-- Create/Edit Flyout -->
    <flux:modal wire:model="showFormFlyout" variant="flyout" :title="$editingId ? __('Edit Leave Type') : __('New Leave Type')" class="w-[28rem] lg:w-[32rem]">
        <div class="space-y-6">
            <div class="space-y-4">
                <flux:input
                    label="{{ __('Leave Name') }}"
                    wire:model.defer="form.name"
                    placeholder="{{ __('e.g. Annual Leave') }}"
                    required
                />

                <flux:input
                    label="{{ __('Leave Code') }}"
                    wire:model.defer="form.code"
                    placeholder="{{ __('e.g. AL') }}"
                    addon-before="LT-"
                    required
                />

                <flux:input
                    label="{{ __('Icon') }}"
                    wire:model.defer="form.icon"
                    placeholder="{{ __('Optional icon name e.g. briefcase') }}"
                    helper-text="{{ __('Uses Heroicons naming. Leave empty for default.') }}"
                />

                <flux:select
                    label="{{ __('Status') }}"
                    wire:model.defer="form.status"
                >
                    <option value="active">{{ __('Active') }}</option>
                    <option value="inactive">{{ __('Inactive') }}</option>
                </flux:select>
            </div>

            <flux:textarea
                label="{{ __('Description') }}"
                rows="3"
                wire:model.defer="form.description"
                placeholder="{{ __('Describe when this leave type should be used...') }}"
            />

            <div class="space-y-4">
                <div class="space-y-3 rounded-lg border border-zinc-200 dark:border-zinc-700 p-4">
                    <flux:switch
                        label="{{ __('Requires Approval') }}"
                        description="{{ __('If disabled, requests are auto-approved.') }}"
                        wire:model.defer="form.requires_approval"
                    />
                </div>

                <div class="space-y-3 rounded-lg border border-zinc-200 dark:border-zinc-700 p-4">
                    <flux:switch
                        label="{{ __('Is Paid Leave') }}"
                        description="{{ __('Marks leave as paid or unpaid.') }}"
                        wire:model.defer="form.is_paid"
                    />
                </div>
            </div>
        </div>

        <div class="flex items-center justify-between w-full sticky bottom-0 pt-4">
            <flux:button variant="ghost" wire:click="$set('showFormFlyout', false)">
                {{ __('Cancel') }}
            </flux:button>
            <flux:button variant="primary" wire:click="saveLeaveType">
                {{ $editingId ? __('Update Leave Type') : __('Create Leave Type') }}
            </flux:button>
        </div>
    </flux:modal>

    <!-- Delete Modal -->
    <flux:modal wire:model="showDeleteModal" icon="trash" :title="__('Delete Leave Type')">
        <div class="space-y-4">
            <p class="text-sm text-zinc-600 dark:text-zinc-400">
                {{ __('This leave type will be moved to the archive and can be restored later. Existing leave balances will not be removed.') }}
            </p>
        </div>

        <x-slot name="footer">
            <div class="flex items-center justify-between w-full">
                <flux:button variant="ghost" wire:click="$set('showDeleteModal', false)">
                    {{ __('Cancel') }}
                </flux:button>
                <flux:button variant="danger" wire:click="deleteLeaveType">
                    {{ __('Delete') }}
                </flux:button>
            </div>
        </x-slot>
    </flux:modal>
</section>