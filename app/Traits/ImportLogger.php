<?php

namespace App\Traits;

use App\Models\Admin;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Models\Activity;

trait ImportLogger
{
    /**
     * لاگ ارسال به صف
     */
    protected function logQueueDispatch($type, $filePath, $adminId = null)
    {
        $admin = $adminId ? Admin::find($adminId) : auth('adminPanel')->user();

        $adminName = $admin->full_name ?? 'نامشخص';

        activity()
            ->causedBy($admin)
            ->event('queue')
            ->withProperties([
                'type' => $type, // products, posts, users
                'file' => $filePath,
                'ip' => request()->ip(),
            ])
            ->log("مدیر {$adminName} فایل اکسل {$type} را برای پردازش به صف ارسال کرد.");
    }

    /**
     * لاگ پردازش موفق
     */
    protected function logImportSuccess($type, $report, $filePath, $adminId)
    {
        $admin = Admin::find($adminId);
        if (!$admin) {
            return;
        }

        $adminName = $admin->full_name ?? 'نامشخص';
        $successCount = $report['success_count'] ?? 0;
        $failCount = $report['fail_count'] ?? 0;
        $totalCount = $report['total_count'] ?? 0;

        // استخراج عناوین برای نمایش در لاگ (حداکثر ۵ مورد)
        $titles = [];
        if (!empty($report['failed_rows'])) {
            $titles = array_column($report['failed_rows'], 'title');
            $titles = array_slice($titles, 0, 5);
        }

        $properties = [
            'type' => $type,
            'success_count' => $successCount,
            'fail_count' => $failCount,
            'total_count' => $totalCount,
            'file' => $filePath,
            'has_errors' => $failCount > 0,
            'sample_titles' => $titles,
        ];

        if ($failCount > 0) {
            $properties['errors'] = $report['failed_rows'] ?? [];
        }

        activity()
            ->causedBy($admin)
            ->event('import')
            ->withProperties($properties)
            ->log("مدیر {$adminName} فایل اکسل {$type} را با {$successCount} موفق و {$failCount} ناموفق (از کل {$totalCount}) وارد کرد.");
    }

    /**
     * لاگ شکست Job (بعد از ۱۰ بار تلاش)
     */
    protected function logImportFailed($type, $filePath, $adminId, $errorMessage)
    {
        $admin = Admin::find($adminId);
        if (!$admin) {
            return;
        }

        $adminName = $admin->full_name ?? 'نامشخص';

        activity()
            ->causedBy($admin)
            ->event('failed')
            ->withProperties([
                'type' => $type,
                'file' => $filePath,
                'error' => $errorMessage,
            ])
            ->log("مدیر {$adminName} فایل اکسل {$type} پس از ۱۰ بار تلاش ناموفق بود: {$errorMessage}");
    }
}
