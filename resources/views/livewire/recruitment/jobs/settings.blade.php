<section class="w-full">
    @include('partials.recruitment-heading')
    
    <x-recruitment.layout>
        @if(session('success'))
            <flux:callout variant="success" icon="check-circle" dismissible class="mb-6">
                {{ session('success') }}
            </flux:callout>
        @endif

        @if(session('error'))
            <flux:callout variant="danger" icon="exclamation-circle" dismissible class="mb-6">
                {{ session('error') }}
            </flux:callout>
        @endif

        <form wire:submit.prevent="save" class="space-y-6">
            <!-- Applicant Access Control -->
            <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700">
                <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Applicant Access Control') }}</h3>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">
                        {{ __('Configure who can access and work on applicants.') }}
                    </p>
                </div>
                <div class="p-6 space-y-6">
                    <flux:switch
                        wire:model.live="form.restrict_applicant_access"
                        label="{{ __('Restrict Applicant Access') }}"
                        description="{{ __('When enabled, only the user who added the applicant can work on and move their cards. When disabled, all authorized users can work on any applicant.') }}"
                    />
                </div>
            </div>

            <!-- Hire Button Settings -->
            <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700">
                <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Hire Button Settings') }}</h3>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">
                        {{ __('Configure when the hire button appears in the candidate detail modal.') }}
                    </p>
                </div>
                <div class="p-6 space-y-6">
                    <flux:switch
                        wire:model.live="form.show_hire_button_last_stage_only"
                        label="{{ __('Show Hire Button Only in Last Stage') }}"
                        description="{{ __('When enabled, the hire button will only appear when the candidate is in the final stage of the pipeline. When disabled, the hire button will always be visible.') }}"
                    />
                </div>
            </div>

            <!-- Applicant Management -->
            <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700">
                <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Applicant Management') }}</h3>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">
                        {{ __('Configure how applicants are managed and processed.') }}
                    </p>
                </div>
                <div class="p-6 space-y-6">
                    <flux:switch
                        wire:model.live="form.auto_assign_applicant_number"
                        label="{{ __('Auto-Assign Applicant Numbers') }}"
                        description="{{ __('When enabled, applicant numbers will be automatically assigned sequentially for each job post.') }}"
                    />

                    <flux:separator variant="subtle" />

                    <flux:switch
                        wire:model.live="form.require_rating_before_move"
                        label="{{ __('Require Rating Before Moving') }}"
                        description="{{ __('When enabled, candidates must have a rating before they can be moved to the next stage.') }}"
                    />

                    <flux:separator variant="subtle" />

                    <flux:switch
                        wire:model.live="form.prevent_move_rejected_candidates"
                        label="{{ __('Prevent Moving Rejected Candidates') }}"
                        description="{{ __('When enabled, rejected candidates cannot be moved to any stage after they have been rejected.') }}"
                    />

                    <flux:separator variant="subtle" />

                    <flux:switch
                        wire:model.live="form.allow_public_applications"
                        label="{{ __('Allow Public Applications') }}"
                        description="{{ __('When enabled, external candidates can apply for job posts through the public application form.') }}"
                    />
                </div>
            </div>

            <!-- Notification Settings -->
            <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700">
                <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Notification Settings') }}</h3>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">
                        {{ __('Configure when notifications are sent for recruitment activities.') }}
                    </p>
                </div>
                <div class="p-6 space-y-6">
                    <flux:switch
                        wire:model.live="form.notify_on_new_application"
                        label="{{ __('Notify on New Application') }}"
                        description="{{ __('When enabled, notifications will be sent when a new candidate applies for a job post.') }}"
                    />

                    <flux:separator variant="subtle" />

                    <flux:switch
                        wire:model.live="form.notify_on_stage_change"
                        label="{{ __('Notify on Stage Change') }}"
                        description="{{ __('When enabled, notifications will be sent when a candidate is moved between pipeline stages.') }}"
                    />

                    <flux:separator variant="subtle" />

                    <flux:input
                        type="number"
                        wire:model.defer="form.application_deadline_reminder_days"
                        label="{{ __('Application Deadline Reminder (Days)') }}"
                        helper-text="{{ __('Number of days before the application deadline to send reminder notifications. Set to 0 to disable reminders.') }}"
                        min="0"
                        max="365"
                    />
                </div>
            </div>

            <!-- Default Pipeline Settings -->
            <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700">
                <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Default Pipeline Settings') }}</h3>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">
                        {{ __('Configure the default pipeline used for new job posts.') }}
                    </p>
                </div>
                <div class="p-6 space-y-6">
                    <flux:field>
                        <flux:label>{{ __('Default Pipeline') }}</flux:label>
                        <flux:select wire:model.defer="form.default_pipeline_id">
                            <option value="">{{ __('Use System Default') }}</option>
                            @foreach($pipelines as $pipeline)
                                <option value="{{ $pipeline['id'] }}">{{ $pipeline['name'] }}</option>
                            @endforeach
                        </flux:select>
                        <flux:description>{{ __('Select the default pipeline to use for new job posts. If not set, the system default pipeline will be used.') }}</flux:description>
                    </flux:field>
                </div>
            </div>

            <!-- Archive Settings -->
            <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700">
                <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Archive Settings') }}</h3>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">
                        {{ __('Configure automatic archiving of candidates.') }}
                    </p>
                </div>
                <div class="p-6 space-y-6">
                    <flux:switch
                        wire:model.live="form.auto_archive_rejected"
                        label="{{ __('Auto-Archive Rejected Candidates') }}"
                        description="{{ __('When enabled, rejected candidates will be automatically archived after the specified number of days.') }}"
                    />

                    <flux:separator variant="subtle" />

                    <flux:input
                        type="number"
                        wire:model.defer="form.archive_after_days"
                        label="{{ __('Archive After (Days)') }}"
                        helper-text="{{ __('Number of days after which rejected candidates will be automatically archived.') }}"
                        min="0"
                        max="3650"
                        :disabled="!$form['auto_archive_rejected']"
                    />
                </div>
            </div>

            <!-- Save Button -->
            <div class="flex justify-end gap-3">
                <flux:button type="submit" color="blue">
                    {{ __('Save Settings') }}
                </flux:button>
            </div>
        </form>
    </x-recruitment.layout>
</section>
