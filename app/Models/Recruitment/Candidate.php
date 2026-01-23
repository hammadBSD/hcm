<?php

namespace App\Models\Recruitment;

use App\Models\Country;
use App\Models\Designation;
use App\Models\Province;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Candidate extends Model
{
    use SoftDeletes;

    protected $table = 'recruitment_candidates';

    protected $fillable = [
        'job_post_id',
        'pipeline_stage_id',
        'applicant_number',
        'first_name',
        'last_name',
        'date_of_birth',
        'description',
        'email',
        'phone',
        'linkedin_url',
        'position',
        'designation_id',
        'experience',
        'source',
        'current_address',
        'city',
        'country_id',
        'province_id',
        'current_company',
        'notice_period',
        'expected_salary',
        'availability_date',
        'rating',
        'status',
        'is_hired',
        'hired_by',
        'hired_at',
        'created_by',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'availability_date' => 'date',
        'experience' => 'decimal:1',
        'expected_salary' => 'decimal:2',
        'rating' => 'decimal:1',
        'is_hired' => 'boolean',
        'hired_at' => 'datetime',
    ];

    // Relationships
    public function jobPost(): BelongsTo
    {
        return $this->belongsTo(JobPost::class, 'job_post_id');
    }

    public function pipelineStage(): BelongsTo
    {
        return $this->belongsTo(PipelineStage::class, 'pipeline_stage_id');
    }

    public function designation(): BelongsTo
    {
        return $this->belongsTo(Designation::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }

    public function hiredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'hired_by');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(CandidateAttachment::class, 'candidate_id');
    }

    public function previousCompanies(): HasMany
    {
        return $this->hasMany(CandidatePreviousCompany::class, 'candidate_id')->orderBy('order');
    }

    public function history(): HasMany
    {
        return $this->hasMany(CandidateHistory::class, 'candidate_id')->orderBy('changed_at', 'desc');
    }

    public function stageHistory(): HasMany
    {
        return $this->hasMany(CandidateStageHistory::class, 'candidate_id')->orderBy('moved_at', 'desc');
    }

    // Accessors
    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }
}
