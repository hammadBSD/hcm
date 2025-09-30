<?php

namespace App\Livewire\Settings;

use App\Models\DeviceAttendance;
use App\Models\DeviceEmployee;
use App\Services\ZKTecoApiService;
use Livewire\Component;
use Carbon\Carbon;

class ApiMonitor extends Component
{
    public $apiStatus = [];
    public $syncStats = [];
    public $recentActivity = [];
    public $isRefreshing = false;

    public function mount()
    {
        $this->loadApiStatus();
        $this->loadSyncStats();
        $this->loadRecentActivity();
    }

    public function refreshData()
    {
        $this->isRefreshing = true;
        
        $this->loadApiStatus();
        $this->loadSyncStats();
        $this->loadRecentActivity();
        
        $this->isRefreshing = false;
        
        session()->flash('message', 'API status refreshed successfully!');
    }

    protected function loadApiStatus()
    {
        try {
            $apiService = new ZKTecoApiService();
            $result = $apiService->testConnection();
            
            $this->apiStatus = [
                'connection' => $result['success'] ? 'Connected' : 'Disconnected',
                'status' => $result['success'] ? 'success' : 'error',
                'message' => $result['message'] ?? $result['error'] ?? 'Unknown status',
                'last_check' => now()->format('Y-m-d H:i:s'),
                'api_url' => config('zkteco.api_url', 'Not configured'),
                'rate_limit' => config('zkteco.rate_limit', 100),
                'timeout' => config('zkteco.timeout', 30)
            ];
        } catch (\Exception $e) {
            $this->apiStatus = [
                'connection' => 'Error',
                'status' => 'error',
                'message' => 'Failed to connect: ' . $e->getMessage(),
                'last_check' => now()->format('Y-m-d H:i:s'),
                'api_url' => config('zkteco.api_url', 'Not configured'),
                'rate_limit' => config('zkteco.rate_limit', 100),
                'timeout' => config('zkteco.timeout', 30)
            ];
        }
    }

    protected function loadSyncStats()
    {
        $totalEmployees = DeviceEmployee::count();
        $totalAttendance = DeviceAttendance::count();
        $unprocessedAttendance = DeviceAttendance::where('is_processed', false)->count();
        $lastSync = DeviceAttendance::latest('sync_timestamp')->first();

        $this->syncStats = [
            'total_employees' => $totalEmployees,
            'total_attendance_records' => $totalAttendance,
            'unprocessed_records' => $unprocessedAttendance,
            'last_sync' => $lastSync ? $lastSync->sync_timestamp : null,
            'processed_percentage' => $totalAttendance > 0 ? round((($totalAttendance - $unprocessedAttendance) / $totalAttendance) * 100, 2) : 0
        ];
    }

    protected function loadRecentActivity()
    {
        $recentAttendance = DeviceAttendance::latest('punch_time')
            ->limit(10)
            ->get()
            ->map(function ($record) {
                return [
                    'type' => 'attendance',
                    'punch_code' => $record->punch_code,
                    'device_ip' => $record->device_ip,
                    'punch_time' => $record->punch_time,
                    'device_type' => $record->device_type,
                    'is_processed' => $record->is_processed
                ];
            });

        $this->recentActivity = $recentAttendance->toArray();
    }

    public function render()
    {
        return view('livewire.settings.api-monitor');
    }
}
