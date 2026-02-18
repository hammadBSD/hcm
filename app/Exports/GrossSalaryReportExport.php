<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class GrossSalaryReportExport implements FromArray, WithColumnWidths, WithEvents
{
    protected array $groupedData;

    protected string $monthLabel;

    public function __construct(array $groupedData, string $monthLabel)
    {
        $this->groupedData = $groupedData;
        $this->monthLabel = $monthLabel;
    }

    public function array(): array
    {
        $rows = [];

        // Include all departments including N/A (employees without a department)
        $groups = array_values($this->groupedData);

        $rows[] = ['Gross Salary Report - ' . $this->monthLabel];
        $rows[] = ['']; // spacer so row indices stay predictable

        foreach ($groups as $group) {
            $deptLabel = (string) ($group['department'] ?? '') === 'N/A'
                ? 'No department'
                : ('Department: ' . $group['department']);
            $rows[] = [$deptLabel];
            $rows[] = [
                'Total Gross Salary: ' . number_format($group['total_gross'], 2),
                'Total Salary (without tax): ' . number_format($group['total_gross'], 2),
                'Total Tax: ' . number_format($group['total_tax'] ?? 0, 2),
                'No. of employees: ' . $group['count'],
            ];
            $rows[] = ['Employee', 'Department', 'Basic Salary', 'Allowances', 'Gross Salary', 'Tax'];
            foreach ($group['employees'] as $r) {
                $emp = $r['employee'];
                $name = trim(($emp->first_name ?? '') . ' ' . ($emp->last_name ?? ''));
                $rows[] = [
                    $name,
                    $r['department'],
                    number_format($r['basic_salary'], 2),
                    number_format($r['allowances'], 2),
                    number_format($r['gross_salary'], 2),
                    number_format($r['tax'] ?? 0, 2),
                ];
            }
            $rows[] = [''];
        }

        $grandBasic = 0;
        $grandAllowances = 0;
        $grandGross = 0;
        $grandTax = 0;
        foreach ($groups as $g) {
            $grandBasic += $g['total_basic'];
            $grandAllowances += $g['total_allowances'];
            $grandGross += $g['total_gross'];
            $grandTax += $g['total_tax'] ?? 0;
        }
        $rows[] = [
            'GRAND TOTAL',
            '',
            number_format($grandBasic, 2),
            number_format($grandAllowances, 2),
            number_format($grandGross, 2),
            number_format($grandTax, 2),
        ];

        return $rows;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 28,
            'B' => 18,
            'C' => 14,
            'D' => 14,
            'E' => 14,
            'F' => 14,
            'G' => 12,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();
                $highestCol = $sheet->getHighestDataColumn() ?: 'G';

                for ($excelRow = 1; $excelRow <= $highestRow; $excelRow++) {
                    $cellA = (string) $sheet->getCell('A' . $excelRow)->getValue();
                    $cellB = (string) $sheet->getCell('B' . $excelRow)->getValue();
                    $range = 'A' . $excelRow . ':' . $highestCol . $excelRow;

                    if (str_starts_with($cellA, 'Gross Salary Report -')) {
                        $sheet->mergeCells('A' . $excelRow . ':' . $highestCol . $excelRow);
                        $sheet->getStyle($range)->applyFromArray([
                            'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => 'FFFFFF']],
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1e3a5f']],
                            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                        ]);
                        $sheet->getRowDimension($excelRow)->setRowHeight(28);
                        continue;
                    }

                    if (str_starts_with($cellA, 'Department:') || $cellA === 'No department') {
                        $sheet->mergeCells('A' . $excelRow . ':' . $highestCol . $excelRow);
                        $sheet->getStyle($range)->applyFromArray([
                            'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '475569']],
                            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '64748b']]],
                        ]);
                        $sheet->getRowDimension($excelRow)->setRowHeight(22);
                        continue;
                    }

                    if (str_starts_with($cellA, 'Total Gross Salary:')) {
                        $sheet->getStyle($range)->applyFromArray([
                            'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '15803d']],
                            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '166534']]],
                        ]);
                        continue;
                    }

                    if ($cellA === 'Employee' && $cellB === 'Department') {
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
