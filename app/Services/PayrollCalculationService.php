<?php

namespace App\Services;

use App\Models\AdvanceSalaryRequest;
use App\Models\Loan;
use App\Models\PayrollSetting;
use App\Models\Tax;

class PayrollCalculationService
{
    /**
     * Get tax amount for a given monthly gross salary based on current settings (percentage or tax slabs).
     * When using tax slabs: annual gross = grossSalary × 12; find slab for that annual amount; slab tax is annual, so return slab.tax / 12 for monthly deduction.
     */
    public static function getTaxAmount(float $grossSalary, ?int $taxYear = null): float
    {
        $settings = PayrollSetting::get();
        $year = $taxYear ?? (int) date('Y');

        if ($settings->useTaxSlabs()) {
            $annualGross = $grossSalary * 12;
            $slab = Tax::where('tax_year', $year)
                ->where('salary_from', '<=', $annualGross)
                ->where('salary_to', '>=', $annualGross)
                ->first();
            if ($slab !== null) {
                $annualTax = (float) $slab->tax;
                return round($annualTax / 12, 2);
            }
            return 0.0;
        }

        $pct = (float) $settings->tax_percentage;
        return round($grossSalary * ($pct / 100), 2);
    }

    /**
     * Parse short_excess_hours string (e.g. "-9:30" or "0:00") to decimal hours. Negative = short.
     */
    public static function parseShortExcessHours(string $shortExcessHours): float
    {
        $shortExcessHours = trim($shortExcessHours);
        if ($shortExcessHours === '' || $shortExcessHours === '0:00') {
            return 0.0;
        }
        $negative = str_starts_with($shortExcessHours, '-');
        $shortExcessHours = ltrim($shortExcessHours, '-');
        $parts = explode(':', $shortExcessHours);
        $hours = (int) ($parts[0] ?? 0);
        $mins = (int) ($parts[1] ?? 0);
        $decimal = $hours + ($mins / 60);
        return $negative ? -$decimal : $decimal;
    }

    /**
     * Get deduction amount when short hours exceed threshold. Returns 0 if short hours <= threshold or not short.
     * When no fixed per-hour rate is set: hourly rate = gross / working_days / hours_per_day; deduction = hourly rate × excess short hours (decimal, so minutes included).
     */
    public static function getShortHoursDeduction(string $shortExcessHoursString, float $grossSalary, int $workingDays): float
    {
        $settings = PayrollSetting::get();
        $threshold = (float) $settings->short_hours_threshold;
        $shortHours = self::parseShortExcessHours($shortExcessHoursString);
        if ($shortHours >= 0) {
            return 0.0; // excess or zero, no deduction
        }
        $shortHoursAbs = -$shortHours;
        if ($shortHoursAbs <= $threshold) {
            return 0.0;
        }
        $hoursOverThreshold = $shortHoursAbs - $threshold;
        $perHourDeduction = $settings->short_hours_deduction_per_hour;
        if ($perHourDeduction !== null && $perHourDeduction > 0) {
            return round($hoursOverThreshold * (float) $perHourDeduction, 2);
        }
        $workingDays = max(1, $workingDays);
        $hoursPerDay = (float) ($settings->hours_per_day ?? 9);
        if ($hoursPerDay <= 0) {
            return 0.0;
        }
        $hourlyRate = $grossSalary / $workingDays / $hoursPerDay;
        return round($hoursOverThreshold * $hourlyRate, 2);
    }

    /**
     * Get deduction for absent days.
     * When "use formula" is enabled: per-day = gross / working_days; total = per-day × absent_days.
     * Otherwise: total = per_day_absent_deduction × absent_days.
     */
    public static function getAbsentDeduction(int $absentDays, float $grossSalary, int $workingDays): float
    {
        if ($absentDays <= 0) {
            return 0.0;
        }
        $settings = PayrollSetting::get();
        if (!empty($settings->absent_deduction_use_formula)) {
            $workingDays = max(1, $workingDays);
            $perDaySalary = $grossSalary / $workingDays;
            return round($absentDays * $perDaySalary, 2);
        }
        $perDay = (float) $settings->per_day_absent_deduction;
        return round($absentDays * $perDay, 2);
    }

    /**
     * Advance salary deduction for an employee for a given month: sum of approved advance amounts
     * whose expected payback date falls in that month.
     */
    public static function getAdvanceDeduction(int $employeeId, int $month, int $year): float
    {
        $total = AdvanceSalaryRequest::where('employee_id', $employeeId)
            ->where('status', AdvanceSalaryRequest::STATUS_APPROVED)
            ->whereYear('expected_payback_date', $year)
            ->whereMonth('expected_payback_date', $month)
            ->sum('amount');
        return round((float) $total, 2);
    }

    /**
     * Loan deduction for an employee: sum of installment amounts for approved loans
     * that still have remaining installments.
     */
    public static function getLoanDeduction(int $employeeId): float
    {
        $total = Loan::where('employee_id', $employeeId)
            ->where('status', Loan::STATUS_APPROVED)
            ->where('remaining_installments', '>', 0)
            ->sum('installment_amount');
        return round((float) $total, 2);
    }

    /**
     * Parse expected hours string "HH:MM" to decimal hours.
     */
    public static function expectedHoursToDecimal(string $expectedHours): float
    {
        $parts = explode(':', trim($expectedHours));
        $h = (int) ($parts[0] ?? 0);
        $m = (int) ($parts[1] ?? 0);
        return $h + ($m / 60);
    }
}
