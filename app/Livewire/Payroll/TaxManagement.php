<?php

namespace App\Livewire\Payroll;

use App\Models\Tax;
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
    public $sortBy = '';
    public $sortDirection = 'asc';

    /** Add Tax Record form */
    public $addTaxYear = '';
    public $salaryFrom = '';
    public $salaryTo = '';
    public $tax = '';
    public $exemptedTaxAmount = '';
    public $additionalTaxAmount = '';

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

    public function viewTaxRecord($id)
    {
        // Placeholder: could open a detail modal or redirect
        session()->flash('message', __('View not implemented for this record.'));
    }

    public function downloadTaxRecord($id)
    {
        // Placeholder: could generate PDF
        session()->flash('message', __('Download not implemented for this record.'));
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
