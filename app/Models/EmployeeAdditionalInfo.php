<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeAdditionalInfo extends Model
{
    use HasFactory;

    protected $table = 'employee_additional_info';

    protected $fillable = [
        'employee_id',
        'date_of_birth',
        'gender',
        'marital_status',
        'nationality',
        'blood_group',
        'place_of_birth',
        'religion',
        'state',
        'country',
        'province',
        'city',
        'area',
        'zip_code',
        'family_code',
        'address',
        'degree',
        'institute',
        'passing_year',
        'grade',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
