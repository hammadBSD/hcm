<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class TaskTemplateAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_template_id',
        'assignable_type',
        'assignable_id',
    ];

    public function taskTemplate(): BelongsTo
    {
        return $this->belongsTo(TaskTemplate::class);
    }

    /**
     * Get the parent assignable model (polymorphic relation)
     */
    public function assignable(): MorphTo
    {
        return $this->morphTo();
    }
}
