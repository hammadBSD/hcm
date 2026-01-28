<?php

namespace App\Models\Recruitment;

use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class JobPost extends Model
{
    use SoftDeletes;

    protected $table = 'recruitment_job_posts';

    protected $fillable = [
        'title',
        'description',
        'department_id',
        'designation_id',
        'entry_level',
        'position_type',
        'work_type',
        'hiring_priority',
        'number_of_positions',
        'status',
        'location',
        'budget',
        'application_deadline',
        'start_date',
        'required_skills',
        'benefits',
        'reporting_to_id',
        'created_by',
        'default_pipeline_id',
        'unique_id',
    ];

    protected $casts = [
        'application_deadline' => 'date',
        'start_date' => 'date',
        'budget' => 'decimal:2',
        'number_of_positions' => 'integer',
    ];

    // Relationships
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function designation(): BelongsTo
    {
        return $this->belongsTo(Designation::class);
    }

    public function reportingTo(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'reporting_to_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function defaultPipeline(): BelongsTo
    {
        return $this->belongsTo(Pipeline::class, 'default_pipeline_id');
    }

    public function candidates(): HasMany
    {
        return $this->hasMany(Candidate::class, 'job_post_id');
    }

    public function history(): HasMany
    {
        return $this->hasMany(JobPostHistory::class, 'job_post_id');
    }
}
