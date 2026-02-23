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
        'salary_from',
        'salary_to',
        'tax',
        'exempted_tax_amount',
        'additional_tax_amount',
    ];

    protected $casts = [
        'tax_year' => 'integer',
        'salary_from' => 'decimal:2',
        'salary_to' => 'decimal:2',
        'tax' => 'decimal:2',
        'exempted_tax_amount' => 'decimal:2',
        'additional_tax_amount' => 'decimal:2',
    ];
}
