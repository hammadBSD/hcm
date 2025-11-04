<?php

namespace App\Livewire\SystemManagement\OrganizationSetting\EmploymentStatus;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\EmploymentStatus;

class Index extends Component
{
    use WithPagination;

    public $sortBy = 'name';
    public $sortDirection = 'asc';
    public $search = '';
    
    // Add Employment Status Flyout Properties
    public $showAddEmploymentStatusFlyout = false;
    public $editingId = null;
    public $statusName = '';
    public $statusCode = '';
    public $description = '';
    public $status = 'active';

    protected $paginationTheme = 'tailwind';

    public function updatingSearch()
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
        $this->resetPage();
    }

    public function createEmploymentStatus()
    {
        $this->resetForm();
        $this->showAddEmploymentStatusFlyout = true;
    }
    
    public function closeAddEmploymentStatusFlyout()
    {
        $this->showAddEmploymentStatusFlyout = false;
        $this->resetForm();
    }
    
    public function submitEmploymentStatus()
    {
        $this->validate([
            'statusName' => 'required|string|max:255',
            'statusCode' => 'nullable|string|max:50|unique:employment_statuses,code,' . $this->editingId,
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        if ($this->editingId) {
            $employmentStatus = EmploymentStatus::findOrFail($this->editingId);
            $employmentStatus->update([
                'name' => $this->statusName,
                'code' => $this->statusCode ?: null,
                'description' => $this->description,
                'status' => $this->status,
            ]);
            session()->flash('message', 'Employment Status updated successfully!');
        } else {
            EmploymentStatus::create([
                'name' => $this->statusName,
                'code' => $this->statusCode ?: null,
                'description' => $this->description,
                'status' => $this->status,
            ]);
            session()->flash('message', 'Employment Status created successfully!');
        }
        
        $this->closeAddEmploymentStatusFlyout();
    }
    
    private function resetForm()
    {
        $this->editingId = null;
        $this->statusName = '';
        $this->statusCode = '';
        $this->description = '';
        $this->status = 'active';
    }

    public function editEmploymentStatus($id)
    {
        $employmentStatus = EmploymentStatus::findOrFail($id);
        $this->editingId = $employmentStatus->id;
        $this->statusName = $employmentStatus->name;
        $this->statusCode = $employmentStatus->code ?? '';
        $this->description = $employmentStatus->description ?? '';
        $this->status = $employmentStatus->status;
        $this->showAddEmploymentStatusFlyout = true;
    }

    public function deleteEmploymentStatus($id)
    {
        $employmentStatus = EmploymentStatus::findOrFail($id);
        $employmentStatus->delete();
        session()->flash('message', 'Employment Status deleted successfully!');
    }

    public function render()
    {
        $query = EmploymentStatus::query();

        if (!empty($this->search)) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('code', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        $query->orderBy($this->sortBy, $this->sortDirection);
        $employmentStatuses = $query->paginate(10);

        return view('livewire.system-management.organization-setting.employment-status.index', [
            'employmentStatuses' => $employmentStatuses,
        ])
            ->layout('components.layouts.app');
    }
}
