<?php

namespace App\Livewire\SystemManagement\AttendanceSettings\ShiftSchedule;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Shift;

class Index extends Component
{
    use WithPagination;

    public $sortBy = 'shift_name';
    public $sortDirection = 'asc';
    public $search = '';
    
    // Add Shift Flyout Properties
    public $showAddShiftFlyout = false;
    public $editingId = null;
    public $shiftName = '';
    public $timeFrom = '';
    public $timeTo = '';
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

    public function createShift()
    {
        $this->resetForm();
        $this->showAddShiftFlyout = true;
    }
    
    public function closeAddShiftFlyout()
    {
        $this->showAddShiftFlyout = false;
        $this->resetForm();
    }
    
    public function submitShift()
    {
        $this->validate([
            'shiftName' => 'required|string|max:255',
            'timeFrom' => 'required|date_format:H:i',
            'timeTo' => 'required|date_format:H:i',
            'status' => 'required|in:active,inactive',
        ]);

        $data = [
            'shift_name' => $this->shiftName,
            'time_from' => $this->timeFrom,
            'time_to' => $this->timeTo,
            'status' => $this->status,
        ];

        if ($this->editingId) {
            $shift = Shift::findOrFail($this->editingId);
            $shift->update($data);
            session()->flash('message', 'Shift updated successfully!');
        } else {
            Shift::create($data);
            session()->flash('message', 'Shift created successfully!');
        }
        
        $this->closeAddShiftFlyout();
    }
    
    private function resetForm()
    {
        $this->editingId = null;
        $this->shiftName = '';
        $this->timeFrom = '';
        $this->timeTo = '';
        $this->status = 'active';
    }

    public function editShift($id)
    {
        $shift = Shift::findOrFail($id);
        $this->editingId = $shift->id;
        $this->shiftName = $shift->shift_name;
        $this->timeFrom = $shift->time_from;
        $this->timeTo = $shift->time_to;
        $this->status = $shift->status;
        $this->showAddShiftFlyout = true;
    }

    public function deleteShift($id)
    {
        $shift = Shift::findOrFail($id);
        $shift->delete();
        session()->flash('message', 'Shift deleted successfully!');
    }

    public function render()
    {
        $query = Shift::withCount('employees');

        if (!empty($this->search)) {
            $query->where(function($q) {
                $q->where('shift_name', 'like', '%' . $this->search . '%')
                  ->orWhere('time_from', 'like', '%' . $this->search . '%')
                  ->orWhere('time_to', 'like', '%' . $this->search . '%');
            });
        }

        // Handle sorting
        $sortField = $this->sortBy;
        if ($sortField === 'count') {
            $query->orderBy('employees_count', $this->sortDirection);
        } else {
            $query->orderBy($sortField, $this->sortDirection);
        }

        $shifts = $query->paginate(10);

        return view('livewire.system-management.attendance-settings.shift-schedule.index', [
            'shifts' => $shifts,
        ])
            ->layout('components.layouts.app');
    }
}

