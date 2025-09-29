<?php

namespace App\Livewire\Employees;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $filterDepartment = '';
    public $filterStatus = '';
    public $perPage = 10;

    protected $queryString = [
        'search' => ['except' => ''],
        'filterDepartment' => ['except' => ''],
        'filterStatus' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterDepartment()
    {
        $this->resetPage();
    }

    public function updatingFilterStatus()
    {
        $this->resetPage();
    }

    public function render()
    {
        $employees = User::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%');
                });
            })
            ->with('roles')
            ->paginate($this->perPage);

        return view('livewire.employees.index', [
            'employees' => $employees,
            'departments' => $this->getDepartments(),
            'statuses' => $this->getStatuses(),
        ])
        ->layout('layouts.employees');
    }

    private function getDepartments()
    {
        // This will be replaced with actual department data later
        return [
            '' => 'All Departments',
            'hr' => 'Human Resources',
            'it' => 'Information Technology',
            'finance' => 'Finance',
            'marketing' => 'Marketing',
            'sales' => 'Sales',
        ];
    }

    private function getStatuses()
    {
        return [
            '' => 'All Status',
            'active' => 'Active',
            'inactive' => 'Inactive',
            'pending' => 'Pending',
        ];
    }
}