<section class="w-full">
    @include('partials.system-management-heading')

    <x-system-management.layout :heading="__('Brands')" :subheading="__('Manage brands')">
        <div class="space-y-6">
            <div class="flex justify-between items-center gap-4">
                <div class="flex items-center gap-3">
                    <flux:input type="search" wire:model.live="search" placeholder="{{ __('Search brands...') }}" class="w-80" />
                    <flux:button variant="outline" icon="funnel" />
                </div>
                <div class="flex items-center gap-3">
                    <flux:button variant="outline" icon="arrow-down-tray">Export</flux:button>
                    <flux:button variant="primary" icon="plus" wire:click="create">{{ __('Add Brand') }}</flux:button>
                </div>
            </div>

            <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 overflow-hidden shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-zinc-50 dark:bg-zinc-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    <button wire:click="sort('name')" class="flex items-center gap-1 hover:text-zinc-700 dark:hover:text-zinc-200">
                                        {{ __('Name') }}
                                        @if($sortBy === 'name')
                                            <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="w-4 h-4" />
                                        @endif
                                    </button>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    <button wire:click="sort('description')" class="flex items-center gap-1 hover:text-zinc-700 dark:hover:text-zinc-200">
                                        {{ __('Description') }}
                                        @if($sortBy === 'description')
                                            <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="w-4 h-4" />
                                        @endif
                                    </button>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    <button wire:click="sort('status')" class="flex items-center gap-1 hover:text-zinc-700 dark:hover:text-zinc-200">
                                        {{ __('Status') }}
                                        @if($sortBy === 'status')
                                            <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="w-4 h-4" />
                                        @endif
                                    </button>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-zinc-800 divide-y divide-zinc-200 dark:divide-zinc-700">
                            @forelse($brands as $brand)
                                <tr class="hover:bg-zinc-100 dark:hover:bg-zinc-600 transition-colors duration-150">
                                    <td class="px-6 py-6 whitespace-nowrap">
                                        <div class="flex items-center gap-3">
                                            <div class="h-8 w-8 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                                                <flux:icon name="tag" class="h-4 w-4 text-blue-600 dark:text-blue-400" />
                                            </div>
                                            <div>
                                                <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $brand->name }}</div>
                                                @if($brand->code)
                                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ $brand->code }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-6">
                                        <div class="text-sm text-zinc-900 dark:text-zinc-100">{{ $brand->description ?? '-' }}</div>
                                    </td>
                                    <td class="px-6 py-6 whitespace-nowrap">
                                        <flux:badge color="{{ $brand->status === 'active' ? 'green' : 'red' }}" size="sm">{{ ucfirst($brand->status) }}</flux:badge>
                                    </td>
                                    <td class="px-6 py-6 whitespace-nowrap text-sm font-medium">
                                        <div class="flex items-center gap-1">
                                            <flux:dropdown>
                                                <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                                                <flux:menu>
                                                    <flux:menu.item icon="pencil" wire:click="edit({{ $brand->id }})">{{ __('Edit') }}</flux:menu.item>
                                                    <flux:menu.item icon="trash" wire:click="delete({{ $brand->id }})" wire:confirm="{{ __('Are you sure you want to delete this brand?') }}" class="text-red-600">{{ __('Delete') }}</flux:menu.item>
                                                </flux:menu>
                                            </flux:dropdown>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-12 text-center">
                                        <flux:icon name="inbox" class="mx-auto h-12 w-12 text-zinc-400" />
                                        <flux:heading size="sm" class="mt-2 text-zinc-500 dark:text-zinc-400">{{ __('No brands found') }}</flux:heading>
                                        <flux:text class="mt-1 text-zinc-500 dark:text-zinc-400">{{ __('Create a new brand to get started.') }}</flux:text>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if($brands->hasPages())
                <div class="mt-6">{{ $brands->links() }}</div>
            @endif
        </div>
    </x-system-management.layout>

    <flux:modal variant="flyout" :open="$showAddFlyout" wire:model="showAddFlyout">
        <div class="p-6">
            <div class="mb-6">
                <flux:heading size="lg">{{ $editingId ? __('Edit Brand') : __('Add Brand') }}</flux:heading>
            </div>
            <form wire:submit="submit" class="space-y-6">
                <flux:field>
                    <flux:label>{{ __('Name') }} <span class="text-red-500">*</span></flux:label>
                    <flux:input wire:model="name" placeholder="{{ __('Enter name') }}" required />
                    <flux:error name="name" />
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('Code') }}</flux:label>
                    <flux:input wire:model="code" placeholder="{{ __('Enter code (optional)') }}" />
                    <flux:error name="code" />
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('Description') }}</flux:label>
                    <flux:textarea wire:model="description" rows="4" placeholder="{{ __('Optional description') }}"></flux:textarea>
                    <flux:error name="description" />
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('Status') }}</flux:label>
                    <flux:select wire:model="status">
                        <option value="active">{{ __('Active') }}</option>
                        <option value="inactive">{{ __('Inactive') }}</option>
                    </flux:select>
                    <flux:error name="status" />
                </flux:field>
                <div class="flex justify-end gap-3 pt-4">
                    <flux:button type="button" variant="outline" wire:click="closeFlyout">{{ __('Cancel') }}</flux:button>
                    <flux:button type="submit" variant="primary">{{ __('Save') }}</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>
</section>
