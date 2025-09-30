<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ZKTecoSyncController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Basic ping endpoint
Route::get('/ping', function () {
    return response()->json([
        'status' => 'success',
        'message' => 'HCM API is running',
        'timestamp' => now()->toISOString()
    ]);
});

// ZKTeco sync API routes
Route::prefix('zkteco')->group(function () {
    
    // Sync attendance records from ZKTeco system
    Route::post('/sync-attendance', [ZKTecoSyncController::class, 'syncAttendance']);
    
    // Sync employee records from ZKTeco system
    Route::post('/sync-employees', [ZKTecoSyncController::class, 'syncEmployees']);
    
    // Sync monthly attendance records from ZKTeco system
    Route::post('/sync-monthly-attendance', [ZKTecoSyncController::class, 'syncMonthlyAttendance']);
    
    // Get sync status
    Route::get('/sync-status', [ZKTecoSyncController::class, 'getSyncStatus']);
});
