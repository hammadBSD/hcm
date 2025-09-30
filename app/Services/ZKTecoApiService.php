<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ZKTecoApiService
{
    protected string $baseUrl;
    protected string $apiKey;
    protected int $timeout;
    protected int $rateLimit;

    public function __construct()
    {
        $this->baseUrl = config('zkteco.api_url', 'http://http://hcm.local/api');
        $this->apiKey = config('zkteco.api_key');
        $this->timeout = config('zkteco.timeout', 30);
        $this->rateLimit = config('zkteco.rate_limit', 100);
    }

    /**
     * Sync attendance data from ZKTeco API
     */
    public function syncAttendance(array $attendanceRecords, string $source = 'hr-system'): array
    {
        try {
            $payload = [
                'attendance_records' => $attendanceRecords,
                'sync_timestamp' => now()->toISOString(),
                'source' => $source
            ];

            Log::info("ZKTeco API: Sending attendance sync request", [
                'records_count' => count($attendanceRecords),
                'source' => $source
            ]);

            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ])
                ->post($this->baseUrl . '/zkteco/sync-attendance', $payload);

            if ($response->successful()) {
                $data = $response->json();
                Log::info("ZKTeco API: Attendance sync successful", $data);
                return [
                    'success' => true,
                    'data' => $data['data'] ?? [],
                    'message' => $data['message'] ?? 'Attendance synced successfully'
                ];
            } else {
                Log::error("ZKTeco API: Attendance sync failed", [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                return [
                    'success' => false,
                    'error' => 'API request failed',
                    'status' => $response->status(),
                    'response' => $response->body()
                ];
            }

        } catch (\Exception $e) {
            Log::error("ZKTeco API: Attendance sync exception", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Sync employee data from ZKTeco API
     */
    public function syncEmployees(array $employees, string $source = 'hr-system'): array
    {
        try {
            $payload = [
                'employees' => $employees,
                'sync_timestamp' => now()->toISOString(),
                'source' => $source
            ];

            Log::info("ZKTeco API: Sending employee sync request", [
                'employees_count' => count($employees),
                'source' => $source
            ]);

            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ])
                ->post($this->baseUrl . '/zkteco/sync-employees', $payload);

            if ($response->successful()) {
                $data = $response->json();
                Log::info("ZKTeco API: Employee sync successful", $data);
                return [
                    'success' => true,
                    'data' => $data['data'] ?? [],
                    'message' => $data['message'] ?? 'Employees synced successfully'
                ];
            } else {
                Log::error("ZKTeco API: Employee sync failed", [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                return [
                    'success' => false,
                    'error' => 'API request failed',
                    'status' => $response->status(),
                    'response' => $response->body()
                ];
            }

        } catch (\Exception $e) {
            Log::error("ZKTeco API: Employee sync exception", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get sync status from ZKTeco API
     */
    public function getSyncStatus(): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Accept' => 'application/json'
                ])
                ->get($this->baseUrl . '/zkteco/sync-status');

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'data' => $data['data'] ?? []
                ];
            } else {
                Log::error("ZKTeco API: Status check failed", [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                return [
                    'success' => false,
                    'error' => 'API request failed',
                    'status' => $response->status()
                ];
            }

        } catch (\Exception $e) {
            Log::error("ZKTeco API: Status check exception", [
                'error' => $e->getMessage()
            ]);
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Test API connection
     */
    public function testConnection(): array
    {
        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Accept' => 'application/json'
                ])
                ->get($this->baseUrl . '/ping');

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'API connection successful',
                    'data' => $response->json()
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'API connection failed',
                    'status' => $response->status()
                ];
            }

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Connection exception: ' . $e->getMessage()
            ];
        }
    }
}
