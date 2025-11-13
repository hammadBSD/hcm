<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveRequestEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'leave_request_id',
        'performed_by',
        'event_type',
        'notes',
        'attachment_path',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function leaveRequest(): BelongsTo
    {
        return $this->belongsTo(LeaveRequest::class);
    }

    public function performer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
