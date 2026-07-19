<?php

namespace App\Providers;

use App\Services\LicenseVerifier;
use App\Services\PackageApiService;
use App\Services\PackageInstallerService;
use Illuminate\Support\ServiceProvider;

class PackageSystemServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PackageApiService::class);
        $this->app->singleton(PackageInstallerService::class);
        $this->app->singleton(LicenseVerifier::class);
    }

    public function boot(): void
    {
        // بارگذاری فایل‌های پروژه
        $this->mergeConfigFrom(__DIR__ . '/../../config/packages.php', 'packages');

        // در صورت انتشار فایل‌ها در پروژه اصلی، فایل routes بارگذاری شود
        $routesFile = base_path('routes/packages.php');
        if (file_exists($routesFile)) {
            $this->loadRoutesFrom($routesFile);
        }

        // بارگذاری views
        $this->loadViewsFrom(base_path('resources/views/back/packages'), 'packages');
    }
}
