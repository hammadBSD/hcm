<?php

namespace App\Services;

use App\Models\Loan;
use Carbon\Carbon;

class LoanScenarioService
{
    /**
     * Build month-by-month repayment schedule with scenario actions applied.
     *
     * @return array<int, array<string, mixed>>
     */
    public function buildSchedule(Loan $loan, int $maxRows = 240): array
    {
        $start = $loan->repayment_start_month
            ? $loan->repayment_start_month->copy()->startOfMonth()
            : ($loan->loan_date ? $loan->loan_date->copy()->startOfMonth() : now()->startOfMonth());

        $actions = $loan->scenarioActions()
            ->orderBy('effective_month')
            ->orderBy('id')
            ->get();

        $actionsByMonth = [];
        $freezeWindows = [];
        foreach ($actions as $a) {
            $key = $a->effective_month ? $a->effective_month->format('Y-m') : null;
            if (!$key) {
                continue;
            }
            $actionsByMonth[$key][] = $a;

            if ($a->scenario === 'freeze') {
                $payload = (array) ($a->payload ?? []);
                $from = (string) ($payload['freeze_from_month'] ?? $key);
                $resume = (string) ($payload['resume_month'] ?? '');

                if ($resume !== '' && preg_match('/^\d{4}-\d{2}$/', $from) && preg_match('/^\d{4}-\d{2}$/', $resume) && $resume > $from) {
                    $freezeWindows[] = ['from' => $from, 'to' => Carbon::createFromFormat('Y-m', $resume)->subMonth()->format('Y-m')];
                } elseif (isset($payload['freeze_months'])) {
                    $freezeMonths = max(1, (int) $payload['freeze_months']);
                    $freezeWindows[] = ['from' => $from, 'to' => Carbon::createFromFormat('Y-m', $from)->addMonths($freezeMonths - 1)->format('Y-m')];
                }
            }
        }

        $rows = [];
        $balance = round((float) $loan->loan_amount, 2);
        $installment = round((float) $loan->installment_amount, 2);
        $manualFinish = false;

        for ($i = 0; $i < $maxRows; $i++) {
            $month = $start->copy()->addMonths($i);
            $monthKey = $month->format('Y-m');
            $principal = $balance;
            $payback = min($installment, $principal);
            $rowType = 'normal';
            $isFrozenMonth = $this->isMonthInFreezeWindows($monthKey, $freezeWindows);

            if ($isFrozenMonth) {
                $payback = 0.0;
                $rowType = 'freeze';
            }

            if (isset($actionsByMonth[$monthKey])) {
                foreach ($actionsByMonth[$monthKey] as $action) {
                    $payload = (array) ($action->payload ?? []);
                    switch ($action->scenario) {
                        case 'setoff':
                        case 'terminate':
                            $payback = $principal;
                            $rowType = $action->scenario;
                            $manualFinish = true;
                            break;

                        case 'partial_payback':
                            $custom = max(0, round((float) ($payload['payback_amount'] ?? 0), 2));
                            $payback = min($custom, $principal);
                            $rowType = 'partial_payback';
                            break;

                        case 'reschedule':
                            $newInstallment = round((float) ($payload['new_installment'] ?? 0), 2);
                            $months = (int) ($payload['months'] ?? 0);
                            if ($newInstallment > 0) {
                                $installment = $newInstallment;
                            } elseif ($months > 0 && $principal > 0) {
                                $installment = round($principal / $months, 2);
                            }
                            $payback = min($installment, $principal);
                            $rowType = 'reschedule';
                            break;

                        case 'freeze':
                            $payback = 0.0;
                            $rowType = 'freeze';
                            break;

                        case 'topup':
                            $topup = max(0, round((float) ($payload['topup_amount'] ?? 0), 2));
                            if ($topup > 0) {
                                $principal = round($principal + $topup, 2);
                                $balance = $principal;
                            }
                            $rowType = 'topup';
                            $payback = min($installment, $principal);
                            break;
                    }
                }
            }
            $payback = round(max(0, min($payback, $principal)), 2);
            $balance = round(max(0, $principal - $payback), 2);

            $rows[] = [
                'no' => $i + 1,
                'month' => $month->format('M Y'),
                'month_key' => $monthKey,
                'principle_amount' => $principal,
                'payback_amount' => $payback,
                'balance' => $balance,
                'row_type' => $rowType,
                'display_payback_amount' => $rowType === 'freeze' ? null : $payback,
                'display_balance' => $rowType === 'freeze' ? null : $balance,
            ];

            if ($balance <= 0 || $manualFinish) {
                break;
            }
        }

        return $rows;
    }

    protected function isMonthInFreezeWindows(string $monthKey, array $freezeWindows): bool
    {
        foreach ($freezeWindows as $w) {
            $from = $w['from'] ?? '';
            $to = $w['to'] ?? '';
            if ($from !== '' && $to !== '' && $monthKey >= $from && $monthKey <= $to) {
                return true;
            }
        }

        return false;
    }

    public function getDeductionForMonth(Loan $loan, int $year, int $month): float
    {
        $monthKey = Carbon::create($year, $month, 1)->format('Y-m');
        $rows = $this->buildSchedule($loan);
        foreach ($rows as $row) {
            if (($row['month_key'] ?? '') === $monthKey) {
                return round((float) ($row['payback_amount'] ?? 0), 2);
            }
        }

        return 0.0;
    }
}
