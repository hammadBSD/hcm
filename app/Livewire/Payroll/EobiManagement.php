<?php

namespace App\Livewire\Payroll;

use App\Models\PayrollEobiYearlySetting;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class EobiManagement extends Component
{
    use WithPagination;

    public $search = '';

    public $selectedFromDate = '';

    public $sortBy = 'date_from';

    public $sortDirection = 'desc';

    public $showAddModal = false;

    public $showViewModal = false;

    public $showEditModal = false;

    public $selectedId = null;

    public $formDateFrom = '';

    public $formDateTo = '';

    public $formMonthlyAmount = '';

    public $editDateFrom = '';

    public $editDateTo = '';

    public $editMonthlyAmount = '';

    public function mount(): void
    {
        $user = Auth::user();
        if (!$user || !$user->can('payroll.export')) {
            abort(403);
        }
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedSelectedFromDate(): void
    {
        $this->resetPage();
    }

    public function sort(string $field): void
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';

            return;
        }
        $this->sortBy = $field;
        $this->sortDirection = 'asc';
    }

    public function openAddModal(): void
    {
        $this->formDateFrom = now()->startOfYear()->format('Y-m-d');
        $this->formDateTo = '';
        $this->formMonthlyAmount = '';
        $this->showAddModal = true;
    }

    public function closeAddModal(): void
    {
        $this->showAddModal = false;
    }

    public function addRecord(): void
    {
        $dateFrom = trim((string) $this->formDateFrom);
        $dateTo = trim((string) $this->formDateTo);
        $amount = trim((string) $this->formMonthlyAmount);

        if ($dateFrom === '') {
            session()->flash('error', __('Please select date from.'));

            return;
        }
        if ($dateTo !== '' && $dateTo < $dateFrom) {
            session()->flash('error', __('Date to must be greater than or equal to date from.'));

            return;
        }
        if ($amount === '' || !is_numeric($amount) || (float) $amount < 0) {
            session()->flash('error', __('Please enter a valid monthly amount.'));

            return;
        }

        PayrollEobiYearlySetting::create([
            'year' => (int) date('Y', strtotime($dateFrom)),
            'date_from' => $dateFrom,
            'date_to' => $dateTo !== '' ? $dateTo : null,
            'monthly_amount' => round((float) $amount, 2),
            'created_by' => Auth::id(),
        ]);

        $this->closeAddModal();
        session()->flash('message', __('EOBI setting saved.'));
    }

    public function viewRecord(int $id): void
    {
        $this->selectedId = $id;
        $this->showViewModal = true;
    }

    public function closeViewModal(): void
    {
        $this->showViewModal = false;
        $this->selectedId = null;
    }

    public function editRecord(int $id): void
    {
        $record = PayrollEobiYearlySetting::find($id);
        if (!$record) {
            session()->flash('error', __('Record not found.'));

            return;
        }
        $this->selectedId = $record->id;
        $this->editDateFrom = $record->date_from ? $record->date_from->format('Y-m-d') : '';
        $this->editDateTo = $record->date_to ? $record->date_to->format('Y-m-d') : '';
        $this->editMonthlyAmount = (string) ((float) $record->monthly_amount);
        $this->showEditModal = true;
    }

    public function closeEditModal(): void
    {
        $this->showEditModal = false;
        $this->selectedId = null;
    }

    public function updateRecord(): void
    {
        $record = PayrollEobiYearlySetting::find((int) $this->selectedId);
        if (!$record) {
            session()->flash('error', __('Record not found.'));

            return;
        }

        $dateFrom = trim((string) $this->editDateFrom);
        $dateTo = trim((string) $this->editDateTo);
        $amount = trim((string) $this->editMonthlyAmount);

        if ($dateFrom === '') {
            session()->flash('error', __('Please select date from.'));

            return;
        }
        if ($dateTo !== '' && $dateTo < $dateFrom) {
            session()->flash('error', __('Date to must be greater than or equal to date from.'));

            return;
        }
        if ($amount === '' || !is_numeric($amount) || (float) $amount < 0) {
            session()->flash('error', __('Please enter a valid monthly amount.'));

            return;
        }

        $record->update([
            'year' => (int) date('Y', strtotime($dateFrom)),
            'date_from' => $dateFrom,
            'date_to' => $dateTo !== '' ? $dateTo : null,
            'monthly_amount' => round((float) $amount, 2),
        ]);

        $this->closeEditModal();
        session()->flash('message', __('EOBI setting updated.'));
    }

    public function deleteRecord(int $id): void
    {
        $record = PayrollEobiYearlySetting::find($id);
        if (!$record) {
            session()->flash('error', __('Record not found.'));

            return;
        }
        $record->delete();
        session()->flash('message', __('EOBI setting deleted.'));
    }

    public function getSelectedRecordProperty(): ?PayrollEobiYearlySetting
    {
        if (!$this->selectedId) {
            return null;
        }

        return PayrollEobiYearlySetting::find($this->selectedId);
    }

    public function render()
    {
        $query = PayrollEobiYearlySetting::query()
            ->when($this->search !== '', function ($q) {
                $term = trim($this->search);
                $q->where(function ($inner) use ($term) {
                    $inner->where('date_from', 'like', '%' . $term . '%')
                        ->orWhere('date_to', 'like', '%' . $term . '%');
                });
            })
            ->when($this->selectedFromDate !== '', function ($q) {
                $q->whereDate('date_from', '>=', $this->selectedFromDate);
            });

        $allowed = ['date_from', 'date_to', 'monthly_amount', 'created_at'];
        $field = in_array($this->sortBy, $allowed, true) ? $this->sortBy : 'date_from';
        $query->orderBy($field, $this->sortDirection === 'asc' ? 'asc' : 'desc');

        $records = $query->paginate(15);

        return view('livewire.payroll.eobi-management', [
            'records' => $records,
        ])->layout('components.layouts.app');
    }
}
