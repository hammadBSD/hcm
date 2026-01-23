<?php

namespace App\Models\Recruitment;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PipelineStage extends Model
{
    protected $table = 'recruitment_pipeline_stages';

    protected $fillable = [
        'pipeline_id',
        'name',
        'color',
        'order',
        'is_default',
    ];

    protected $casts = [
        'order' => 'integer',
        'is_default' => 'boolean',
    ];

    // Relationships
    public function pipeline(): BelongsTo
    {
        return $this->belongsTo(Pipeline::class, 'pipeline_id');
    }

    public function candidates(): HasMany
    {
        return $this->hasMany(Candidate::class, 'pipeline_stage_id');
    }

    public function stageHistory(): HasMany
    {
        return $this->hasMany(CandidateStageHistory::class, 'to_stage_id');
    }

    public function fromStageHistory(): HasMany
    {
        return $this->hasMany(CandidateStageHistory::class, 'from_stage_id');
    }
}
