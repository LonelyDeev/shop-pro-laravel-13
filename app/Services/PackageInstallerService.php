<?php

namespace App\Services;

use App\Models\InstalledModule;
use App\Models\ModuleInstallLog;
use App\Models\Permission;
use Exception;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use ZipArchive;

class PackageInstallerService
{
    private array $steps = [];
    private array $backupPaths = [];

    public function __construct(
        private PackageApiService $api,
        private Filesystem $files
    ) {}

    /* ===================================================================
     *  نصب پکیج (دانلود → استخراج → migration → seeder)
     * =================================================================== */
    public function install(
        string $slug,
        string $licenseKey,
        ?int $adminId = null,
        ?string $downloadToken = null
    ): InstalledModule {
        $this->steps = [];
        $this->backupPaths = [];
        $log = $this->startLog(ModuleInstallLog::ACTION_INSTALL, $slug, $adminId);
        $zipPath = null;
        $moduleName = null;

        try {
            // 1) در صورت نیاز، دریافت download_token از API
            if (!$downloadToken) {
                $this->step('verify_license', 'بررسی لایسنس');
                $verify = $this->api->verifyLicense($slug, $licenseKey);

                if (!($verify['valid'] ?? false)) {
                    throw new RuntimeException('لایسنس نامعتبر است.');
                }

                $downloadToken = $verify['download_token']
                    ?? throw new RuntimeException('token دانلود دریافت نشد.');
            }

            // 2) دانلود ZIP
            $this->step('download', 'دانلود فایل پکیج');
            $zipPath = $this->downloadZip($downloadToken);

            // 3) تأیید امضای فایل (در صورت فعال بودن)
            $expectedHash = $verify['signature'] ?? null;
            if (config('packages.security.verify_signature') && $expectedHash) {
                $this->step('verify_signature', 'تأیید یکپارچگی فایل');
                $this->verifySignature($zipPath, $expectedHash);
            }

            // 4) باز کردن و بررسی ساختار ZIP
            $this->step('extract', 'استخراج فایل‌های پکیج');
            $moduleName = $this->extractZip($zipPath, $slug);

            // 5) اعتبارسنجی ساختار ماژول
            $this->step('validate', 'اعتبارسنجی ساختار ماژول');
            $this->validateModuleStructure($moduleName);

            // 6) ثبت در جدول installed_modules (در صورت وجود، آپدیت)
            $installed = $this->registerInstall($slug, $moduleName, $licenseKey, $verify ?? []);

            // 7) ثبت Service Provider
            $this->step('register_provider', 'ثبت Service Provider');
            $this->registerServiceProvider($moduleName);

            // 8) اجرای migrationها
            $this->step('migrate', 'اجرای migrationهای ماژول');
            $this->runMigrations($moduleName);

            // 9) اجرای seederها
            $this->step('seed', 'اجرای seederهای ماژول');
            $this->runSeeders($moduleName);

            // 10) نصب پرمیژن‌های ماژول
            $this->step('install_permissions', 'نصب پرمیژن‌های ماژول');
            $this->installModulePermissions($moduleName);

            // 11) انتشار assetهای ماژول
            $this->step('publish_assets', 'انتشار فایل‌های استاتیک');
            $this->publishModuleAssets($moduleName);

            // 12) اجرای کامندهای سفارشی نصب
            $this->step('custom_commands', 'اجرای کامندهای سفارشی');
            $this->runCustomInstallCommands($moduleName);

            // 13) رفرش کش‌های لاراول
            $this->step('cache_refresh', 'بروزرسانی کش‌ها');
            $this->refreshCaches();

            // 14) پاکسازی فایل موقت
            $this->cleanupTemp($zipPath);

            // 15) به‌روزرسانی وضعیت نصب
            $installed->markAsInstalled($verify['version'] ?? $this->readModuleVersion($moduleName));

            // 16) لاگ موفقیت
            $this->finishLog($log, ModuleInstallLog::STATUS_SUCCESS, $installed);

            Log::info("Module installed successfully", [
                'module' => $moduleName,
                'slug' => $slug,
                'version' => $installed->version
            ]);

            return $installed;

        } catch (Exception $e) {
            // پاکسازی در صورت خطا
            $this->rollbackOnError($moduleName, $zipPath);
            $this->failLog($log, $e, $slug);
            throw $e;
        }
    }

    /* ===================================================================
     *  آپدیت پکیج
     * =================================================================== */
    public function update(string $slug, ?int $adminId = null): InstalledModule
    {
        $installed = InstalledModule::where('slug', $slug)->firstOrFail();
        $oldVersion = $installed->version;

        $installed->markAsUpdating();

        $log = $this->startLog(
            ModuleInstallLog::ACTION_UPDATE,
            $slug,
            $adminId,
            $oldVersion
        );

        try {
            // بررسی لایسنس موجود
            $verify = $this->api->verifyLicense($slug, $installed->license_key);

            if (!($verify['valid'] ?? false)) {
                throw new RuntimeException(
                    $verify['message'] ?? 'لایسنس منقضی یا نامعتبر است.'
                );
            }

            $newVersion = $verify['version'] ?? null;

            if (!$newVersion || version_compare($newVersion, $oldVersion, '<=')) {
                throw new RuntimeException('نسخه جدیدی برای نصب وجود ندارد.');
            }

            // پشتیبان‌گیری از نسخه فعلی
            $this->step('backup', 'پشتیبان‌گیری از نسخه فعلی');
            $this->backupModule($installed->name);

            // نصب نسخه جدید
            $this->install($slug, $installed->license_key, $adminId, $verify['download_token'] ?? null);

            // حذف پشتیبان قدیمی
            $this->cleanupOldBackups($installed->name);

            $installed->refresh();
            $log->update([
                'to_version' => $installed->version,
                'status'     => ModuleInstallLog::STATUS_SUCCESS,
            ]);

            Log::info("Module updated successfully", [
                'module' => $installed->name,
                'slug' => $slug,
                'old_version' => $oldVersion,
                'new_version' => $installed->version
            ]);

            return $installed;

        } catch (Exception $e) {
            // برگرداندن به نسخه قبلی در صورت خطا
            $this->rollbackUpdate($installed->name);
            $this->failLog($log, $e, $slug);
            throw $e;
        }
    }

    /* ===================================================================
     *  حذف ماژول
     * =================================================================== */
    public function uninstall(string $slug, ?int $adminId = null): bool
    {
        $installed = InstalledModule::where('slug', $slug)->firstOrFail();

        $log = $this->startLog(
            ModuleInstallLog::ACTION_UNINSTALL,
            $slug,
            $adminId,
            $installed->version
        );

        try {
            $moduleName = $installed->name;

            // تایید حذف
            if (!$this->confirmUninstall($moduleName)) {
                throw new RuntimeException('عملیات حذف ماژول تأیید نشد.');
            }

            // 1) اجرای کامندهای قبل از حذف
            $this->step('pre_uninstall', 'اجرای کامندهای قبل از حذف');
            $this->runPreUninstallCommands($moduleName);

            // 2) حذف جدول‌ها
            $this->step('drop_tables', 'حذف جداول ماژول');
            $this->dropAllModuleTables($moduleName);

            // 3) حذف پرمیژن‌ها
            $this->step('remove_permissions', 'حذف پرمیژن‌های ماژول');
            $this->removeModulePermissions($moduleName);

            // 4) حذف assetها
            $this->step('remove_assets', 'حذف فایل‌های استاتیک');
            $this->removePublishedAssets($moduleName);

            // 5) حذف پوشه ماژول
            $this->step('remove_files', 'حذف فایل‌های ماژول');
            $this->removeModuleFiles($moduleName);

            // 6) حذف Service Provider
            $this->step('remove_provider', 'حذف Service Provider');
            $this->unregisterServiceProvider($moduleName);

            // 7) رفرش کش
            $this->step('clear_cache', 'پاکسازی کش');
            $this->refreshCaches();

            // 8) حذف رکورد
            $installed->delete();

            $this->finishLog($log, ModuleInstallLog::STATUS_SUCCESS, null, 'ماژول با موفقیت حذف شد');

            Log::info("Module uninstalled successfully", [
                'module' => $moduleName,
                'slug' => $slug
            ]);

            return true;

        } catch (Exception $e) {
            $this->failLog($log, $e, $slug);
            throw $e;
        }
    }

    /* ===================================================================
     *  فعال/غیرفعال‌سازی
     * =================================================================== */
    public function toggleActivation(string $slug): InstalledModule
    {
        $installed = InstalledModule::where('slug', $slug)->firstOrFail();

        try {
            // با دستور module:disable / module:enable
            Artisan::call(
                $installed->is_active ? 'module:disable' : 'module:enable',
                ['module' => $installed->name]
            );

            Log::info("Module toggled via artisan", [
                'module' => $installed->name,
                'action' => $installed->is_active ? 'disable' : 'enable'
            ]);

        } catch (Exception $e) {
            Log::warning('Module toggle failed via artisan', [
                'module' => $installed->name,
                'error'  => $e->getMessage(),
            ]);
        }

        $installed->update(['is_active' => !$installed->is_active]);
        $this->refreshCaches();

        return $installed->refresh();
    }

    /* ===================================================================
     *  دانلود ZIP
     * =================================================================== */
    private function downloadZip(string $token): string
    {
        $url = $this->api->getDownloadUrl($token);
        $disk = Storage::disk(config('packages.download.disk', 'local'));
        $relPath = config('packages.download.temp_path', 'temp') . '/' . Str::uuid() . '.zip';
        $fullPath = $disk->path($relPath);

        // اطمینان از وجود پوشه
        $directory = dirname($fullPath);
        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        Log::info('Starting package download', [
            'url' => $url,
            'path' => $fullPath,
            'token_preview' => substr($token, 0, 10) . '...'
        ]);

        try {
            $response = Http::timeout(config('packages.download.timeout', 300))
                ->connectTimeout(config('packages.download.connect_timeout', 30))
                ->withToken(config('packages.api.token'))
                ->withHeaders([
                    'X-Project-Key' => config('packages.api.project_key'),
                    'Accept' => 'application/zip',
                ])
                ->retry(
                    config('packages.download.retry_times', 3),
                    config('packages.download.retry_sleep', 1000)
                )
                ->sink($fullPath)
                ->get($url);

            if (!$response->successful()) {
                if (File::exists($fullPath)) {
                    File::delete($fullPath);
                }
                throw new RuntimeException('دانلود فایل پکیج ناموفق بود (کد: ' . $response->status() . ')');
            }

            $fileSize = File::size($fullPath);
            Log::info('Download completed', [
                'status' => $response->status(),
                'size' => $fileSize
            ]);

            if ($fileSize === 0) {
                throw new RuntimeException('فایل دانلود شده خالی است.');
            }

            return $fullPath;

        } catch (ConnectionException $e) {
            Log::error('Download connection failed', [
                'url' => $url,
                'error' => $e->getMessage()
            ]);

            if (File::exists($fullPath)) {
                File::delete($fullPath);
            }

            throw new RuntimeException('اتصال به سرور پکیج‌ها برقرار نشد. لطفاً اتصال اینترنت خود را بررسی کنید.');
        }
    }

    /* ===================================================================
     *  تأیید SHA-256
     * =================================================================== */
    private function verifySignature(string $zipPath, string $expectedHash): void
    {
        if (!File::exists($zipPath)) {
            throw new RuntimeException('فایل برای بررسی امضا وجود ندارد.');
        }

        $actual = hash_file('sha256', $zipPath);
        if (!hash_equals($expectedHash, $actual)) {
            throw new RuntimeException(
                'تأیید یکپارچگی فایل ناموفق بود! فایل احتمالاً دستکاری شده است.'
            );
        }

        Log::info('Signature verified successfully');
    }

    /* ===================================================================
     *  استخراج ZIP و تشخیص نام ماژول
     * =================================================================== */
    private function extractZip(string $zipPath, string $slug): string
    {
        if (!class_exists('ZipArchive')) {
            throw new RuntimeException('افزونه ZipArchive روی سرور فعال نیست.');
        }

        $zip = new ZipArchive();
        if ($zip->open($zipPath) !== true) {
            throw new RuntimeException('باز کردن فایل ZIP ناموفق بود.');
        }

        // دیباگ: نمایش محتویات ZIP
        $filesInZip = [];
        for ($i = 0; $i < min(50, $zip->numFiles); $i++) {
            $filesInZip[] = $zip->getNameIndex($i);
        }

        Log::info('ZIP contents preview', [
            'total_files' => $zip->numFiles,
            'files' => $filesInZip,
            'zip_path' => $zipPath
        ]);

        // خواندن module.json از داخل ZIP
        $moduleName = null;
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entry = $zip->getNameIndex($i);
            if (preg_match('#^([^/]+)/module\.json$#', $entry, $m)) {
                $moduleName = $m[1];
                break;
            }
        }

        if (!$moduleName) {
            $zip->close();
            throw new RuntimeException('فایل module.json در ZIP پیدا نشد. ساختار پکیج نامعتبر است.');
        }

        $modulesPath = config('packages.modules.path', base_path('Modules'));

        // اطمینان از وجود پوشه با دسترسی کامل
        if (!File::exists($modulesPath)) {
            File::makeDirectory($modulesPath, 0755, true);
        }

        // تنظیم دسترسی پوشه
        @chmod($modulesPath, 0755);

        $targetPath = $modulesPath . '/' . $moduleName;

        // اگر پوشه وجود دارد، ابتدا آن را پاک کن
        if (File::exists($targetPath)) {
            Log::warning('Module directory already exists, removing', ['path' => $targetPath]);
            File::deleteDirectory($targetPath);
        }

        // استخراج با مسیر کامل
        $extractPath = $modulesPath;
        Log::info('Extracting to', ['path' => $extractPath]);

        // تلاش برای استخراج با ZipArchive
        $extracted = $zip->extractTo($extractPath);

        if (!$extracted) {
            $error = $zip->getStatusString();
            $zip->close();

            Log::error('ZipArchive extraction failed', [
                'error' => $error,
                'extract_path' => $extractPath,
                'zip_path' => $zipPath
            ]);

            // تلاش با system command در لینوکس
            if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
                Log::info('Trying system unzip command');
                $command = "unzip -o '{$zipPath}' -d '{$extractPath}' 2>&1";
                exec($command, $output, $returnCode);

                Log::info('System unzip result', [
                    'return_code' => $returnCode,
                    'output' => $output
                ]);

                if ($returnCode === 0) {
                    $zip->close();
                    $this->verifyExtractedFiles($moduleName, $targetPath);
                    $this->setPermissions($targetPath);
                    Log::info('Module extracted successfully with system unzip', [
                        'module' => $moduleName,
                        'path' => $targetPath
                    ]);
                    return $moduleName;
                }
            }

            throw new RuntimeException('استخراج فایل ZIP ناموفق بود: ' . $error);
        }

        $zip->close();

        // بررسی اینکه آیا فایل‌ها استخراج شده‌اند
        $this->verifyExtractedFiles($moduleName, $targetPath);

        // تنظیم دسترسی‌ها
        $this->setPermissions($targetPath);

        Log::info('Module extracted successfully', [
            'module' => $moduleName,
            'path' => $targetPath,
            'exists' => File::exists($targetPath),
            'files' => File::exists($targetPath) ? count(File::allFiles($targetPath)) : 0
        ]);

        return $moduleName;
    }

    /**
     * بررسی فایل‌های استخراج شده
     */
    private function verifyExtractedFiles(string $moduleName, string $targetPath): void
    {
        if (!File::exists($targetPath)) {
            throw new RuntimeException("پوشه ماژول پس از استخراج وجود ندارد: {$targetPath}");
        }

        // بررسی فایل module.json
        $moduleJsonPath = $targetPath . '/module.json';
        if (!File::exists($moduleJsonPath)) {
            Log::error('module.json not found after extraction', [
                'target_path' => $targetPath,
                'contents' => $this->getDirectoryContents($targetPath)
            ]);
            throw new RuntimeException("فایل module.json پس از استخراج وجود ندارد: {$moduleJsonPath}");
        }

        // بررسی Service Provider
        $providerPath = $targetPath . '/Providers/' . $moduleName . 'ServiceProvider.php';
        if (!File::exists($providerPath)) {
            // بررسی با حروف کوچک
            $providerPathLower = $targetPath . '/providers/' . $moduleName . 'ServiceProvider.php';
            if (!File::exists($providerPathLower)) {
                Log::warning('Service Provider not found', [
                    'expected' => $providerPath,
                    'contents' => $this->getDirectoryContents($targetPath . '/Providers')
                ]);
            }
        }

        // بررسی پوشه Migration (هر دو حالت)
        $migrationPaths = [
            $targetPath . '/Database/Migrations',
            $targetPath . '/database/migrations',
        ];

        $migrationExists = false;
        foreach ($migrationPaths as $path) {
            if (is_dir($path)) {
                $migrationExists = true;
                break;
            }
        }

        if (!$migrationExists) {
            Log::warning('Migrations directory not found after extraction', [
                'checked_paths' => $migrationPaths,
                'contents' => $this->getDirectoryContents($targetPath)
            ]);
        }
    }

    /**
     * دریافت محتویات یک دایرکتوری برای دیباگ
     */
    private function getDirectoryContents(string $path): array
    {
        if (!File::exists($path)) {
            return ['path_not_exists' => $path];
        }

        $contents = [];
        try {
            $items = File::directories($path);
            foreach ($items as $item) {
                $contents['directories'][] = basename($item);
            }

            $files = File::files($path);
            foreach ($files as $file) {
                $contents['files'][] = $file->getFilename();
            }
        } catch (Exception $e) {
            $contents['error'] = $e->getMessage();
        }

        return $contents;
    }

    /* ===================================================================
     *  اعتبارسنجی ساختار ماژول
     * =================================================================== */
    private function validateModuleStructure(string $moduleName): void
    {
        $basePath = config('packages.modules.path', base_path('Modules')) . '/' . $moduleName;

        // بررسی فایل module.json
        $moduleJsonPath = $basePath . '/module.json';
        if (!File::exists($moduleJsonPath)) {
            throw new RuntimeException("فایل module.json در ماژول {$moduleName} یافت نشد.");
        }

        $moduleJson = json_decode(File::get($moduleJsonPath), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException("فایل module.json معتبر نیست: " . json_last_error_msg());
        }

        // بررسی فیلدهای ضروری
        $requiredFields = ['name', 'version', 'providers'];
        foreach ($requiredFields as $field) {
            if (!isset($moduleJson[$field])) {
                throw new RuntimeException("فیلد '{$field}' در module.json وجود ندارد.");
            }
        }

        Log::info('Module structure validated', [
            'module' => $moduleName,
            'version' => $moduleJson['version']
        ]);
    }

    /* ===================================================================
     *  ثبت یا آپدیت رکورد installed_modules
     * =================================================================== */
    private function registerInstall(
        string $slug,
        string $moduleName,
        string $licenseKey,
        array $verifyData
    ): InstalledModule {
        $version = $verifyData['version'] ?? $this->readModuleVersion($moduleName);
        $expiresAt = isset($verifyData['expires_at']) ? new \DateTime($verifyData['expires_at']) : null;

        $installed = InstalledModule::updateOrCreate(
            ['slug' => $slug],
            [
                'name'                => $moduleName,
                'version'             => $version,
                'license_key'         => $licenseKey,
                'license_expires_at'  => $expiresAt,
                'installed_at'        => now(),
                'is_active'           => true,
                'status'              => InstalledModule::STATUS_UPDATING,
                'last_error'          => null,
            ]
        );

        Log::info("Module registered in database", [
            'module' => $moduleName,
            'slug' => $slug,
            'version' => $version
        ]);

        return $installed;
    }

    /* ===================================================================
     *  ثبت Service Provider
     * =================================================================== */
    private function registerServiceProvider(string $moduleName): void
    {
        try {
            // استفاده از دستور module:discover
            if ($this->artisanCommandExists('module:discover')) {
                Artisan::call('module:discover');
                Log::info("Module discovery completed", ['module' => $moduleName]);
            } elseif ($this->artisanCommandExists('module:dump-autoload')) {
                Artisan::call('module:dump-autoload');
                Log::info("Module autoload dumped", ['module' => $moduleName]);
            }

            // فعال کردن ماژول
            if ($this->artisanCommandExists('module:enable')) {
                Artisan::call('module:enable', ['module' => $moduleName]);
                Log::info("Module enabled", ['module' => $moduleName]);
            }

        } catch (Exception $e) {
            Log::warning("Service provider registration failed", [
                'module' => $moduleName,
                'error' => $e->getMessage()
            ]);
        }
    }

    /* ===================================================================
     *  حذف Service Provider
     * =================================================================== */
    private function unregisterServiceProvider(string $moduleName): void
    {
        try {
            if ($this->artisanCommandExists('module:discover')) {
                Artisan::call('module:discover');
                Log::info("Module discovery updated after uninstall", ['module' => $moduleName]);
            }
        } catch (Exception $e) {
            Log::warning("Service provider unregistration failed", [
                'module' => $moduleName,
                'error' => $e->getMessage()
            ]);
        }
    }

    /* ===================================================================
     *  اجرای migrationهای ماژول
     * =================================================================== */
    private function runMigrations(string $moduleName): void
    {
        try {
            // بررسی هر دو حالت حروف
            $migrationPaths = [
                base_path("Modules/{$moduleName}/Database/Migrations"),
                base_path("Modules/{$moduleName}/database/migrations"),
            ];

            $migrationPath = null;
            foreach ($migrationPaths as $path) {
                if (is_dir($path)) {
                    $migrationPath = $path;
                    break;
                }
            }

            if (!$migrationPath) {
                Log::info('No migrations directory found', [
                    'module' => $moduleName,
                    'checked_paths' => $migrationPaths
                ]);
                return;
            }

            $migrationFiles = glob($migrationPath . '/*.php');
            if (empty($migrationFiles)) {
                Log::info('No migration files found', ['module' => $moduleName]);
                return;
            }

            Log::info('Found migration files', [
                'module' => $moduleName,
                'path' => $migrationPath,
                'count' => count($migrationFiles)
            ]);

            // بررسی وجود ماژول در سیستم
            if (!$this->moduleExists($moduleName)) {
                Log::warning('Module not found in system, registering...', ['module' => $moduleName]);
                $this->registerModule($moduleName);
            }

            // اجرای migration با مسیر صحیح
            $relativePath = str_replace(base_path(), '', $migrationPath);
            $relativePath = ltrim($relativePath, '/\\');

            Artisan::call('migrate', [
                '--path' => $relativePath,
                '--force' => true,
            ]);

            $output = Artisan::output();
            Log::info('Migrations completed', [
                'module' => $moduleName,
                'output' => $output
            ]);

        } catch (Exception $e) {
            Log::error('Migration failed', [
                'module' => $moduleName,
                'error' => $e->getMessage(),
                'output' => Artisan::output()
            ]);

            throw new RuntimeException(
                'اجرای migrationهای ماژول ناموفق بود: ' . $e->getMessage()
            );
        }
    }

    /**
     * بررسی وجود ماژول در سیستم
     */
    private function moduleExists(string $moduleName): bool
    {
        try {
            // استفاده از nwidart/laravel-modules
            if (class_exists('Nwidart\Modules\Facades\Module')) {
                $module = \Nwidart\Modules\Facades\Module::find($moduleName);
                return $module !== null;
            }

            // بررسی دستی
            $modulePath = config('packages.modules.path', base_path('Modules')) . '/' . $moduleName;
            return is_dir($modulePath) && file_exists($modulePath . '/module.json');

        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * ثبت ماژول در سیستم
     */
    private function registerModule(string $moduleName): void
    {
        try {
            if (class_exists('Nwidart\Modules\Facades\Module')) {
                if ($this->artisanCommandExists('module:enable')) {
                    Artisan::call('module:enable', ['module' => $moduleName]);
                }
                if ($this->artisanCommandExists('module:discover')) {
                    Artisan::call('module:discover');
                }
                Log::info('Module registered', ['module' => $moduleName]);
            }
        } catch (Exception $e) {
            Log::warning('Module registration failed', [
                'module' => $moduleName,
                'error' => $e->getMessage()
            ]);
        }
    }

    /* ===================================================================
     *  اجرای seederهای ماژول
     * =================================================================== */
    private function runSeeders(string $moduleName): void
    {
        try {
            // بررسی هر دو حالت حروف
            $seederPaths = [
                base_path("Modules/{$moduleName}/Database/Seeders"),
                base_path("Modules/{$moduleName}/database/seeders"),
            ];

            $seederPath = null;
            foreach ($seederPaths as $path) {
                if (is_dir($path)) {
                    $seederPath = $path;
                    break;
                }
            }

            if (!$seederPath) {
                Log::info('No seeders directory found', ['module' => $moduleName]);
                return;
            }

            $seederFiles = glob($seederPath . '/*.php');
            if (empty($seederFiles)) {
                Log::info('No seeder files found', ['module' => $moduleName]);
                return;
            }

            Log::info('Running seeders', [
                'module' => $moduleName,
                'count' => count($seederFiles),
                'path' => $seederPath
            ]);

            // اجرای DatabaseSeeder اصلی
            $mainSeeder = "Modules\\{$moduleName}\\Database\\Seeders\\{$moduleName}DatabaseSeeder";
            if (!class_exists($mainSeeder)) {
                $mainSeeder = "Modules\\{$moduleName}\\database\\seeders\\{$moduleName}DatabaseSeeder";
            }

            if (class_exists($mainSeeder)) {
                Artisan::call('db:seed', [
                    '--class' => $mainSeeder,
                    '--force' => true,
                ]);
                Log::info("Main seeder executed", ['class' => $mainSeeder]);
            }

            // اجرای سایر seederها
            foreach ($seederFiles as $seederFile) {
                $seederClass = pathinfo($seederFile, PATHINFO_FILENAME);

                if ($seederClass === "{$moduleName}DatabaseSeeder") {
                    continue;
                }

                $fullClass = "Modules\\{$moduleName}\\Database\\Seeders\\{$seederClass}";
                if (!class_exists($fullClass)) {
                    $fullClass = "Modules\\{$moduleName}\\database\\seeders\\{$seederClass}";
                }

                if (class_exists($fullClass)) {
                    Artisan::call('db:seed', [
                        '--class' => $fullClass,
                        '--force' => true,
                    ]);
                    Log::info("Additional seeder executed", ['class' => $fullClass]);
                }
            }

        } catch (Exception $e) {
            Log::error('Seeder execution failed', [
                'module' => $moduleName,
                'error' => $e->getMessage()
            ]);
        }
    }

    /* ===================================================================
     *  نصب پرمیژن‌های ماژول
     * =================================================================== */
    private function installModulePermissions(string $moduleName): void
    {
        $command = strtolower($moduleName) . ':install-permissions';

        if (!$this->artisanCommandExists($command)) {
            Log::info('No permission command found', ['module' => $moduleName]);
            return;
        }

        try {
            Artisan::call($command);
            Log::info("Module permissions installed", [
                'command' => $command,
                'output' => Artisan::output()
            ]);
        } catch (Exception $e) {
            Log::warning('Permission install failed (continuing)', [
                'module' => $moduleName,
                'command' => $command,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /* ===================================================================
     *  حذف پرمیژن‌های ماژول
     * =================================================================== */
    private function removeModulePermissions(string $moduleName): void
    {
        try {
            if (class_exists(Permission::class)) {
                $permissions = Permission::where('name', 'like', strtolower($moduleName) . '.%')
                    ->orWhere('name', 'like', strtolower($moduleName) . '_%')
                    ->orWhere('name', '=', strtolower($moduleName))
                    ->get();

                if ($permissions->isNotEmpty()) {
                    // گرفتن IDها به صورت آرایه ساده
                    $permissionIds = $permissions->pluck('id')->toArray();

                    // حذف از role_has_permissions
                    DB::table('role_has_permissions')
                        ->whereIn('permission_id', $permissionIds)
                        ->delete();

                    // حذف از permissions
                    Permission::whereIn('id', $permissionIds)->delete();

                    Log::info('Permissions removed', [
                        'module' => $moduleName,
                        'count' => count($permissionIds)
                    ]);
                }
            }

            // حذف از جدول دستی permissions
            $deleted = DB::table('permissions')
                ->where('name', 'like', strtolower($moduleName) . '.%')
                ->orWhere('name', 'like', strtolower($moduleName) . '_%')
                ->orWhere('name', '=', strtolower($moduleName))
                ->orWhere('module', '=', strtolower($moduleName))
                ->delete();

            if ($deleted > 0) {
                Log::info('Permissions deleted from database', [
                    'module' => $moduleName,
                    'count' => $deleted
                ]);
            }

        } catch (Exception $e) {
            Log::warning('Permission removal failed', [
                'module' => $moduleName,
                'error' => $e->getMessage()
            ]);
        }
    }

    /* ===================================================================
     *  انتشار assetهای ماژول
     * =================================================================== */
    private function publishModuleAssets(string $moduleName): void
    {
        $sourcePath = config('packages.modules.path', base_path('Modules')) . '/' . $moduleName . '/Resources/assets';

        if (!File::exists($sourcePath)) {
            Log::info('No assets found', ['module' => $moduleName]);
            return;
        }

        $publicPath = public_path('modules/' . strtolower($moduleName));

        try {
            if (!File::exists(dirname($publicPath))) {
                File::makeDirectory(dirname($publicPath), 0755, true);
            }

            $this->copyDirectory($sourcePath, $publicPath);

            Log::info("Assets published", [
                'module' => $moduleName,
                'source' => $sourcePath,
                'target' => $publicPath,
            ]);

        } catch (Exception $e) {
            Log::warning('Asset publish failed (continuing)', [
                'module' => $moduleName,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /* ===================================================================
     *  حذف assetهای منتشرشده
     * =================================================================== */
    private function removePublishedAssets(string $moduleName): void
    {
        $publicPath = public_path('modules/' . strtolower($moduleName));

        if (File::exists($publicPath)) {
            try {
                File::deleteDirectory($publicPath);
                Log::info("Assets removed", ['module' => $moduleName]);
            } catch (Exception $e) {
                Log::warning('Asset removal failed (continuing)', [
                    'module' => $moduleName,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /* ===================================================================
     *  حذف کامل تمام جدول‌های ماژول
     * =================================================================== */
    private function dropAllModuleTables(string $moduleName): void
    {
        // بررسی هر دو حالت حروف
        $migrationPaths = [
            base_path("Modules/{$moduleName}/Database/Migrations"),
            base_path("Modules/{$moduleName}/database/migrations"),
        ];

        $migrationPath = null;
        foreach ($migrationPaths as $path) {
            if (is_dir($path)) {
                $migrationPath = $path;
                break;
            }
        }

        if (!$migrationPath) {
            Log::warning('Migrations directory not found', [
                'module' => $moduleName,
                'checked_paths' => $migrationPaths
            ]);
            return;
        }

        $files = glob($migrationPath . '/*.php');
        $tableNames = [];
        $migrationNames = [];

        foreach ($files as $file) {
            $filename = pathinfo($file, PATHINFO_FILENAME);
            $migrationNames[] = $filename;

            $content = file_get_contents($file);
            preg_match_all(
                "/Schema::(?:create|table)\s*\(\s*['\"]([^'\"]+)['\"]/",
                $content,
                $matches
            );

            if (!empty($matches[1])) {
                $tableNames = array_merge($tableNames, $matches[1]);
            }
        }

        $tableNames = array_unique($tableNames);
        $tableNamesToDrop = array_filter($tableNames, function($table) {
            return Schema::hasTable($table);
        });

        if (empty($tableNamesToDrop)) {
            Log::info('No tables to drop', ['module' => $moduleName]);
            return;
        }

        Log::info('Dropping tables', [
            'module' => $moduleName,
            'tables' => $tableNamesToDrop
        ]);

        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        foreach (array_reverse($tableNamesToDrop) as $table) {
            try {
                Schema::dropIfExists($table);
                Log::info('Table dropped', ['table' => $table]);
            } catch (Exception $e) {
                Log::error('Failed to drop table', [
                    'table' => $table,
                    'error' => $e->getMessage()
                ]);
            }
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // حذف رکوردهای migration
        if (!empty($migrationNames)) {
            try {
                $deleted = DB::table('migrations')
                    ->whereIn('migration', $migrationNames)
                    ->delete();
                Log::info('Migration records deleted', [
                    'module' => $moduleName,
                    'count' => $deleted
                ]);
            } catch (Exception $e) {
                Log::warning('Failed to delete migration records', [
                    'module' => $moduleName,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /* ===================================================================
     *  حذف فایل‌های ماژول
     * =================================================================== */
    private function removeModuleFiles(string $moduleName): void
    {
        $modulePath = config('packages.modules.path', base_path('Modules')) . '/' . $moduleName;

        if (File::exists($modulePath)) {
            try {
                File::deleteDirectory($modulePath);
                Log::info("Module files removed", ['path' => $modulePath]);
            } catch (Exception $e) {
                Log::error('Failed to remove module files', [
                    'path' => $modulePath,
                    'error' => $e->getMessage()
                ]);
                throw new RuntimeException('حذف فایل‌های ماژول ناموفق بود: ' . $e->getMessage());
            }
        }
    }

    /* ===================================================================
     *  اجرای کامندهای سفارشی نصب
     * =================================================================== */
    private function runCustomInstallCommands(string $moduleName): void
    {
        $commands = [
            strtolower($moduleName) . ':install',
            strtolower($moduleName) . ':setup',
            strtolower($moduleName) . ':init',
        ];

        foreach ($commands as $command) {
            if ($this->artisanCommandExists($command)) {
                try {
                    Artisan::call($command, ['--force' => true]);
                    Log::info("Custom install command executed", [
                        'command' => $command,
                        'output' => Artisan::output()
                    ]);
                } catch (Exception $e) {
                    Log::warning('Custom install command failed (continuing)', [
                        'command' => $command,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }
    }

    /* ===================================================================
     *  اجرای کامندهای قبل از حذف
     * =================================================================== */
    private function runPreUninstallCommands(string $moduleName): void
    {
        $command = strtolower($moduleName) . ':uninstall';

        if ($this->artisanCommandExists($command)) {
            try {
                Artisan::call($command, ['--force' => true]);
                Log::info("Pre-uninstall command executed", [
                    'command' => $command,
                    'output' => Artisan::output()
                ]);
            } catch (Exception $e) {
                Log::warning('Pre-uninstall command failed (continuing)', [
                    'command' => $command,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /* ===================================================================
     *  بررسی وجود artisan command
     * =================================================================== */
    private function artisanCommandExists(string $command): bool
    {
        try {
            return collect(Artisan::all())->has($command);
        } catch (\Throwable $e) {
            return false;
        }
    }

    /* ===================================================================
     *  کپی کامل دایرکتوری
     * =================================================================== */
    private function copyDirectory(string $source, string $destination): void
    {
        if (!File::exists($destination)) {
            File::makeDirectory($destination, 0755, true);
        }

        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($items as $item) {
            $target = $destination . '/' . $items->getSubPathName();
            if ($item->isDir()) {
                if (!File::exists($target)) {
                    File::makeDirectory($target, 0755, true);
                }
            } else {
                File::copy($item->getRealPath(), $target);
            }
        }
    }

    /* ===================================================================
     *  تنظیم دسترسی‌ها
     * =================================================================== */
    private function setPermissions(string $path): void
    {
        try {
            if (File::isDirectory($path)) {
                @chmod($path, 0755);

                $files = File::allFiles($path);
                foreach ($files as $file) {
                    @chmod($file, 0644);
                }

                $directories = File::directories($path);
                foreach ($directories as $directory) {
                    $this->setPermissions($directory);
                }
            }
        } catch (Exception $e) {
            Log::warning('Failed to set permissions', [
                'path' => $path,
                'error' => $e->getMessage()
            ]);
        }
    }

    /* ===================================================================
     *  پشتیبان‌گیری از ماژول
     * =================================================================== */
    private function backupModule(string $moduleName): void
    {
        $modulePath = config('packages.modules.path', base_path('Modules')) . '/' . $moduleName;
        $backupPath = storage_path('backups/modules/' . $moduleName . '_' . date('Y-m-d_H-i-s'));

        if (File::exists($modulePath)) {
            try {
                if (!File::exists(dirname($backupPath))) {
                    File::makeDirectory(dirname($backupPath), 0755, true);
                }

                File::copyDirectory($modulePath, $backupPath);
                $this->backupPaths[] = $backupPath;

                Log::info("Module backed up", [
                    'module' => $moduleName,
                    'backup_path' => $backupPath
                ]);
            } catch (Exception $e) {
                Log::warning('Module backup failed', [
                    'module' => $moduleName,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /* ===================================================================
     *  برگرداندن به نسخه قبلی در صورت خطای آپدیت
     * =================================================================== */
    private function rollbackUpdate(string $moduleName): void
    {
        $backupPath = null;

        // پیدا کردن آخرین پشتیبان
        $backupDir = storage_path('backups/modules');
        if (File::exists($backupDir)) {
            $backups = File::glob($backupDir . '/' . $moduleName . '_*');
            if (!empty($backups)) {
                $backupPath = end($backups);
            }
        }

        if ($backupPath && File::exists($backupPath)) {
            try {
                $modulePath = config('packages.modules.path', base_path('Modules')) . '/' . $moduleName;

                // حذف نسخه جدید
                if (File::exists($modulePath)) {
                    File::deleteDirectory($modulePath);
                }

                // برگرداندن پشتیبان
                File::copyDirectory($backupPath, $modulePath);

                Log::info("Module rolled back", [
                    'module' => $moduleName,
                    'from' => $backupPath
                ]);
            } catch (Exception $e) {
                Log::error('Rollback failed', [
                    'module' => $moduleName,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /* ===================================================================
     *  پاکسازی پشتیبان‌های قدیمی
     * =================================================================== */
    private function cleanupOldBackups(string $moduleName): void
    {
        $backupDir = storage_path('backups/modules');
        if (!File::exists($backupDir)) {
            return;
        }

        try {
            $backups = File::glob($backupDir . '/' . $moduleName . '_*');
            $keep = config('packages.backup.keep', 3);

            if (count($backups) > $keep) {
                $toDelete = array_slice($backups, 0, count($backups) - $keep);
                foreach ($toDelete as $backup) {
                    File::deleteDirectory($backup);
                    Log::info("Old backup deleted", ['path' => $backup]);
                }
            }
        } catch (Exception $e) {
            Log::warning('Failed to cleanup old backups', [
                'module' => $moduleName,
                'error' => $e->getMessage()
            ]);
        }
    }

    /* ===================================================================
     *  رول‌بک در صورت خطای نصب
     * =================================================================== */
    private function rollbackOnError(?string $moduleName, ?string $zipPath): void
    {
        // پاکسازی فایل‌های ماژول در صورت وجود
        if ($moduleName) {
            $modulePath = config('packages.modules.path', base_path('Modules')) . '/' . $moduleName;
            if (File::exists($modulePath)) {
                try {
                    File::deleteDirectory($modulePath);
                    Log::info("Module directory cleaned after error", ['module' => $moduleName]);
                } catch (Exception $e) {
                    // نادیده
                }
            }
        }

        // پاکسازی فایل ZIP موقت
        if ($zipPath && File::exists($zipPath)) {
            try {
                File::delete($zipPath);
                Log::info("Temp file cleaned after error", ['path' => $zipPath]);
            } catch (Exception $e) {
                // نادیده
            }
        }

        // پاکسازی پشتیبان‌ها
        foreach ($this->backupPaths as $backupPath) {
            if (File::exists($backupPath)) {
                try {
                    File::deleteDirectory($backupPath);
                } catch (Exception $e) {
                    // نادیده
                }
            }
        }
    }

    /* ===================================================================
     *  تایید حذف ماژول
     * =================================================================== */
    private function confirmUninstall(string $moduleName): bool
    {
        return true;
    }

    /* ===================================================================
     *  خواندن نسخه از module.json
     * =================================================================== */
    private function readModuleVersion(string $moduleName): ?string
    {
        $path = config('packages.modules.path', base_path('Modules')) . '/' . $moduleName . '/module.json';
        if (!File::exists($path)) {
            return null;
        }

        try {
            $data = json_decode(File::get($path), true);
            return $data['version'] ?? null;
        } catch (Exception $e) {
            Log::warning('Failed to read module version', [
                'module' => $moduleName,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /* ===================================================================
     *  پاکسازی فایل موقت
     * =================================================================== */
    private function cleanupTemp(string $zipPath): void
    {
        try {
            if (File::exists($zipPath)) {
                File::delete($zipPath);
                Log::info("Temp file cleaned up", ['path' => $zipPath]);
            }
        } catch (Exception $e) {
            Log::warning('Failed to cleanup temp file', [
                'path' => $zipPath,
                'error' => $e->getMessage()
            ]);
        }
    }

    /* ===================================================================
     *  رفرش کش‌های لاراول
     * =================================================================== */
    private function refreshCaches(): void
    {
        try {
            // فقط دستوراتی که وجود دارند را اجرا کن
            $commands = [
                'config:clear',
                'cache:clear',
                'view:clear',
                'route:clear',
            ];

            foreach ($commands as $command) {
                if ($this->artisanCommandExists($command)) {
                    Artisan::call($command);
                }
            }

            // بررسی وجود دستور module:dump-autoload
            if ($this->artisanCommandExists('module:dump-autoload')) {
                Artisan::call('module:dump-autoload');
            } elseif ($this->artisanCommandExists('module:discover')) {
                Artisan::call('module:discover');
            }

            Log::info('Caches refreshed');

        } catch (Exception $e) {
            Log::warning('Cache refresh partial failure', ['error' => $e->getMessage()]);
        }
    }

    /* ===================================================================
     *  لاگ‌گیری مراحل
     * =================================================================== */
    private function step(string $name, string $label): void
    {
        $this->steps[] = [
            'name' => $name,
            'label' => $label,
            'at' => now()->toDateTimeString()
        ];
    }

    private function startLog(
        string $action,
        string $slug,
        ?int $adminId,
        ?string $fromVersion = null
    ): ModuleInstallLog {
        return ModuleInstallLog::create([
            'admin_id'      => $adminId,
            'module_slug'   => $slug,
            'module_name'   => $slug,
            'action'        => $action,
            'from_version'  => $fromVersion,
            'status'        => ModuleInstallLog::STATUS_RUNNING,
        ]);
    }

    private function finishLog(
        ModuleInstallLog $log,
        string $status,
        ?InstalledModule $installed = null,
        ?string $message = null
    ): void {
        try {
            $log->update([
                'status'     => $status,
                'to_version' => $installed?->version,
                'message'    => $message,
                'details'    => ['steps' => $this->steps],
            ]);
        } catch (Exception $e) {
            Log::error('Failed to update install log', [
                'log_id' => $log->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    private function failLog(ModuleInstallLog $log, Exception $e, string $slug): void
    {
        try {
            $errorData = [
                'slug' => $slug,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'code' => $e->getCode(),
                'steps' => $this->steps
            ];

            Log::error('Package install/update failed', $errorData);

            // ذخیره در فایل لاگ ساده (امن‌تر)
            $logFile = storage_path('logs/package_errors.log');
            $logEntry = date('Y-m-d H:i:s') . " | " . json_encode($errorData) . PHP_EOL;
            @file_put_contents($logFile, $logEntry, FILE_APPEND);

            $log->update([
                'status' => ModuleInstallLog::STATUS_FAILED,
                'message' => substr($e->getMessage(), 0, 500),
                'details' => [
                    'steps' => $this->steps,
                    'error' => [
                        'message' => substr($e->getMessage(), 0, 200),
                        'file' => $e->getFile(),
                        'line' => $e->getLine()
                    ]
                ],
            ]);

            $installed = InstalledModule::where('slug', $slug)->first();
            if ($installed) {
                $installed->markAsFailed(substr($e->getMessage(), 0, 500));
            }

        } catch (Exception $logError) {
            // اگر لاگ‌گیری هم مشکل داشت، حداقل یک فایل ساده بنویسیم
            @file_put_contents(
                storage_path('logs/critical_error.log'),
                date('Y-m-d H:i:s') . " | " . $e->getMessage() . " | " . $e->getFile() . ":" . $e->getLine() . PHP_EOL,
                FILE_APPEND
            );
        }
    }
}
