<?php

namespace App\Console\Commands;

use App\Models\Designation;
use App\Models\Employee;
use Illuminate\Console\Command;

class SyncDesignationsFromEmployees extends Command
{
    protected $signature = 'designations:sync-from-employees';

    protected $description = 'Add all unique designations from the employees table (DSG column) into the designations table.';

    public function handle(): int
    {
        $this->info('Collecting unique designations from employees...');

        $employees = Employee::with('designation')->get();
        $uniqueNames = collect();

        foreach ($employees as $employee) {
            $name = $this->getDisplayDesignation($employee);
            if ($name !== '' && $name !== 'N/A') {
                $uniqueNames->push(trim($name));
            }
        }

        $uniqueNames = $uniqueNames->unique()->filter()->values();
        $this->info('Found ' . $uniqueNames->count() . ' unique designation(s).');

        if ($uniqueNames->isEmpty()) {
            $this->warn('No designations to add.');
            return self::SUCCESS;
        }

        $added = 0;
        foreach ($uniqueNames as $name) {
            $existing = Designation::where('name', $name)->first();
            if (!$existing) {
                Designation::create([
                    'name'        => $name,
                    'description' => $name,
                    'status'      => 'active',
                ]);
                $added++;
                $this->line('  Added: ' . $name);
            }
        }

        $this->info('Done. Added ' . $added . ' new designation(s). ' . ($uniqueNames->count() - $added) . ' already existed.');
        return self::SUCCESS;
    }

    protected function getDisplayDesignation(Employee $employee): string
    {
        if ($employee->designation_id) {
            $des = $employee->relationLoaded('designation')
                ? $employee->getRelation('designation')
                : $employee->designation()->first();
            if ($des && is_object($des)) {
                return (string) ($des->name ?? '');
            }
        }
        $legacy = $employee->getRawOriginal('designation');
        return trim((string) $legacy);
    }
}
