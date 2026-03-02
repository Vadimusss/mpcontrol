<?php

namespace App\Providers;

use App\Events\JobFailed;
use App\Services\Integrations\GoogleSheetsService;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Sleep;
use Illuminate\Support\Facades\Log;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(GoogleSheetsService::class, function () {
            $configPath = storage_path('app/credentials/google-service-account.json');
            if (!file_exists($configPath)) {
                throw new \RuntimeException('Google Service Account file not found at: ' . $configPath);
            }
            return new GoogleSheetsService(json_decode(file_get_contents($configPath), true));
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);
    }
}
