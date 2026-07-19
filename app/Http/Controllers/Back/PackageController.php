<?php

namespace App\Http\Controllers\Back;

use App\Http\Controllers\Controller;
use App\Jobs\InstallPackageJob;
use App\Jobs\UpdatePackageJob;
use App\Models\InstalledModule;
use App\Models\ModuleInstallLog;
use App\Models\PackageCache;
use App\Services\LicenseVerifier;
use App\Services\PackageApiService;
use App\Services\PackageInstallerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class PackageController extends Controller
{
    public function __construct(
        private PackageApiService $api,
        private LicenseVerifier $licenseVerifier
    ) {}

    /* ===================================================================
     *  لیست پکیج‌های موجود در API
     * =================================================================== */
    public function index(Request $request)
    {
        $query = $request->only(['page', 'search', 'category', 'sort']);
        $cacheKey = 'packages.list.' . md5(json_encode($query));

        try {
            $data = Cache::remember($cacheKey, now()->addMinutes(config('packages.cache.list_ttl')), function () use ($query) {
                return $this->api->listPackages($query);
            });
            $packages = $data['data'] ?? $data['packages'] ?? [];
            $pagination = $data['meta'] ?? $data['pagination'] ?? [];

            // محاسبه کمترین قیمت و تشخیص رایگان بودن برای هر پکیج
            $packages = array_map(function ($pkg) {
                $plans = $pkg['active_pricing_plans'] ?? $pkg['pricing_plans'] ?? $pkg['plans'] ?? [];

                // محاسبه قیمت نهایی هر پلن (با احتساب تخفیف)
                $planPrices = [];
                foreach ($plans as $plan) {
                    $finalPrice = $plan['discount_price'] ?? $plan['price'] ?? 0;
                    $planPrices[] = (int) $finalPrice;
                }

                // اگر هیچ پلنی نبود، از default_price استفاده کن
                if (empty($planPrices)) {
                    $minPrice = (int) ($pkg['default_price'] ?? $pkg['price'] ?? 0);
                } else {
                    $minPrice = min($planPrices);
                }

                // is_free فقط اگر خود API گفته باشه (پکیج کاملاً رایگان)
                // has_free_plan: اگر حداقل یک پلن رایگان وجود داره
                $pkg['min_price'] = $minPrice;
                $pkg['plans'] = $plans;
                $pkg['has_free_plan'] = $minPrice === 0;
                // is_free از API حفظ می‌شه (اگه API گفته false، همون false می‌مونه)

                return $pkg;
            }, $packages);

            // sync با جدول packages_cache (در background)
            $this->syncCache($packages);

            // دریافت ماژول‌های نصب‌شده برای مقایسه نسخه
            $installedMap = InstalledModule::pluck('version', 'slug')->toArray();

        } catch (RuntimeException $e) {
            $packages = [];
            $pagination = [];
            $installedMap = [];
            session()->flash('error', $e->getMessage());
        }

        return view('back.packages.index', compact('packages', 'pagination', 'installedMap'));
    }

    /* ===================================================================
     *  جزئیات یک پکیج (AJAX - برای نمایش در مدال)
     * =================================================================== */
    public function show(Request $request, string $slug): JsonResponse
    {
        $cacheKey = 'packages.detail.' . $slug;

        try {
            $data = Cache::remember($cacheKey, now()->addMinutes(config('packages.cache.detail_ttl')), function () use ($slug) {
                return $this->api->getPackage($slug);
            });

            $package = $data['data'] ?? $data;

            // enrich: اطلاعات نصب محلی
            $installed = InstalledModule::where('slug', $slug)->first();
            $activeVersions = $package['active_versions'] ?? [];

            if (!empty($activeVersions)) {
                // مرتب‌سازی بر اساس نسخه به صورت نزولی
                usort($activeVersions, function($a, $b) {
                    return version_compare($b['version'], $a['version']);
                });

                $latestVersion = $activeVersions[0]['version'];
            } else {
                $latestVersion = $package['version'] ?? '';
            }
            $hasUpdate = $installed && version_compare($latestVersion, $installed->version, '>');

            $package['installed'] = $installed ? [
                'version'              => $installed->version,
                'status'               => $installed->status,
                'is_active'            => $installed->is_active,
                'is_expired'           => $installed->isExpired(),
                'license_expires_at'   => $installed->license_expires_at?->toDateTimeString(),
                'license_expires_human'=> $installed->license_expires_at?->diffForHumans(),
                'last_error'           => $installed->last_error,
            ] : null;
            $package['has_update'] = $hasUpdate;
            $package['latestVersion'] = $latestVersion;

            // enrich: لاگ‌های اخیر
            $logs = ModuleInstallLog::where('module_slug', $slug)
                ->latest()
                ->limit(10)
                ->get()
                ->map(fn ($log) => [
                    'id'          => $log->id,
                    'action'      => $log->action,
                    'from_version'=> $log->from_version,
                    'to_version'  => $log->to_version,
                    'status'      => $log->status,
                    'message'     => $log->message,
                    'created_at'  => $log->created_at->diffForHumans(),
                ]);

            return response()->json([
                'success' => true,
                'data'    => $package,
                'logs'    => $logs,
            ]);
        } catch (RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /* ===================================================================
     *  ماژول‌های نصب‌شده
     * =================================================================== */
    public function installed()
    {
        $modules = InstalledModule::with('cache')
            ->latest('installed_at')
            ->paginate(20);

        return view('back.packages.installed', compact('modules'));
    }

    /* ===================================================================
     *  شروع فرآیند نصب (ریدایرکت به درگاه)
     *  - پکیج رایگان: مستقیم به install
     *  - پکیج پولی: ایجاد purchase با pricing_plan_id → ریدایرکت به درگاه
     * =================================================================== */
    public function startInstall(Request $request, string $slug)
    {
        $request->validate([
            'version'         => 'nullable|string|max:50',
            'pricing_plan_id' => 'nullable|integer',
        ]);

        try {
            $package = $this->api->getPackage($slug);
            $package = $package['data'] ?? $package;
            $version = $package['active_versions'] ? $package['active_versions'][0]['version'] : null;
            $pricingPlanId = $request->pricing_plan_id;
            $selectedPlan = collect($package['active_pricing_plans'])
                ->firstWhere('id', $pricingPlanId);
            $isFree = false;
            if ($selectedPlan) {
                $isFree = ($selectedPlan['price'] == 0);
            } else {
                // اگر پلن پیدا نشد، از مقدار is_free پکیج استفاده کن
                $isFree = (bool) ($package['is_free'] ?? false);
            }


            // اگر قبلاً نصب شده، جلوگیری شود
            if (InstalledModule::where('slug', $slug)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'این پکیج قبلاً نصب شده است.',
                ], 422);
            }
            if ($isFree) {
                // پکیج رایگان: مستقیم به job
                return $this->dispatchInstall($slug, $version, null,$pricingPlanId);
            }

            // پکیج پولی: ایجاد درخواست خرید با pricing_plan_id
            $callbackUrl = route(config('packages.payment.callback_route'));

            $purchase = $this->api->createPurchase($slug, $callbackUrl, $request->user('adminPanel')->id ?? null, $pricingPlanId);

            // ذخیره رکورد خرید
            $purchaseRecord = \App\Models\PackagePurchase::create([
                'admin_id'      => $request->user('adminPanel')->id ?? null,
                'package_slug'  => $slug,
                'package_name'  => $package['name'] ?? $slug,
                'version'       => $version,
                'amount'        => $purchase['amount'] ?? ($package['price'] ?? 0),
                'gateway'       => $purchase['gateway'] ?? null,
                'transaction_id' => $purchase['transaction_id'] ?? null,
                'payment_url'   => isset($purchase['payment_url']) ? $purchase['payment_url'] : null,
                'status'        => \App\Models\PackagePurchase::STATUS_PENDING,
                'meta'          => $purchase,
            ]);

            return response()->json([
                'success'      => true,
                'redirect_url' => isset($purchase['payment_url']) ? $purchase['payment_url'] : null,
                'purchase_id'  => $purchaseRecord->id,
            ]);

        } catch (RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /* ===================================================================
     *  آپدیت ماژول نصب‌شده
     * =================================================================== */
    public function update(Request $request, string $slug)
    {
        $installed = InstalledModule::where('slug', $slug)->first();

        if (!$installed) {
            return response()->json([
                'success' => false,
                'message' => 'ماژول نصب نشده است.',
            ], 404);
        }

        if ($installed->status === InstalledModule::STATUS_UPDATING) {
            return response()->json([
                'success' => false,
                'message' => 'آپدیت قبلی همچنان در حال اجراست.',
            ], 422);
        }

        if ($installed->isExpired()) {
            return response()->json([
                'success' => false,
                'message' => 'لایسنس ماژول منقضی شده است. ابتدا آن را تمدید کنید.',
            ], 422);
        }
        UpdatePackageJob::dispatch($slug, $request->user('adminPanel')->id ?? null);

        return response()->json([
            'success' => true,
            'message' => 'آپدیت در صف پردازش قرار گرفت.',
        ]);
    }

    /* ===================================================================
     *  حذف ماژول
     * =================================================================== */
    public function uninstall(Request $request, string $slug)
    {
        try {
            app(PackageInstallerService::class)->uninstall(
                $slug,
                $request->user('adminPanel')->id ?? null
            );

            return response()->json([
                'success' => true,
                'message' => 'ماژول با موفقیت حذف شد.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حذف ناموفق بود: ' . $e->getMessage(),
            ], 500);
        }
    }

    /* ===================================================================
     *  فعال/غیرفعال‌سازی
     * =================================================================== */
    public function toggleActivation(Request $request, string $slug)
    {
        try {
            $installed = app(PackageInstallerService::class)->toggleActivation($slug);
            return response()->json([
                'success'  => true,
                'is_active' => $installed->is_active,
                'message'  => $installed->is_active
                    ? 'ماژول فعال شد.'
                    : 'ماژول غیرفعال شد.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /* ===================================================================
     *  بررسی وضعیت نصب (برای polling)
     * =================================================================== */
    public function status(string $slug): JsonResponse
    {
        $installed = InstalledModule::where('slug', $slug)->first();
        $latestLog = ModuleInstallLog::where('module_slug', $slug)
            ->latest()
            ->first();

        return response()->json([
            'installed'  => $installed ? [
                'version'    => $installed->version,
                'status'     => $installed->status,
                'is_active'  => $installed->is_active,
                'error'      => $installed->last_error,
                'updated_at' => $installed->updated_at?->diffForHumans(),
            ] : null,
            'last_log' => $latestLog ? [
                'action'      => $latestLog->action,
                'status'      => $latestLog->status,
                'message'     => $latestLog->message,
                'created_at'  => $latestLog->created_at->diffForHumans(),
                'details'     => $latestLog->details,
            ] : null,
        ]);
    }

    /* ===================================================================
     *  چک آپدیت همه ماژول‌ها
     * =================================================================== */
    public function checkUpdates(): JsonResponse
    {
        $modules = InstalledModule::all();
        $updates = [];

        foreach ($modules as $module) {
            try {
                $result = $this->api->checkUpdate($module->slug, $module->version);
                if ($result['has_update'] ?? false) {
                    $updates[] = [
                        'slug'           => $module->slug,
                        'name'           => $module->name,
                        'current'        => $module->version,
                        'latest'         => $result['latest_version'],
                        'changelog'      => $result['changelog'] ?? null,
                    ];
                }
            } catch (\Exception $e) {
                Log::warning("Update check failed for {$module->slug}: " . $e->getMessage());
            }
        }

        return response()->json([
            'success'      => true,
            'updates'      => $updates,
            'total'        => $modules->count(),
            'update_count' => count($updates),
        ]);
    }

    /* ===================================================================
     *  Private Helpers
     * =================================================================== */
    private function dispatchInstall(string $slug, ?string $version, ?string $licenseKey,?int $pricingPlanId): JsonResponse
    {
        // برای پکیج رایگان، licenseKey برابر null است و download_token مستقیم از API گرفته می‌شود
        $callbackUrl = route(config('packages.payment.callback_route'));
        $purchase = $this->api->createPurchase($slug, $callbackUrl, auth('adminPanel')->id(),$pricingPlanId);
        $licenseKey = $purchase['license_key'] ?? null;
        try {
            // برای پکیج رایگان، ابتدا یک درخواست "purchase" آزاد به API می‌زنیم
            if (!$licenseKey) {
                $callbackUrl = route(config('packages.payment.callback_route'));
                $purchase = $this->api->createPurchase($slug, $callbackUrl, auth('adminPanel')->id(),$pricingPlanId);
                $licenseKey = $purchase['license_key'] ?? null;
            }

            InstallPackageJob::dispatch(
                $slug,
                $licenseKey,
                auth('adminPanel')->id(),
                null, // purchaseId در callback پر می‌شود
                null  // downloadToken از داخل job گرفته می‌شود
            );

            // برای پکیج رایگان، redirect_url برنمی‌گردونیم تا JS progress bar رو نشون بده
            return response()->json([
                'success' => true,
                'message' => 'پکیج در صف نصب قرار گرفت.',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    private function syncCache(array $packages): void
    {
        try {
            DB::transaction(function () use ($packages) {
                foreach ($packages as $pkg) {
                    PackageCache::updateOrCreate(
                        ['slug' => $pkg['slug']],
                        [
                            'name'           => $pkg['name'] ?? $pkg['slug'],
                            'description'    => $pkg['description'] ?? null,
                            'latest_version' => $pkg['latest_version'] ? $pkg['latest_version']['version'] : ($pkg['version'] ?? ''),
                            'author'         => $pkg['author'] ?? null,
                            'category'       => $pkg['category'] ?? null,
                            'thumbnail'      => $pkg['thumbnail'] ?? null,
                            'price'          => $pkg['price'] ?? 0,
                            'is_free'        => $pkg['is_free'] ?? false,
                            'meta'           => $pkg,
                            'versions'       => $pkg['versions'] ?? null,
                            'fetched_at'     => now(),
                        ]
                    );
                }
            });
        } catch (\Exception $e) {
            Log::warning('Packages cache sync failed', ['error' => $e->getMessage()]);
        }
    }
}
