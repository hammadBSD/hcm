<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeviceAttendance;
use App\Models\DeviceEmployee;
use App\Models\ZktecoSyncLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class ZKTecoSyncController extends Controller
{
    /**
     * Sync attendance data from ZKTeco project
     */
    public function syncAttendance(Request $request): JsonResponse
    {
        try {
            // Validate the request
            $validator = Validator::make($request->all(), [
                'attendance_records' => 'required|array',
                'attendance_records.*.punch_code_id' => 'required|string',
                'attendance_records.*.device_ip' => 'required|string',
                'attendance_records.*.device_type' => 'required|string|in:IN,OUT',
                'attendance_records.*.punch_time' => 'required|date',
                'attendance_records.*.verify_mode' => 'required|integer',
                'attendance_records.*.is_processed' => 'boolean',
                'sync_timestamp' => 'required|date',
                'source' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 400);
            }

            $attendanceRecords = $request->input('attendance_records');
            $syncTimestamp = $request->input('sync_timestamp');
            $source = $request->input('source');
            
            $savedCount = 0;
            $duplicateCount = 0;
            $errorCount = 0;

            Log::info("ZKTeco Sync: Received " . count($attendanceRecords) . " attendance records from {$source}");

            foreach ($attendanceRecords as $record) {
                try {
                    // Use updateOrCreate to handle duplicates gracefully
                    // This matches the unique constraint: (punch_code, device_ip, punch_time)
                    $attendance = DeviceAttendance::updateOrCreate(
                        [
                            'punch_code' => $record['punch_code_id'],
                            'device_ip' => $record['device_ip'],
                            'punch_time' => Carbon::parse($record['punch_time']),
                        ],
                        [
                            'device_type' => $record['device_type'],
                            'verify_mode' => $record['verify_mode'],
                            'is_processed' => $record['is_processed'] ?? false,
                            'sync_timestamp' => Carbon::parse($syncTimestamp),
                        ]
                    );
                    
                    // Check if it was newly created or updated
                    if ($attendance->wasRecentlyCreated) {
                        $savedCount++;
                    } else {
                        $duplicateCount++;
                    }
                } catch (\Exception $e) {
                    Log::error("ZKTeco Sync: Error saving attendance record: " . $e->getMessage());
                    $errorCount++;
                }
            }

            Log::info("ZKTeco Sync: Saved {$savedCount} records, {$duplicateCount} duplicates, {$errorCount} errors");

            ZktecoSyncLog::create([
                'sync_type' => ZktecoSyncLog::TYPE_ATTENDANCE,
                'synced_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Attendance data synced successfully',
                'data' => [
                    'saved_records' => $savedCount,
                    'duplicates_skipped' => $duplicateCount,
                    'errors' => $errorCount,
                    'total_received' => count($attendanceRecords)
                ]
            ]);

        } catch (\Exception $e) {
            Log::error("ZKTeco Sync: Fatal error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Internal server error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sync employee data from ZKTeco project
     */
    public function syncEmployees(Request $request): JsonResponse
    {
        try {
            // Validate the request
            $validator = Validator::make($request->all(), [
                'employees' => 'required|array',
                'employees.*.punch_code_id' => 'required|string',
                'employees.*.name' => 'required|string',
                'employees.*.device_ip' => 'nullable|string',
                'employees.*.device_type' => 'nullable|string|in:IN,OUT',
                'employees.*.is_active' => 'boolean',
                'sync_timestamp' => 'required|date',
                'source' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 400);
            }

            $employees = $request->input('employees');
            $syncTimestamp = $request->input('sync_timestamp');
            $source = $request->input('source');
            
            $savedCount = 0;
            $updatedCount = 0;
            $errorCount = 0;

            Log::info("ZKTeco Sync: Received " . count($employees) . " employee records from {$source}");

            foreach ($employees as $employee) {
                try {
                    $existingEmployee = DeviceEmployee::where('punch_code_id', $employee['punch_code_id'])->first();

                    if (!$existingEmployee) {
                        DeviceEmployee::create([
                            'punch_code_id' => $employee['punch_code_id'],
                            'name' => $employee['name'],
                            'email' => $employee['email'] ?? null,
                            'department' => $employee['department'] ?? null,
                            'position' => $employee['position'] ?? null,
                            'is_active' => $employee['is_active'] ?? true,
                            'device_ip' => $employee['device_ip'] ?? null,
                            'device_type' => $employee['device_type'] ?? null,
                        ]);
                        $savedCount++;
                    } else {
                        // Update existing employee
                        $existingEmployee->update([
                            'name' => $employee['name'],
                            'email' => $employee['email'] ?? null,
                            'department' => $employee['department'] ?? null,
                            'position' => $employee['position'] ?? null,
                            'is_active' => $employee['is_active'] ?? true,
                            'device_ip' => $employee['device_ip'] ?? null,
                            'device_type' => $employee['device_type'] ?? null,
                        ]);
                        $updatedCount++;
                    }
                } catch (\Exception $e) {
                    Log::error("ZKTeco Sync: Error saving employee record: " . $e->getMessage());
                    $errorCount++;
                }
            }

            Log::info("ZKTeco Sync: Saved {$savedCount} new employees, updated {$updatedCount} existing, {$errorCount} errors");

            ZktecoSyncLog::create([
                'sync_type' => ZktecoSyncLog::TYPE_EMPLOYEES,
                'synced_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Employee data synced successfully',
                'data' => [
                    'new_employees' => $savedCount,
                    'updated_employees' => $updatedCount,
                    'errors' => $errorCount,
                    'total_received' => count($employees)
                ]
            ]);

        } catch (\Exception $e) {
            Log::error("ZKTeco Sync: Fatal error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Internal server error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sync monthly attendance data from ZKTeco project
     */
    public function syncMonthlyAttendance(Request $request): JsonResponse
    {
        try {
            // Validate the request
            $validator = Validator::make($request->all(), [
                'monthly_attendance_records' => 'required|array',
                'monthly_attendance_records.*.punch_code' => 'required|string',
                'monthly_attendance_records.*.device_ip' => 'required|string',
                'monthly_attendance_records.*.device_type' => 'required|string|in:IN,OUT',
                'monthly_attendance_records.*.punch_time' => 'required|date',
                'monthly_attendance_records.*.punch_type' => 'nullable|string',
                'monthly_attendance_records.*.verify_mode' => 'nullable|integer',
                'monthly_attendance_records.*.is_processed' => 'boolean',
                'sync_timestamp' => 'required|date',
                'source' => 'required|string',
                'month' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 400);
            }

            $monthlyRecords = $request->input('monthly_attendance_records');
            $syncTimestamp = $request->input('sync_timestamp');
            $source = $request->input('source');
            $month = $request->input('month');
            
            $savedCount = 0;
            $duplicateCount = 0;
            $errorCount = 0;

            Log::info("ZKTeco Sync: Received " . count($monthlyRecords) . " monthly attendance records from {$source} for month: {$month}");

            foreach ($monthlyRecords as $record) {
                try {
                    // Use updateOrCreate to handle duplicates gracefully
                    // This matches the unique constraint: (punch_code, device_ip, punch_time)
                    $attendance = DeviceAttendance::updateOrCreate(
                        [
                            'punch_code' => $record['punch_code'],
                            'device_ip' => $record['device_ip'],
                            'punch_time' => Carbon::parse($record['punch_time']),
                        ],
                        [
                            'device_type' => $record['device_type'],
                            'punch_type' => $record['punch_type'] ?? null,
                            'verify_mode' => $record['verify_mode'] ?? null,
                            'is_processed' => $record['is_processed'] ?? false,
                            'status' => 'On Time', // Default status for monthly attendance
                            'sync_timestamp' => Carbon::parse($syncTimestamp),
                        ]
                    );
                    
                    // Check if it was newly created or updated
                    if ($attendance->wasRecentlyCreated) {
                        $savedCount++;
                    } else {
                        $duplicateCount++;
                    }
                } catch (\Exception $e) {
                    Log::error("ZKTeco Sync: Error saving monthly attendance record: " . $e->getMessage());
                    $errorCount++;
                }
            }

            Log::info("ZKTeco Sync: Saved {$savedCount} monthly records, {$duplicateCount} duplicates, {$errorCount} errors for month: {$month}");

            ZktecoSyncLog::create([
                'sync_type' => ZktecoSyncLog::TYPE_MONTHLY_ATTENDANCE,
                'synced_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Monthly attendance data synced successfully',
                'data' => [
                    'saved_records' => $savedCount,
                    'duplicates_skipped' => $duplicateCount,
                    'errors' => $errorCount,
                    'total_received' => count($monthlyRecords),
                    'month' => $month
                ]
            ]);

        } catch (\Exception $e) {
            Log::error("ZKTeco Sync: Fatal error in monthly attendance sync: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Internal server error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get sync status
     */
    public function getSyncStatus(): JsonResponse
    {
        try {
            $totalEmployees = DeviceEmployee::count();
            $totalAttendance = DeviceAttendance::count();
            $lastSync = DeviceAttendance::latest('sync_timestamp')->first();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'total_employees' => $totalEmployees,
                    'total_attendance_records' => $totalAttendance,
                    'last_sync' => $lastSync ? $lastSync->sync_timestamp : null,
                    'status' => 'active'
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching sync status',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}