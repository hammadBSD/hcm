<?php

namespace App\Models\Recruitment;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CandidatePreviousCompany extends Model
{
    protected $table = 'recruitment_candidate_previous_companies';

    protected $fillable = [
        'candidate_id',
        'company_name',
        'position',
        'from_date',
        'to_date',
        'description',
        'order',
    ];

    protected $casts = [
        'from_date' => 'date',
        'to_date' => 'date',
        'order' => 'integer',
    ];

    // Relationships
    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class, 'candidate_id');
    }
}
