<?php

namespace App\Http\Controllers\Back;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Codedge\Updater\UpdaterManager;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;
use ZipArchive;

class DeveloperController extends Controller
{
    private $panelUrl;
    private $updateCode;

    public function __construct()
    {
        // این مقادیر را از تنظیمات یا .env بخوانید
        $this->panelUrl ='https://update.webtpro.ir/api/v1';
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
        $currentVersion = config('app.version', '1.0.0'); // نسخه فعلی را از جایی بخوانید
        $versionInstalled =$currentVersion;
        // درخواست به پنل برای چک کردن نسخه جدید
        $response = Http::get($this->panelUrl.'/check-update', [
            'token' => $this->updateCode,
            'version' => $currentVersion
        ]);

        $isNewVersionAvailable = false;
        $versionAvailable = $currentVersion;

        if ($response->successful()) {
            $data = $response->json();
            if ($data['update_available']) {
                $isNewVersionAvailable = true;
                $versionAvailable = $data['version'];
            }
        }

        return view('back.developer.updater', compact(
            'versionInstalled',
            'isNewVersionAvailable',
            'versionAvailable',
            'currentVersion' // متغیر ویو را اصلاح کردیم
        ));
    }

    public function updateApplication(Request $request)
    {
        $currentVersion = config('app.version', '1.0.0');

        // 1. دریافت اطلاعات از پنل
        $response = Http::timeout(30)->get($this->panelUrl . '/check-update', [
            'token'   => $this->updateCode,
            'version' => $currentVersion,
        ]);

        if (!$response->successful() || !$response->json('update_available')) {
            return response()->json(['status' => 'error', 'message' => 'نسخه جدیدی یافت نشد یا دسترسی غیرمجاز است.'], 403);
        }

        $data        = $response->json();
        $downloadUrl = $data['download_url'];
        $newVersion  = $data['version'];
        $checksum    = $data['checksum'] ?? null; // اگه پنل sha256 بده

        $zipPath     = storage_path('app/temp/update-' . $newVersion . '.zip');
        $extractPath = storage_path('app/temp/extract-' . $newVersion);
        $backupPath  = storage_path('app/backups/backup-' . $currentVersion . '-' . time());

        try {
            File::ensureDirectoryExists(dirname($zipPath));

            // 2. دانلود فایل
            $downloadResponse = Http::timeout(300)->sink($zipPath)->get($downloadUrl);
            if (!$downloadResponse->successful()) {
                throw new \Exception('خطا در دانلود فایل آپدیت.');
            }

            // 2.1 بررسی صحت فایل (اختیاری ولی توصیه‌شده)
            if ($checksum && hash_file('sha256', $zipPath) !== $checksum) {
                throw new \Exception('فایل دانلود شده معتبر نیست (checksum mismatch).');
            }

            // 3. استخراج
            $zip = new ZipArchive();
            if ($zip->open($zipPath) !== true) {
                throw new \Exception('خطا در باز کردن فایل ZIP.');
            }
            File::ensureDirectoryExists($extractPath);
            $zip->extractTo($extractPath);
            $zip->close();

            // 4. بکاپ گرفتن از فایل‌های فعلی قبل از رونویسی (برای Rollback)
            File::ensureDirectoryExists($backupPath);
            // فقط از فایل‌هایی که قراره تغییر کنن بکاپ بگیر
            $this->copyFiles($extractPath, base_path(), $backupPath);

            // 5. پاکسازی
            File::deleteDirectory($extractPath);
            File::delete($zipPath);

            // 6. ذخیره دائمی نسخه جدید
            $this->setVersion($newVersion);

            // 7. پاک کردن OPcache
            if (function_exists('opcache_reset')) {
                opcache_reset();
            }

            return response()->json(['status' => 'success', 'message' => "نسخه {$newVersion} با موفقیت نصب شد."]);

        } catch (\Exception $e) {
            // در صورت خطا، فایل‌های temp رو پاک کن
            File::deleteDirectory($extractPath);
            File::delete($zipPath);

            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
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

            return response()->json(['status' => 'success', 'message' => 'دستورات پس از بروزرسانی با موفقیت اجرا شدند.']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'خطا در اجرای دستورات: ' . $e->getMessage()], 500);
        }
    }

    private function copyFiles($source, $destination, $backupPath = null)
    {
        // مسیرهای نسبی که نباید رونویسی بشن
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

            // بررسی استثناها بر اساس مسیر نسبی (نه فقط نام فایل)
            foreach ($excluded as $ex) {
                if ($relativePath === $ex || str_starts_with($relativePath, $ex . DIRECTORY_SEPARATOR)) {
                    continue 2;
                }
            }

            $targetPath = $destination . DIRECTORY_SEPARATOR . $relativePath;

            if ($file->isDir()) {
                File::ensureDirectoryExists($targetPath, 0755);
            } else {
                // بکاپ گرفتن از فایل فعلی قبل از رونویسی
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
        // ساده‌ترین راه: ذخیره در یک فایل json
        File::put(
            storage_path('app/version.json'),
            json_encode(['version' => $version])
        );
        // و در showUpdater این مقدار رو بخون به جای config
    }

}
