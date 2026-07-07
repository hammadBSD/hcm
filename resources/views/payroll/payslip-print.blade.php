@php
    $fmt = fn ($amount) => number_format((float) $amount, 2, '.', ',');
    $companyName = 'BSDtechs';

    $totalEarnings = (float) $snapshot->gross_salary
        + (float) $snapshot->bonus
        + (float) $snapshot->salary_adjustment;

    $earnings = array_values(array_filter([
        ['label' => __('Basic Salary'), 'amount' => $snapshot->basic_salary],
        ['label' => __('Allowances'), 'amount' => $snapshot->allowances],
        (float) $snapshot->ot_amt > 0
            ? ['label' => __('Overtime') . ' (' . number_format((float) $snapshot->ot_hrs, 2) . ' ' . __('hrs') . ')', 'amount' => $snapshot->ot_amt]
            : null,
        (float) $snapshot->amount_extra_days > 0
            ? ['label' => __('Extra Days') . ' (' . (int) $snapshot->extra_days . ')', 'amount' => $snapshot->amount_extra_days]
            : null,
        ['label' => __('Gross Salary'), 'amount' => $snapshot->gross_salary],
        ['label' => __('Bonus'), 'amount' => $snapshot->bonus],
        (float) $snapshot->salary_adjustment != 0
            ? ['label' => __('Salary Adjustment'), 'amount' => $snapshot->salary_adjustment]
            : null,
    ]));

    $deductions = array_values(array_filter([
        ['label' => __('Short Hours') . ' (' . ($snapshot->short_excess_hours ?: '0:00') . ')', 'amount' => $snapshot->hourly_deduction_amount],
        ['label' => __('Lates') . ' (' . (int) $snapshot->deduction_late_days . ' ' . __('days') . ')', 'amount' => $snapshot->deduction_late_amount],
        ['label' => __('Absent Days'), 'amount' => $snapshot->deduction_absent_days],
        ['label' => __('Tax'), 'amount' => $snapshot->tax],
        (float) $snapshot->tax_adjustment != 0
            ? ['label' => __('Tax Adjustment'), 'amount' => $snapshot->tax_adjustment]
            : null,
        (float) $snapshot->prof_tax > 0
            ? ['label' => __('Professional Tax'), 'amount' => $snapshot->prof_tax]
            : null,
        ['label' => __('EOBI'), 'amount' => $snapshot->eobi],
        (float) $snapshot->epf_ee > 0
            ? ['label' => __('EPF (Employee)'), 'amount' => $snapshot->epf_ee]
            : null,
        (float) $snapshot->esic_ee > 0
            ? ['label' => __('ESIC (Employee)'), 'amount' => $snapshot->esic_ee]
            : null,
        ['label' => __('Advance'), 'amount' => $snapshot->advance],
        ['label' => __('Loan'), 'amount' => $snapshot->loan],
    ]));

    $rowCount = max(count($earnings) + 1, count($deductions) + 1);
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Salary Slip') }} — {{ $monthLabel }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            color: #1a1a1a;
            background: #f4f4f5;
            padding: 2rem 1rem;
            line-height: 1.5;
        }
        .slip {
            max-width: 820px;
            margin: 0 auto;
            background: #fff;
            border: 1px solid #e4e4e7;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.06);
        }
        .slip-inner { padding: 2.5rem 2.75rem 2rem; }
        .header {
            text-align: center;
            padding-bottom: 1.5rem;
            border-bottom: 2px solid #18181b;
            margin-bottom: 1.75rem;
        }
        .logo {
            height: 56px;
            width: auto;
            margin: 0 auto 0.75rem;
            display: block;
        }
        .company-name {
            font-size: 1.125rem;
            font-weight: 700;
            letter-spacing: 0.02em;
            color: #18181b;
        }
        .company-meta {
            margin-top: 0.35rem;
            font-size: 0.75rem;
            color: #71717a;
        }
        .title {
            margin-top: 1.25rem;
            font-size: 1.75rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: #18181b;
        }
        .employee-info {
            margin-bottom: 1.75rem;
            font-size: 0.9rem;
        }
        .employee-info p {
            margin-bottom: 0.35rem;
        }
        .employee-info strong {
            display: inline-block;
            min-width: 9.5rem;
            color: #3f3f46;
        }
        table.main {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.875rem;
        }
        table.main th,
        table.main td {
            border: 1px solid #d4d4d8;
            padding: 0.55rem 0.85rem;
        }
        table.main thead th {
            background: #f4f4f5;
            font-weight: 700;
            text-align: left;
            color: #27272a;
        }
        table.main thead th.amount,
        table.main td.amount {
            text-align: right;
            width: 22%;
        }
        table.main tbody td.label {
            color: #3f3f46;
        }
        table.main tbody tr.total td {
            font-weight: 700;
            background: #fafafa;
            border-top: 2px solid #a1a1aa;
        }
        table.net-pay {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1.25rem;
            font-size: 0.9rem;
        }
        table.net-pay th,
        table.net-pay td {
            border: 1px solid #d4d4d8;
            padding: 0.65rem 0.85rem;
        }
        table.net-pay th {
            background: #f4f4f5;
            text-align: left;
            font-weight: 700;
        }
        table.net-pay td.amount {
            text-align: right;
            font-size: 1.125rem;
            font-weight: 700;
            color: #1d4ed8;
        }
        .footer {
            margin-top: 2rem;
            padding-top: 1.25rem;
            border-top: 1px solid #e4e4e7;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            gap: 2rem;
            font-size: 0.8rem;
            color: #71717a;
        }
        .signature {
            min-width: 220px;
        }
        .signature-line {
            margin-top: 2.5rem;
            border-top: 1px solid #a1a1aa;
            padding-top: 0.35rem;
            font-weight: 600;
            color: #3f3f46;
        }
        @media print {
            body { background: #fff; padding: 0; }
            .slip { border: none; box-shadow: none; max-width: none; }
        }
    </style>
</head>
<body>
    <div class="slip">
        <div class="slip-inner">
            <header class="header">
                @if(!empty($logoDataUri))
                    <img src="{{ $logoDataUri }}" alt="{{ $companyName }}" class="logo">
                @endif
                <div class="company-name">{{ $companyName }}</div>
                <div class="company-meta">{{ __('Human Capital Management') }}</div>
                <h1 class="title">{{ __('Salary Slip') }}</h1>
            </header>

            <section class="employee-info">
                <p><strong>{{ __('Pay Period') }}:</strong> {{ $payPeriodStart }} {{ __('to') }} {{ $payPeriodEnd }}</p>
                @if($paidDate)
                    <p><strong>{{ __('Pay Date') }}:</strong> {{ $paidDate }}</p>
                @endif
                <p><strong>{{ __('Employee Name') }}:</strong> {{ $employeeName }}</p>
                <p><strong>{{ __('Employee ID') }}:</strong> {{ $employeeCode }}</p>
                <p><strong>{{ __('Position') }}:</strong> {{ $designation ?: '—' }}</p>
                @if($department)
                    <p><strong>{{ __('Department') }}:</strong> {{ $department }}</p>
                @endif
            </section>

            <table class="main">
                <thead>
                    <tr>
                        <th>{{ __('Earnings') }}</th>
                        <th class="amount">{{ __('Amount') }}</th>
                        <th>{{ __('Deductions') }}</th>
                        <th class="amount">{{ __('Amount') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @for($i = 0; $i < $rowCount - 1; $i++)
                        <tr>
                            <td class="label">{{ $earnings[$i]['label'] ?? '' }}</td>
                            <td class="amount">{{ isset($earnings[$i]) ? $fmt($earnings[$i]['amount']) : '' }}</td>
                            <td class="label">{{ $deductions[$i]['label'] ?? '' }}</td>
                            <td class="amount">{{ isset($deductions[$i]) ? $fmt($deductions[$i]['amount']) : '' }}</td>
                        </tr>
                    @endfor
                    <tr class="total">
                        <td class="label">{{ __('Total Earnings') }}</td>
                        <td class="amount">{{ $fmt($totalEarnings) }}</td>
                        <td class="label">{{ __('Total Deductions') }}</td>
                        <td class="amount">{{ $fmt($snapshot->total_deductions) }}</td>
                    </tr>
                </tbody>
            </table>

            <table class="net-pay">
                <thead>
                    <tr>
                        <th>{{ __('Net Pay') }}</th>
                        <th class="amount">{{ __('Amount') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>{{ __('Net Salary') }}</strong></td>
                        <td class="amount">{{ $fmt($snapshot->net_salary) }}</td>
                    </tr>
                </tbody>
            </table>

            <footer class="footer">
                <div>
                    @if($paidDate)
                        <div>{{ __('Paid') }}: {{ $paidDate }}</div>
                    @endif
                    <div>{{ __('Month') }}: {{ $monthLabel }}</div>
                </div>
                <div class="signature">
                    <div class="signature-line">{{ __('Employee Signature') }}</div>
                </div>
            </footer>
        </div>
    </div>
</body>
</html>
