<?php

namespace App\Livewire\SystemManagement\OrganizationSetting\EmploymentType;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\EmploymentType;

class Index extends Component
{
    use WithPagination;

    public $sortBy = 'name';
    public $sortDirection = 'asc';
    public $search = '';
    
    // Add Employment Type Flyout Properties
    public $showAddEmploymentTypeFlyout = false;
    public $editingId = null;
    public $typeName = '';
    public $typeCode = '';
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

    public function createEmploymentType()
    {
        $this->resetForm();
        $this->showAddEmploymentTypeFlyout = true;
    }
    
    public function closeAddEmploymentTypeFlyout()
    {
        $this->showAddEmploymentTypeFlyout = false;
        $this->resetForm();
    }
    
    public function submitEmploymentType()
    {
        $this->validate([
            'typeName' => 'required|string|max:255',
            'typeCode' => 'nullable|string|max:50|unique:employment_types,code,' . $this->editingId,
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        if ($this->editingId) {
            // Update existing
            $employmentType = EmploymentType::findOrFail($this->editingId);
            $employmentType->update([
                'name' => $this->typeName,
                'code' => $this->typeCode ?: null,
                'description' => $this->description,
                'status' => $this->status,
            ]);
            session()->flash('message', 'Employment Type updated successfully!');
        } else {
            // Create new
            EmploymentType::create([
                'name' => $this->typeName,
                'code' => $this->typeCode ?: null,
                'description' => $this->description,
                'status' => $this->status,
            ]);
            session()->flash('message', 'Employment Type created successfully!');
        }
        
        $this->closeAddEmploymentTypeFlyout();
    }
    
    private function resetForm()
    {
        $this->editingId = null;
        $this->typeName = '';
        $this->typeCode = '';
        $this->description = '';
        $this->status = 'active';
    }

    public function editEmploymentType($id)
    {
        $employmentType = EmploymentType::findOrFail($id);
        $this->editingId = $employmentType->id;
        $this->typeName = $employmentType->name;
        $this->typeCode = $employmentType->code ?? '';
        $this->description = $employmentType->description ?? '';
        $this->status = $employmentType->status;
        $this->showAddEmploymentTypeFlyout = true;
    }

    public function deleteEmploymentType($id)
    {
        $employmentType = EmploymentType::findOrFail($id);
        $employmentType->delete();
        session()->flash('message', 'Employment Type deleted successfully!');
    }

    public function render()
    {
        $query = EmploymentType::query();

        // Apply search filter
        if (!empty($this->search)) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('code', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        // Apply sorting
        $query->orderBy($this->sortBy, $this->sortDirection);

        // Get paginated results
        $employmentTypes = $query->paginate(10);

        return view('livewire.system-management.organization-setting.employment-type.index', [
            'employmentTypes' => $employmentTypes,
        ])
            ->layout('components.layouts.app');
    }
}
