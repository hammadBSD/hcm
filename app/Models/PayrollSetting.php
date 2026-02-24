<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollSetting extends Model
{
    protected $table = 'payroll_settings';

    protected $fillable = [
        'payroll_frequency',
        'payroll_day',
        'overtime_rate',
        'allowance_percentage',
        'tax_percentage',
        'provident_fund_percentage',
        'tax_calculation_method',
        'short_hours_threshold',
        'hours_per_day',
        'per_day_absent_deduction',
        'absent_deduction_use_formula',
        'short_hours_deduction_per_hour',
        'auto_process',
        'email_payslips',
        'backup_payroll',
    ];

    protected $casts = [
        'payroll_day' => 'integer',
        'overtime_rate' => 'float',
        'allowance_percentage' => 'float',
        'tax_percentage' => 'float',
        'provident_fund_percentage' => 'float',
        'short_hours_threshold' => 'float',
        'hours_per_day' => 'float',
        'per_day_absent_deduction' => 'float',
        'absent_deduction_use_formula' => 'boolean',
        'short_hours_deduction_per_hour' => 'float',
        'auto_process' => 'boolean',
        'email_payslips' => 'boolean',
        'backup_payroll' => 'boolean',
    ];

    public const TAX_METHOD_PERCENTAGE = 'percentage';
    public const TAX_METHOD_SLABS = 'tax_slabs';

    private static ?self $instance = null;

    /**
     * Get the single payroll settings row (id=1 or first).
     */
    public static function get(): self
    {
        if (self::$instance !== null) {
            return self::$instance;
        }
        self::$instance = self::first() ?? new self([
            'payroll_frequency' => 'monthly',
            'payroll_day' => 1,
            'overtime_rate' => 1.5,
            'allowance_percentage' => 10,
            'tax_percentage' => 15,
            'provident_fund_percentage' => 5,
            'tax_calculation_method' => self::TAX_METHOD_PERCENTAGE,
            'short_hours_threshold' => 9,
            'hours_per_day' => 9,
            'per_day_absent_deduction' => 0,
            'absent_deduction_use_formula' => true,
            'short_hours_deduction_per_hour' => null,
            'auto_process' => false,
            'email_payslips' => true,
            'backup_payroll' => true,
        ]);
        return self::$instance;
    }

    public static function clearCached(): void
    {
        self::$instance = null;
    }

    public function useTaxSlabs(): bool
    {
        return $this->tax_calculation_method === self::TAX_METHOD_SLABS;
    }
}
