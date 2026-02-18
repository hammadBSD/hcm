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
            $lastSync = ZktecoSyncLog::latest('synced_at')->first();
            $view->with('lastZktecoSync', $lastSync?->synced_at);
        });
    }
}
