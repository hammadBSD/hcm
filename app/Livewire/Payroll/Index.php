<?php

namespace App\Livewire\Payroll;

use App\Models\Employee;
use App\Models\PayrollMonthLock;
use App\Models\PayrollMonthSnapshot;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Symfony\Component\HttpFoundation\StreamedResponse;

class Index extends Component
{
    public $employee;

    public bool $canViewTeam = false;

    public $selectedEmployeeId = null;

    public array $employeeOptions = [];

    public $payslips = [];

    public $currentMonth;

    public $selectedYear;

    public $selectedMonth = '';

    public bool $showPayslipPasswordModal = false;

    public string $payslipPassword = '';

    public ?int $pendingPayslipId = null;

    public string $passwordAction = '';

    public bool $showPayslipDetailModal = false;

    public ?array $viewingPayslip = null;

    public function mount()
    {
        $user = Auth::user();

        if (!$user || !$user->can('payroll.view.self')) {
            abort(403);
        }

        $this->canViewTeam = $user->can('payroll.view.team');

        $selfEmployee = Employee::with('department')->where('user_id', $user->id)->first();
        $this->selectedEmployeeId = $selfEmployee?->id;

        if ($this->canViewTeam) {
            $this->employeeOptions = Employee::query()
                ->orderBy('first_name')
                ->orderBy('last_name')
                ->get(['id', 'first_name', 'last_name', 'employee_code', 'status'])
                ->map(fn (Employee $e) => [
                    'id' => $e->id,
                    'label' => trim($e->first_name . ' ' . $e->last_name)
                        . ($e->employee_code ? ' (' . $e->employee_code . ')' : '')
                        . ($e->status !== 'active' ? ' — ' . __('Inactive') : ''),
                ])
                ->all();
        }

        $this->resolveEmployee();

        $this->currentMonth = now()->format('Y-m');
        $this->selectedYear = now()->year;

        $this->loadPayslips();
    }

    protected function resolveEmployee(): void
    {
        $user = Auth::user();
        $selfEmployee = Employee::with('department')->where('user_id', $user->id)->first();

        if ($this->canViewTeam && $this->selectedEmployeeId) {
            $employee = Employee::with('department')->find($this->selectedEmployeeId);
            $this->employee = $employee ?: $selfEmployee;
            $this->selectedEmployeeId = $this->employee?->id;
        } else {
            $this->employee = $selfEmployee;
            $this->selectedEmployeeId = $selfEmployee?->id;
        }
    }

    public function updatedSelectedEmployeeId(): void
    {
        $this->resolveEmployee();
        $this->loadPayslips();
    }

    public function loadPayslips()
    {
        if (!$this->employee) {
            $this->payslips = [];
            return;
        }

        $lockedMonths = PayrollMonthLock::query()->pluck('year_month');

        $snapshots = PayrollMonthSnapshot::query()
            ->where('employee_id', $this->employee->id)
            ->whereIn('year_month', $lockedMonths)
            ->where('year_month', 'like', sprintf('%04d-', (int) $this->selectedYear) . '%')
            ->when($this->selectedMonth !== '' && $this->selectedMonth !== null, function ($query) {
                $yearMonth = sprintf('%04d-%02d', (int) $this->selectedYear, (int) $this->selectedMonth);
                $query->where('year_month', $yearMonth);
            })
            ->orderByDesc('year_month')
            ->get();

        $locksByMonth = PayrollMonthLock::query()
            ->whereIn('year_month', $snapshots->pluck('year_month')->unique())
            ->get()
            ->keyBy('year_month');

        $this->payslips = $snapshots->map(function (PayrollMonthSnapshot $snapshot) use ($locksByMonth) {
            $lock = $locksByMonth->get($snapshot->year_month);

            return [
                'id' => $snapshot->id,
                'month' => $snapshot->year_month,
                'net_salary' => (float) $snapshot->net_salary,
                'status' => 'paid',
                'paid_date' => $lock?->locked_at?->format('Y-m-d'),
            ];
        })->values()->all();
    }

    public function requestViewPayslip(int $snapshotId): void
    {
        $this->beginPayslipPasswordFlow($snapshotId, 'view');
    }

    public function requestDownloadPayslip(int $snapshotId): void
    {
        $this->beginPayslipPasswordFlow($snapshotId, 'download');
    }

    protected function beginPayslipPasswordFlow(int $snapshotId, string $action): void
    {
        if (!$this->findOwnedSnapshot($snapshotId)) {
            return;
        }

        $this->pendingPayslipId = $snapshotId;
        $this->passwordAction = $action;
        $this->payslipPassword = '';
        $this->resetErrorBag('payslipPassword');
        $this->showPayslipPasswordModal = true;
    }

    public function closePayslipPasswordModal(): void
    {
        $this->showPayslipPasswordModal = false;
        $this->payslipPassword = '';
        $this->pendingPayslipId = null;
        $this->passwordAction = '';
    }

    public function closePayslipDetailModal(): void
    {
        $this->showPayslipDetailModal = false;
        $this->viewingPayslip = null;
    }

    public function confirmPayslipPassword()
    {
        $this->validate([
            'payslipPassword' => 'required|string',
        ]);

        $user = Auth::user();
        if (!$user || !Hash::check($this->payslipPassword, $user->password)) {
            $this->addError('payslipPassword', __('Incorrect password. Use your login password.'));
            return;
        }

        $snapshotId = $this->pendingPayslipId;
        $action = $this->passwordAction;
        $this->closePayslipPasswordModal();

        if (!$snapshotId) {
            return;
        }

        if ($action === 'view') {
            $this->openPayslipDetail($snapshotId);
            return;
        }

        if ($action === 'download') {
            return $this->streamPayslipDownload($snapshotId);
        }
    }

    protected function openPayslipDetail(int $snapshotId): void
    {
        $snapshot = $this->findOwnedSnapshot($snapshotId);
        if (!$snapshot) {
            return;
        }

        $lock = PayrollMonthLock::query()->where('year_month', $snapshot->year_month)->first();

        $this->viewingPayslip = [
            'id' => $snapshot->id,
            'month' => $snapshot->year_month,
            'month_label' => Carbon::createFromFormat('Y-m', $snapshot->year_month)->format('F Y'),
            'basic_salary' => (float) $snapshot->basic_salary,
            'allowances' => (float) $snapshot->allowances,
            'gross_salary' => (float) $snapshot->gross_salary,
            'bonus' => (float) $snapshot->bonus,
            'tax' => (float) $snapshot->tax,
            'eobi' => (float) $snapshot->eobi,
            'advance' => (float) $snapshot->advance,
            'loan' => (float) $snapshot->loan,
            'deductions' => (float) $snapshot->total_deductions,
            'net_salary' => (float) $snapshot->net_salary,
            'paid_date' => $lock?->locked_at?->format('M d, Y'),
        ];
        $this->showPayslipDetailModal = true;
    }

    protected function streamPayslipDownload(int $snapshotId): StreamedResponse
    {
        $snapshot = $this->findOwnedSnapshot($snapshotId);
        if (!$snapshot || !$this->employee) {
            abort(404);
        }

        $lock = PayrollMonthLock::query()->where('year_month', $snapshot->year_month)->first();
        $month = Carbon::createFromFormat('Y-m', $snapshot->year_month);
        $monthLabel = $month->format('F Y');
        $filename = 'payslip-' . $snapshot->year_month . '.html';

        $logoPath = public_path('bsd-logo-dark.svg');
        $logoDataUri = file_exists($logoPath)
            ? 'data:image/svg+xml;base64,' . base64_encode(file_get_contents($logoPath))
            : null;

        $html = view('payroll.payslip-print', [
            'snapshot' => $snapshot,
            'monthLabel' => $monthLabel,
            'payPeriodStart' => $month->copy()->startOfMonth()->format('M d, Y'),
            'payPeriodEnd' => $month->copy()->endOfMonth()->format('M d, Y'),
            'employeeName' => trim($this->employee->first_name . ' ' . $this->employee->last_name),
            'employeeCode' => $this->employee->employee_code ?? '—',
            'designation' => $snapshot->designation
                ?? $this->employee->designation
                ?? '',
            'department' => $snapshot->department
                ?? $this->employee->department?->title
                ?? $this->employee->department
                ?? '',
            'paidDate' => $lock?->locked_at?->format('M d, Y'),
            'logoDataUri' => $logoDataUri,
        ])->render();

        return response()->streamDownload(function () use ($html) {
            echo $html;
        }, $filename, [
            'Content-Type' => 'text/html; charset=UTF-8',
        ]);
    }

    protected function findOwnedSnapshot(int $snapshotId): ?PayrollMonthSnapshot
    {
        if (!$this->employee) {
            return null;
        }

        return PayrollMonthSnapshot::query()
            ->where('id', $snapshotId)
            ->where('employee_id', $this->employee->id)
            ->whereIn('year_month', PayrollMonthLock::query()->pluck('year_month'))
            ->first();
    }

    public function updatedSelectedYear()
    {
        $this->loadPayslips();
    }

    public function updatedSelectedMonth()
    {
        $this->loadPayslips();
    }

    public function render()
    {
        $lockedMonths = PayrollMonthLock::query()
            ->orderByDesc('year_month')
            ->pluck('year_month')
            ->all();

        return view('livewire.payroll.index', [
            'lockedMonths' => $lockedMonths,
        ])->layout('components.layouts.app');
    }
}
