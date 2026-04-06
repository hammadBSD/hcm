<?php

namespace App\Livewire\SystemManagement\UserManagement\Users;

use App\Models\DeviceEmployee;
use App\Models\Employee;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    /** all | hr_only | device_not_in_hr */
    public string $viewFilter = 'all';

    public string $search = '';

    public function updatedViewFilter(): void
    {
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Distinct normalized punch codes from employees (for device matching).
     *
     * @return list<string>
     */
    protected function employeePunchCodeList(): array
    {
        return Employee::query()
            ->whereNotNull('punch_code')
            ->pluck('punch_code')
            ->map(fn ($c) => trim((string) $c))
            ->filter(fn ($c) => $c !== '')
            ->unique()
            ->values()
            ->all();
    }

    public function unmatchedDeviceEmployeeCount(): int
    {
        $codes = $this->employeePunchCodeList();

        return DeviceEmployee::query()
            ->get()
            ->filter(function (DeviceEmployee $de) use ($codes) {
                $p = trim((string) ($de->punch_code_id ?? ''));

                if ($p === '') {
                    return true;
                }

                return ! in_array($p, $codes, true);
            })
            ->count();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    protected function buildHrRows(): Collection
    {
        return Employee::query()
            ->with(['user', 'department'])
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get()
            ->map(function (Employee $e) {
                $name = trim(($e->first_name ?? '') . ' ' . ($e->last_name ?? ''));
                if ($name === '') {
                    $name = $e->user?->name ?? '—';
                }

                return [
                    'row_key' => 'hr-' . $e->id,
                    'source' => 'hr',
                    'employee_id' => $e->id,
                    'device_employee_id' => null,
                    'punch_code' => $e->punch_code ? trim((string) $e->punch_code) : '—',
                    'employee_code' => $e->employee_code ?? '—',
                    'name' => $name,
                    'email' => $e->user?->email ?? '—',
                    'department' => optional($e->department)->title ?? ($e->department ?: '—'),
                    'hr_status' => $e->status ?? '—',
                    'device_ip' => null,
                    'device_type' => null,
                ];
            });
    }

    /**
     * Device directory rows that do not match any employee punch_code.
     *
     * @return Collection<int, array<string, mixed>>
     */
    protected function buildDeviceOnlyRows(): Collection
    {
        $codes = $this->employeePunchCodeList();

        return DeviceEmployee::query()
            ->orderBy('name')
            ->get()
            ->filter(function (DeviceEmployee $de) use ($codes) {
                $p = trim((string) ($de->punch_code_id ?? ''));

                if ($p === '') {
                    return true;
                }

                return ! in_array($p, $codes, true);
            })
            ->values()
            ->map(function (DeviceEmployee $de) {
                return [
                    'row_key' => 'device-' . $de->id,
                    'source' => 'device_only',
                    'employee_id' => null,
                    'device_employee_id' => $de->id,
                    'punch_code' => $de->punch_code_id ? trim((string) $de->punch_code_id) : '—',
                    'employee_code' => '—',
                    'name' => $de->name ?? '—',
                    'email' => $de->email ?? '—',
                    'department' => $de->department ?? '—',
                    'hr_status' => '—',
                    'device_ip' => $de->device_ip,
                    'device_type' => $de->device_type,
                ];
            });
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    protected function mergedRows(): Collection
    {
        $hr = $this->buildHrRows();
        $deviceOnly = $this->buildDeviceOnlyRows();

        $merged = match ($this->viewFilter) {
            'hr_only' => $hr,
            'device_not_in_hr' => $deviceOnly,
            default => $hr->concat($deviceOnly),
        };

        $term = trim(mb_strtolower($this->search));
        if ($term !== '') {
            $merged = $merged->filter(function (array $row) use ($term) {
                $hay = mb_strtolower(
                    ($row['punch_code'] ?? '') . ' '
                    . ($row['employee_code'] ?? '') . ' '
                    . ($row['name'] ?? '') . ' '
                    . ($row['email'] ?? '') . ' '
                    . ($row['department'] ?? '') . ' '
                    . ($row['device_ip'] ?? '')
                );

                return str_contains($hay, $term);
            })->values();
        }

        return $merged;
    }

    public function paginatedRows(): LengthAwarePaginator
    {
        $rows = $this->mergedRows();
        $perPage = 15;
        $page = (int) $this->getPage();

        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $rows->forPage($page, $perPage)->values(),
            $rows->count(),
            $perPage,
            max(1, $page),
            ['path' => request()->url(), 'pageName' => 'page']
        );

        return $paginator->withQueryString();
    }

    public function render()
    {
        $unmatchedCount = $this->unmatchedDeviceEmployeeCount();

        return view('livewire.system-management.user-management.users.index', [
            'rows' => $this->paginatedRows(),
            'unmatchedDeviceCount' => $unmatchedCount,
        ])->layout('components.layouts.app');
    }
}
