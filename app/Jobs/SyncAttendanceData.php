<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\ZKTecoApiService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SyncAttendanceData implements ShouldQueue
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
            Log::info("ZKTeco Sync: Starting attendance data sync job");

            // Get users with punch codes who haven't synced recently
            $users = User::whereNotNull('punch_code')
                ->where('updated_at', '>=', now()->subMinutes(config('zkteco.sync_interval', 5)))
                ->limit($this->batchSize)
                ->get();

            if ($users->isEmpty()) {
                Log::info("ZKTeco Sync: No users found for attendance sync");
                return;
            }

            // Prepare attendance records for API
            $attendanceRecords = [];
            foreach ($users as $user) {
                $attendanceRecords[] = [
                    'punch_code_id' => $user->punch_code,
                    'device_ip' => 'HR-SYSTEM',
                    'device_type' => 'IN', // Default type
                    'punch_time' => now()->toISOString(),
                    'verify_mode' => 1,
                    'is_processed' => false
                ];
            }

            // Send to ZKTeco API
            $result = $apiService->syncAttendance($attendanceRecords, $this->source);

            if ($result['success']) {
                Log::info("ZKTeco Sync: Attendance sync completed successfully", [
                    'users_count' => $users->count(),
                    'api_response' => $result['data']
                ]);
            } else {
                Log::error("ZKTeco Sync: Attendance sync failed", [
                    'error' => $result['error'] ?? 'Unknown error'
                ]);
                throw new \Exception($result['error'] ?? 'Attendance sync failed');
            }

        } catch (\Exception $e) {
            Log::error("ZKTeco Sync: Attendance sync job failed", [
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
        Log::error("ZKTeco Sync: Attendance sync job permanently failed", [
            'error' => $exception->getMessage()
        ]);
    }
}
