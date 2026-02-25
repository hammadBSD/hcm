<?php

namespace App\Livewire\SystemManagement\OrganizationSetting\Brand;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Brand;

class Index extends Component
{
    use WithPagination;

    public $sortBy = 'name';
    public $sortDirection = 'asc';
    public $search = '';

    public $showAddFlyout = false;
    public $editingId = null;
    public $name = '';
    public $code = '';
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

    public function create()
    {
        $this->resetForm();
        $this->showAddFlyout = true;
    }

    public function closeFlyout()
    {
        $this->showAddFlyout = false;
        $this->resetForm();
    }

    public function submit()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:brands,code,' . $this->editingId,
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        if ($this->editingId) {
            $item = Brand::findOrFail($this->editingId);
            $item->update([
                'name' => $this->name,
                'code' => $this->code ?: null,
                'description' => $this->description,
                'status' => $this->status,
            ]);
            session()->flash('message', __('Brand updated successfully.'));
        } else {
            Brand::create([
                'name' => $this->name,
                'code' => $this->code ?: null,
                'description' => $this->description,
                'status' => $this->status,
            ]);
            session()->flash('message', __('Brand created successfully.'));
        }

        $this->closeFlyout();
    }

    private function resetForm()
    {
        $this->editingId = null;
        $this->name = '';
        $this->code = '';
        $this->description = '';
        $this->status = 'active';
    }

    public function edit($id)
    {
        $item = Brand::findOrFail($id);
        $this->editingId = $item->id;
        $this->name = $item->name;
        $this->code = $item->code ?? '';
        $this->description = $item->description ?? '';
        $this->status = $item->status;
        $this->showAddFlyout = true;
    }

    public function delete($id)
    {
        Brand::findOrFail($id)->delete();
        session()->flash('message', __('Brand deleted successfully.'));
    }

    public function render()
    {
        $query = Brand::query();

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('code', 'like', '%' . $this->search . '%')
                    ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        $brands = $query->orderBy($this->sortBy, $this->sortDirection)->paginate(10);

        return view('livewire.system-management.organization-setting.brand.index', [
            'brands' => $brands,
        ])->layout('components.layouts.app');
    }
}
