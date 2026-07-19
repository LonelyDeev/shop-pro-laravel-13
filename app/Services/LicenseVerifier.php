<?php

namespace App\Services;

use App\Models\InstalledModule;
use RuntimeException;

class LicenseVerifier
{
    public function __construct(private PackageApiService $api) {}

    /**
     * بررسی اعتبار لایسنس یک ماژول نصب‌شده
     * Returns: ['valid' => bool, 'expires_at' => ?string, 'message' => ?string]
     */
    public function verify(InstalledModule $installed): array
    {
        if (!$installed->license_key) {
            return [
                'valid'      => false,
                'message'    => 'ماژول فاقد لایسنس است.',
                'expires_at' => null,
            ];
        }

        try {
            $result = $this->api->verifyLicense($installed->slug, $installed->license_key);
        } catch (RuntimeException $e) {
            // اگر API در دسترس نبود، از تاریخ محلی استفاده می‌کنیم
            return [
                'valid'      => !$installed->isExpired(),
                'expires_at' => $installed->license_expires_at?->toDateTimeString(),
                'message'    => 'بررسی آنلاین ناموفق بود. وضعیت محلی بررسی شد.',
                'offline'    => true,
            ];
        }

        return [
            'valid'      => $result['valid'] ?? false,
            'expires_at' => $result['expires_at'] ?? null,
            'message'    => $result['message'] ?? null,
        ];
    }

    /**
     * بررسی گروهی همه ماژول‌های نصب‌شده (مثلاً با Schedule روزانه)
     */
    public function verifyAll(): array
    {
        $results = [];
        $modules = InstalledModule::whereNotNull('license_key')->get();

        foreach ($modules as $module) {
            $result = $this->verify($module);
            $results[$module->slug] = $result;

            // آپدیت تاریخ انقضا در صورت دریافت از API
            if (!empty($result['expires_at']) && $result['expires_at'] !== $module->license_expires_at?->toDateTimeString()) {
                $module->update(['license_expires_at' => $result['expires_at']]);
            }
        }

        return $results;
    }
}
