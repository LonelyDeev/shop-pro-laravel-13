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
        $this->panelUrl =update_url();
        $this->updateCode = env('SELF_UPDATER_HTTP_PRIVATE_ACCESS_TOKEN',config('self-update.updater_token'));
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
        $response = Http::get($this->panelUrl, [
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

        // 1. دریافت اطلاعات دانلود از پنل
        $response = Http::get($this->panelUrl . '/api/update/check', [
            'token' => $this->updateCode,
            'version' => $currentVersion
        ]);

        if (!$response->successful() || !$response->json('has_update')) {
            return response()->json(['status' => 'error', 'message' => 'نسخه جدیدی یافت نشد یا دسترسی غیرمجاز است.'], 403);
        }

        $data = $response->json();
        $downloadUrl = $data['download_url'];
        $newVersion = $data['version'];

        // 2. دانلود فایل ZIP
        $zipPath = storage_path('app/temp/update-' . $newVersion . '.zip');
        File::ensureDirectoryExists(dirname($zipPath));

        $downloadResponse = Http::sink($zipPath)->get($downloadUrl);

        if (!$downloadResponse->successful()) {
            return response()->json(['status' => 'error', 'message' => 'خطا در دانلود فایل آپدیت.'], 500);
        }

        // 3. استخراج فایل‌ها (با احتیاط)
        $zip = new ZipArchive();
        if ($zip->open($zipPath) === true) {
            // فرض بر این است که فایل‌ها در روت زیپ هستند یا در یک پوشه اصلی
            // بهتر است فایل‌ها را در یک پوشه موقت اکسترکت کرده و سپس جایگزین کنید
            $extractPath = storage_path('app/temp/extract');
            File::ensureDirectoryExists($extractPath);

            $zip->extractTo($extractPath);
            $zip->close();

            // کپی فایل‌ها به پوشه اصلی پروژه (به جز پوشه‌های حساس مثل .env یا storage)
            // این بخش بسته به ساختار پروژه شما ممکن است نیاز به دقت بیشتری داشته باشد
            $this->copyFiles($extractPath, base_path());

            // پاکسازی
            File::deleteDirectory($extractPath);
            File::delete($zipPath);

            // بروزرسانی شماره نسخه در فایل کانفیگ یا دیتابیس (اختیاری)
            // config(['app.version' => $newVersion]);

            return response()->json(['status' => 'success', 'message' => "نسخه {$newVersion} با موفقیت نصب شد."]);
        } else {
            return response()->json(['status' => 'error', 'message' => 'خطا در باز کردن فایل ZIP.'], 500);
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

    private function copyFiles($source, $destination)
    {
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($source),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($files as $file) {
            if ($file->isDir()) {
                File::makeDirectory($destination . DIRECTORY_SEPARATOR . $files->getSubPathName(), 0777, true, true);
            } else {
                // جلوگیری از رونویسی فایل‌های حیاتی مثل .env
                $fileName = $file->getFilename();
                if ($fileName === '.env' || $fileName === 'web.php') {
                    continue;
                }
                File::copy($file, $destination . DIRECTORY_SEPARATOR . $files->getSubPathName());
            }
        }
    }
}
