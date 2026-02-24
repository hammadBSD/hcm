<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tax extends Model
{
    use HasFactory;

    protected $table = 'taxes';

    protected $fillable = [
        'tax_year',
        'start_year',
        'start_month',
        'end_year',
        'end_month',
        'salary_from',
        'salary_to',
        'tax',
        'exempted_tax_amount',
        'additional_tax_amount',
    ];

    protected $casts = [
        'tax_year' => 'integer',
        'start_year' => 'integer',
        'start_month' => 'integer',
        'end_year' => 'integer',
        'end_month' => 'integer',
        'salary_from' => 'decimal:2',
        'salary_to' => 'decimal:2',
        'tax' => 'decimal:2',
        'exempted_tax_amount' => 'decimal:2',
        'additional_tax_amount' => 'decimal:2',
    ];

    /**
     * Display label for tax year range, e.g. "2025-26" (start year - short end year).
     */
    public function getTaxYearLabelAttribute(): string
    {
        if ($this->start_year !== null && $this->end_year !== null) {
            return $this->start_year . '-' . substr((string) $this->end_year, -2);
        }
        $y = (int) $this->tax_year;
        return ($y - 1) . '-' . substr((string) $y, -2);
    }
}
