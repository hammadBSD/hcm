<?php

namespace App\Livewire\Payroll;

use App\Models\Tax;
use App\Services\PayrollCalculationService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class TaxManagement extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedDepartment = '';
    /** Filter: tax year range label e.g. "2025-26" or "" for all */
    public $taxYear = '';
    public $showAddTaxModal = false;
    public $showViewTaxModal = false;
    public $showEditTaxModal = false;
    public $sortBy = '';
    public $sortDirection = 'asc';

    /** Selected tax record for view/edit */
    public $selectedTaxId = null;

    /** Add Tax Record form */
    public $addStartYear = '';
    public $addStartMonth = '7';
    public $addEndYear = '';
    public $addEndMonth = '6';
    public $salaryFrom = '';
    public $salaryTo = '';
    public $tax = '';
    public $exemptedTaxAmount = '';
    public $additionalTaxAmount = '';

    /** Edit Tax Record form */
    public $editStartYear = '';
    public $editStartMonth = '';
    public $editEndYear = '';
    public $editEndMonth = '';
    public $editSalaryFrom = '';
    public $editSalaryTo = '';
    public $editTax = '';
    public $editExemptedTaxAmount = '';
    public $editAdditionalTaxAmount = '';

    /** Tax calculator in View flyout */
    public $calculatorSalary = '';
    public $calculatorTaxYear = '';

    public function mount()
    {
        $user = Auth::user();

        if (!$user || !$user->can('payroll.export')) {
            abort(403);
        }

        $y = now()->year;
        $this->taxYear = ($y - 1) . '-' . substr((string) $y, -2);
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedSelectedDepartment()
    {
        $this->resetPage();
    }

    public function updatedTaxYear()
    {
        $this->resetPage();
    }

    public function sort($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function openAddTaxModal()
    {
        $y = now()->year;
        $this->addStartYear = (string) ($y - 1);
        $this->addStartMonth = '7';
        $this->addEndYear = (string) $y;
        $this->addEndMonth = '6';
        $this->salaryFrom = '1';
        $this->salaryTo = '600000';
        $this->tax = '0';
        $this->exemptedTaxAmount = '0';
        $this->additionalTaxAmount = '0';
        $this->showAddTaxModal = true;
    }

    public function closeAddTaxModal()
    {
        $this->showAddTaxModal = false;
    }

    public function addTaxRecord()
    {
        $year = (int) $this->addTaxYear;
        $salaryFrom = (float) $this->salaryFrom;
        $salaryTo = (float) $this->salaryTo;
        $tax = (float) $this->tax;
        $exempted = (float) $this->exemptedTaxAmount;
        $additional = (float) $this->additionalTaxAmount;

        if ($year < 2000 || $year > 2100) {
            session()->flash('error', __('Tax year must be between 2000 and 2100.'));
            return;
        }
        if ($salaryFrom < 0 || $salaryTo < 0 || $salaryFrom > $salaryTo) {
            session()->flash('error', __('Salary From must be less than or equal to Salary To, and both must be non-negative.'));
            return;
        }

        Tax::create([
            'tax_year' => $year,
            'salary_from' => $salaryFrom,
            'salary_to' => $salaryTo,
            'tax' => $tax,
            'exempted_tax_amount' => $exempted,
            'additional_tax_amount' => $additional,
        ]);

        $this->closeAddTaxModal();
        session()->flash('message', __('Tax record added successfully.'));
    }

    public function generateTaxReport()
    {
        // This would generate tax report
        session()->flash('message', 'Tax report generated successfully!');
    }

    public function viewTaxRecord(int $id): void
    {
        $record = Tax::find($id);
        $this->selectedTaxId = $id;
        $this->calculatorTaxYear = $record ? $record->tax_year_label : ((string) (now()->year - 1) . '-' . substr((string) now()->year, -2));
        $this->showViewTaxModal = true;
    }

    public function closeViewTaxModal(): void
    {
        $this->showViewTaxModal = false;
        $this->selectedTaxId = null;
    }

    public function editTaxRecord(int $id): void
    {
        $record = Tax::find($id);
        if (!$record) {
            session()->flash('error', __('Tax record not found.'));
            return;
        }
        $this->selectedTaxId = $id;
        $this->editStartYear = $record->start_year !== null ? (string) $record->start_year : (string) ($record->tax_year - 1);
        $this->editStartMonth = $record->start_month !== null ? (string) $record->start_month : '7';
        $this->editEndYear = (string) ($record->end_year ?? $record->tax_year);
        $this->editEndMonth = $record->end_month !== null ? (string) $record->end_month : '6';
        $this->editSalaryFrom = $this->formatTaxFieldForInput($record->salary_from);
        $this->editSalaryTo = $this->formatTaxFieldForInput($record->salary_to);
        $this->editTax = $this->formatTaxFieldForInput($record->tax);
        $this->editExemptedTaxAmount = $this->formatTaxFieldForInput($record->exempted_tax_amount);
        $this->editAdditionalTaxAmount = $this->formatTaxFieldForInput($record->additional_tax_amount);
        $this->showEditTaxModal = true;
    }

    /**
     * Format decimal for input display: no trailing .00 for whole numbers.
     */
    protected function formatTaxFieldForInput($value): string
    {
        $f = (float) $value;
        return $f === (float) (int) $f ? (string) (int) $f : (string) $f;
    }

    public function closeEditTaxModal(): void
    {
        $this->showEditTaxModal = false;
        $this->selectedTaxId = null;
    }

    public function updateTaxRecord(): void
    {
        $id = (int) $this->selectedTaxId;
        if ($id <= 0) {
            session()->flash('error', __('Invalid tax record.'));
            return;
        }
        $record = Tax::find($id);
        if (!$record) {
            session()->flash('error', __('Tax record not found.'));
            return;
        }
        $startYear = (int) $this->editStartYear;
        $startMonth = (int) $this->editStartMonth;
        $endYear = (int) $this->editEndYear;
        $endMonth = (int) $this->editEndMonth;
        $salaryFrom = (float) $this->editSalaryFrom;
        $salaryTo = (float) $this->editSalaryTo;
        $tax = (float) $this->editTax;
        $exempted = (float) $this->editExemptedTaxAmount;
        $additional = (float) $this->editAdditionalTaxAmount;

        if ($startYear < 2000 || $startYear > 2100 || $endYear < 2000 || $endYear > 2100) {
            session()->flash('error', __('Tax year start and end must be between 2000 and 2100.'));
            return;
        }
        if ($startMonth < 1 || $startMonth > 12 || $endMonth < 1 || $endMonth > 12) {
            session()->flash('error', __('Start month and end month must be between 1 and 12.'));
            return;
        }
        $startKey = $startYear * 12 + $startMonth;
        $endKey = $endYear * 12 + $endMonth;
        if ($startKey >= $endKey) {
            session()->flash('error', __('Tax period end must be after start.'));
            return;
        }
        if ($salaryFrom < 0 || $salaryTo < 0 || $salaryFrom > $salaryTo) {
            session()->flash('error', __('Salary From must be less than or equal to Salary To, and both must be non-negative.'));
            return;
        }

        $record->update([
            'tax_year' => $endYear,
            'start_year' => $startYear,
            'start_month' => $startMonth,
            'end_year' => $endYear,
            'end_month' => $endMonth,
            'salary_from' => $salaryFrom,
            'salary_to' => $salaryTo,
            'tax' => $tax,
            'exempted_tax_amount' => $exempted,
            'additional_tax_amount' => $additional,
        ]);

        $this->closeEditTaxModal();
        session()->flash('message', __('Tax record updated successfully.'));
    }

    public function getSelectedTaxRecordProperty(): ?Tax
    {
        if (!$this->selectedTaxId) {
            return null;
        }
        return Tax::find($this->selectedTaxId);
    }

    /**
     * Distinct tax year options for filter dropdown (value = label e.g. "2025-26").
     */
    public function getTaxYearOptionsProperty(): array
    {
        $labels = Tax::all()->map(fn ($t) => $t->tax_year_label)->unique()->sort()->values()->reverse()->toArray();
        $options = [['value' => '', 'label' => __('All Years')]];
        foreach ($labels as $l) {
            $options[] = ['value' => $l, 'label' => __('Tax year :label', ['label' => $l])];
        }
        return $options;
    }

    /**
     * Calculator results for the View flyout (uses PayrollCalculationService when slabs are enabled).
     * calculatorTaxYear is a label e.g. "2025-26"; we resolve to a payroll month for slab lookup.
     */
    public function getCalculatorResultsProperty(): array
    {
        $monthly = (float) ($this->calculatorSalary ?? 0);
        $label = trim((string) ($this->calculatorTaxYear ?? ''));
        $payrollMonth = null;
        if ($label !== '') {
            $parts = explode('-', $label);
            if (count($parts) >= 2 && is_numeric($parts[0]) && is_numeric($parts[1])) {
                $endYear = (int) $parts[1];
                if ($endYear < 100) {
                    $endYear += 2000;
                }
                $payrollMonth = $endYear . '-01';
            }
        }
        $year = $payrollMonth ? (int) substr($payrollMonth, 0, 4) : (int) date('Y');
        $monthlyTax = $monthly > 0 ? PayrollCalculationService::getTaxAmount($monthly, $year, $payrollMonth) : 0.0;
        $yearlyIncome = $monthly * 12;
        $yearlyTax = $monthlyTax * 12;
        return [
            'monthly_income' => $monthly,
            'monthly_tax' => round($monthlyTax, 2),
            'salary_after_tax' => round($monthly - $monthlyTax, 2),
            'yearly_income' => round($yearlyIncome, 2),
            'yearly_tax' => round($yearlyTax, 2),
            'yearly_after_tax' => round($yearlyIncome - $yearlyTax, 2),
        ];
    }

    public function downloadTaxRecord($id)
    {
        // Placeholder: could generate PDF
        session()->flash('message', __('Download not implemented for this record.'));
    }

    public function deleteTaxRecord(int $id): void
    {
        $record = Tax::find($id);
        if ($record) {
            $record->delete();
            session()->flash('message', __('Tax record deleted successfully.'));
        } else {
            session()->flash('error', __('Tax record not found.'));
        }
    }

    public function render()
    {
        $query = Tax::query();
        $label = trim((string) $this->taxYear);
        if ($label !== '') {
            $parts = explode('-', $label);
            if (count($parts) >= 2 && is_numeric($parts[0]) && is_numeric($parts[1])) {
                $startY = (int) $parts[0];
                $endY = (int) $parts[1];
                if ($endY < 100) {
                    $endY += 2000;
                }
                $query->where(function ($q) use ($startY, $endY) {
                    $q->where(function ($q2) use ($startY, $endY) {
                        $q2->where('start_year', $startY)->where('end_year', $endY);
                    })->orWhere(function ($q2) use ($endY) {
                        $q2->whereNull('start_year')->where('tax_year', $endY);
                    });
                });
            }
        }

        $sortField = $this->sortBy ?: 'tax_year';
        $sortDir = $this->sortDirection === 'asc' ? 'asc' : 'desc';
        $allowedSort = ['tax_year', 'salary_from', 'salary_to', 'tax', 'exempted_tax_amount', 'additional_tax_amount'];
        if (in_array($sortField, $allowedSort, true)) {
            $query->orderBy($sortField, $sortDir);
        } else {
            $query->orderBy('tax_year', 'desc')->orderBy('salary_from', 'asc');
        }

        $taxRecords = $query->paginate(15);

        return view('livewire.payroll.tax-management', [
            'taxRecords' => $taxRecords,
        ])->layout('components.layouts.app');
    }
}
