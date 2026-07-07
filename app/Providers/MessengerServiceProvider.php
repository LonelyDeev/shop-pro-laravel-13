<?php

namespace App\Providers;

use App\Contracts\BaleMessengerContract;
use App\Services\Messenger\BaleMessenger;
use Illuminate\Support\ServiceProvider;

class MessengerServiceProvider extends ServiceProvider
{
    /**
     * ثبت سرویس‌های پیام‌رسان
     */
    public function register()
    {
        // بایند کردن کانتڑکت به پیاده‌سازی بله
        $this->app->bind(BaleMessengerContract::class, function ($app, $params) {
            $mobile = $params['mobile'] ?? null;
            $data   = $params['data'] ?? [];
            return new BaleMessenger($mobile, $data);
        });
    }
}
