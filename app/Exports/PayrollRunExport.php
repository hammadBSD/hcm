<?php

namespace App\Exports;

use App\Models\PayrollRun;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PayrollRunExport implements FromArray, WithHeadings, WithColumnWidths
{
    public function __construct(
        protected PayrollRun $run
    ) {}

    public function headings(): array
    {
        return [
            __('Employee'),
            __('Department'),
            __('Working days'),
            __('Absent'),
            __('Gross'),
            __('Tax'),
            __('EOBI'),
            __('Advance'),
            __('Loan'),
            __('Deductions'),
            __('Net'),
        ];
    }

    public function array(): array
    {
        $rows = [];
        $run = $this->run->loadMissing(['lines.employee']);

        foreach ($run->lines as $line) {
            $name = $line->employee
                ? trim((string) ($line->employee->first_name ?? '') . ' ' . (string) ($line->employee->last_name ?? ''))
                : '—';
            $rows[] = [
                $name,
                $line->department ?? 'N/A',
                (int) $line->working_days,
                (int) $line->absent,
                $this->num($line->gross_salary),
                $this->num($line->tax),
                $this->num($line->eobi),
                $this->num($line->advance),
                $this->num($line->loan),
                $this->num($line->total_deductions),
                $this->num($line->net_salary),
            ];
        }

        return $rows;
    }

    protected function num($value): string
    {
        $v = (float) ($value ?? 0);
        return $v == 0 ? '0' : number_format($v, 2);
    }

    public function columnWidths(): array
    {
        return [
            'A' => 22,
            'B' => 16,
            'C' => 14,
            'D' => 10,
            'E' => 12,
            'F' => 12,
            'G' => 10,
            'H' => 12,
            'I' => 10,
            'J' => 14,
            'K' => 12,
        ];
    }
}
