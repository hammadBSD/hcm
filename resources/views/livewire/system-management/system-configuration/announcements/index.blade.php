<section class="w-full">
    @include('partials.system-management-heading')

    <x-system-management.layout :heading="__('Announcements')" :subheading="__('Manage announcements')">
        <div class="space-y-6">
            @if (session()->has('message'))
                <flux:callout variant="success" icon="check-circle">
                    <flux:callout.heading>{{ session('message') }}</flux:callout.heading>
                </flux:callout>
            @endif

            <div class="flex justify-between items-center gap-4 flex-wrap">
                <div class="flex items-center gap-3">
                    <flux:input
                        type="search"
                        wire:model.live="search"
                        placeholder="{{ __('Search announcements…') }}"
                        class="w-80 max-w-full"
                    />
                </div>
                <flux:button variant="primary" icon="plus" wire:click="createAnnouncement">
                    {{ __('Add announcement') }}
                </flux:button>
            </div>

            <div class="mt-4">
                <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 overflow-hidden shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-zinc-50 dark:bg-zinc-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                        <button type="button" wire:click="sort('title')" class="flex items-center gap-1 hover:text-zinc-700 dark:hover:text-zinc-200">
                                            {{ __('Title') }}
                                            @if($sortBy === 'title')
                                                <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="w-4 h-4" />
                                            @endif
                                        </button>
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                        <button type="button" wire:click="sort('start_date')" class="flex items-center gap-1 hover:text-zinc-700 dark:hover:text-zinc-200">
                                            {{ __('Start') }}
                                            @if($sortBy === 'start_date')
                                                <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="w-4 h-4" />
                                            @endif
                                        </button>
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                        <button type="button" wire:click="sort('end_date')" class="flex items-center gap-1 hover:text-zinc-700 dark:hover:text-zinc-200">
                                            {{ __('End') }}
                                            @if($sortBy === 'end_date')
                                                <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="w-4 h-4" />
                                            @endif
                                        </button>
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                        {{ __('Audience') }}
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                        {{ __('Type') }}
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                        <button type="button" wire:click="sort('status')" class="flex items-center gap-1 hover:text-zinc-700 dark:hover:text-zinc-200">
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
                                @forelse($announcements as $row)
                                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-700/50">
                                        <td class="px-6 py-4">
                                            <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $row->title }}</div>
                                            @if($row->is_pinned)
                                                <flux:badge size="sm" color="zinc" class="mt-1">{{ __('Pinned') }}</flux:badge>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-700 dark:text-zinc-300">
                                            {{ $row->start_date->format('M j, Y') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-700 dark:text-zinc-300">
                                            {{ $row->end_date->format('M j, Y') }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-zinc-700 dark:text-zinc-300">
                                            @if(($row->scope_type ?? 'all_employees') === \App\Models\Announcement::SCOPE_ALL)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400">
                                                    {{ __('All employees') }}
                                                </span>
                                            @elseif($row->scope_type === \App\Models\Announcement::SCOPE_DEPARTMENT)
                                                <div class="flex flex-col gap-1">
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400">
                                                        {{ __('Departments') }}
                                                    </span>
                                                    @if($row->departments->isNotEmpty())
                                                        <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ $row->departments->pluck('title')->join(', ') }}</span>
                                                    @endif
                                                </div>
                                            @elseif($row->scope_type === \App\Models\Announcement::SCOPE_ROLE)
                                                <div class="flex flex-col gap-1">
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400">
                                                        {{ __('Roles') }}
                                                    </span>
                                                    @if($row->roles->isNotEmpty())
                                                        <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ $row->roles->pluck('name')->join(', ') }}</span>
                                                    @endif
                                                </div>
                                            @elseif($row->scope_type === \App\Models\Announcement::SCOPE_GROUP)
                                                <div class="flex flex-col gap-1">
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-400">
                                                        {{ __('Groups') }}
                                                    </span>
                                                    @if($row->groups->isNotEmpty())
                                                        <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ $row->groups->pluck('name')->join(', ') }}</span>
                                                    @endif
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <flux:badge size="sm" color="{{ $row->type === 'error' ? 'red' : ($row->type === 'warning' ? 'amber' : ($row->type === 'success' ? 'green' : 'blue')) }}">
                                                {{ ucfirst($row->type) }}
                                            </flux:badge>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <flux:badge color="{{ $row->status === 'active' ? 'green' : 'zinc' }}" size="sm">
                                                {{ ucfirst($row->status) }}
                                            </flux:badge>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right">
                                            <flux:dropdown>
                                                <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                                                <flux:menu>
                                                    <flux:menu.item icon="pencil" wire:click="editAnnouncement({{ $row->id }})">
                                                        {{ __('Edit') }}
                                                    </flux:menu.item>
                                                    <flux:menu.item icon="trash" wire:click="deleteAnnouncement({{ $row->id }})" wire:confirm="{{ __('Delete this announcement?') }}" class="text-red-600">
                                                        {{ __('Delete') }}
                                                    </flux:menu.item>
                                                </flux:menu>
                                            </flux:dropdown>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-6 py-12 text-center">
                                            <flux:icon name="inbox" class="mx-auto h-12 w-12 text-zinc-400" />
                                            <flux:heading size="sm" class="mt-2 text-zinc-500 dark:text-zinc-400">
                                                {{ __('No announcements yet') }}
                                            </flux:heading>
                                            <flux:text class="mt-1 text-zinc-500 dark:text-zinc-400">
                                                {{ __('Create one to show it on employee dashboards during the active dates.') }}
                                            </flux:text>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            @if($announcements->hasPages())
                <div class="mt-6">
                    {{ $announcements->links() }}
                </div>
            @endif
        </div>
    </x-system-management.layout>

    <flux:modal variant="flyout" :open="$showFlyout" wire:model="showFlyout">
        <div class="p-6 max-w-2xl">
            <div class="mb-6">
                <flux:heading size="lg">{{ $editingId ? __('Edit announcement') : __('Add announcement') }}</flux:heading>
            </div>

            <form wire:submit="submitAnnouncement" class="space-y-6">
                <flux:field>
                    <flux:label>{{ __('Title') }} <span class="text-red-500">*</span></flux:label>
                    <flux:input wire:model="title" placeholder="{{ __('Short headline') }}" />
                    <flux:error name="title" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Message') }} <span class="text-red-500">*</span></flux:label>
                    <flux:textarea wire:model="content" rows="8" placeholder="{{ __('Announcement body') }}" />
                    <flux:error name="content" />
                </flux:field>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <flux:field>
                        <flux:label>{{ __('Start date') }} <span class="text-red-500">*</span></flux:label>
                        <flux:input type="date" wire:model="startDate" />
                        <flux:error name="startDate" />
                    </flux:field>
                    <flux:field>
                        <flux:label>{{ __('End date') }} <span class="text-red-500">*</span></flux:label>
                        <flux:input type="date" wire:model="endDate" />
                        <flux:error name="endDate" />
                    </flux:field>
                </div>

                <flux:field>
                    <flux:label>{{ __('Audience') }} <span class="text-red-500">*</span></flux:label>
                    <flux:select wire:model.live="scopeType">
                        <option value="{{ \App\Models\Announcement::SCOPE_ALL }}">{{ __('All employees') }}</option>
                        <option value="{{ \App\Models\Announcement::SCOPE_DEPARTMENT }}">{{ __('Specific departments') }}</option>
                        <option value="{{ \App\Models\Announcement::SCOPE_ROLE }}">{{ __('Specific roles') }}</option>
                        <option value="{{ \App\Models\Announcement::SCOPE_GROUP }}">{{ __('Specific groups') }}</option>
                    </flux:select>
                    <flux:error name="scopeType" />
                </flux:field>

                @if($scopeType === \App\Models\Announcement::SCOPE_DEPARTMENT)
                    <flux:field>
                        <flux:label>{{ __('Departments') }} <span class="text-red-500">*</span></flux:label>
                        <flux:input wire:model.live.debounce.300ms="departmentSearchTerm" placeholder="{{ __('Search…') }}" icon="magnifying-glass" class="mb-2" />
                        <select wire:model.live="selectedDepartmentIds" multiple class="w-full px-3 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 min-h-[150px]" size="6">
                            @foreach($filteredDepartments as $department)
                                <option value="{{ $department['value'] }}">{{ $department['label'] }}</option>
                            @endforeach
                        </select>
                        <flux:description>{{ __('Hold Ctrl/Cmd to select multiple.') }}</flux:description>
                        <flux:error name="selectedDepartmentIds" />
                    </flux:field>
                @endif

                @if($scopeType === \App\Models\Announcement::SCOPE_ROLE)
                    <flux:field>
                        <flux:label>{{ __('Roles') }} <span class="text-red-500">*</span></flux:label>
                        <flux:input wire:model.live.debounce.300ms="roleSearchTerm" placeholder="{{ __('Search…') }}" icon="magnifying-glass" class="mb-2" />
                        <select wire:model.live="selectedRoleIds" multiple class="w-full px-3 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 min-h-[150px]" size="6">
                            @foreach($filteredRoles as $role)
                                <option value="{{ $role['value'] }}">{{ $role['label'] }}</option>
                            @endforeach
                        </select>
                        <flux:description>{{ __('Hold Ctrl/Cmd to select multiple.') }}</flux:description>
                        <flux:error name="selectedRoleIds" />
                    </flux:field>
                @endif

                @if($scopeType === \App\Models\Announcement::SCOPE_GROUP)
                    <flux:field>
                        <flux:label>{{ __('Groups') }} <span class="text-red-500">*</span></flux:label>
                        <flux:input wire:model.live.debounce.300ms="groupSearchTerm" placeholder="{{ __('Search…') }}" icon="magnifying-glass" class="mb-2" />
                        <select wire:model.live="selectedGroupIds" multiple class="w-full px-3 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 min-h-[150px]" size="6">
                            @foreach($filteredGroups as $group)
                                <option value="{{ $group['value'] }}">{{ $group['label'] }}</option>
                            @endforeach
                        </select>
                        <flux:description>{{ __('Hold Ctrl/Cmd to select multiple.') }}</flux:description>
                        <flux:error name="selectedGroupIds" />
                    </flux:field>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <flux:field>
                        <flux:label>{{ __('Style') }}</flux:label>
                        <flux:select wire:model="type">
                            <option value="info">{{ __('Info') }}</option>
                            <option value="warning">{{ __('Warning') }}</option>
                            <option value="success">{{ __('Success') }}</option>
                            <option value="error">{{ __('Important / error') }}</option>
                        </flux:select>
                    </flux:field>
                    <flux:field>
                        <flux:label>{{ __('Status') }}</flux:label>
                        <flux:select wire:model="status">
                            <option value="active">{{ __('Active') }}</option>
                            <option value="inactive">{{ __('Inactive') }}</option>
                        </flux:select>
                    </flux:field>
                </div>

                <flux:checkbox wire:model="isPinned" :label="__('Pin on dashboard (shown above others)')" />

                <div class="flex justify-end gap-3 pt-4">
                    <flux:button type="button" variant="outline" wire:click="closeFlyout">{{ __('Cancel') }}</flux:button>
                    <flux:button type="submit" variant="primary">
                        {{ $editingId ? __('Save changes') : __('Create announcement') }}
                    </flux:button>
                </div>
            </form>
        </div>
    </flux:modal>
</section>
