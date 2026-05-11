<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

class Announcement extends Model
{
    use HasFactory;

    public const SCOPE_ALL = 'all_employees';

    public const SCOPE_DEPARTMENT = 'department';

    public const SCOPE_ROLE = 'role';

    public const SCOPE_GROUP = 'group';

    protected $fillable = [
        'title',
        'content',
        'type',
        'scope_type',
        'start_date',
        'end_date',
        'is_pinned',
        'created_by',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_pinned' => 'boolean',
        'status' => 'string',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function departments(): BelongsToMany
    {
        return $this->belongsToMany(Department::class, 'announcement_departments');
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(\Spatie\Permission\Models\Role::class, 'announcement_roles', 'announcement_id', 'role_id');
    }

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class, 'announcement_groups');
    }

    /**
     * Announcements visible on the dashboard for the given user today (active, in date range, matching scope).
     */
    public static function visibleForUser(?User $user, ?Carbon $on = null): Builder
    {
        $day = ($on ?? now())->toDateString();

        return static::query()
            ->where('status', 'active')
            ->whereDate('start_date', '<=', $day)
            ->whereDate('end_date', '>=', $day)
            ->where(function (Builder $outer) use ($user) {
                $outer->where('scope_type', self::SCOPE_ALL);

                if (! $user) {
                    return;
                }

                $employee = $user->relationLoaded('employee')
                    ? $user->employee
                    : $user->employee()->first();

                if ($employee?->department_id) {
                    $outer->orWhere(function (Builder $q) use ($employee) {
                        $q->where('scope_type', self::SCOPE_DEPARTMENT)
                            ->whereHas('departments', fn (Builder $d) => $d->where('departments.id', $employee->department_id));
                    });
                }

                if ($employee?->group_id) {
                    $outer->orWhere(function (Builder $q) use ($employee) {
                        $q->where('scope_type', self::SCOPE_GROUP)
                            ->whereHas('groups', fn (Builder $g) => $g->where('groups.id', $employee->group_id));
                    });
                }

                $roleIds = $user->roles->pluck('id');
                if ($roleIds->isNotEmpty()) {
                    $outer->orWhere(function (Builder $q) use ($roleIds) {
                        $q->where('scope_type', self::SCOPE_ROLE)
                            ->whereHas('roles', fn (Builder $r) => $r->whereIn('roles.id', $roleIds->all()));
                    });
                }
            })
            ->orderByDesc('is_pinned')
            ->orderByDesc('start_date');
    }
}
