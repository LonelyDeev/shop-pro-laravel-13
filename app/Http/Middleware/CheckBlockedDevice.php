<?php
// app/Http/Middleware/CheckBlockedDevice.php

namespace App\Http\Middleware;

use App\Models\BlockedDevice;
use App\Models\AdminSession;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class CheckBlockedDevice
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::guard('adminPanel')->check()) {
            $admin = Auth::guard('adminPanel')->user();
            $sessionId = Session::getId();
            $ipAddress = $request->ip();
            $userAgent = $request->userAgent();

            // تولید اثر انگشت
            $deviceFingerprint = $this->generateDeviceFingerprint($userAgent);
            $browserFingerprint = $this->generateBrowserFingerprint($userAgent);

            // بررسی بلاک شدن
            $blocked = BlockedDevice::where('admin_id', $admin->id)
                ->where(function($q) use ($sessionId, $ipAddress, $deviceFingerprint, $browserFingerprint) {
                    $q->where('session_id', $sessionId)
                        ->orWhere('ip_address', $ipAddress)
                        ->orWhere('device_fingerprint', $deviceFingerprint)
                        ->orWhere('browser_fingerprint', $browserFingerprint);
                })
                ->where(function($q) {
                    $q->where('is_permanent', true)
                        ->orWhere('blocked_until', '>', now());
                })
                ->first();

            if ($blocked) {
                // حذف نشست فعلی
                AdminSession::where('session_id', $sessionId)->delete();

                Auth::guard('adminPanel')->logout();
                Session::flush();

                $blockTypeText = $blocked->block_type_text;
                $message = "دسترسی شما به دلیل {.$blocked->reason ?? 'بلاک شدن'} مسدود شده است. (نوع بلاک: {$blockTypeText})";

                if (!$blocked->is_permanent && $blocked->blocked_until) {
                    $message .= " تا تاریخ " . jdate($blocked->blocked_until)->format('d F Y H:i');
                }

                return redirect()->route('admin.login')->withErrors(['blocked' => $message]);
            }
        }

        return $next($request);
    }

    private function generateDeviceFingerprint($userAgent)
    {
        $browser = $this->getBrowserName($userAgent);
        $platform = $this->getPlatformName($userAgent);
        $deviceType = $this->getDeviceType($userAgent);

        return md5($browser . $platform . $deviceType);
    }

    private function generateBrowserFingerprint($userAgent)
    {
        $browser = $this->getBrowserName($userAgent);
        $browserVersion = $this->getBrowserVersion($userAgent);

        return md5($browser . $browserVersion);
    }

    private function getBrowserName($userAgent)
    {
        if (strpos($userAgent, 'Chrome') !== false && strpos($userAgent, 'Edg') === false) return 'Chrome';
        if (strpos($userAgent, 'Firefox') !== false) return 'Firefox';
        if (strpos($userAgent, 'Safari') !== false && strpos($userAgent, 'Chrome') === false) return 'Safari';
        if (strpos($userAgent, 'Edg') !== false) return 'Edge';
        if (strpos($userAgent, 'Opera') !== false || strpos($userAgent, 'OPR') !== false) return 'Opera';
        return 'Unknown';
    }

    private function getBrowserVersion($userAgent)
    {
        if (preg_match('/Chrome\/(\d+)/', $userAgent, $matches)) return $matches[1];
        if (preg_match('/Firefox\/(\d+)/', $userAgent, $matches)) return $matches[1];
        if (preg_match('/Edg\/(\d+)/', $userAgent, $matches)) return $matches[1];
        if (preg_match('/Safari\/(\d+)/', $userAgent, $matches)) return $matches[1];
        if (preg_match('/OPR\/(\d+)/', $userAgent, $matches)) return $matches[1];
        return 'unknown';
    }

    private function getPlatformName($userAgent)
    {
        if (strpos($userAgent, 'Windows') !== false) return 'Windows';
        if (strpos($userAgent, 'Mac') !== false) return 'macOS';
        if (strpos($userAgent, 'Linux') !== false && strpos($userAgent, 'Android') === false) return 'Linux';
        if (strpos($userAgent, 'Android') !== false) return 'Android';
        if (strpos($userAgent, 'iPhone') !== false || strpos($userAgent, 'iPad') !== false) return 'iOS';
        return 'Unknown';
    }

    private function getDeviceType($userAgent)
    {
        if (preg_match('/(mobile|android|iphone|ipod|blackberry|windows phone)/i', $userAgent)) return 'mobile';
        if (preg_match('/(ipad|tablet)/i', $userAgent)) return 'tablet';
        return 'desktop';
    }
}
