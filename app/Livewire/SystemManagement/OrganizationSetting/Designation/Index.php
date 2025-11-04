<?php

namespace App\Livewire\SystemManagement\OrganizationSetting\Designation;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Designation;

class Index extends Component
{
    use WithPagination;

    public $sortBy = 'name';
    public $sortDirection = 'asc';
    public $search = '';
    
    // Add Designation Flyout Properties
    public $showAddDesignationFlyout = false;
    public $editingId = null;
    public $designationName = '';
    public $designationCode = '';
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

    public function createDesignation()
    {
        $this->resetForm();
        $this->showAddDesignationFlyout = true;
    }
    
    public function closeAddDesignationFlyout()
    {
        $this->showAddDesignationFlyout = false;
        $this->resetForm();
    }
    
    public function submitDesignation()
    {
        $this->validate([
            'designationName' => 'required|string|max:255',
            'designationCode' => 'nullable|string|max:50|unique:designations,code,' . $this->editingId,
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        if ($this->editingId) {
            $designation = Designation::findOrFail($this->editingId);
            $designation->update([
                'name' => $this->designationName,
                'code' => $this->designationCode ?: null,
                'description' => $this->description,
                'status' => $this->status,
            ]);
            session()->flash('message', 'Designation updated successfully!');
        } else {
            Designation::create([
                'name' => $this->designationName,
                'code' => $this->designationCode ?: null,
                'description' => $this->description,
                'status' => $this->status,
            ]);
            session()->flash('message', 'Designation created successfully!');
        }
        
        $this->closeAddDesignationFlyout();
    }
    
    private function resetForm()
    {
        $this->editingId = null;
        $this->designationName = '';
        $this->designationCode = '';
        $this->description = '';
        $this->status = 'active';
    }

    public function editDesignation($id)
    {
        $designation = Designation::findOrFail($id);
        $this->editingId = $designation->id;
        $this->designationName = $designation->name;
        $this->designationCode = $designation->code ?? '';
        $this->description = $designation->description ?? '';
        $this->status = $designation->status;
        $this->showAddDesignationFlyout = true;
    }

    public function deleteDesignation($id)
    {
        $designation = Designation::findOrFail($id);
        $designation->delete();
        session()->flash('message', 'Designation deleted successfully!');
    }

    public function render()
    {
        $query = Designation::query();

        if (!empty($this->search)) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('code', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        $query->orderBy($this->sortBy, $this->sortDirection);
        $designations = $query->paginate(10);

        return view('livewire.system-management.organization-setting.designation.index', [
            'designations' => $designations,
        ])
            ->layout('components.layouts.app');
    }
}
