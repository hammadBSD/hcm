<?php

namespace App\Livewire\Payroll;

use App\Models\Department;
use App\Models\Employee;
use App\Models\Loan;
use App\Models\LoanScenarioAction;
use App\Services\LoanScenarioService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class LoanManagement extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedDepartment = '';
    public $loanStatus = '';
    public $showAddLoanModal = false;
    public $showViewLoanModal = false;
    public $showApproveLoanModal = false;
    public $showRejectLoanModal = false;
    public $showEditLoanModal = false;
    public $showRepaymentScheduleModal = false;
    public $selectedLoanId = null;
    public $sortBy = '';
    public $sortDirection = 'asc';

    /** Add Loan form (flyout) */
    public $selectedEmployeeId = '';
    public $loanType = 'Personal';
    public $loanAmount = '';
    public $totalInstallments = '12';
    public $loanRequestDate = '';
    public $loanDescription = '';
    public $editEmployeeId = '';
    public $editLoanType = 'Personal';
    public $editLoanAmount = '';
    public $editTotalInstallments = '12';
    public $editLoanRequestDate = '';
    public $editLoanDescription = '';
    public $approvalDate = '';
    public $approvalRepaymentStartMonth = '';
    public $approvalComments = '';
    public $rejectDate = '';
    public $rejectComments = '';
    public $activeScheduleScenario = '';
    public $scenarioMonth = '';
    public $scenarioSetoffReason = '';
    public $scenarioPaybackAmount = '';
    public $scenarioRescheduleMonths = '';
    public $scenarioRescheduleInstallment = '';
    public $scenarioFreezeFromMonth = '';
    public $scenarioResumeMonth = '';
    public $scenarioTopupAmount = '';
    public $scenarioCustomPayAmount = '';
    public $scenarioCustomPayMethod = 'salary_deduction';

    public function mount()
    {
        $user = Auth::user();

        if (!$user || !$user->can('salary.edit')) {
            abort(403);
        }
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedSelectedDepartment()
    {
        $this->resetPage();
    }

    public function updatedLoanStatus()
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
    }

    public function openAddLoanModal()
    {
        $this->authorizeLoanRequest();
        $this->selectedEmployeeId = '';
        $this->loanType = 'Personal';
        $this->loanAmount = '';
        $this->totalInstallments = '12';
        $this->loanRequestDate = now()->format('Y-m-d');
        $this->loanDescription = '';
        $this->showAddLoanModal = true;
    }

    public function closeAddLoanModal()
    {
        $this->showAddLoanModal = false;
        $this->loanRequestDate = '';
    }

    public function editLoan($id): void
    {
        $this->authorizeLoanManagement();
        $loan = Loan::find($id);
        if (!$loan || !$loan->isPending()) {
            session()->flash('error', __('Only pending loans can be edited.'));
            return;
        }

        $this->selectedLoanId = (int) $loan->id;
        $this->editEmployeeId = (string) $loan->employee_id;
        $this->editLoanType = (string) $loan->loan_type;
        $this->editLoanAmount = (string) ((float) $loan->loan_amount);
        $this->editTotalInstallments = (string) ((int) $loan->total_installments);
        $this->editLoanRequestDate = $loan->loan_date ? $loan->loan_date->format('Y-m-d') : '';
        $this->editLoanDescription = (string) ($loan->description ?? '');
        $this->showEditLoanModal = true;
    }

    public function closeEditLoanModal(): void
    {
        $this->showEditLoanModal = false;
        $this->selectedLoanId = null;
        $this->editEmployeeId = '';
        $this->editLoanType = 'Personal';
        $this->editLoanAmount = '';
        $this->editTotalInstallments = '12';
        $this->editLoanRequestDate = '';
        $this->editLoanDescription = '';
    }

    public function updateLoan(): void
    {
        $this->authorizeLoanManagement();
        $loan = Loan::find($this->selectedLoanId);
        if (!$loan || !$loan->isPending()) {
            session()->flash('error', __('Only pending loans can be edited.'));
            $this->closeEditLoanModal();
            return;
        }

        $employeeId = (int) $this->editEmployeeId;
        $amount = (float) $this->editLoanAmount;
        $installments = (int) $this->editTotalInstallments;
        $loanRequestDate = trim((string) $this->editLoanRequestDate);

        if ($employeeId <= 0) {
            session()->flash('error', __('Please select an employee.'));
            return;
        }
        if ($amount <= 0) {
            session()->flash('error', __('Loan amount must be greater than zero.'));
            return;
        }
        if ($installments < 1) {
            session()->flash('error', __('Number of installments must be at least 1.'));
            return;
        }
        if ($loanRequestDate === '') {
            session()->flash('error', __('Please select a loan request date.'));
            return;
        }
        $installmentAmount = round($amount / $installments, 2);

        $loan->update([
            'employee_id' => $employeeId,
            'loan_type' => $this->editLoanType,
            'loan_amount' => $amount,
            'installment_amount' => $installmentAmount,
            'total_installments' => $installments,
            'remaining_installments' => $installments,
            'loan_date' => $loanRequestDate,
            'description' => trim((string) $this->editLoanDescription),
        ]);

        $this->closeEditLoanModal();
        session()->flash('message', __('Loan updated successfully.'));
    }

    public function deleteLoan($id): void
    {
        $this->authorizeLoanManagement();
        $loan = Loan::find($id);
        if (!$loan || !$loan->isPending()) {
            session()->flash('error', __('Only pending loans can be deleted.'));
            return;
        }

        $loan->delete();
        session()->flash('message', __('Loan deleted successfully.'));
    }

    public function deleteLoanAnyStatus($id): void
    {
        $this->authorizeLoanAnyDelete();
        $loan = Loan::find($id);
        if (!$loan) {
            session()->flash('error', __('Loan record not found.'));
            return;
        }

        $loan->delete();
        session()->flash('message', __('Loan deleted (all-status delete) successfully.'));
    }

    public function addLoan()
    {
        $this->authorizeLoanRequest();

        $employeeId = (int) $this->selectedEmployeeId;
        $amount = (float) $this->loanAmount;
        $installments = (int) $this->totalInstallments;
        $loanRequestDate = trim((string) $this->loanRequestDate);
        if ($employeeId <= 0) {
            session()->flash('error', __('Please select an employee.'));
            return;
        }
        if ($amount <= 0) {
            session()->flash('error', __('Loan amount must be greater than zero.'));
            return;
        }
        if ($installments < 1) {
            session()->flash('error', __('Number of installments must be at least 1.'));
            return;
        }
        if ($loanRequestDate === '') {
            session()->flash('error', __('Please select a loan request date.'));
            return;
        }
        $installmentAmount = round($amount / $installments, 2);

        Loan::create([
            'employee_id' => $employeeId,
            'loan_type' => $this->loanType,
            'loan_amount' => $amount,
            'installment_amount' => $installmentAmount,
            'total_installments' => $installments,
            'remaining_installments' => $installments,
            'loan_date' => $loanRequestDate,
            'repayment_start_month' => null,
            'description' => trim((string) $this->loanDescription),
            'status' => Loan::STATUS_PENDING,
            'requested_by' => Auth::id(),
        ]);

        $this->closeAddLoanModal();
        session()->flash('message', __('Loan request submitted successfully.'));
    }

    public function approveLoan($id)
    {
        $this->authorizeLoanManagement();
        $loan = Loan::find($id);
        if (!$loan || !$loan->isPending()) {
            session()->flash('error', __('Loan not found or already processed.'));
            return;
        }

        $this->selectedLoanId = (int) $id;
        $this->approvalDate = now()->format('Y-m-d');
        $this->approvalRepaymentStartMonth = Carbon::parse($this->approvalDate)->format('Y-m');
        $this->approvalComments = '';
        $this->showApproveLoanModal = true;
    }

    public function closeApproveLoanModal(): void
    {
        $this->showApproveLoanModal = false;
        $this->selectedLoanId = null;
        $this->approvalDate = '';
        $this->approvalRepaymentStartMonth = '';
        $this->approvalComments = '';
    }

    public function confirmApproveLoan(): void
    {
        $this->authorizeLoanManagement();
        $loan = Loan::find($this->selectedLoanId);
        if (!$loan || !$loan->isPending()) {
            session()->flash('error', __('Loan not found or already processed.'));
            $this->closeApproveLoanModal();
            return;
        }
        if (trim((string) $this->approvalDate) === '') {
            session()->flash('error', __('Please select an approval date.'));
            return;
        }
        $approvalStartMonth = $this->normalizeMonthToDate(
            trim((string) $this->approvalRepaymentStartMonth),
            (string) $this->approvalDate
        );
        if (Carbon::parse($approvalStartMonth)->lt(Carbon::parse($this->approvalDate)->startOfMonth())) {
            session()->flash('error', __('Loan return start month cannot be earlier than approval month.'));
            return;
        }

        $loan->update([
            'status' => Loan::STATUS_APPROVED,
            'repayment_start_month' => $approvalStartMonth,
            'approved_by' => Auth::id(),
            'approved_at' => $this->approvalDate . ' 00:00:00',
            'decision_comments' => trim((string) $this->approvalComments),
        ]);
        $this->syncLoanComputedState($loan->fresh('scenarioActions'));
        $this->closeApproveLoanModal();
        session()->flash('message', __('Loan approved successfully.'));
    }

    public function rejectLoan($id)
    {
        $this->authorizeLoanManagement();
        $loan = Loan::find($id);
        if (!$loan || !$loan->isPending()) {
            session()->flash('error', __('Loan not found or already processed.'));
            return;
        }

        $this->selectedLoanId = (int) $id;
        $this->rejectDate = now()->format('Y-m-d');
        $this->rejectComments = '';
        $this->showRejectLoanModal = true;
    }

    public function closeRejectLoanModal(): void
    {
        $this->showRejectLoanModal = false;
        $this->selectedLoanId = null;
        $this->rejectDate = '';
        $this->rejectComments = '';
    }

    public function confirmRejectLoan(): void
    {
        $this->authorizeLoanManagement();
        $loan = Loan::find($this->selectedLoanId);
        if (!$loan || !$loan->isPending()) {
            session()->flash('error', __('Loan not found or already processed.'));
            $this->closeRejectLoanModal();
            return;
        }
        if (trim((string) $this->rejectDate) === '') {
            session()->flash('error', __('Please select a rejection date.'));
            return;
        }

        $loan->update([
            'status' => Loan::STATUS_REJECTED,
            'approved_by' => Auth::id(),
            'approved_at' => $this->rejectDate . ' 00:00:00',
            'decision_comments' => trim((string) $this->rejectComments),
        ]);
        $this->closeRejectLoanModal();
        session()->flash('message', __('Loan rejected.'));
    }

    public function viewLoan($id)
    {
        $loan = Loan::find($id);
        if (!$loan) {
            session()->flash('error', __('Loan record not found.'));
            return;
        }

        $this->selectedLoanId = (int) $id;
        $this->showViewLoanModal = true;
    }

    public function closeViewLoanModal(): void
    {
        $this->showViewLoanModal = false;
        $this->selectedLoanId = null;
    }

    public function openRepaymentSchedule($id): void
    {
        $loan = Loan::with(['scenarioActions'])->find($id);
        if (!$loan) {
            session()->flash('error', __('Loan record not found.'));
            return;
        }
        if (!in_array($loan->status, [Loan::STATUS_APPROVED, Loan::STATUS_COMPLETED], true)) {
            session()->flash('error', __('Repayment schedule is available for active/completed loans only.'));
            return;
        }

        $this->selectedLoanId = (int) $id;
        $this->showRepaymentScheduleModal = true;
    }

    public function closeRepaymentScheduleModal(): void
    {
        $this->showRepaymentScheduleModal = false;
        $this->selectedLoanId = null;
        $this->resetScenarioInputs();
    }

    public function chooseScheduleScenario(string $scenario): void
    {
        $allowed = ['setoff', 'terminate', 'partial_payback', 'reschedule', 'freeze', 'topup', 'custom_pay'];
        if (!in_array($scenario, $allowed, true)) {
            return;
        }
        $this->activeScheduleScenario = $scenario;
        $this->scenarioMonth = now()->format('Y-m');
        if ($scenario === 'freeze') {
            $this->scenarioFreezeFromMonth = now()->format('Y-m');
            $this->scenarioResumeMonth = now()->addMonth()->format('Y-m');
        }
        if ($scenario === 'custom_pay') {
            $this->scenarioCustomPayMethod = 'salary_deduction';
            $this->scenarioCustomPayAmount = '';
        }
    }

    public function updatedActiveScheduleScenario($value): void
    {
        $scenario = trim((string) $value);
        if ($scenario === '') {
            return;
        }
        $this->chooseScheduleScenario($scenario);
    }

    protected function resetScenarioInputs(): void
    {
        $this->activeScheduleScenario = '';
        $this->scenarioMonth = '';
        $this->scenarioSetoffReason = '';
        $this->scenarioPaybackAmount = '';
        $this->scenarioRescheduleMonths = '';
        $this->scenarioRescheduleInstallment = '';
        $this->scenarioFreezeFromMonth = '';
        $this->scenarioResumeMonth = '';
        $this->scenarioTopupAmount = '';
        $this->scenarioCustomPayAmount = '';
        $this->scenarioCustomPayMethod = 'salary_deduction';
    }

    public function getSelectedLoanProperty(): ?Loan
    {
        if (!$this->selectedLoanId) {
            return null;
        }

        return Loan::with(['employee.department', 'requestedByUser', 'approvedByUser', 'scenarioActions.createdByUser'])->find($this->selectedLoanId);
    }

    public function getRepaymentScheduleRowsProperty(): array
    {
        $loan = $this->selectedLoan;
        if (!$loan) {
            return [];
        }

        return app(LoanScenarioService::class)->buildSchedule($loan);
    }

    public function getSelectedLoanScenarioHistoryProperty()
    {
        $loan = $this->selectedLoan;
        if (!$loan) {
            return collect();
        }

        return $loan->scenarioActions->sortByDesc(fn ($a) => ($a->effective_month ? $a->effective_month->format('Y-m-d') : '') . '-' . $a->id)->values();
    }

    public function applySelectedScenario(): void
    {
        $loan = $this->selectedLoan;
        if (!$loan) {
            session()->flash('error', __('Loan not found.'));
            return;
        }
        if (!in_array($loan->status, [Loan::STATUS_APPROVED, Loan::STATUS_COMPLETED], true)) {
            session()->flash('error', __('Scenarios are available for active/completed loans only.'));
            return;
        }
        if ($this->activeScheduleScenario === '') {
            session()->flash('error', __('Please choose a scenario.'));
            return;
        }

        $effectiveMonth = $this->scenarioMonth !== '' && preg_match('/^\d{4}-\d{2}$/', $this->scenarioMonth)
            ? $this->scenarioMonth . '-01'
            : now()->format('Y-m-01');

        $payload = [];
        $notes = '';

        switch ($this->activeScheduleScenario) {
            case 'setoff':
                $notes = trim((string) $this->scenarioSetoffReason);
                break;
            case 'terminate':
                $notes = trim((string) $this->scenarioSetoffReason);
                break;
            case 'partial_payback':
                $amount = round((float) $this->scenarioPaybackAmount, 2);
                if ($amount < 0) {
                    session()->flash('error', __('Payback amount cannot be negative.'));
                    return;
                }
                $payload['payback_amount'] = $amount;
                break;
            case 'reschedule':
                $months = (int) $this->scenarioRescheduleMonths;
                $newInstallment = round((float) $this->scenarioRescheduleInstallment, 2);
                if ($months <= 0 && $newInstallment <= 0) {
                    session()->flash('error', __('Provide either reschedule months or new payback amount.'));
                    return;
                }
                if ($months > 0) {
                    $payload['months'] = $months;
                }
                if ($newInstallment > 0) {
                    $payload['new_installment'] = $newInstallment;
                }
                break;
            case 'freeze':
                $freezeFrom = trim((string) $this->scenarioFreezeFromMonth);
                $resume = trim((string) $this->scenarioResumeMonth);
                if (!preg_match('/^\d{4}-\d{2}$/', $freezeFrom) || !preg_match('/^\d{4}-\d{2}$/', $resume)) {
                    session()->flash('error', __('Please select freeze from and resume month.'));
                    return;
                }
                if ($resume <= $freezeFrom) {
                    session()->flash('error', __('Resume payback month must be after freeze month.'));
                    return;
                }
                $effectiveMonth = $freezeFrom . '-01';
                $payload['freeze_from_month'] = $freezeFrom;
                $payload['resume_month'] = $resume;
                break;
            case 'topup':
                $topupAmount = round((float) $this->scenarioTopupAmount, 2);
                if ($topupAmount <= 0) {
                    session()->flash('error', __('Topup amount must be greater than zero.'));
                    return;
                }
                $payload['topup_amount'] = $topupAmount;
                break;

            case 'custom_pay':
                $customAmount = round((float) $this->scenarioCustomPayAmount, 2);
                $method = trim((string) $this->scenarioCustomPayMethod);
                $allowedMethods = ['salary_deduction', 'cash', 'bank_transfer'];
                if ($customAmount <= 0) {
                    session()->flash('error', __('Custom pay amount must be greater than zero.'));
                    return;
                }
                if (!in_array($method, $allowedMethods, true)) {
                    session()->flash('error', __('Please select a valid custom pay method.'));
                    return;
                }
                $payload['payback_amount'] = $customAmount;
                $payload['payment_method'] = $method;
                break;
        }

        LoanScenarioAction::create([
            'loan_id' => $loan->id,
            'scenario' => $this->activeScheduleScenario,
            'effective_month' => $effectiveMonth,
            'payload' => $payload,
            'notes' => $notes !== '' ? $notes : null,
            'created_by' => Auth::id(),
        ]);

        // Recompute current snapshot after scenario action.
        $loan->refresh()->loadMissing('scenarioActions');
        $this->syncLoanComputedState($loan);

        $this->resetScenarioInputs();
        $this->activeScheduleScenario = '';
        session()->flash('message', __('Scenario applied and recorded in loan history.'));
    }

    protected function syncLoanComputedState(Loan $loan): void
    {
        if (!in_array($loan->status, [Loan::STATUS_APPROVED, Loan::STATUS_COMPLETED], true)) {
            return;
        }

        $rows = app(LoanScenarioService::class)->buildSchedule($loan);
        $currentMonthKey = now()->format('Y-m');
        $monthsLeft = 0;

        foreach ($rows as $r) {
            if (($r['month_key'] ?? '') >= $currentMonthKey && (float) ($r['payback_amount'] ?? 0) > 0) {
                $monthsLeft++;
            }
        }

        $loan->remaining_installments = max(0, $monthsLeft);
        $loan->status = $loan->remaining_installments <= 0
            ? Loan::STATUS_COMPLETED
            : Loan::STATUS_APPROVED;
        $loan->save();
    }

    protected function authorizeLoanRequest(): void
    {
        $user = Auth::user();

        if (!$user || (!$user->can('payroll.loan.manage') && !$user->can('payroll.loan.request'))) {
            abort(403);
        }
    }

    protected function authorizeLoanManagement(): void
    {
        $user = Auth::user();

        if (!$user || !$user->can('payroll.loan.manage')) {
            abort(403);
        }
    }

    protected function authorizeLoanAnyDelete(): void
    {
        $user = Auth::user();

        if (!$user || (!$user->hasRole('Super Admin') && !$user->can('payroll.loan.delete.any'))) {
            abort(403);
        }
    }

    protected function normalizeMonthToDate(string $monthValue, string $fallbackDate): string
    {
        if ($monthValue !== '' && preg_match('/^\d{4}-\d{2}$/', $monthValue)) {
            return $monthValue . '-01';
        }

        $fallback = trim($fallbackDate) !== '' ? Carbon::parse($fallbackDate) : now();

        return $fallback->copy()->startOfMonth()->format('Y-m-d');
    }

    public function getAddDeductionPreviewProperty(): array
    {
        $loanAmount = max(0.0, (float) $this->loanAmount);
        $installments = max(1, (int) $this->totalInstallments);
        $requestDate = trim((string) $this->loanRequestDate) !== '' ? Carbon::parse($this->loanRequestDate) : now();

        return [
            'total_loan' => round($loanAmount, 2),
            'request_date' => $requestDate->format('M d, Y'),
            'start_month' => __('Set on approval'),
            'months' => $installments,
            'deduction_per_month' => round($loanAmount / $installments, 2),
        ];
    }

    public function getEditDeductionPreviewProperty(): array
    {
        $loanAmount = max(0.0, (float) $this->editLoanAmount);
        $installments = max(1, (int) $this->editTotalInstallments);
        $requestDate = trim((string) $this->editLoanRequestDate) !== '' ? Carbon::parse($this->editLoanRequestDate) : now();

        return [
            'total_loan' => round($loanAmount, 2),
            'request_date' => $requestDate->format('M d, Y'),
            'start_month' => __('Set on approval'),
            'months' => $installments,
            'deduction_per_month' => round($loanAmount / $installments, 2),
        ];
    }

    public function getApprovalDeductionPreviewProperty(): array
    {
        $loan = Loan::find($this->selectedLoanId);
        if (!$loan) {
            return [
                'total_loan' => 0,
                'request_date' => '—',
                'start_month' => '—',
                'months' => 0,
                'deduction_per_month' => 0,
            ];
        }

        $startMonthDate = Carbon::parse(
            $this->normalizeMonthToDate((string) $this->approvalRepaymentStartMonth, (string) $this->approvalDate)
        );

        return [
            'total_loan' => round((float) $loan->loan_amount, 2),
            'request_date' => $loan->loan_date ? $loan->loan_date->format('M d, Y') : '—',
            'start_month' => $startMonthDate->format('M Y'),
            'months' => (int) $loan->total_installments,
            'deduction_per_month' => round((float) $loan->installment_amount, 2),
        ];
    }

    public function getFreezeRangeLabelProperty(): string
    {
        $from = trim((string) $this->scenarioFreezeFromMonth);
        $resume = trim((string) $this->scenarioResumeMonth);
        if (!preg_match('/^\d{4}-\d{2}$/', $from) || !preg_match('/^\d{4}-\d{2}$/', $resume) || $resume <= $from) {
            return __('Select valid freeze and resume months.');
        }

        $fromLabel = Carbon::createFromFormat('Y-m', $from)->format('M Y');
        $toLabel = Carbon::createFromFormat('Y-m', $resume)->subMonth()->format('M Y');
        $resumeLabel = Carbon::createFromFormat('Y-m', $resume)->format('M Y');

        return __('Frozen range: :from to :to (resume :resume)', [
            'from' => $fromLabel,
            'to' => $toLabel,
            'resume' => $resumeLabel,
        ]);
    }

    public function formatScenarioHistoryDetails($action): string
    {
        if (!empty($action->notes)) {
            return (string) $action->notes;
        }

        $payload = (array) ($action->payload ?? []);
        $scenario = (string) ($action->scenario ?? '');

        switch ($scenario) {
            case 'freeze':
                $from = (string) ($payload['freeze_from_month'] ?? '');
                $resume = (string) ($payload['resume_month'] ?? '');
                if (preg_match('/^\d{4}-\d{2}$/', $from) && preg_match('/^\d{4}-\d{2}$/', $resume) && $resume > $from) {
                    $fromLabel = Carbon::createFromFormat('Y-m', $from)->format('M Y');
                    $toLabel = Carbon::createFromFormat('Y-m', $resume)->subMonth()->format('M Y');
                    $resumeLabel = Carbon::createFromFormat('Y-m', $resume)->format('M Y');
                    return __('Frozen range: :from to :to (resume :resume)', [
                        'from' => $fromLabel,
                        'to' => $toLabel,
                        'resume' => $resumeLabel,
                    ]);
                }
                if (isset($payload['freeze_months'])) {
                    return __('Freeze months: :m', ['m' => (int) $payload['freeze_months']]);
                }
                return '—';

            case 'partial_payback':
                if (isset($payload['payback_amount'])) {
                    return __('Payback amount: :amount', ['amount' => number_format((float) $payload['payback_amount'], 2)]);
                }
                return '—';

            case 'reschedule':
                $parts = [];
                if (isset($payload['months'])) {
                    $parts[] = __('Months: :m', ['m' => (int) $payload['months']]);
                }
                if (isset($payload['new_installment'])) {
                    $parts[] = __('New payback amount: :a', ['a' => number_format((float) $payload['new_installment'], 2)]);
                }
                return empty($parts) ? '—' : implode(' | ', $parts);

            case 'topup':
                if (isset($payload['topup_amount'])) {
                    return __('Topup amount: :amount', ['amount' => number_format((float) $payload['topup_amount'], 2)]);
                }
                return '—';

            case 'custom_pay':
                $amount = isset($payload['payback_amount']) ? number_format((float) $payload['payback_amount'], 2) : '0.00';
                $method = (string) ($payload['payment_method'] ?? 'salary_deduction');
                $methodLabel = match ($method) {
                    'cash' => __('Cash'),
                    'bank_transfer' => __('Bank transfer'),
                    default => __('Salary deduction'),
                };
                return __('Custom pay: :amount via :method', ['amount' => $amount, 'method' => $methodLabel]);

            case 'setoff':
                return __('Complete setoff');

            case 'terminate':
                return __('Terminate/Resign setoff');
        }

        return empty($payload) ? '—' : json_encode($payload);
    }

    public function render()
    {
        $query = Loan::query()
            ->with(['employee.department', 'scenarioActions'])
            ->when($this->search !== '', function ($q) {
                $term = '%' . trim($this->search) . '%';
                $q->whereHas('employee', function ($q2) use ($term) {
                    $q2->where('first_name', 'like', $term)
                        ->orWhere('last_name', 'like', $term)
                        ->orWhere('employee_code', 'like', $term);
                });
            })
            ->when($this->selectedDepartment !== '', function ($q) {
                $q->whereHas('employee', function ($q2) {
                    $q2->whereHas('department', function ($q3) {
                        $q3->where('title', $this->selectedDepartment);
                    });
                });
            })
            ->when($this->loanStatus !== '', function ($q) {
                $q->where('status', $this->loanStatus);
            });

        $sortField = $this->sortBy ?: 'created_at';
        $sortDir = $this->sortDirection === 'asc' ? 'asc' : 'desc';
        $allowedSort = ['loan_amount', 'installment_amount', 'status', 'created_at', 'loan_type', 'employee_name', 'department', 'loan_date'];
        if (in_array($sortField, $allowedSort, true)) {
            if ($sortField === 'employee_name') {
                $query->join('employees', 'loans.employee_id', '=', 'employees.id')
                    ->orderByRaw('CONCAT(employees.first_name, " ", employees.last_name) ' . $sortDir)
                    ->select('loans.*');
            } elseif ($sortField === 'department') {
                $query->join('employees', 'loans.employee_id', '=', 'employees.id')
                    ->leftJoin('departments', 'employees.department_id', '=', 'departments.id')
                    ->orderBy('departments.title', $sortDir)
                    ->select('loans.*');
            } else {
                $query->orderBy($sortField, $sortDir);
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $loans = $query->paginate(15);
        $loanDisplayMap = [];
        $scheduleService = app(LoanScenarioService::class);
        $currentMonthKey = now()->format('Y-m');
        foreach ($loans as $loan) {
            if (in_array($loan->status, [Loan::STATUS_APPROVED, Loan::STATUS_COMPLETED], true)) {
                $this->syncLoanComputedState($loan);
                $rows = $scheduleService->buildSchedule($loan);
                $totalRows = count($rows);

                $currentBalance = !empty($rows) ? (float) ($rows[0]['principle_amount'] ?? 0) : 0.0;
                foreach ($rows as $r) {
                    if (($r['month_key'] ?? '') <= $currentMonthKey) {
                        $currentBalance = (float) ($r['balance'] ?? $currentBalance);
                    } else {
                        break;
                    }
                }
                $currentBalance = round(max(0, $currentBalance), 2);

                $remainingRows = 0;
                foreach ($rows as $r) {
                    if (($r['month_key'] ?? '') > $currentMonthKey) {
                        $remainingRows++;
                    }
                }

                $loanDisplayMap[$loan->id] = [
                    'total_rows' => $totalRows,
                    'remaining_rows' => $remainingRows,
                    'balance' => $currentBalance,
                ];
            } else {
                $loanDisplayMap[$loan->id] = [
                    'total_rows' => (int) $loan->total_installments,
                    'remaining_rows' => (int) $loan->remaining_installments,
                    'balance' => round((float) $loan->installment_amount * (int) $loan->remaining_installments, 2),
                ];
            }
        }

        $departments = Department::where('status', 'active')
            ->orderBy('title')
            ->pluck('title')
            ->toArray();

        $activeEmployees = Employee::where('status', 'active')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get()
            ->map(fn ($e) => [
                'id' => $e->id,
                'label' => trim($e->first_name . ' ' . $e->last_name) . ' (' . ($e->employee_code ?? '') . ')',
            ])
            ->toArray();

        $loanTypes = ['Personal', 'Housing', 'Vehicle', 'Education', 'Medical'];
        $statuses = [Loan::STATUS_PENDING, Loan::STATUS_APPROVED, Loan::STATUS_REJECTED, Loan::STATUS_COMPLETED];
        $user = Auth::user();
        $canDeleteAnyLoan = $user && ($user->hasRole('Super Admin') || $user->can('payroll.loan.delete.any'));

        return view('livewire.payroll.loan-management', [
            'loans' => $loans,
            'departments' => $departments,
            'loanTypes' => $loanTypes,
            'statuses' => $statuses,
            'activeEmployees' => $activeEmployees,
            'canDeleteAnyLoan' => $canDeleteAnyLoan,
            'loanDisplayMap' => $loanDisplayMap,
        ])->layout('components.layouts.app');
    }
}
