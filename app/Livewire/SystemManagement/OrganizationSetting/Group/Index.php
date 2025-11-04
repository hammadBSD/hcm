<?php

namespace App\Livewire\SystemManagement\OrganizationSetting\Group;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Group;

class Index extends Component
{
    use WithPagination;

    public $sortBy = 'name';
    public $sortDirection = 'asc';
    public $search = '';
    
    // Add Group Flyout Properties
    public $showAddGroupFlyout = false;
    public $editingId = null;
    public $groupName = '';
    public $groupCode = '';
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

    public function createGroup()
    {
        $this->resetForm();
        $this->showAddGroupFlyout = true;
    }
    
    public function closeAddGroupFlyout()
    {
        $this->showAddGroupFlyout = false;
        $this->resetForm();
    }
    
    public function submitGroup()
    {
        $this->validate([
            'groupName' => 'required|string|max:255',
            'groupCode' => 'nullable|string|max:50|unique:groups,code,' . $this->editingId,
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        if ($this->editingId) {
            $group = Group::findOrFail($this->editingId);
            $group->update([
                'name' => $this->groupName,
                'code' => $this->groupCode ?: null,
                'description' => $this->description,
                'status' => $this->status,
            ]);
            session()->flash('message', 'Group updated successfully!');
        } else {
            Group::create([
                'name' => $this->groupName,
                'code' => $this->groupCode ?: null,
                'description' => $this->description,
                'status' => $this->status,
            ]);
            session()->flash('message', 'Group created successfully!');
        }
        
        $this->closeAddGroupFlyout();
    }
    
    private function resetForm()
    {
        $this->editingId = null;
        $this->groupName = '';
        $this->groupCode = '';
        $this->description = '';
        $this->status = 'active';
    }

    public function editGroup($id)
    {
        $group = Group::findOrFail($id);
        $this->editingId = $group->id;
        $this->groupName = $group->name;
        $this->groupCode = $group->code ?? '';
        $this->description = $group->description ?? '';
        $this->status = $group->status;
        $this->showAddGroupFlyout = true;
    }

    public function deleteGroup($id)
    {
        $group = Group::findOrFail($id);
        $group->delete();
        session()->flash('message', 'Group deleted successfully!');
    }

    public function render()
    {
        $query = Group::query();

        if (!empty($this->search)) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('code', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        $query->orderBy($this->sortBy, $this->sortDirection);
        $groups = $query->paginate(10);

        return view('livewire.system-management.organization-setting.group.index', [
            'groups' => $groups,
        ])
            ->layout('components.layouts.app');
    }
}
