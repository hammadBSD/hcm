<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\ZKTecoApiService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SyncEmployeeData implements ShouldQueue
{
    use Queueable;

    protected $batchSize;
    protected $source;

    /**
     * Create a new job instance.
     */
    public function __construct(int $batchSize = null, string $source = 'hr-system')
    {
        $this->batchSize = $batchSize ?? config('zkteco.batch_size', 100);
        $this->source = $source;
    }

    /**
     * Execute the job.
     */
    public function handle(ZKTecoApiService $apiService): void
    {
        try {
            Log::info("ZKTeco Sync: Starting employee data sync job");

            // Get users with punch codes
            $users = User::whereNotNull('punch_code')
                ->limit($this->batchSize)
                ->get();

            if ($users->isEmpty()) {
                Log::info("ZKTeco Sync: No users found for employee sync");
                return;
            }

            // Prepare employee records for API
            $employees = [];
            foreach ($users as $user) {
                $employees[] = [
                    'punch_code_id' => $user->punch_code,
                    'name' => $user->name,
                    'email' => $user->email,
                    'department' => $user->department ?? null,
                    'position' => $user->designation ?? null,
                    'is_active' => $user->status === 'active',
                    'device_ip' => 'HR-SYSTEM',
                    'device_type' => 'IN'
                ];
            }

            // Send to ZKTeco API
            $result = $apiService->syncEmployees($employees, $this->source);

            if ($result['success']) {
                Log::info("ZKTeco Sync: Employee sync completed successfully", [
                    'users_count' => $users->count(),
                    'api_response' => $result['data']
                ]);
            } else {
                Log::error("ZKTeco Sync: Employee sync failed", [
                    'error' => $result['error'] ?? 'Unknown error'
                ]);
                throw new \Exception($result['error'] ?? 'Employee sync failed');
            }

        } catch (\Exception $e) {
            Log::error("ZKTeco Sync: Employee sync job failed", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("ZKTeco Sync: Employee sync job permanently failed", [
            'error' => $exception->getMessage()
        ]);
    }
}
