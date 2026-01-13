<section class="w-full">
    @include('partials.system-management-heading')

    <x-system-management.layout :heading="__('Tasks')" :subheading="__('Manage task templates and assignments')">
        <div class="space-y-6">
            <!-- Action Bar -->
            <div class="flex justify-between items-center gap-4">
                <div class="flex items-center gap-3">
                    <flux:input
                        type="search"
                        icon="magnifying-glass"
                        wire:model.live.debounce.300ms="search"
                        placeholder="{{ __('Search templates...') }}"
                        class="w-72"
                    />
                    <flux:select wire:model.live="statusFilter" class="w-36">
                        <option value="all">{{ __('All Status') }}</option>
                        <option value="active">{{ __('Active') }}</option>
                        <option value="inactive">{{ __('Inactive') }}</option>
                    </flux:select>
                </div>

                <div class="flex items-center gap-3">
                    <flux:button variant="primary" icon="plus" wire:click="openCreateModal">
                        {{ __('New Template') }}
                    </flux:button>
                </div>
            </div>

            <!-- Templates Table -->
            <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 overflow-hidden shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-zinc-50 dark:bg-zinc-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    <button wire:click="sort('name')" class="flex items-center gap-1 hover:text-zinc-700 dark:hover:text-zinc-200">
                                        {{ __('Template Name') }}
                                        @if($sortBy === 'name')
                                            <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="w-4 h-4" />
                                        @endif
                                    </button>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    {{ __('Description') }}
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    {{ __('Fields') }}
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    <button wire:click="sort('is_active')" class="flex items-center gap-1 hover:text-zinc-700 dark:hover:text-zinc-200">
                                        {{ __('Status') }}
                                        @if($sortBy === 'is_active')
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
                            @forelse($templates as $template)
                                <tr class="hover:bg-zinc-100 dark:hover:bg-zinc-600 transition-colors duration-150">
                                    <td class="px-6 py-6">
                                        <div class="font-medium text-zinc-900 dark:text-zinc-100">
                                            {{ $template->name }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-6">
                                        <div class="text-sm text-zinc-600 dark:text-zinc-400">
                                            {{ $template->description ? \Illuminate\Support\Str::limit($template->description, 50) : '—' }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-6 whitespace-nowrap">
                                        <flux:badge color="blue" size="sm">
                                            {{ count($template->fields ?? []) }} {{ __('Fields') }}
                                        </flux:badge>
                                    </td>
                                    <td class="px-6 py-6 whitespace-nowrap">
                                        <flux:badge :color="$template->is_active ? 'green' : 'zinc'" size="sm">
                                            {{ $template->is_active ? __('Active') : __('Inactive') }}
                                        </flux:badge>
                                    </td>
                                    <td class="px-6 py-6 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center justify-end gap-2">
                                            <flux:button
                                                variant="ghost"
                                                size="sm"
                                                icon="user-plus"
                                                wire:click="openAssignModal({{ $template->id }})"
                                            >
                                                {{ __('Assign') }}
                                            </flux:button>
                                            <flux:button
                                                variant="ghost"
                                                size="sm"
                                                icon="pencil"
                                                wire:click="openEditModal({{ $template->id }})"
                                            >
                                                {{ __('Edit') }}
                                            </flux:button>
                                            <flux:button
                                                variant="ghost"
                                                size="sm"
                                                icon="trash"
                                                color="red"
                                                wire:click="confirmDelete({{ $template->id }})"
                                            >
                                                {{ __('Delete') }}
                                            </flux:button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center gap-3">
                                            <flux:icon name="document-text" class="w-12 h-12 text-zinc-400 dark:text-zinc-500" />
                                            <div class="text-sm text-zinc-500 dark:text-zinc-400">
                                                {{ __('No tasks found.') }}
                                            </div>
                                            <flux:button variant="outline" size="sm" wire:click="openCreateModal">
                                                {{ __('Create your first template') }}
                                            </flux:button>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($templates->hasPages())
                    <div class="px-6 py-4 border-t border-zinc-200 dark:border-zinc-700">
                        {{ $templates->links() }}
                    </div>
                @endif
            </div>
        </div>
    </x-system-management.layout>

    <!-- Create/Edit Template Flyout -->
    <flux:modal wire:model="showFormFlyout" variant="flyout" :title="$editingId ? __('Edit Template') : __('New Template')" class="w-[40rem] max-w-[90vw]">
        <div class="space-y-6">
            <div class="space-y-4">
                <flux:input
                    label="{{ __('Template Name') }}"
                    wire:model.defer="form.name"
                    placeholder="{{ __('e.g. Sales Daily Tasks') }}"
                    required
                />

                <flux:textarea
                    label="{{ __('Description') }}"
                    rows="3"
                    wire:model.defer="form.description"
                    placeholder="{{ __('Describe what this template is for...') }}"
                />

                <flux:checkbox
                    label="{{ __('Active') }}"
                    wire:model.defer="form.is_active"
                />
            </div>

            <!-- Fields Section -->
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <flux:heading size="sm">{{ __('Template Fields') }}</flux:heading>
                    <flux:button variant="outline" size="sm" icon="plus" wire:click="addField">
                        {{ __('Add Field') }}
                    </flux:button>
                </div>

                @if(count($form['fields']) > 0)
                    <div class="space-y-4 max-h-96 overflow-y-auto">
                        @foreach($form['fields'] as $index => $field)
                            <div class="p-4 border border-zinc-200 dark:border-zinc-700 rounded-lg bg-zinc-50 dark:bg-zinc-800/50">
                                <div class="flex items-start justify-between mb-3">
                                    <flux:heading size="xs">{{ __('Field') }} {{ $index + 1 }}</flux:heading>
                                    <flux:button
                                        variant="ghost"
                                        size="sm"
                                        icon="trash"
                                        color="red"
                                        wire:click="removeField({{ $index }})"
                                    >
                                    </flux:button>
                                </div>

                                <div class="grid grid-cols-2 gap-3 mb-3">
                                    <flux:input
                                        label="{{ __('Field Name') }}"
                                        wire:model.defer="form.fields.{{ $index }}.name"
                                        placeholder="{{ __('e.g. sales_count') }}"
                                        required
                                    />
                                    <flux:input
                                        label="{{ __('Field Label') }}"
                                        wire:model.defer="form.fields.{{ $index }}.label"
                                        placeholder="{{ __('e.g. Number of Sales') }}"
                                        required
                                    />
                                </div>

                                <div class="grid grid-cols-2 gap-3 mb-3">
                                    <flux:select
                                        label="{{ __('Field Type') }}"
                                        wire:model.defer="form.fields.{{ $index }}.type"
                                        required
                                    >
                                        <option value="text">{{ __('Text') }}</option>
                                        <option value="number">{{ __('Number') }}</option>
                                        <option value="textarea">{{ __('Textarea') }}</option>
                                        <option value="select">{{ __('Select/Dropdown') }}</option>
                                        <option value="date">{{ __('Date') }}</option>
                                        <option value="time">{{ __('Time') }}</option>
                                        <option value="checkbox">{{ __('Checkbox') }}</option>
                                    </flux:select>

                                    <flux:checkbox
                                        label="{{ __('Required') }}"
                                        wire:model.defer="form.fields.{{ $index }}.required"
                                    />
                                </div>

                                @if($form['fields'][$index]['type'] === 'select')
                                    <div class="mt-3">
                                        <flux:input
                                            label="{{ __('Options (comma-separated)') }}"
                                            wire:model.defer="form.fields.{{ $index }}.options"
                                            placeholder="{{ __('e.g. Option 1, Option 2, Option 3') }}"
                                            helper-text="{{ __('Enter options separated by commas') }}"
                                        />
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8 border border-dashed border-zinc-300 dark:border-zinc-600 rounded-lg">
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">
                            {{ __('No fields added yet. Click "Add Field" to get started.') }}
                        </p>
                    </div>
                @endif
            </div>

            <!-- Actions -->
            <div class="flex justify-end gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                <flux:button variant="outline" wire:click="closeFormFlyout">
                    {{ __('Cancel') }}
                </flux:button>
                <flux:button variant="primary" wire:click="saveTemplate">
                    {{ $editingId ? __('Update') : __('Create') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>

    <!-- Assign Template Flyout -->
    <flux:modal wire:model="showAssignFlyout" variant="flyout" title="{{ __('Assign Template') }}" class="w-[32rem]">
        <div class="space-y-6">
            <div class="space-y-4">
                <flux:select
                    label="{{ __('Assign To') }}"
                    wire:model.defer="assignForm.assignable_type"
                >
                    <option value="employee">{{ __('Individual Employee') }}</option>
                    <option value="department">{{ __('Department') }}</option>
                    <option value="group">{{ __('Group') }}</option>
                    <option value="role">{{ __('Role') }}</option>
                </flux:select>

                <flux:select
                    label="{{ __('Select') }}"
                    wire:model.defer="assignForm.assignable_id"
                    required
                >
                    <option value="">{{ __('Choose...') }}</option>
                    @foreach($assignableOptions as $option)
                        <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                    @endforeach
                </flux:select>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                <flux:button variant="outline" wire:click="closeAssignFlyout">
                    {{ __('Cancel') }}
                </flux:button>
                <flux:button variant="primary" wire:click="saveAssignment">
                    {{ __('Assign') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>

    <!-- Delete Confirmation Modal -->
    <flux:modal wire:model="showDeleteModal" variant="dialog" title="{{ __('Delete Template') }}">
        <div class="space-y-4">
            <p class="text-zinc-600 dark:text-zinc-400">
                {{ __('Are you sure you want to delete this template? This action cannot be undone.') }}
            </p>
            <div class="flex justify-end gap-3">
                <flux:button variant="outline" wire:click="cancelDelete">
                    {{ __('Cancel') }}
                </flux:button>
                <flux:button variant="primary" color="red" wire:click="deleteTemplate">
                    {{ __('Delete') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
</section>
