<?php

namespace App\Services;

use App\Models\AdvanceSalaryRequest;
use App\Models\Loan;
use App\Models\PayrollSetting;
use App\Models\Tax;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

class PayrollCalculationService
{
    /**
     * Get taxable gross after applying Tax Exempt Percentage from Salary (if set).
     * Returns ['gross' => original, 'exempt_amount' => deducted amount, 'taxable' => amount used for tax].
     */
    public static function getTaxableGrossAfterExempt(float $grossSalary): array
    {
        $settings = PayrollSetting::get();
        $pct = (float) ($settings->tax_exempt_percentage_from_salary ?? 0);
        if ($pct <= 0) {
            return ['gross' => $grossSalary, 'exempt_amount' => 0.0, 'taxable' => $grossSalary];
        }
        $exemptAmount = round($grossSalary * ($pct / 100), 2);
        $taxable = round($grossSalary - $exemptAmount, 2);
        return ['gross' => $grossSalary, 'exempt_amount' => $exemptAmount, 'taxable' => max(0, $taxable)];
    }

    /**
     * Get tax amount for a given monthly gross salary based on current settings (percentage or tax slabs).
     * If "Tax Exempt Percentage from Salary" is set, tax is calculated on (salary - exempt % of salary).
     * When using tax slabs (e.g. Pakistan 2025-2026 style):
     * - Annual gross = grossSalary × 12.
     * - Find slab: if $payrollMonth (Y-m) is given and slab has start_year/end_year, match by range; else by tax_year.
     * - Progressive: if slab has additional_tax_amount (rate %), annual_tax = tax + (annual_gross - exempted_tax_amount) × (rate/100).
     * - Flat: if no rate, annual_tax = slab.tax.
     * - Surcharge: if annual gross > 10,000,000, add 9% to tax.
     * - Return monthly = annual_tax / 12.
     */
    public static function getTaxAmount(float $grossSalary, ?int $taxYear = null, ?string $payrollMonth = null): float
    {
        $settings = PayrollSetting::get();
        $breakdown = self::getTaxableGrossAfterExempt($grossSalary);
        $grossForTax = $breakdown['taxable'];

        $year = $taxYear ?? (int) date('Y');

        if ($settings->useTaxSlabs()) {
            $annualGross = $grossForTax * 12;
            $slab = null;
            $hasRangeColumns = Schema::hasColumn((new Tax)->getTable(), 'start_year');
            if ($hasRangeColumns && $payrollMonth !== null && $payrollMonth !== '') {
                $parts = explode('-', $payrollMonth);
                $y = (int) ($parts[0] ?? 0);
                $m = (int) ($parts[1] ?? 1);
                $monthKey = $y * 12 + $m;
                $slab = Tax::whereNotNull('start_year')
                    ->whereNotNull('end_year')
                    ->whereRaw('(start_year * 12 + start_month) <= ?', [$monthKey])
                    ->whereRaw('(end_year * 12 + end_month) >= ?', [$monthKey])
                    ->where('salary_from', '<=', $annualGross)
                    ->where('salary_to', '>=', $annualGross)
                    ->first();
            }
            if ($slab === null) {
                $slab = Tax::where('tax_year', $year)
                    ->where('salary_from', '<=', $annualGross)
                    ->where('salary_to', '>=', $annualGross)
                    ->first();
            }
            if ($slab !== null) {
                $baseTax = (float) $slab->tax;
                $rate = (float) ($slab->additional_tax_amount ?? 0);
                $excessOver = (float) ($slab->exempted_tax_amount ?? 0);

                if ($rate > 0 && $annualGross > $excessOver) {
                    $annualTax = $baseTax + ($annualGross - $excessOver) * ($rate / 100);
                } else {
                    $annualTax = $baseTax;
                }

                if ($annualGross > 10_000_000) {
                    $annualTax *= 1.09;
                }

                return round($annualTax / 12, 2);
            }
            return 0.0;
        }

        $pct = (float) $settings->tax_percentage;
        return round($grossForTax * ($pct / 100), 2);
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
        $policy = $settings->short_hours_deduction_policy ?? 'excess_only';
        $hoursToDeduct = ($threshold > 0 && $policy === 'full_when_over_threshold')
            ? $shortHoursAbs
            : $hoursOverThreshold;
        $perHourDeduction = $settings->short_hours_deduction_per_hour;
        if ($perHourDeduction !== null && $perHourDeduction > 0) {
            return round($hoursToDeduct * (float) $perHourDeduction, 2);
        }
        $workingDays = max(1, $workingDays);
        $hoursPerDay = (float) ($settings->hours_per_day ?? 9);
        if ($hoursPerDay <= 0) {
            return 0.0;
        }
        $hourlyRate = $grossSalary / $workingDays / $hoursPerDay;
        return round($hoursToDeduct * $hourlyRate, 2);
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
        $requests = AdvanceSalaryRequest::where('employee_id', $employeeId)
            ->where('status', AdvanceSalaryRequest::STATUS_APPROVED)
            ->whereNotNull('expected_payback_date')
            ->get();

        $targetMonthKey = ($year * 12) + $month;
        $total = 0.0;

        foreach ($requests as $req) {
            $type = (string) ($req->payback_transaction_type ?? 'deduct_from_salary');
            if ($type !== 'deduct_from_salary') {
                continue;
            }

            $start = $req->expected_payback_date;
            if (!$start) {
                continue;
            }
            $startMonthKey = (((int) $start->format('Y')) * 12) + ((int) $start->format('n'));
            $months = max(1, (int) ($req->payback_months ?? 1));
            $mode = (string) ($req->payback_mode ?? 'all_at_once');
            $amount = (float) $req->amount;

            if ($months <= 1 || $mode === 'all_at_once') {
                if ($targetMonthKey === $startMonthKey) {
                    $total += $amount;
                }
                continue;
            }

            $endMonthKey = $startMonthKey + $months - 1;
            if ($targetMonthKey >= $startMonthKey && $targetMonthKey <= $endMonthKey) {
                $total += ($amount / $months);
            }
        }

        return round((float) $total, 2);
    }

    /**
     * Loan deduction for an employee: sum of installment amounts for approved loans
     * that still have remaining installments.
     */
    public static function getLoanDeduction(int $employeeId, int $month, int $year): float
    {
        $loans = Loan::where('employee_id', $employeeId)
            ->where('status', Loan::STATUS_APPROVED)
            ->get();

        $service = app(LoanScenarioService::class);
        $total = 0.0;
        foreach ($loans as $loan) {
            $total += $service->getDeductionForMonth($loan, $year, $month);
        }
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

    /**
     * Split a gross increment/decrement between basic (60%) and allowances (40%).
     * Remainder from rounding goes to allowances so basic + allowances equals $incrementAmount.
     *
     * @return array{0: float, 1: float} [to_basic, to_allowances]
     */
    public static function splitIncrementBetweenBasicAndAllowances(float $incrementAmount): array
    {
        $toBasic = round($incrementAmount * 0.6, 2);
        $toAllowances = round($incrementAmount - $toBasic, 2);

        return [$toBasic, $toAllowances];
    }

    /**
     * How much of the increment applied to basic vs allowances for a stored increment row.
     * When $useSplitApportionment is true (allowances_after was persisted), use 60/40.
     * Legacy rows had the full amount on basic only.
     *
     * @return array{0: float, 1: float} [to_basic, to_allowances]
     */
    public static function incrementAmountToBasicAndAllowances(float $incrementAmount, bool $useSplitApportionment): array
    {
        if ($useSplitApportionment) {
            return self::splitIncrementBetweenBasicAndAllowances($incrementAmount);
        }

        return [round($incrementAmount, 2), 0.0];
    }
}
