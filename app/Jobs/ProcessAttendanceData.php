<?php

namespace App\Jobs;

use App\Models\DeviceAttendance;
use App\Models\DeviceEmployee;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ProcessAttendanceData implements ShouldQueue
{
    use Queueable;

    protected $batchSize;

    /**
     * Create a new job instance.
     */
    public function __construct(int $batchSize = null)
    {
        $this->batchSize = $batchSize ?? config('zkteco.batch_size', 100);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::info("ZKTeco Process: Starting attendance data processing job");

            // Get unprocessed attendance records
            $attendanceRecords = DeviceAttendance::where('is_processed', false)
                ->limit($this->batchSize)
                ->get();

            if ($attendanceRecords->isEmpty()) {
                Log::info("ZKTeco Process: No unprocessed attendance records found");
                return;
            }

            $processedCount = 0;
            $errorCount = 0;

            foreach ($attendanceRecords as $record) {
                try {
                    // Find user by punch code
                    $user = User::where('punch_code', $record->punch_code)->first();
                    
                    if (!$user) {
                        Log::warning("ZKTeco Process: User not found for punch code", [
                            'punch_code' => $record->punch_code
                        ]);
                        $errorCount++;
                        continue;
                    }

                    // Process attendance record
                    $this->processAttendanceRecord($record, $user);
                    
                    // Mark as processed
                    $record->update(['is_processed' => true]);
                    $processedCount++;

                } catch (\Exception $e) {
                    Log::error("ZKTeco Process: Error processing attendance record", [
                        'record_id' => $record->id,
                        'error' => $e->getMessage()
                    ]);
                    $errorCount++;
                }
            }

            Log::info("ZKTeco Process: Attendance processing completed", [
                'processed' => $processedCount,
                'errors' => $errorCount,
                'total' => $attendanceRecords->count()
            ]);

        } catch (\Exception $e) {
            Log::error("ZKTeco Process: Attendance processing job failed", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Process individual attendance record
     */
    protected function processAttendanceRecord(DeviceAttendance $record, User $user): void
    {
        // Here you can add logic to:
        // 1. Create attendance records in your main attendance system
        // 2. Calculate working hours
        // 3. Update user's last attendance
        // 4. Send notifications if needed
        
        Log::info("ZKTeco Process: Processing attendance for user", [
            'user_id' => $user->id,
            'punch_code' => $record->punch_code,
            'punch_time' => $record->punch_time,
            'device_type' => $record->device_type
        ]);

        // Example: Update user's last activity
        $user->update([
            'updated_at' => $record->punch_time
        ]);
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("ZKTeco Process: Attendance processing job permanently failed", [
            'error' => $exception->getMessage()
        ]);
    }
}
