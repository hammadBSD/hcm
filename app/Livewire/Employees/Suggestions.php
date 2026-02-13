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

    public $filterPriority = '';
    public $filterStatus = '';
    public $filterMonth = '';

    // Status change flyout
    public $showStatusFlyout = false;
    public $selectedSuggestionId = null;
    public $selectedSuggestion = null;
    public $newStatus = '';
    public $statusNotes = '';
    // Edit complaint flyout (separate from status flyout)
    public $showEditFlyout = false;
    public $editMessage = '';
    public $editPriority = '';
    public $editDepartmentId = '';

    public function updatingFilterPriority()
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
        $this->selectedSuggestion = EmployeeSuggestion::with(['employee.user', 'statusHistory.changedBy', 'department'])->find($suggestionId);
        $this->newStatus = $this->selectedSuggestion->status;
        $this->statusNotes = '';
        $this->showEditFlyout = false;
        $this->showStatusFlyout = true;
    }

    public function closeStatusFlyout()
    {
        $this->showStatusFlyout = false;
        $this->selectedSuggestionId = null;
        $this->selectedSuggestion = null;
        $this->newStatus = '';
        $this->statusNotes = '';
        $this->showEditFlyout = false;
        $this->resetEditForm();
        $this->resetErrorBag();
    }

    public function startEdit(): void
    {
        if (!$this->selectedSuggestion) {
            return;
        }
        $s = $this->selectedSuggestion;
        $this->editMessage = $s->message ?? '';
        $this->editPriority = $s->priority ?? 'medium';
        $this->editDepartmentId = $s->department_id ? (string) $s->department_id : '';
        $this->showStatusFlyout = false;
        $this->showEditFlyout = true;
    }

    protected function resetEditForm(): void
    {
        $this->editMessage = '';
        $this->editPriority = 'medium';
        $this->editDepartmentId = '';
    }

    public function closeEditFlyout(): void
    {
        $this->showEditFlyout = false;
        $this->resetEditForm();
        $this->resetErrorBag();
    }

    public function saveEditComplaint(): void
    {
        $user = Auth::user();
        if (!$user->can('complaints.edit') && !$user->can('employees.manage.suggestions')) {
            session()->flash('error', __('You are not allowed to edit this complaint.'));
            return;
        }
        $this->validate([
            'editMessage' => 'required|string|max:5000',
            'editPriority' => 'required|in:low,medium,high,urgent',
            'editDepartmentId' => 'nullable|exists:departments,id',
        ]);
        $suggestion = EmployeeSuggestion::find($this->selectedSuggestionId);
        if ($suggestion) {
            $suggestion->message = $this->editMessage;
            $suggestion->priority = $this->editPriority;
            $suggestion->department_id = $this->editDepartmentId ?: null;
            $suggestion->save();
            $this->selectedSuggestion = $suggestion->load(['employee.user', 'statusHistory.changedBy', 'department']);
            $this->showEditFlyout = false;
            $this->resetEditForm();
            session()->flash('success', __('Complaint updated.'));
        }
    }

    public function deleteSuggestion(): void
    {
        $user = Auth::user();
        if (!$user->can('complaints.delete') && !$user->can('employees.manage.suggestions')) {
            session()->flash('error', __('You are not allowed to delete this complaint.'));
            return;
        }
        $suggestion = EmployeeSuggestion::find($this->selectedSuggestionId);
        if ($suggestion) {
            $suggestion->delete();
            session()->flash('success', __('Suggestion/complaint deleted.'));
            $this->closeStatusFlyout();
        }
    }

    public function updateStatus()
    {
        $user = Auth::user();
        $canUpdate = $user->can('complaints.edit') || $user->can('employees.manage.suggestions')
            || $user->can('complaints.resolve') || $user->can('complaints.acknowledge_resolution');
        if (!$canUpdate) {
            session()->flash('error', __('You are not allowed to update status.'));
            return;
        }

        $this->validate([
            'newStatus' => 'required|in:pending,in_progress,dismissed',
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
            
            // If status is dismissed, also update admin_response
            if ($this->newStatus === 'dismissed' && !$suggestion->admin_response) {
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

    public function resolverResolve(): void
    {
        $suggestion = EmployeeSuggestion::with('department')->find($this->selectedSuggestionId);
        if (!$suggestion) {
            session()->flash('error', __('Suggestion not found.'));
            return;
        }

        $user = Auth::user();
        $employee = \App\Models\Employee::where('user_id', $user->id)->first();
        $canResolveAny = $user->can('complaints.resolve');
        $isDepartmentResolver = $employee && $suggestion->department_id && (int) $suggestion->department_id === (int) $employee->department_id && $suggestion->employee_id !== $employee->id;

        if (!$canResolveAny && !$isDepartmentResolver) {
            session()->flash('error', __('You are not allowed to resolve this complaint.'));
            return;
        }

        if (!$canResolveAny && $suggestion->employee_id === $employee->id) {
            session()->flash('error', __('As the lodger you cannot use this button. Use "I acknowledge resolution" instead.'));
            return;
        }

        if ($suggestion->status === 'resolved') {
            session()->flash('info', __('Already resolved.'));
            return;
        }

        $suggestion->status = 'resolved';
        $suggestion->responded_by = Auth::id();
        $suggestion->responded_at = now();
        $suggestion->admin_response = $suggestion->admin_response ?: __('Resolved by department.');
        $suggestion->save();

        EmployeeSuggestionStatusHistory::create([
            'employee_suggestion_id' => $suggestion->id,
            'status' => 'resolved',
            'notes' => __('Marked resolved by department.'),
            'changed_by' => Auth::id(),
        ]);

        $this->selectedSuggestion = $suggestion->load(['employee.user', 'statusHistory.changedBy', 'department']);
        session()->flash('success', __('Complaint marked as resolved.'));
    }

    public function lodgerAcknowledge(): void
    {
        $suggestion = EmployeeSuggestion::find($this->selectedSuggestionId);
        if (!$suggestion) {
            session()->flash('error', __('Suggestion not found.'));
            return;
        }

        $user = Auth::user();
        $employee = \App\Models\Employee::where('user_id', $user->id)->first();
        $isLodger = $employee && (int) $suggestion->employee_id === (int) $employee->id;
        $canAcknowledgeAny = $user->can('complaints.acknowledge_resolution');

        if (!$canAcknowledgeAny && !$isLodger) {
            session()->flash('error', __('Only the person who lodged the complaint can acknowledge resolution.'));
            return;
        }

        $suggestion->lodger_acknowledged_at = now();
        $suggestion->save();

        EmployeeSuggestionStatusHistory::create([
            'employee_suggestion_id' => $suggestion->id,
            'status' => 'lodger_acknowledged',
            'notes' => __('Lodger acknowledged resolution.'),
            'changed_by' => Auth::id(),
        ]);

        $this->selectedSuggestion = $suggestion->load(['employee.user', 'statusHistory.changedBy', 'department']);
        session()->flash('success', __('You have acknowledged resolution.'));
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

        $query = EmployeeSuggestion::with(['employee.user', 'respondedBy', 'statusHistory.changedBy', 'department'])
            ->orderBy('created_at', 'desc');

        // Visibility: Super Admin / view.all = all; view.own_department = own + department's; view.self = own only
        if ($user->hasRole('Super Admin') || $user->can('complaints.view.all')) {
            // see all
        } elseif ($user->can('complaints.view.own_department') && $employee) {
            $query->where(function ($q) use ($employee) {
                $q->where('employee_id', $employee->id)
                    ->orWhere('department_id', $employee->department_id);
            });
        } elseif ($user->can('complaints.view.self') && $employee) {
            $query->where('employee_id', $employee->id);
        } else {
            // Legacy: employees.manage.suggestions = own + department
            if ($user->can('employees.manage.suggestions') && $employee) {
                $query->where(function ($q) use ($employee) {
                    $q->where('employee_id', $employee->id)
                        ->orWhere('department_id', $employee->department_id);
                });
            } elseif ($employee) {
                $query->where('employee_id', $employee->id);
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        // Apply filters
        if ($this->filterPriority) {
            $query->where('priority', $this->filterPriority);
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

        $currentEmployee = $employee;
        $isLodger = false;
        $isResolver = false;
        if ($this->selectedSuggestion && $currentEmployee) {
            $isLodger = (int) $this->selectedSuggestion->employee_id === (int) $currentEmployee->id;
            $isResolver = !$isLodger && $this->selectedSuggestion->department_id && (int) $this->selectedSuggestion->department_id === (int) $currentEmployee->department_id;
        }
        $canShowEditButton = $user->can('complaints.edit');
        $canChangeStatus = $user->can('complaints.resolve') || $user->can('complaints.acknowledge_resolution');
        $canEdit = $user->can('complaints.edit') || $user->can('employees.manage.suggestions');
        $canDelete = $user->can('complaints.delete') || $user->can('employees.manage.suggestions');
        $canResolveAny = $user->can('complaints.resolve');
        $canAcknowledgeAny = $user->can('complaints.acknowledge_resolution');

        $departments = \App\Models\Department::where('status', 'active')->orderBy('title')->get();

        return view('livewire.employees.suggestions', [
            'suggestions' => $suggestions,
            'availableMonths' => $this->getAvailableMonths(),
            'currentEmployee' => $currentEmployee,
            'isLodger' => $isLodger,
            'isResolver' => $isResolver,
            'canShowEditButton' => $canShowEditButton,
            'canChangeStatus' => $canChangeStatus,
            'canEdit' => $canEdit,
            'canDelete' => $canDelete,
            'canResolveAny' => $canResolveAny,
            'canAcknowledgeAny' => $canAcknowledgeAny,
            'departments' => $departments,
        ])->layout('components.layouts.app');
    }
}
