<?php

namespace App\Models\Recruitment;

use Illuminate\Database\Eloquent\Model;

class RecruitmentSetting extends Model
{
    protected $table = 'recruitment_settings';

    protected $fillable = [
        'restrict_applicant_access',
        'show_hire_button_last_stage_only',
        'auto_assign_applicant_number',
        'require_rating_before_move',
        'prevent_move_rejected_candidates',
        'notify_on_new_application',
        'notify_on_stage_change',
        'allow_public_applications',
        'default_pipeline_id',
        'application_deadline_reminder_days',
        'auto_archive_rejected',
        'archive_after_days',
    ];

    protected $casts = [
        'restrict_applicant_access' => 'boolean',
        'show_hire_button_last_stage_only' => 'boolean',
        'auto_assign_applicant_number' => 'boolean',
        'require_rating_before_move' => 'boolean',
        'prevent_move_rejected_candidates' => 'boolean',
        'notify_on_new_application' => 'boolean',
        'notify_on_stage_change' => 'boolean',
        'allow_public_applications' => 'boolean',
        'default_pipeline_id' => 'integer',
        'application_deadline_reminder_days' => 'integer',
        'auto_archive_rejected' => 'boolean',
        'archive_after_days' => 'integer',
    ];

    /**
     * Get the singleton recruitment setting instance
     */
    public static function getInstance(): self
    {
        return static::firstOrCreate([], [
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
        ]);
    }
}
