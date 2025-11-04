<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'code',
        'parent_id',
        'group_id',
        'department_head_id',
        'description',
        'status',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Department::class, 'parent_id');
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function departmentHead(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'department_head_id');
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class, 'department_id');
    }
}
