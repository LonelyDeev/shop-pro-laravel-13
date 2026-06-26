<?php

namespace App\Http\Controllers\Back;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessUpdateJob;
use Carbon\Carbon;
use Codedge\Updater\UpdaterManager;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;
use ZipArchive;

class DeveloperController extends Controller
{
    private $panelUrl;
    private $updateCode;

    public function __construct()
    {
        // این مقادیر را از تنظیمات یا .env بخوانید
        $this->panelUrl ='https://update.webtpro.ir/api/v1/check-update';
        $this->updateCode =env('SELF_UPDATER_HTTP_PRIVATE_ACCESS_TOKEN',config('self-update.updater_token'));
    }
    public function showSettings()
    {
        $schedule_last_work = option('schedule_run');
        $schedule_run       = false;
        $random_str         = str_random(15);

        if ($schedule_last_work) {
            if (!is_object($schedule_last_work)) {
                $schedule_last_work = Carbon::createFromDate($schedule_last_work);
            }

            $diff = $schedule_last_work->diffInMinutes(now());
            $schedule_run = ($diff <= 2);
        }

        return view('back.developer.settings', compact('schedule_run', 'random_str'));
    }

    public function updateSettings(Request $request)
    {
        $developer_options = $request->except(['SELF_UPDATER_HTTP_PRIVATE_ACCESS_TOKEN']);

        foreach ($developer_options as $option => $value) {
            option_update($option, $value);
        }

        if ($request->app_debug_mode) {
            change_env('APP_DEBUG', 'true');
        } else {
            change_env('APP_DEBUG', 'false');
        }

        change_env('SELF_UPDATER_HTTP_PRIVATE_ACCESS_TOKEN', $request->SELF_UPDATER_HTTP_PRIVATE_ACCESS_TOKEN);

        if ($request->debugbar_enabled) {
            change_env('DEBUGBAR_ENABLED', 'true');
        } else {
            change_env('DEBUGBAR_ENABLED', 'false');
        }

        return response('success');
    }

    public function downApplication(Request $request)
    {
        $request->validate([
            'secret' => 'required|string'
        ]);

        $down_options = $request->except(['secret']);

        foreach ($down_options as $option => $value) {
            option_update($option, $value);
        }

        Artisan::call("down --render='errors::503' --secret='$request->secret'");

        return response()->json(['secret' => $request->secret]);
    }

    public function upApplication()
    {
        Artisan::call("up");

        return response('success');
    }

    public function webpushNotification()
    {
        Artisan::call('webpush:vapid');

        return response('success');
    }

    public function showUpdater()
    {
        // خواندن نسخه از فایل
        $currentVersion = $this->getVersion();
        $versionInstalled = $currentVersion;

        // دریافت وضعیت آپدیت
        $isProcessing = Cache::get('update_processing', false);
        $updateStatus = Cache::get('update_status');
        $updateError = Cache::get('update_error');
        $newVersion = Cache::get('update_version');

        // درخواست به پنل برای چک کردن نسخه جدید
        $isNewVersionAvailable = false;
        $versionAvailable = $currentVersion;

        try {
            $response = Http::timeout(30)->get($this->panelUrl, [
                'token' => $this->updateCode,
                'version' => $currentVersion
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if ($data['update_available'] ?? false) {
                    $isNewVersionAvailable = true;
                    $versionAvailable = $data['version'];
                }
            }
        } catch (Exception $e) {
            // خطا در ارتباط با پنل
        }

        return view('back.developer.updater', compact(
            'versionInstalled',
            'isNewVersionAvailable',
            'versionAvailable',
            'currentVersion',
            'isProcessing',
            'updateStatus',
            'updateError',
            'newVersion'
        ));
    }

    public function updateApplication(Request $request)
    {
        // بررسی اینکه آیا آپدیت در حال اجراست یا قبلاً در صف قرار گرفته
        if (Cache::get('update_processing', false)) {
            return response()->json([
                'status' => 'error',
                'message' => 'آپدیت دیگری در حال اجراست. لطفاً صبر کنید.'
            ], 429);
        }

        // بررسی اینکه آیا Job قبلاً به صف ارسال شده
        if (Cache::get('update_job_dispatched', false)) {
            return response()->json([
                'status' => 'error',
                'message' => 'درخواست بروزرسانی قبلاً به صف ارسال شده است. لطفاً منتظر بمانید.'
            ], 429);
        }

        // بررسی وضعیت موفقیت قبلی
        if (Cache::get('update_status') === 'success') {
            return response()->json([
                'status' => 'error',
                'message' => 'آپدیت قبلاً با موفقیت انجام شده است.'
            ], 400);
        }

        $currentVersion = $this->getVersion();

        // 1. دریافت اطلاعات از پنل برای تایید وجود آپدیت
        $response = Http::timeout(30)->get($this->panelUrl, [
            'token' => $this->updateCode,
            'version' => $currentVersion,
        ]);

        if (!$response->successful() || !$response->json('update_available')) {
            return response()->json([
                'status' => 'error',
                'message' => 'نسخه جدیدی یافت نشد یا دسترسی غیرمجاز است.'
            ], 403);
        }

        $data = $response->json();
        $newVersion = $data['version'];
        $downloadUrl = $data['download_url'];

        // ذخیره اطلاعات آپدیت در کش برای استفاده در Job
        Cache::put('update_processing', true, now()->addHours(2));
        Cache::put('update_queued', true, now()->addHours(2));
        Cache::put('update_status', 'queued', now()->addHours(2));
        Cache::put('update_progress', 0, now()->addHours(2));
        Cache::put('update_step', 'در حال قرار گرفتن در صف...', now()->addHours(2));
        Cache::put('update_version', $newVersion, now()->addHours(2));
        Cache::put('update_job_dispatched', true, now()->addHours(2)); // جلوگیری از ارسال مجدد
        Cache::forget('update_error');
        Cache::forget('update_error_details');

        // ارسال Job به صف
        try {
            $job = new ProcessUpdateJob($this->panelUrl, $this->updateCode, $currentVersion);
            $jobId = dispatch($job);
            Cache::put('update_job_id', $jobId, now()->addHours(2));

            return response()->json([
                'status' => 'queued',
                'message' => 'درخواست بروزرسانی در صف قرار گرفت. لطفاً منتظر بمانید...',
                'version' => $newVersion,
                'job_id' => $jobId
            ]);
        } catch (Exception $e) {
            // در صورت خطا، کش را پاک کن
            Cache::forget('update_processing');
            Cache::forget('update_queued');
            Cache::forget('update_job_dispatched');

            return response()->json([
                'status' => 'error',
                'message' => 'خطا در ارسال درخواست: ' . $e->getMessage()
            ], 500);
        }
    }
    public function updaterAfter()
    {
        try {
            // کش‌ها را پاک کنید
            Artisan::call('cache:clear');
            Artisan::call('config:clear');
            Artisan::call('view:clear');

            // اگر مایگریشن جدیدی دارید اجرا کنید
            // Artisan::call('migrate', ['--force' => true]);

            return response()->json([
                'status' => 'success',
                'message' => 'دستورات پس از بروزرسانی با موفقیت اجرا شدند.'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'خطا در اجرای دستورات: ' . $e->getMessage()
            ], 500);
        }
    }

    // متدهای کمکی
    private function getVersion()
    {
        $versionFile = storage_path('app/version.json');
        if (File::exists($versionFile)) {
            $content = json_decode(File::get($versionFile), true);
            return $content['version'] ?? config('app.version', '1.0.0');
        }
        return config('app.version', '1.0.0');
    }

    private function copyFiles($source, $destination, $backupPath = null)
    {
        $excluded = [
            '.env',
            '.env.example',
            '.git',
            'storage',
            'bootstrap/cache',
            'vendor',
            'node_modules',
        ];

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($files as $file) {
            $relativePath = $files->getSubPathName();

            foreach ($excluded as $ex) {
                if ($relativePath === $ex || str_starts_with($relativePath, $ex . DIRECTORY_SEPARATOR)) {
                    continue 2;
                }
            }

            $targetPath = $destination . DIRECTORY_SEPARATOR . $relativePath;

            if ($file->isDir()) {
                File::ensureDirectoryExists($targetPath, 0755);
            } else {
                if ($backupPath && File::exists($targetPath)) {
                    $backupTarget = $backupPath . DIRECTORY_SEPARATOR . $relativePath;
                    File::ensureDirectoryExists(dirname($backupTarget), 0755);
                    File::copy($targetPath, $backupTarget);
                }

                File::ensureDirectoryExists(dirname($targetPath), 0755);
                File::copy($file->getPathname(), $targetPath);
            }
        }
    }

    private function setVersion($version)
    {
        File::put(
            storage_path('app/version.json'),
            json_encode(['version' => $version])
        );
    }

    public function updateStatus()
    {
        $isProcessing = Cache::get('update_processing', false);
        $progress = Cache::get('update_progress', 0);
        $status = Cache::get('update_status');
        $error = Cache::get('update_error');
        $version = Cache::get('update_version');
        $step = Cache::get('update_step');
        $jobId = Cache::get('update_job_id');

        // وضعیت موفقیت
        if ($status === 'success') {
            Cache::forget('update_job_dispatched'); // پاک کردن فلگ
            return response()->json([
                'status' => 'success',
                'version' => $version,
                'message' => 'بروزرسانی با موفقیت انجام شد',
                'step' => 'کامل شد ✅',
                'progress' => 100
            ]);
        }

        // وضعیت خطا
        if ($status === 'error') {
            Cache::forget('update_job_dispatched'); // پاک کردن فلگ
            return response()->json([
                'status' => 'error',
                'message' => $error ?? 'خطای ناشناخته',
                'details' => Cache::get('update_error_details'),
                'step' => 'خطا ❌',
                'progress' => $progress
            ]);
        }

        // وضعیت در حال پردازش
        if ($isProcessing) {
            return response()->json([
                'status' => 'processing',
                'progress' => $progress,
                'message' => $step ?? 'در حال بروزرسانی...',
                'step' => $step ?? 'در حال بروزرسانی...',
                'job_id' => $jobId
            ]);
        }

        // اگر Job در صف است ولی هنوز شروع نشده
        if (Cache::get('update_queued', false)) {
            return response()->json([
                'status' => 'waiting',
                'message' => 'در انتظار شروع بروزرسانی...',
                'step' => 'در انتظار شروع',
                'progress' => 0,
                'job_id' => $jobId
            ]);
        }

        return response()->json([
            'status' => 'idle'
        ]);
    }

    public function checkUpdate()
    {
        $currentVersion = $this->getVersion();

        try {
            $response = Http::timeout(30)->get($this->panelUrl, [
                'token' => $this->updateCode,
                'version' => $currentVersion
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return response()->json([
                    'update_available' => $data['update_available'] ?? false,
                    'version' => $data['version'] ?? null,
                    'changelog' => $data['changelog'] ?? null
                ]);
            }
        } catch (Exception $e) {
            // خطا در ارتباط
        }

        return response()->json([
            'update_available' => false,
            'error' => 'خطا در ارتباط با سرور'
        ]);
    }
}
