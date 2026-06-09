<?php
// app/Providers/HolidayServiceProvider.php

namespace App\Providers;

use App\Services\HolidayService;
use Illuminate\Support\ServiceProvider;

class HolidayServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(HolidayService::class, function ($app) {
            return new HolidayService();
        });
    }

    public function boot()
    {
        //
    }
}
