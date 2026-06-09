<?php
// app/Helpers/DeviceHelper.php

namespace App\Helpers;

use Illuminate\Http\Request;

class DeviceHelper
{
    public static function detect(Request $request)
    {
        $userAgent = $request->userAgent();

        // تشخیص نوع دستگاه
        $deviceType = 'desktop';
        if (preg_match('/(mobile|android|iphone|ipod|blackberry|windows phone)/i', $userAgent)) {
            $deviceType = 'mobile';
        }
        if (preg_match('/(ipad|tablet)/i', $userAgent)) {
            $deviceType = 'tablet';
        }

        // تشخیص مرورگر
        $browser = 'نامشخص';
        if (strpos($userAgent, 'Chrome') !== false && strpos($userAgent, 'Edg') === false) {
            $browser = 'Chrome';
        } elseif (strpos($userAgent, 'Firefox') !== false) {
            $browser = 'Firefox';
        } elseif (strpos($userAgent, 'Safari') !== false && strpos($userAgent, 'Chrome') === false) {
            $browser = 'Safari';
        } elseif (strpos($userAgent, 'Edg') !== false) {
            $browser = 'Edge';
        } elseif (strpos($userAgent, 'Opera') !== false || strpos($userAgent, 'OPR') !== false) {
            $browser = 'Opera';
        }

        // تشخیص سیستم عامل
        $platform = 'نامشخص';
        if (strpos($userAgent, 'Windows') !== false) {
            $platform = 'Windows';
        } elseif (strpos($userAgent, 'Mac') !== false) {
            $platform = 'macOS';
        } elseif (strpos($userAgent, 'Linux') !== false && strpos($userAgent, 'Android') === false) {
            $platform = 'Linux';
        } elseif (strpos($userAgent, 'Android') !== false) {
            $platform = 'Android';
        } elseif (strpos($userAgent, 'iPhone') !== false || strpos($userAgent, 'iPad') !== false) {
            $platform = 'iOS';
        }

        // نام دستگاه (ساده)
        $deviceName = null;
        if ($deviceType == 'mobile') {
            if (strpos($userAgent, 'iPhone') !== false) $deviceName = 'iPhone';
            elseif (strpos($userAgent, 'Samsung') !== false) $deviceName = 'Samsung';
            elseif (strpos($userAgent, 'Xiaomi') !== false) $deviceName = 'Xiaomi';
            elseif (strpos($userAgent, 'Huawei') !== false) $deviceName = 'Huawei';
            else $deviceName = 'موبایل';
        } elseif ($deviceType == 'tablet') {
            if (strpos($userAgent, 'iPad') !== false) $deviceName = 'iPad';
            else $deviceName = 'تبلت';
        } else {
            $deviceName = 'کامپیوتر';
        }

        return [
            'device_name' => $deviceName,
            'device_type' => $deviceType,
            'browser' => $browser,
            'platform' => $platform,
            'user_agent' => $userAgent,
            'ip_address' => $request->ip(),
        ];
    }
}
