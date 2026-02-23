<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class DeptWiseSummaryExport implements FromArray, WithColumnWidths, WithEvents
{
    protected array $summaryRows;

    protected array $grandTotal;

    protected string $monthLabel;

    public function __construct(array $summaryRows, array $grandTotal, string $monthLabel)
    {
        $this->summaryRows = $summaryRows;
        $this->grandTotal = $grandTotal;
        $this->monthLabel = $monthLabel;
    }

    public function array(): array
    {
        $rows = [];

        $rows[] = ['Payroll ' . $this->monthLabel];
        $rows[] = [''];

        $rows[] = [
            'Department', 'No. of Emp.', 'MCS', 'Brand',
            'Gross Salary Before Vehicle Allowance', 'Gross Salary', 'Deduction Absent Days', 'Ded Amt Hrs', 'Net Gross',
            'Tax', 'Eobi', 'Advance / Rentals', 'Loan',
            'Net Pay', 'HBL to HBL', 'Cheque', 'IBFT', 'Cash', 'To be Disbursed', 'Hold', 'Total',
            'Already Paid', 'Balance', 'EOBI Contribution (Employer)',
        ];

        foreach ($this->summaryRows as $r) {
            $rows[] = [
                $r['department'],
                $r['no_of_emp'],
                $r['mcs'] ?? '—',
                $r['brand'] ?? '—',
                number_format($r['total_basic'], 2),
                number_format($r['total_gross'], 2),
                $r['total_absent'],
                number_format($r['ded_amt_hrs'], 2),
                number_format($r['net_gross'], 2),
                number_format($r['total_tax'], 2),
                number_format($r['total_eobi'], 2),
                number_format($r['total_advance'], 2),
                number_format($r['total_loan'], 2),
                number_format($r['total_net_salary'], 2),
                number_format($r['hbl_to_hbl'], 2),
                number_format($r['cheque'], 2),
                number_format($r['ibft'], 2),
                number_format($r['cash'], 2),
                number_format($r['to_be_disbursed'], 2),
                number_format($r['hold'], 2),
                number_format($r['total'], 2),
                number_format($r['already_paid'], 2),
                number_format($r['balance'], 2),
                number_format($r['eobi_employer'], 2),
            ];
        }

        $g = $this->grandTotal;
        $rows[] = [
            'GRAND TOTAL',
            $g['no_of_emp'], '', '',
            number_format($g['total_basic'], 2),
            number_format($g['total_gross'], 2),
            $g['total_absent'],
            number_format($g['ded_amt_hrs'], 2),
            number_format($g['net_gross'], 2),
            number_format($g['total_tax'], 2),
            number_format($g['total_eobi'], 2),
            number_format($g['total_advance'], 2),
            number_format($g['total_loan'], 2),
            number_format($g['total_net_salary'], 2),
            number_format($g['hbl_to_hbl'], 2),
            number_format($g['cheque'], 2),
            number_format($g['ibft'], 2),
            number_format($g['cash'], 2),
            number_format($g['to_be_disbursed'], 2),
            number_format($g['hold'], 2),
            number_format($g['total'], 2),
            number_format($g['already_paid'], 2),
            number_format($g['balance'], 2),
            number_format($g['eobi_employer'], 2),
        ];

        return $rows;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 18, 'B' => 10, 'C' => 8, 'D' => 10,
            'E' => 18, 'F' => 14, 'G' => 10, 'H' => 10, 'I' => 12,
            'J' => 10, 'K' => 10, 'L' => 10, 'M' => 14, 'N' => 10,
            'O' => 12, 'P' => 12, 'Q' => 10, 'R' => 10, 'S' => 10, 'T' => 14, 'U' => 10, 'V' => 10,
            'W' => 12, 'X' => 18,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();
                $highestCol = $sheet->getHighestDataColumn() ?: 'X';

                for ($excelRow = 1; $excelRow <= $highestRow; $excelRow++) {
                    $cellA = (string) $sheet->getCell('A' . $excelRow)->getValue();
                    $range = 'A' . $excelRow . ':' . $highestCol . $excelRow;

                    if (str_starts_with($cellA, 'Payroll ')) {
                        $sheet->mergeCells('A' . $excelRow . ':' . $highestCol . $excelRow);
                        $sheet->getStyle($range)->applyFromArray([
                            'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => 'FFFFFF']],
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1e3a5f']],
                            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                        ]);
                        $sheet->getRowDimension($excelRow)->setRowHeight(28);
                        continue;
                    }

                    if ($cellA === 'Department') {
                        $sheet->getStyle($range)->applyFromArray([
                            'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => '334155']],
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'cbd5e1']],
                            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '94a3b8']]],
                        ]);
                        continue;
                    }

                    if ($cellA === 'GRAND TOTAL') {
                        $sheet->getStyle($range)->applyFromArray([
                            'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0f766e']],
                            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '0d9488']]],
                        ]);
                        $sheet->getRowDimension($excelRow)->setRowHeight(24);
                        continue;
                    }

                    if ($cellA !== '') {
                        $sheet->getStyle($range)->applyFromArray([
                            'font' => ['color' => ['rgb' => '000000']],
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFFFF']],
                            'borders' => ['outline' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'e2e8f0']]],
                        ]);
                    }
                }
            },
        ];
    }
}
