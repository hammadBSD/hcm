<?php

namespace App\Providers;

use App\Models\ZktecoSyncLog;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('dashboard', function ($view) {
            $lastSync = ZktecoSyncLog::where('sync_type', ZktecoSyncLog::TYPE_MONTHLY_ATTENDANCE)
                ->orderByDesc('synced_at')
                ->first();
            $syncedAt = $lastSync?->synced_at;
            if ($syncedAt) {
                $syncedAt = $syncedAt->copy()->setTimezone(config('app.timezone'));
            }
            $view->with('lastZktecoSync', $syncedAt);
        });
    }
}
