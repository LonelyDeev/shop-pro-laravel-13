<?php
// app/Http/Controllers/Back/AdminSessionController.php

namespace App\Http\Controllers\Back;

use App\Http\Controllers\Controller;
use App\Models\AdminSession;
use App\Models\BlockedDevice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;

class AdminSessionController extends Controller
{
    /**
     * نمایش لیست نشست‌های فعال ادمین‌ها
     */
    public function index()
    {
        $sessions = AdminSession::with('admin')
            ->where('admin_id', auth('adminPanel')->user()->id)
            ->where('is_active', true)
            ->orderBy('last_activity', 'desc')
            ->paginate(20);

        if(auth('adminPanel')->user()->isCreator()){
            $sessions = AdminSession::with('admin')
                ->where('is_active', true)
                ->orderBy('last_activity', 'desc')
                ->paginate(20);
        }

        $currentSessionId = Session::getId();
        $currentAdminId = Auth::guard('adminPanel')->id();

        return view('back.sessions.index', compact('sessions', 'currentSessionId', 'currentAdminId'));
    }

    /**
     * حذف یک نشست خاص (خروج اجباری)
     */
    public function destroy($id)
    {
        $this->authorize('sessions.exit');
        $session = AdminSession::findOrFail($id);

        // جلوگیری از حذف نشست فعلی خود ادمین
        if ($session->session_id === Session::getId()) {
            return response()->json([
                'success' => false,
                'message' => 'نمی‌توانید نشست فعلی خود را حذف کنید'
            ], 400);
        }

        $adminName = auth('adminPanel')->user()->full_name;
        $targetAdminName = $session->admin->full_name ?? 'نامشخص';
        $deviceInfo = $session->device_name ?? 'دستگاه نامشخص';
        $ipAddress = $session->ip_address ?? 'نامشخص';

        // حذف نشست از دیتابیس
        $session->delete();

        // حذف نشست از جدول sessions لاراول
        try {
            DB::table('sessions')->where('id', $session->session_id)->delete();
        } catch (\Exception $e) {
            // اگر از file driver استفاده می‌شود، نیازی به این کار نیست
        }

        // ثبت لاگ دستی
        activity()
            ->withProperties([
                'action' => 'logout_session',
                'target_admin' => $targetAdminName,
                'target_admin_id' => $session->admin_id,
                'device_info' => $deviceInfo,
                'ip_address' => $ipAddress,
                'session_id' => $session->session_id
            ])
            ->log("مدیر {$adminName} نشست {$targetAdminName} را از دستگاه {$deviceInfo} خارج کرد");

        return response()->json([
            'success' => true,
            'message' => 'نشست مورد نظر با موفقیت حذف شد'
        ]);
    }

    /**
     * حذف تمام نشست‌های یک ادمین (به جز نشست فعلی)
     */
    public function destroyAllAdminSessions($adminId)
    {
        if(!auth('adminPanel')->user()->isCreator()){
            abort(403);
        }

        $targetAdmin = \App\Models\Admin::find($adminId);
        $adminName = auth('adminPanel')->user()->full_name;
        $targetAdminName = $targetAdmin->full_name ?? 'نامشخص';

        $sessions = AdminSession::where('admin_id', $adminId)
            ->where('session_id', '!=', Session::getId())
            ->get();

        $deletedCount = 0;
        $devicesList = [];

        foreach ($sessions as $session) {
            $devicesList[] = $session->device_name ?? 'دستگاه نامشخص';
            $session->delete();
            try {
                DB::table('sessions')->where('id', $session->session_id)->delete();
            } catch (\Exception $e) {}
            $deletedCount++;
        }

        // ثبت لاگ دستی
        activity()
            ->withProperties([
                'action' => 'logout_all_sessions',
                'target_admin' => $targetAdminName,
                'target_admin_id' => $adminId,
                'devices_count' => $deletedCount,
                'devices_list' => $devicesList
            ])
            ->log("مدیر {$adminName} تمام نشست‌های {$targetAdminName} ({$deletedCount} نشست) را خارج کرد");

        return response()->json([
            'success' => true,
            'message' => "{$deletedCount} نشست با موفقیت حذف شد",
            'deleted_count' => $deletedCount
        ]);
    }

    /**
     * حذف تمام نشست‌های غیرفعال (بیش از 30 روز)
     */
    public function clearInactive()
    {
        if(!auth('adminPanel')->user()->isCreator()){
            abort(403);
        }

        $adminName = auth('adminPanel')->user()->full_name;
        $count = AdminSession::where('last_activity', '<', now()->subDays(30))->delete();

        // ثبت لاگ دستی
        if ($count > 0) {
            activity()
                ->withProperties([
                    'action' => 'clear_inactive_sessions',
                    'deleted_count' => $count,
                    'days_threshold' => 30
                ])
                ->log("مدیر {$adminName} تعداد {$count} نشست غیرفعال را حذف کرد");
        }

        return response()->json([
            'success' => true,
            'message' => "{$count} نشست غیرفعال حذف شد",
            'deleted_count' => $count
        ]);
    }

    /**
     * خروج اجباری از تمام دستگاه‌های ادمین فعلی (به جز دستگاه فعلی)
     */
    public function logoutOtherDevices()
    {
        if(!auth('adminPanel')->user()->isCreator()){
            abort(403);
        }

        $adminId = Auth::guard('adminPanel')->id();
        $adminName = auth('adminPanel')->user()->full_name;

        $sessions = AdminSession::where('admin_id', $adminId)
            ->where('session_id', '!=', Session::getId())
            ->get();

        $deletedCount = 0;

        foreach ($sessions as $session) {
            $session->delete();
            try {
                DB::table('sessions')->where('id', $session->session_id)->delete();
            } catch (\Exception $e) {}
            $deletedCount++;
        }

        // ثبت لاگ دستی
        activity()
            ->withProperties([
                'action' => 'logout_other_devices',
                'deleted_count' => $deletedCount
            ])
            ->log("مدیر {$adminName} از {$deletedCount} دستگاه دیگر خود خارج شد");

        return response()->json([
            'success' => true,
            'message' => "{$deletedCount} دستگاه با موفقیت خارج شد",
            'deleted_count' => $deletedCount
        ]);
    }

    public function blockedList()
    {
        $this->authorize('sessions.blocked');
        $blockedDevices = BlockedDevice::with('admin')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('back.sessions.blocked', compact('blockedDevices'));
    }

    public function blockSession($id, Request $request)
    {
        $session = AdminSession::findOrFail($id);

        // جلوگیری از بلاک نشست فعلی خود کاربر
        if ($session->session_id === Session::getId()) {
            return response()->json([
                'success' => false,
                'message' => 'نمی‌توانید نشست فعلی خود را بلاک کنید'
            ], 400);
        }

        $request->validate([
            'reason' => 'nullable|string|max:500',
            'duration' => 'nullable|in:permanent,1day,1week,1month',
            'block_type' => 'required|in:session,ip,device,browser,all'
        ]);

        $adminName = auth('adminPanel')->user()->full_name;
        $targetAdminName = $session->admin->full_name ?? 'نامشخص';
        $blockType = $request->block_type;
        $duration = $request->duration ?? 'permanent';
        $reason = $request->reason ?? 'بدون دلیل';

        // محاسبه زمان انقضا
        $blockedUntil = null;
        $isPermanent = false;

        if ($duration === 'permanent') {
            $isPermanent = true;
            $durationText = 'دائمی';
        } elseif ($duration === '1day') {
            $blockedUntil = now()->addDay();
            $durationText = '1 روز';
        } elseif ($duration === '1week') {
            $blockedUntil = now()->addWeek();
            $durationText = '1 هفته';
        } elseif ($duration === '1month') {
            $blockedUntil = now()->addMonth();
            $durationText = '1 ماه';
        } else {
            $durationText = 'نامشخص';
        }

        // تولید اثر انگشت
        $deviceFingerprint = $this->generateDeviceFingerprint($session->user_agent);
        $browserFingerprint = $this->generateBrowserFingerprint($session->user_agent);

        $blockedItems = [];

        // بلاک بر اساس نوع انتخاب شده
        if (in_array($blockType, ['session', 'all'])) {
            BlockedDevice::create([
                'admin_id' => $session->admin_id,
                'session_id' => $session->session_id,
                'block_type' => 'session',
                'reason' => $reason,
                'blocked_until' => $blockedUntil,
                'is_permanent' => $isPermanent
            ]);
            $blockedItems[] = 'نشست';
        }

        if (in_array($blockType, ['ip', 'all'])) {
            BlockedDevice::create([
                'admin_id' => $session->admin_id,
                'ip_address' => $session->ip_address,
                'block_type' => 'ip',
                'reason' => $reason,
                'blocked_until' => $blockedUntil,
                'is_permanent' => $isPermanent
            ]);
            $blockedItems[] = 'آیپی';
        }

        if (in_array($blockType, ['device', 'all'])) {
            BlockedDevice::create([
                'admin_id' => $session->admin_id,
                'device_fingerprint' => $deviceFingerprint,
                'block_type' => 'device',
                'reason' => $reason,
                'blocked_until' => $blockedUntil,
                'is_permanent' => $isPermanent
            ]);
            $blockedItems[] = 'دستگاه';
        }

        if (in_array($blockType, ['browser', 'all'])) {
            BlockedDevice::create([
                'admin_id' => $session->admin_id,
                'browser_fingerprint' => $browserFingerprint,
                'block_type' => 'browser',
                'reason' => $reason,
                'blocked_until' => $blockedUntil,
                'is_permanent' => $isPermanent
            ]);
            $blockedItems[] = 'مرورگر';
        }

        // حذف نشست فعلی
        $session->delete();
        try {
            DB::table('sessions')->where('id', $session->session_id)->delete();
        } catch (\Exception $e) {}

        // ثبت لاگ دستی برای بلاک
        $blockedItemsText = implode('، ', $blockedItems);
        $deviceInfo = $session->device_name ?? 'دستگاه نامشخص';

        activity()
            ->withProperties([
                'action' => 'block_session',
                'target_admin' => $targetAdminName,
                'target_admin_id' => $session->admin_id,
                'block_type' => $blockType,
                'blocked_items' => $blockedItems,
                'duration' => $durationText,
                'reason' => $reason,
                'device_info' => $deviceInfo,
                'ip_address' => $session->ip_address,
                'session_id' => $session->session_id,
                'blocked_until' => $blockedUntil
            ])
            ->log("مدیر {$adminName} دسترسی {$targetAdminName} را از نوع {$blockedItemsText} به مدت {$durationText} مسدود کرد");

        return response()->json([
            'success' => true,
            'message' => 'بلاک با موفقیت انجام شد'
        ]);
    }

    /**
     * تولید اثر انگشت دستگاه
     */
    private function generateDeviceFingerprint($userAgent)
    {
        $browser = $this->getBrowserName($userAgent);
        $platform = $this->getPlatformName($userAgent);
        $deviceType = $this->getDeviceType($userAgent);

        return md5($browser . $platform . $deviceType);
    }

    /**
     * تولید اثر انگشت مرورگر
     */
    private function generateBrowserFingerprint($userAgent)
    {
        $browser = $this->getBrowserName($userAgent);
        $browserVersion = $this->getBrowserVersion($userAgent);

        return md5($browser . $browserVersion);
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

    private function getBrowserName($userAgent)
    {
        if (strpos($userAgent, 'Chrome') !== false && strpos($userAgent, 'Edg') === false) return 'Chrome';
        if (strpos($userAgent, 'Firefox') !== false) return 'Firefox';
        if (strpos($userAgent, 'Safari') !== false && strpos($userAgent, 'Chrome') === false) return 'Safari';
        if (strpos($userAgent, 'Edg') !== false) return 'Edge';
        if (strpos($userAgent, 'Opera') !== false || strpos($userAgent, 'OPR') !== false) return 'Opera';
        return 'Unknown';
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

    public function unblockDevice($id)
    {
        $this->authorize('sessions.blocked');
        $blocked = BlockedDevice::findOrFail($id);

        $adminName = auth('adminPanel')->user()->full_name;
        $blockType = $blocked->block_type;
        $targetAdminName = $blocked->admin->full_name ?? 'نامشخص';

        $blocked->delete();

        // ثبت لاگ دستی برای آنبلاک
        activity()
            ->withProperties([
                'action' => 'unblock_device',
                'target_admin' => $targetAdminName,
                'target_admin_id' => $blocked->admin_id,
                'block_type' => $blockType,
                'unblocked_by' => $adminName
            ])
            ->log("مدیر {$adminName} مسدودیت نوع {$blockType} برای {$targetAdminName} را لغو کرد");

        return response()->json([
            'success' => true,
            'message' => 'بلاک دستگاه با موفقیت حذف شد'
        ]);
    }

    public function clearAdminBlocks($adminId)
    {
        $this->authorize('sessions.blocked');

        $targetAdmin = \App\Models\Admin::find($adminId);
        $adminName = auth('adminPanel')->user()->full_name;
        $targetAdminName = $targetAdmin->full_name ?? 'نامشخص';

        $count = BlockedDevice::where('admin_id', $adminId)->delete();

        // ثبت لاگ دستی
        if ($count > 0) {
            activity()
                ->withProperties([
                    'action' => 'clear_admin_blocks',
                    'target_admin' => $targetAdminName,
                    'target_admin_id' => $adminId,
                    'deleted_count' => $count
                ])
                ->log("مدیر {$adminName} تمام {$count} مسدودیت {$targetAdminName} را پاک کرد");
        }

        return response()->json([
            'success' => true,
            'message' => "{$count} بلاک با موفقیت حذف شد"
        ]);
    }
}
