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
    public $taxYear;
    public $showAddTaxModal = false;
    public $showViewTaxModal = false;
    public $showEditTaxModal = false;
    public $sortBy = '';
    public $sortDirection = 'asc';

    /** Selected tax record for view/edit */
    public $selectedTaxId = null;

    /** Add Tax Record form */
    public $addTaxYear = '';
    public $salaryFrom = '';
    public $salaryTo = '';
    public $tax = '';
    public $exemptedTaxAmount = '';
    public $additionalTaxAmount = '';

    /** Edit Tax Record form */
    public $editTaxYear = '';
    public $editSalaryFrom = '';
    public $editSalaryTo = '';
    public $editTax = '';
    public $editExemptedTaxAmount = '';
    public $editAdditionalTaxAmount = '';

    /** Tax calculator in View flyout */
    public $calculatorSalary = '260000';
    public $calculatorTaxYear = '';

    public function mount()
    {
        $user = Auth::user();

        if (!$user || !$user->can('payroll.export')) {
            abort(403);
        }

        $this->taxYear = now()->year;
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
        $this->addTaxYear = (string) ($this->taxYear ?? now()->year);
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
        $this->calculatorTaxYear = $record ? (string) $record->tax_year : (string) now()->year;
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
        $this->editTaxYear = (string) $record->tax_year;
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
        $year = (int) $this->editTaxYear;
        $salaryFrom = (float) $this->editSalaryFrom;
        $salaryTo = (float) $this->editSalaryTo;
        $tax = (float) $this->editTax;
        $exempted = (float) $this->editExemptedTaxAmount;
        $additional = (float) $this->editAdditionalTaxAmount;

        if ($year < 2000 || $year > 2100) {
            session()->flash('error', __('Tax year must be between 2000 and 2100.'));
            return;
        }
        if ($salaryFrom < 0 || $salaryTo < 0 || $salaryFrom > $salaryTo) {
            session()->flash('error', __('Salary From must be less than or equal to Salary To, and both must be non-negative.'));
            return;
        }

        $record->update([
            'tax_year' => $year,
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
     * Calculator results for the View flyout (uses PayrollCalculationService when slabs are enabled).
     */
    public function getCalculatorResultsProperty(): array
    {
        $monthly = (float) ($this->calculatorSalary ?? 0);
        $year = (int) ($this->calculatorTaxYear ?: now()->year);
        $monthlyTax = $monthly > 0 ? PayrollCalculationService::getTaxAmount($monthly, $year) : 0.0;
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
        $query = Tax::query()
            ->when((int) $this->taxYear > 0, fn ($q) => $q->where('tax_year', (int) $this->taxYear));

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
