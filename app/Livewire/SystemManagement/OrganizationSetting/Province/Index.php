<?php

namespace App\Livewire\SystemManagement\OrganizationSetting\Province;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Province;
use App\Models\Country;

class Index extends Component
{
    use WithPagination;

    public $sortBy = 'name';
    public $sortDirection = 'asc';
    public $search = '';
    public $countryFilter = '';
    
    // Add Province Flyout Properties
    public $showAddProvinceFlyout = false;
    public $editingId = null;
    public $provinceName = '';
    public $provinceCode = '';
    public $countryId = '';
    public $status = 'active';

    protected $paginationTheme = 'tailwind';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingCountryFilter()
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

    public function createProvince()
    {
        $this->resetForm();
        $this->showAddProvinceFlyout = true;
    }
    
    public function closeAddProvinceFlyout()
    {
        $this->showAddProvinceFlyout = false;
        $this->resetForm();
    }
    
    public function submitProvince()
    {
        $this->validate([
            'provinceName' => 'required|string|max:255',
            'provinceCode' => 'nullable|string|max:50',
            'countryId' => 'required|exists:countries,id',
            'status' => 'required|in:active,inactive',
        ]);

        if ($this->editingId) {
            $province = Province::findOrFail($this->editingId);
            $province->update([
                'name' => $this->provinceName,
                'code' => $this->provinceCode ?: null,
                'country_id' => $this->countryId,
                'status' => $this->status,
            ]);
            session()->flash('message', 'Province updated successfully!');
        } else {
            Province::create([
                'name' => $this->provinceName,
                'code' => $this->provinceCode ?: null,
                'country_id' => $this->countryId,
                'status' => $this->status,
            ]);
            session()->flash('message', 'Province created successfully!');
        }
        
        $this->closeAddProvinceFlyout();
    }
    
    private function resetForm()
    {
        $this->editingId = null;
        $this->provinceName = '';
        $this->provinceCode = '';
        $this->countryId = '';
        $this->status = 'active';
    }

    public function editProvince($id)
    {
        $province = Province::findOrFail($id);
        $this->editingId = $province->id;
        $this->provinceName = $province->name;
        $this->provinceCode = $province->code ?? '';
        $this->countryId = $province->country_id;
        $this->status = $province->status;
        $this->showAddProvinceFlyout = true;
    }

    public function deleteProvince($id)
    {
        $province = Province::findOrFail($id);
        $province->delete();
        session()->flash('message', 'Province deleted successfully!');
    }

    public function render()
    {
        $query = Province::with('country');

        if (!empty($this->search)) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('code', 'like', '%' . $this->search . '%');
            });
        }

        if (!empty($this->countryFilter)) {
            $query->where('country_id', $this->countryFilter);
        }

        $query->orderBy($this->sortBy, $this->sortDirection);
        $provinces = $query->paginate(10);

        $countries = Country::where('status', 'active')->orderBy('name')->get();

        return view('livewire.system-management.organization-setting.province.index', [
            'provinces' => $provinces,
            'countries' => $countries,
        ])
            ->layout('components.layouts.app');
    }
}
