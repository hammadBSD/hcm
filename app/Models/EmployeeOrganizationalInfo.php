<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeOrganizationalInfo extends Model
{
    use HasFactory;

    protected $table = 'employee_organizational_info';

    protected $fillable = [
        'employee_id',
        'previous_company_name',
        'previous_designation',
        'from_date',
        'to_date',
        'reason_for_leaving',
        'joining_date',
        'confirmation_date',
        'expected_confirmation_days',
        'contract_start_date',
        'contract_end_date',
        'resign_date',
        'leaving_date',
        'leaving_reason',
        'vendor',
        'division',
        'grade',
        'employee_status',
        'employee_group',
        'cost_center',
        'region',
        'gl_class',
        'position_type',
        'position',
        'station',
        'sub_department',
    ];

    protected $casts = [
        'from_date' => 'date',
        'to_date' => 'date',
        'joining_date' => 'date',
        'confirmation_date' => 'date',
        'contract_start_date' => 'date',
        'contract_end_date' => 'date',
        'resign_date' => 'date',
        'leaving_date' => 'date',
        'expected_confirmation_days' => 'integer',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
