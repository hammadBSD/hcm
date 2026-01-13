<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'task_template_id',
        'log_date',
        'period',
        'data',
        'is_locked',
        'locked_at',
        'created_by',
    ];

    protected $casts = [
        'log_date' => 'date',
        'data' => 'array',
        'is_locked' => 'boolean',
        'locked_at' => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function taskTemplate(): BelongsTo
    {
        return $this->belongsTo(TaskTemplate::class);
    }
    
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Check if the task log can be edited
     */
    public function canEdit(): bool
    {
        if ($this->is_locked) {
            return false;
        }

        $settings = TaskSetting::getInstance();
        
        if (!$settings->lock_after_shift) {
            return true;
        }

        // Check if shift has ended (with grace period)
        $employee = $this->employee;
        if (!$employee) {
            return true;
        }

        $shift = $employee->getEffectiveShiftForDate($this->log_date->format('Y-m-d'));
        if (!$shift) {
            return true;
        }

        // Parse shift end time
        $shiftEndTime = $this->parseShiftTime($shift->end_time);
        if (!$shiftEndTime) {
            return true;
        }

        // Calculate lock time (shift end + grace period)
        $lockTime = Carbon::parse($this->log_date->format('Y-m-d') . ' ' . $shiftEndTime)
            ->addMinutes($settings->lock_grace_period_minutes);

        // If shift is overnight, add a day
        $shiftStartTime = $this->parseShiftTime($shift->start_time);
        if ($shiftStartTime && $shiftStartTime > $shiftEndTime) {
            $lockTime->addDay();
        }

        return Carbon::now()->lt($lockTime);
    }

    /**
     * Lock the task log
     */
    public function lock(): void
    {
        $this->update([
            'is_locked' => true,
            'locked_at' => Carbon::now(),
        ]);
    }

    /**
     * Parse shift time string (e.g., "09:00" or "09:00:00")
     */
    private function parseShiftTime(?string $time): ?string
    {
        if (!$time) {
            return null;
        }

        // Handle formats like "09:00" or "09:00:00"
        $parts = explode(':', $time);
        if (count($parts) >= 2) {
            return sprintf('%02d:%02d:00', (int)$parts[0], (int)$parts[1]);
        }

        return null;
    }
}
