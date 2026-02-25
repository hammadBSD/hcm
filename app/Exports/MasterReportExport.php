<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class MasterReportExport implements FromArray, WithColumnWidths, WithEvents
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

        $groups = array_values($this->groupedData);

        $rows[] = ['Master Report - ' . $this->monthLabel];
        $rows[] = [''];

        $grandGross = 0;
        $grandDeductions = 0;
        $grandNetSalary = 0;
        $grandCount = 0;
        foreach ($groups as $g) {
            $grandGross += $g['total_gross'] ?? 0;
            $grandDeductions += $g['total_deductions'] ?? 0;
            $grandNetSalary += $g['total_net_salary'] ?? 0;
            $grandCount += $g['count'] ?? 0;
        }
        $rows[] = ['Grand Total'];
        $rows[] = [
            'Total Gross Salary: ' . number_format($grandGross, 2),
            'Total Deductions: ' . number_format($grandDeductions, 2),
            'Total Net Salary: ' . number_format($grandNetSalary, 2),
            'No. of employees: ' . $grandCount,
        ];
        $rows[] = [''];

        foreach ($groups as $group) {
            $deptLabel = (string) ($group['department'] ?? '') === 'N/A'
                ? 'No department'
                : ('Department: ' . $group['department']);
            $rows[] = [$deptLabel];
            $rows[] = [
                'Total Gross Salary: ' . number_format($group['total_gross'], 2),
                'Total Deductions: ' . number_format($group['total_deductions'] ?? 0, 2),
                'Total Net Salary: ' . number_format($group['total_net_salary'] ?? 0, 2),
                'No. of employees: ' . $group['count'],
            ];
            $rows[] = [
                'Sr No', 'Emp Code', 'Employee Name', 'DEPT', 'DSG', 'DOJ', 'Current Status', 'Reporting Manager',
                'MCS', 'Brands', 'Employment Status', 'CNIC',
                'Date of Last Increment', 'Increment Amount', '# Months Since Last Increment', 'Job Duration',
                'Working Days', 'Present Days', 'Extra Days', 'Amount of extra days', 'Hourly Rate', 'Hourly Deduction Amount', 'Leaves (approved)',
                'Leave Paid', 'Leave Unpaid', 'Leave LWP', 'Absent Days', 'Late Days', 'Total Break Time', 'Holidays', 'Total Hours Worked', 'Monthly Expected Hours', 'Short/Excess Hours',
                'Salary Type', 'Basic Salary', 'Allowances', 'OT Hrs', 'OT Amt', 'Gross Salary', 'Bonus',
                'Tax', 'Prof Tax', 'EOBI', 'Advance', 'Loan',
                'Other Deductions', 'Total Deductions', 'Net Salary',
                'Bank Name', 'Account Title', 'Bank Account',
            ];
            foreach ($group['employees'] as $r) {
                $emp = $r['employee'];
                $name = trim(($emp->first_name ?? '') . ' ' . ($emp->last_name ?? ''));
                $rows[] = [
                    $r['sr_no'] ?? '',
                    $emp->employee_code ?? 'N/A',
                    $name,
                    $r['department'],
                    $r['designation'],
                    $r['doj'] ?? '—',
                    $r['current_status'] ?? '—',
                    $r['reporting_manager'] ?? '—',
                    $r['mcs'] ?? '—',
                    $r['brands'] ?? '—',
                    $r['employment_status'] ?? '—',
                    $r['cnic'] ?? '—',
                    $r['last_increment_date'] ?? '—',
                    number_format($r['last_increment_amount'] ?? 0, 2),
                    $r['months_since_increment'] ?? 0,
                    $r['job_duration'] ?? '—',
                    $r['working_days'] ?? 0,
                    $r['days_present'] ?? 0,
                    $r['extra_days'] ?? 0,
                    number_format($r['amount_extra_days'] ?? 0, 2),
                    number_format($r['hourly_rate'] ?? 0, 2),
                    number_format($r['hourly_deduction_amount'] ?? 0, 2),
                    $r['leaves_approved'] ?? 0,
                    $r['leave_paid'] ?? 0,
                    $r['leave_unpaid'] ?? 0,
                    $r['leave_lwp'] ?? 0,
                    $r['absent'] ?? 0,
                    $r['late_days'] ?? 0,
                    $r['total_break_time'] ?? '0:00',
                    $r['holiday'] ?? 0,
                    $r['total_hours_worked'] ?? '0:00',
                    $r['monthly_expected_hours'] ?? '0:00',
                    $r['short_excess_hours'] ?? '0:00',
                    $r['salary_type'] ?? '—',
                    number_format($r['basic_salary'], 2),
                    number_format($r['allowances'] ?? 0, 2),
                    $r['ot_hrs'] ?? 0,
                    number_format($r['ot_amt'] ?? 0, 2),
                    number_format($r['gross_salary'], 2),
                    number_format($r['bonus'] ?? 0, 2),
                    number_format($r['tax'] ?? 0, 2),
                    number_format($r['prof_tax'] ?? 0, 2),
                    number_format($r['eobi'] ?? 0, 2),
                    number_format($r['advance'] ?? 0, 2),
                    number_format($r['loan'] ?? 0, 2),
                    number_format($r['other_deductions'] ?? 0, 2),
                    number_format($r['total_deductions'] ?? 0, 2),
                    number_format($r['net_salary'] ?? 0, 2),
                    $r['bank_name'] ?? '—',
                    $r['account_title'] ?? '—',
                    $r['bank_account'] ?? '—',
                ];
            }
            $rows[] = [''];
        }

        $grandBasic = 0;
        $grandGross = 0;
        $grandDeductions = 0;
        $grandNetSalary = 0;
        foreach ($groups as $g) {
            $grandBasic += $g['total_basic'];
            $grandGross += $g['total_gross'];
            $grandDeductions += $g['total_deductions'] ?? 0;
            $grandNetSalary += $g['total_net_salary'] ?? 0;
        }
        $rows[] = array_merge(
            ['GRAND TOTAL'],
            array_fill(0, 32, ''),
            ['', number_format($grandBasic, 2), '', '0', '0.00', number_format($grandGross, 2), '0.00'],
            array_fill(0, 5, '0.00'),
            ['0.00', number_format($grandDeductions, 2), number_format($grandNetSalary, 2)],
            array_fill(0, 3, '')
        );

        return $rows;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 8, 'B' => 12, 'C' => 22, 'D' => 14, 'E' => 14, 'F' => 12,
            'G' => 12, 'H' => 12, 'I' => 10, 'J' => 10, 'K' => 8, 'L' => 8, 'M' => 8,
            'N' => 12, 'O' => 14, 'P' => 10, 'Q' => 12, 'R' => 14,
            'S' => 10, 'T' => 10, 'U' => 10, 'V' => 10, 'W' => 10, 'X' => 14, 'Y' => 14, 'Z' => 14,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();
                $highestCol = $sheet->getHighestDataColumn() ?: 'AK';

                for ($excelRow = 1; $excelRow <= $highestRow; $excelRow++) {
                    $cellA = (string) $sheet->getCell('A' . $excelRow)->getValue();
                    $cellB = (string) $sheet->getCell('B' . $excelRow)->getValue();
                    $range = 'A' . $excelRow . ':' . $highestCol . $excelRow;

                    if (str_starts_with($cellA, "Master Report -")) {
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

                    if ($cellA === 'Grand Total') {
                        $sheet->mergeCells('A' . $excelRow . ':' . $highestCol . $excelRow);
                        $sheet->getStyle($range)->applyFromArray([
                            'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0f766e']],
                            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '0d9488']]],
                        ]);
                        $sheet->getRowDimension($excelRow)->setRowHeight(22);
                        continue;
                    }

                    if ($cellA === 'Sr No' && $cellB === 'Emp Code' && (string) $sheet->getCell('C' . $excelRow)->getValue() === 'Employee Name') {
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
