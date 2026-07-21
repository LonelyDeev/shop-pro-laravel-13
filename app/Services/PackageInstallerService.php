<?php

namespace App\Services;

use App\Models\InstalledModule;
use App\Models\ModuleInstallLog;
use Exception;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use ZipArchive;

class PackageInstallerService
{
    private array $steps = [];

    public function __construct(
        private PackageApiService $api,
        private Filesystem $files
    ) {}

    /* ===================================================================
     *  نصب پکیج (دانلود → استخراج → migration)
     * =================================================================== */
    public function install(
        string $slug,
        string $licenseKey,
        ?int $adminId = null,
        ?string $downloadToken = null
    ): InstalledModule {
        $this->steps = [];
        $log = $this->startLog(ModuleInstallLog::ACTION_INSTALL, $slug, $adminId);

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

            // 5) ثبت در جدول installed_modules (در صورت وجود، آپدیت)
            $installed = $this->registerInstall($slug, $moduleName, $licenseKey, $verify ?? []);

            // 6) اجرای migrationها
            $this->step('migrate', 'اجرای migrationهای ماژول');
            $this->runMigrations($moduleName);

            // 7) نصب پرمیژن‌های ماژول (در صورت وجود command)
            $this->installModulePermissions($moduleName);

            // 8) انتشار assetهای ماژول (CSS/JS) به پوشه public
            $this->publishModuleAssets($moduleName);

            // 9) رفرش کش‌های لاراول
            $this->step('cache_refresh', 'بروزرسانی کش‌ها');
            $this->refreshCaches();

            // 10) پاکسازی فایل موقت
            $this->cleanupTemp($zipPath);

            $installed->markAsInstalled($verify['version'] ?? $this->readModuleVersion($moduleName));

            $this->finishLog($log, ModuleInstallLog::STATUS_SUCCESS, $installed);

            return $installed;
        } catch (Exception $e) {
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

            // نصب نسخه جدید
            $this->install($slug, $installed->license_key, $adminId, $verify['download_token'] ?? null);

            $installed->refresh();
            $log->update([
                'to_version' => $installed->version,
                'status'     => ModuleInstallLog::STATUS_SUCCESS,
            ]);

            return $installed;
        } catch (Exception $e) {
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

            // حذف پرمیژن‌های ماژول (قبل از حذف فایل‌ها)
            $this->removeModulePermissions($moduleName);

            // اجرای rollback migrationها (در صورت تمایل)
            $this->step('rollback_migrations', 'حذف جداول ماژول');
            try {
                Artisan::call('module:migrate-rollback', ['module' => $moduleName]);
            } catch (Exception $e) {
                Log::warning('Rollback migration failed (continuing)', [
                    'module' => $moduleName,
                    'error'  => $e->getMessage(),
                ]);
            }

            // حذف assetهای منتشرشده در public
            $this->step('remove_assets', 'حذف فایل‌های استاتیک ماژول');
            $this->removePublishedAssets($moduleName);

            // حذف پوشه ماژول
            $this->step('remove_files', 'حذف فایل‌های ماژول');
            $modulePath = config('packages.modules.path') . '/' . $moduleName;
            if (File::exists($modulePath)) {
                File::deleteDirectory($modulePath);
            }

            // رفرش کش
            $this->refreshCaches();

            // حذف رکورد
            $installed->delete();

            $this->finishLog($log, ModuleInstallLog::STATUS_SUCCESS, null, 'ماژول با موفقیت حذف شد');

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

        // با دستور module:disable / module:enable نیدویت
        try {
            Artisan::call(
                $installed->is_active ? 'module:disable' : 'module:enable',
                ['module' => $installed->name]
            );
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
        $disk = Storage::disk(config('packages.download.disk'));
        $relPath = config('packages.download.temp_path') . '/' . Str::uuid() . '.zip';

        // استفاده از streaming برای فایل‌های بزرگ
        $response = Http::timeout(config('packages.download.timeout', 600))
            ->withToken(config('packages.api.token'))
            ->withHeaders(['X-Project-Key' => config('packages.api.project_key')])
            ->get($url);

        if (!$response->successful()) {
            throw new RuntimeException('دانلود فایل پکیج ناموفق بود (کد: ' . $response->status() . ')');
        }

        $disk->put($relPath, $response->body());

        return $disk->path($relPath);
    }

    /* ===================================================================
     *  تأیید SHA-256
     * =================================================================== */
    private function verifySignature(string $zipPath, string $expectedHash): void
    {
        $actual = hash_file('sha256', $zipPath);
        if (!hash_equals($expectedHash, $actual)) {
            throw new RuntimeException(
                'تأیید یکپارچگی فایل ناموفق بود! فایل احتمالاً دستکاری شده است.'
            );
        }
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

        // خواندن module.json از داخل ZIP برای تشخیص نام ماژول
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

        $modulesPath = config('packages.modules.path');
        if (!File::exists($modulesPath)) {
            File::makeDirectory($modulesPath, 0755, true);
        }

        $targetPath = $modulesPath . '/' . $moduleName;

        // backup نسخه قبلی (در صورت آپدیت)
        if (File::exists($targetPath)) {
            $backupPath = $modulesPath . '/.backups/' . $moduleName . '_' . time();
            File::makeDirectory(dirname($backupPath), 0755, true);
            File::moveDirectory($targetPath, $backupPath);
        }

        if (!$zip->extractTo($modulesPath)) {
            $zip->close();
            throw new RuntimeException('استخراج فایل ZIP ناموفق بود.');
        }

        $zip->close();

        return $moduleName;
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
        return InstalledModule::updateOrCreate(
            ['slug' => $slug],
            [
                'name'                => $moduleName,
                'version'             => $verifyData['version'] ?? $this->readModuleVersion($moduleName),
                'license_key'         => $licenseKey,
                'license_expires_at'  => $verifyData['expires_at'] ?? null,
                'installed_at'        => now(),
                'is_active'           => true,
                'status'              => InstalledModule::STATUS_UPDATING,
                'last_error'          => null,
            ]
        );
    }

    /* ===================================================================
     *  اجرای migrationهای ماژول
     * =================================================================== */
    private function runMigrations(string $moduleName): void
    {
        // بررسی وجود پوشه‌ی migrations با case-insensitive (برای لینوکس)
        $modulePath = config('packages.modules.path') . '/' . $moduleName;
        $migrationsPath = $this->findMigrationsPath($modulePath);

        if ($migrationsPath === null) {
            Log::warning('No migrations directory found', [
                'module' => $moduleName,
                'module_path' => $modulePath,
                'checked_paths' => [
                    $modulePath . '/database/migrations',
                    $modulePath . '/Database/Migrations',
                    $modulePath . '/database',
                    $modulePath . '/Database',
                ],
            ]);
            // نیازی نیست ارور بدیم - شاید ماژول migration نداره
            return;
        }

        Log::info('Found migration files', [
            'module' => $moduleName,
            'path' => $migrationsPath,
            'count' => count(glob($migrationsPath . '/*.php')),
        ]);

        try {
            Artisan::call('module:migrate', [
                'module' => $moduleName,
                '--force' => true,
            ]);

            Log::info('Migrations completed', [
                'module' => $moduleName,
                'output' => Artisan::output(),
            ]);
        } catch (Exception $e) {
            throw new RuntimeException(
                'اجرای migrationهای ماژول ناموفق بود: ' . $e->getMessage()
            );
        }
    }

    /**
     * پیدا کردن مسیر migrations با پشتیبانی از case sensitivity
     * (لینوکس case-sensitive هست، ویندوز نه)
     */
    private function findMigrationsPath(string $modulePath): ?string
    {
        // الگوهای ممکن برای مسیر migrations (به ترتیب اولویت)
        $patterns = [
            'database/migrations',  // استاندارد Laravel
            'Database/Migrations',  // حالت PascalCase
            'database/Migrations',  // حالت mixed
            'Database/migrations',  // حالت mixed
        ];

        foreach ($patterns as $pattern) {
            $path = $modulePath . '/' . $pattern;
            if (is_dir($path)) {
                return $path;
            }
        }

        // اگه پوشه‌ی database وجود داره، داخلش رو بگرد
        $databasePatterns = ['database', 'Database'];
        foreach ($databasePatterns as $dbPattern) {
            $dbPath = $modulePath . '/' . $dbPattern;
            if (is_dir($dbPath)) {
                // پوشه‌های داخل database/ رو بگرد
                $subdirs = scandir($dbPath);
                foreach ($subdirs as $subdir) {
                    if ($subdir === '.' || $subdir === '..') continue;
                    if (strtolower($subdir) === 'migrations') {
                        $migrationsPath = $dbPath . '/' . $subdir;
                        if (is_dir($migrationsPath)) {
                            return $migrationsPath;
                        }
                    }
                }
            }
        }

        return null;
    }

    /* ===================================================================
     *  نصب پرمیژن‌های ماژول (در صورت وجود command اختصاصی)
     *  Convention: هر ماژول می‌تونه command "{module-lower}:install-permissions" داشته باشه
     * =================================================================== */
    private function installModulePermissions(string $moduleName): void
    {
        $command = strtolower($moduleName) . ':install-permissions';

        if (! $this->artisanCommandExists($command)) {
            return; // ماژول پرمیژن اختصاصی نداره - نادیده گرفته می‌شه
        }

        $this->step('install_permissions', 'نصب پرمیژن‌های ماژول');

        try {
            Artisan::call($command);
            Log::info("Module permissions installed: {$command}", [
                'output' => Artisan::output(),
            ]);
        } catch (Exception $e) {
            Log::warning('Permission install failed (continuing)', [
                'module' => $moduleName,
                'command' => $command,
                'error'   => $e->getMessage(),
            ]);
        }
    }

    /**
     * حذف پرمیژن‌های ماژول هنگام uninstall
     */
    private function removeModulePermissions(string $moduleName): void
    {
        $command = strtolower($moduleName) . ':install-permissions';

        if (! $this->artisanCommandExists($command)) {
            return;
        }

        $this->step('remove_permissions', 'حذف پرمیژن‌های ماژول');

        try {
            Artisan::call($command, ['--remove' => true]);
            Log::info("Module permissions removed: {$command}");
        } catch (Exception $e) {
            Log::warning('Permission removal failed (continuing)', [
                'module'  => $moduleName,
                'command' => $command,
                'error'   => $e->getMessage(),
            ]);
        }
    }

    /**
     * بررسی وجود یک artisan command
     */
    private function artisanCommandExists(string $command): bool
    {
        try {
            return collect(Artisan::all())->has($command);
        } catch (\Throwable $e) {
            return false;
        }
    }

    /* ===================================================================
     *  انتشار assetهای ماژول (CSS/JS) به پوشه public/modules/{module}
     * =================================================================== */
    private function publishModuleAssets(string $moduleName): void
    {
        $modulePath = config('packages.modules.path') . '/' . $moduleName;
        $sourcePath = $this->findAssetsPath($modulePath);

        if (! $sourcePath) {
            return; // ماژول asset نداره
        }

        $this->step('publish_assets', 'انتشار فایل‌های استاتیک ماژول');

        $publicPath = public_path('modules/' . strtolower($moduleName));

        try {
            if (! File::exists(dirname($publicPath))) {
                File::makeDirectory(dirname($publicPath), 0755, true);
            }
            // کپی کل پوشه assets به public
            $this->copyDirectory($sourcePath, $publicPath);

            Log::info("Module assets published: {$moduleName}", [
                'source' => $sourcePath,
                'target' => $publicPath,
            ]);
        } catch (Exception $e) {
            Log::warning('Asset publish failed (continuing)', [
                'module' => $moduleName,
                'error'  => $e->getMessage(),
            ]);
        }
    }

    /**
     * پیدا کردن مسیر assets با پشتیبانی از case sensitivity
     */
    private function findAssetsPath(string $modulePath): ?string
    {
        // الگوهای ممکن (به ترتیب اولویت)
        $patterns = [
            'Resources/assets',
            'resources/assets',
            'Resources/Assets',
            'resources/assets',
        ];

        foreach ($patterns as $pattern) {
            $path = $modulePath . '/' . $pattern;
            if (is_dir($path)) {
                return $path;
            }
        }

        // اگه پوشه‌ی Resources یا resources وجود داره، داخلش رو بگرد
        $resourcePatterns = ['Resources', 'resources'];
        foreach ($resourcePatterns as $resPattern) {
            $resPath = $modulePath . '/' . $resPattern;
            if (is_dir($resPath)) {
                $subdirs = scandir($resPath);
                foreach ($subdirs as $subdir) {
                    if ($subdir === '.' || $subdir === '..') continue;
                    if (strtolower($subdir) === 'assets') {
                        $assetsPath = $resPath . '/' . $subdir;
                        if (is_dir($assetsPath)) {
                            return $assetsPath;
                        }
                    }
                }
            }
        }

        return null;
    }

    /**
     * حذف assetهای منتشرشده هنگام uninstall
     */
    private function removePublishedAssets(string $moduleName): void
    {
        $publicPath = public_path('modules/' . strtolower($moduleName));

        if (File::exists($publicPath)) {
            try {
                File::deleteDirectory($publicPath);
                Log::info("Module assets removed: {$moduleName}");
            } catch (Exception $e) {
                Log::warning('Asset removal failed (continuing)', [
                    'module' => $moduleName,
                    'error'  => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * کپی کامل یک دایرکتوری (شامل زیرپوشه‌ها)
     */
    private function copyDirectory(string $source, string $destination): void
    {
        if (! File::exists($destination)) {
            File::makeDirectory($destination, 0755, true);
        }

        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($items as $item) {
            $target = $destination . '/' . $items->getSubPathName();
            if ($item->isDir()) {
                if (! File::exists($target)) {
                    File::makeDirectory($target, 0755, true);
                }
            } else {
                File::copy($item->getRealPath(), $target);
            }
        }
    }

    /* ===================================================================
     *  رفرش کش‌های لاراول
     * =================================================================== */
    private function refreshCaches(): void
    {
        try {
            Artisan::call('config:clear');
            Artisan::call('cache:clear');
            Artisan::call('module:dump-autoload');
        } catch (Exception $e) {
            Log::warning('Cache refresh partial failure', ['error' => $e->getMessage()]);
        }
    }

    /* ===================================================================
     *  خواندن نسخه از module.json
     * =================================================================== */
    private function readModuleVersion(string $moduleName): ?string
    {
        $path = config('packages.modules.path') . '/' . $moduleName . '/module.json';
        if (!File::exists($path)) {
            return null;
        }
        $data = json_decode(File::get($path), true);
        return $data['version'] ?? null;
    }

    /* ===================================================================
     *  پاکسازی فایل موقت
     * =================================================================== */
    private function cleanupTemp(string $zipPath): void
    {
        try {
            @unlink($zipPath);
        } catch (Exception $e) {
            // نادیده
        }
    }

    /* ===================================================================
     *  لاگ‌گیری مراحل
     * =================================================================== */
    private function step(string $name, string $label): void
    {
        $this->steps[] = ['name' => $name, 'label' => $label, 'at' => now()->toDateTimeString()];
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
        $log->update([
            'status'     => $status,
            'to_version' => $installed?->version,
            'message'    => $message,
            'details'    => ['steps' => $this->steps],
        ]);
    }

    private function failLog(ModuleInstallLog $log, Exception $e, string $slug): void
    {
        Log::error('Package install/update failed', [
            'slug'  => $slug,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);

        $log->update([
            'status'  => ModuleInstallLog::STATUS_FAILED,
            'message' => $e->getMessage(),
            'details' => ['steps' => $this->steps, 'trace' => $e->getTraceAsString()],
        ]);

        $installed = InstalledModule::where('slug', $slug)->first();
        if ($installed) {
            $installed->markAsFailed($e->getMessage());
        }
    }
}
