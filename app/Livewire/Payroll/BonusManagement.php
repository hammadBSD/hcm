<?php

namespace App\Livewire\Payroll;

use App\Models\Employee;
use Livewire\Component;
use Livewire\WithPagination;

class BonusManagement extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedDepartment = '';
    public $bonusType = '';
    public $showAddBonusModal = false;
    public $sortBy = '';
    public $sortDirection = 'asc';

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedSelectedDepartment()
    {
        $this->resetPage();
    }

    public function updatedBonusType()
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

    public function openAddBonusModal()
    {
        $this->showAddBonusModal = true;
    }

    public function closeAddBonusModal()
    {
        $this->showAddBonusModal = false;
    }

    public function addBonus()
    {
        // This would handle adding bonus to employees
        $this->closeAddBonusModal();
        session()->flash('message', 'Bonus added successfully!');
    }

    public function render()
    {
        $employees = Employee::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('first_name', 'like', '%' . $this->search . '%')
                      ->orWhere('last_name', 'like', '%' . $this->search . '%')
                      ->orWhere('employee_code', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->selectedDepartment, function ($query) {
                $query->where('department', $this->selectedDepartment);
            })
            ->where('status', 'active')
            ->paginate(10);

        $departments = Employee::distinct()->pluck('department')->filter();
        $bonusTypes = ['Performance', 'Annual', 'Festival', 'Project', 'Other'];

        return view('livewire.payroll.bonus-management', [
            'employees' => $employees,
            'departments' => $departments,
            'bonusTypes' => $bonusTypes
        ])->layout('components.layouts.app');
    }
}
