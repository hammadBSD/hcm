<?php

namespace App\Livewire\Employees;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\EmployeeSuggestion;
use App\Models\EmployeeSuggestionStatusHistory;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class Suggestions extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public $filterType = '';
    public $filterStatus = '';
    public $filterMonth = '';

    // Status change flyout
    public $showStatusFlyout = false;
    public $selectedSuggestionId = null;
    public $selectedSuggestion = null;
    public $newStatus = '';
    public $statusNotes = '';

    public function updatingFilterType()
    {
        $this->resetPage();
    }

    public function updatingFilterStatus()
    {
        $this->resetPage();
    }

    public function updatingFilterMonth()
    {
        $this->resetPage();
    }

    public function openStatusFlyout($suggestionId)
    {
        $this->selectedSuggestionId = $suggestionId;
        $this->selectedSuggestion = EmployeeSuggestion::with(['employee.user', 'statusHistory.changedBy'])->find($suggestionId);
        $this->newStatus = $this->selectedSuggestion->status;
        $this->statusNotes = '';
        $this->showStatusFlyout = true;
    }

    public function closeStatusFlyout()
    {
        $this->showStatusFlyout = false;
        $this->selectedSuggestionId = null;
        $this->selectedSuggestion = null;
        $this->newStatus = '';
        $this->statusNotes = '';
        $this->resetErrorBag();
    }

    public function updateStatus()
    {
        $this->validate([
            'newStatus' => 'required|in:pending,in_progress,resolved,dismissed',
            'statusNotes' => 'nullable|string|max:1000',
        ]);

        $suggestion = EmployeeSuggestion::find($this->selectedSuggestionId);
        
        if (!$suggestion) {
            session()->flash('error', 'Suggestion not found.');
            return;
        }

        $oldStatus = $suggestion->status;
        
        // Only update if status changed
        if ($oldStatus !== $this->newStatus) {
            // Update the suggestion status
            $suggestion->status = $this->newStatus;
            
            // If status is resolved or dismissed, also update admin_response
            if (in_array($this->newStatus, ['resolved', 'dismissed']) && !$suggestion->admin_response) {
                $suggestion->admin_response = $this->statusNotes;
                $suggestion->responded_by = Auth::id();
                $suggestion->responded_at = now();
            } elseif ($this->statusNotes && $this->newStatus === 'resolved') {
                // Update response if notes provided
                $suggestion->admin_response = $this->statusNotes;
                $suggestion->responded_by = Auth::id();
                $suggestion->responded_at = now();
            }
            
            $suggestion->save();

            // Create status history record
            EmployeeSuggestionStatusHistory::create([
                'employee_suggestion_id' => $suggestion->id,
                'status' => $this->newStatus,
                'notes' => $this->statusNotes,
                'changed_by' => Auth::id(),
            ]);
        } elseif ($this->statusNotes) {
            // If status didn't change but notes were provided, still create history
            EmployeeSuggestionStatusHistory::create([
                'employee_suggestion_id' => $suggestion->id,
                'status' => $this->newStatus,
                'notes' => $this->statusNotes,
                'changed_by' => Auth::id(),
            ]);
        }

        session()->flash('success', 'Status updated successfully!');
        $this->closeStatusFlyout();
    }

    public function getAvailableMonths()
    {
        $months = [];
        $current = Carbon::now();
        
        // Add current month
        $months[] = [
            'value' => $current->format('Y-m'),
            'label' => $current->format('F Y') . ' (Current)',
        ];
        
        // Add previous 11 months
        for ($i = 1; $i <= 11; $i++) {
            $date = $current->copy()->subMonths($i);
            $months[] = [
                'value' => $date->format('Y-m'),
                'label' => $date->format('F Y'),
            ];
        }
        
        return $months;
    }

    public function render()
    {
        $user = Auth::user();
        $employee = \App\Models\Employee::where('user_id', $user->id)->first();

        $query = EmployeeSuggestion::with(['employee.user', 'respondedBy', 'statusHistory.changedBy'])
            ->orderBy('created_at', 'desc');

        // If user is not admin, only show their own suggestions/complaints
        if (!$user->hasRole('Super Admin') && !$user->can('employees.manage.suggestions')) {
            if ($employee) {
                $query->where('employee_id', $employee->id);
            } else {
                $query->whereRaw('1 = 0'); // No results if no employee record
            }
        }

        // Apply filters
        if ($this->filterType) {
            $query->where('type', $this->filterType);
        }

        if ($this->filterStatus) {
            $query->where('status', $this->filterStatus);
        }

        // Filter by month
        if ($this->filterMonth) {
            $query->whereYear('created_at', Carbon::parse($this->filterMonth)->year)
                  ->whereMonth('created_at', Carbon::parse($this->filterMonth)->month);
        }

        $suggestions = $query->paginate(15);

        return view('livewire.employees.suggestions', [
            'suggestions' => $suggestions,
            'availableMonths' => $this->getAvailableMonths(),
        ])->layout('components.layouts.app');
    }
}
