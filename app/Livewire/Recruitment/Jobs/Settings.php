<?php

namespace App\Livewire\Recruitment\Jobs;

use App\Models\Recruitment\Pipeline;
use App\Models\Recruitment\RecruitmentSetting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Settings extends Component
{
    public $form = [
        'restrict_applicant_access' => false,
        'show_hire_button_last_stage_only' => true,
        'auto_assign_applicant_number' => true,
        'require_rating_before_move' => false,
        'prevent_move_rejected_candidates' => false,
        'notify_on_new_application' => true,
        'notify_on_stage_change' => true,
        'allow_public_applications' => true,
        'default_pipeline_id' => null,
        'application_deadline_reminder_days' => 7,
        'auto_archive_rejected' => false,
        'archive_after_days' => 90,
    ];

    public $pipelines = [];

    protected $setting = null;

    public function mount()
    {
        $user = Auth::user();
        
        // Check if user is Super Admin or HR Manager
        if (!$user || (!$user->hasRole('Super Admin') && !$user->hasRole('HR Manager'))) {
            abort(403, 'Unauthorized access. Only Super Admin and HR Manager can access this module.');
        }

        // Load settings
        $setting = $this->resolveSetting();
        $this->form = array_merge($this->form, $setting->only(array_keys($this->form)));

        // Load pipelines for dropdown
        $this->pipelines = Pipeline::orderBy('name')->get()->map(function ($pipeline) {
            return [
                'id' => $pipeline->id,
                'name' => $pipeline->name,
            ];
        })->toArray();
    }

    public function save()
    {
        $validated = $this->validate([
            'form.restrict_applicant_access' => 'required|boolean',
            'form.show_hire_button_last_stage_only' => 'required|boolean',
            'form.auto_assign_applicant_number' => 'required|boolean',
            'form.require_rating_before_move' => 'required|boolean',
            'form.prevent_move_rejected_candidates' => 'required|boolean',
            'form.notify_on_new_application' => 'required|boolean',
            'form.notify_on_stage_change' => 'required|boolean',
            'form.allow_public_applications' => 'required|boolean',
            'form.default_pipeline_id' => 'nullable|exists:recruitment_pipelines,id',
            'form.application_deadline_reminder_days' => 'required|integer|min:0|max:365',
            'form.auto_archive_rejected' => 'required|boolean',
            'form.archive_after_days' => 'required|integer|min:0|max:3650',
        ]);

        try {
            DB::beginTransaction();

            $payload = $validated['form'];
            
            // Convert boolean values
            $payload['restrict_applicant_access'] = (bool) ($payload['restrict_applicant_access'] ?? false);
            $payload['show_hire_button_last_stage_only'] = (bool) ($payload['show_hire_button_last_stage_only'] ?? true);
            $payload['auto_assign_applicant_number'] = (bool) ($payload['auto_assign_applicant_number'] ?? true);
            $payload['require_rating_before_move'] = (bool) ($payload['require_rating_before_move'] ?? false);
            $payload['prevent_move_rejected_candidates'] = (bool) ($payload['prevent_move_rejected_candidates'] ?? false);
            $payload['notify_on_new_application'] = (bool) ($payload['notify_on_new_application'] ?? true);
            $payload['notify_on_stage_change'] = (bool) ($payload['notify_on_stage_change'] ?? true);
            $payload['allow_public_applications'] = (bool) ($payload['allow_public_applications'] ?? true);
            $payload['auto_archive_rejected'] = (bool) ($payload['auto_archive_rejected'] ?? false);

            $setting = $this->resolveSetting();
            $setting->update($payload);

            DB::commit();

            session()->flash('success', 'Recruitment settings saved successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Failed to save settings: ' . $e->getMessage());
        }
    }

    protected function resolveSetting(): RecruitmentSetting
    {
        if (!$this->setting) {
            $this->setting = RecruitmentSetting::getInstance();
        }
        return $this->setting;
    }

    public function render()
    {
        return view('livewire.recruitment.jobs.settings')
            ->layout('components.layouts.app');
    }
}
