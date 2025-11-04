<?php

namespace App\Livewire\SystemManagement\OrganizationSetting\Country;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Country;

class Index extends Component
{
    use WithPagination;

    public $sortBy = 'name';
    public $sortDirection = 'asc';
    public $search = '';
    
    // Add Country Flyout Properties
    public $showAddCountryFlyout = false;
    public $editingId = null;
    public $countryName = '';
    public $countryCode = '';
    public $phoneCode = '';
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

    public function createCountry()
    {
        $this->resetForm();
        $this->showAddCountryFlyout = true;
    }
    
    public function closeAddCountryFlyout()
    {
        $this->showAddCountryFlyout = false;
        $this->resetForm();
    }
    
    public function submitCountry()
    {
        $this->validate([
            'countryName' => 'required|string|max:255',
            'countryCode' => 'required|string|max:3|unique:countries,code,' . $this->editingId,
            'phoneCode' => 'nullable|string|max:10',
            'status' => 'required|in:active,inactive',
        ]);

        if ($this->editingId) {
            $country = Country::findOrFail($this->editingId);
            $country->update([
                'name' => $this->countryName,
                'code' => strtoupper($this->countryCode),
                'phone_code' => $this->phoneCode,
                'status' => $this->status,
            ]);
            session()->flash('message', 'Country updated successfully!');
        } else {
            Country::create([
                'name' => $this->countryName,
                'code' => strtoupper($this->countryCode),
                'phone_code' => $this->phoneCode,
                'status' => $this->status,
            ]);
            session()->flash('message', 'Country created successfully!');
        }
        
        $this->closeAddCountryFlyout();
    }
    
    private function resetForm()
    {
        $this->editingId = null;
        $this->countryName = '';
        $this->countryCode = '';
        $this->phoneCode = '';
        $this->status = 'active';
    }

    public function editCountry($id)
    {
        $country = Country::findOrFail($id);
        $this->editingId = $country->id;
        $this->countryName = $country->name;
        $this->countryCode = $country->code;
        $this->phoneCode = $country->phone_code ?? '';
        $this->status = $country->status;
        $this->showAddCountryFlyout = true;
    }

    public function deleteCountry($id)
    {
        $country = Country::findOrFail($id);
        $country->delete();
        session()->flash('message', 'Country deleted successfully!');
    }

    public function render()
    {
        $query = Country::query();

        if (!empty($this->search)) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('code', 'like', '%' . $this->search . '%')
                  ->orWhere('phone_code', 'like', '%' . $this->search . '%');
            });
        }

        $query->orderBy($this->sortBy, $this->sortDirection);
        $countries = $query->paginate(10);

        return view('livewire.system-management.organization-setting.country.index', [
            'countries' => $countries,
        ])
            ->layout('components.layouts.app');
    }
}
