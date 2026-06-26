<?php


namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use ZipArchive;
use Exception;

class ProcessUpdateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 3600; // 1 ساعت
    public $tries = 1;

    protected $panelUrl;
    protected $updateCode;
    protected $currentVersion;

    public function __construct($panelUrl, $updateCode, $currentVersion)
    {
        $this->panelUrl = $panelUrl;
        $this->updateCode = $updateCode;
        $this->currentVersion = $currentVersion;
    }

    public function handle()
    {
        $zipPath = null;
        $extractPath = null;

        try {
            // مرحله 1: شروع
            Cache::put('update_step', 'شروع بروزرسانی', now()->addHours(2));
            Cache::put('update_progress', 0, now()->addHours(2));

            // 1. دریافت اطلاعات از پنل
            Cache::put('update_step', 'دریافت اطلاعات آپدیت', now()->addHours(2));
            Cache::put('update_progress', 10, now()->addHours(2));

            $response = Http::timeout(60)->get($this->panelUrl, [
                'token' => $this->updateCode,
                'version' => $this->currentVersion,
            ]);

            if (!$response->successful() || !$response->json('update_available')) {
                throw new Exception('نسخه جدیدی یافت نشد یا دسترسی غیرمجاز است.');
            }

            $data = $response->json();
            $downloadUrl = $data['download_url'];
            $newVersion = $data['version'];
            $checksum = $data['checksum'] ?? null;

            $zipPath = storage_path('app/temp/update-' . $newVersion . '.zip');
            $extractPath = storage_path('app/temp/extract-' . $newVersion);
            $backupPath = storage_path('app/backups/backup-' . $this->currentVersion . '-' . time());

            File::ensureDirectoryExists(dirname($zipPath));

            // 2. دانلود فایل با timeout بیشتر

            Cache::put('update_step', 'شروع دانلود فایل', now()->addHours(2));
            Cache::put('update_progress', 20, now()->addHours(2));

            $downloadResponse = Http::timeout(600)
                ->sink($zipPath)
                ->withOptions([
                    'connect_timeout' => 60,
                    'read_timeout' => 600,
                ])
                ->get($downloadUrl);

            if (!$downloadResponse->successful()) {
                throw new Exception('خطا در دانلود فایل آپدیت.');
            }

            // 2.1 بررسی صحت فایل
            if ($checksum && hash_file('sha256', $zipPath) !== $checksum) {
                throw new Exception('فایل دانلود شده معتبر نیست (checksum mismatch).');
            }

            // 3. استخراج

            Cache::put('update_step', 'در حال استخراج فایل...', now()->addHours(2));
            Cache::put('update_progress', 90, now()->addHours(2));

            $zip = new ZipArchive();
            if ($zip->open($zipPath) !== true) {
                throw new Exception('خطا در باز کردن فایل ZIP.');
            }
            File::ensureDirectoryExists($extractPath);
            $zip->extractTo($extractPath);
            $zip->close();

            // 4. بکاپ گرفتن
            Cache::put('update_step', 'در حال نصب فایل‌ها...', now()->addHours(2));
            Cache::put('update_progress', 95, now()->addHours(2));

            File::ensureDirectoryExists($backupPath);
            $this->copyFiles($extractPath, base_path(), $backupPath);

            // 5. پاکسازی

            Cache::put('update_step', 'در حال نهایی‌سازی...', now()->addHours(2));
            Cache::put('update_progress', 98, now()->addHours(2));

            File::deleteDirectory($extractPath);
            File::delete($zipPath);

            // 6. ذخیره دائمی نسخه جدید
            $this->setVersion($newVersion);

            // 7. پاک کردن OPcache
            if (function_exists('opcache_reset')) {
                opcache_reset();
            }

            // 8. اجرای دستورات پس از آپدیت (به جای updaterAfter)
            Artisan::call('cache:clear');
            Artisan::call('config:clear');
            Artisan::call('view:clear');
            // Artisan::call('migrate', ['--force' => true]);

            // ذخیره وضعیت موفقیت
            Cache::put('update_status', 'success', now()->addHours(24));
            Cache::put('update_version', $newVersion, now()->addHours(24));
            Cache::put('update_progress', 100, now()->addHours(2));
            Cache::put('update_step', 'کامل شد', now()->addHours(2));
            Cache::forget('update_processing');

        } catch (Exception $e) {
            // پاکسازی در صورت خطا
            File::deleteDirectory($extractPath ?? '');
            File::delete($zipPath ?? '');

            // ذخیره خطا
            Cache::put('update_status', 'error', now()->addHours(24));
            Cache::put('update_error', $e->getMessage(), now()->addHours(24));
            Cache::put('update_error_details', $e->getTraceAsString(), now()->addHours(24));
            Cache::forget('update_processing');

            throw $e;
        }
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
}
