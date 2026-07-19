<?php

namespace App\Services;

/**
 * Registry مرکزی برای تعریف ویجت‌های قابل انتخاب در پنل ادمین
 *
 * هر ماژول می‌تونه ویجت‌های خودش رو با متادیتای کامل (title, image, options, rules)
 * برای یک "صفحه" خاص (home, posts, products, ...) ثبت کنه.
 */
class WidgetTemplateRegistry
{
    /**
     * @var array<string, array<string, array>> لیست ویجت‌های ثبت‌شده
     *   ساختار: [page => [key => config]]
     */
    protected static array $widgets = [];

    /**
     * ثبت یک ویجت برای یک صفحه‌ی مشخص
     *
     * @param string $page    نوع صفحه: 'home', 'posts', 'products', ...
     * @param string $key     کلید یکتا ویجت (مثلاً 'main-story')
     * @param array  $config  شامل title, image, options, rules
     * @param string $module  نام ماژول (مثلاً 'Story')
     */
    public static function register(string $page, string $key, array $config, string $module): void
    {
        // اگر image از نوع relative هست، URL کامل بساز
        $imageUrl = $config['image'] ?? null;
        if ($imageUrl && !preg_match('#^https?://#', $imageUrl)) {
            $imageUrl = module_asset($module, $config['image']);
        }

        if (!isset(static::$widgets[$page])) {
            static::$widgets[$page] = [];
        }

        static::$widgets[$page][$key] = array_merge($config, [
            'key'       => $key,
            'module'    => $module,
            'page'      => $page,
            'image_url' => $imageUrl,
        ]);
    }

    /**
     * ثبت گروهی ویجت‌ها برای یک صفحه
     *
     * @param string $page     نوع صفحه
     * @param array  $widgets  آرایه‌ی ویجت‌ها (key => config)
     * @param string $module   نام ماژول
     */
    public static function registerMany(string $page, array $widgets, string $module): void
    {
        foreach ($widgets as $key => $config) {
            static::register($page, $key, $config, $module);
        }
    }

    /**
     * دریافت یک ویجت با کلید (از همه‌ی صفحه‌ها جستجو می‌کنه)
     */
    public static function get(string $key): ?array
    {
        foreach (static::$widgets as $page => $widgets) {
            if (isset($widgets[$key])) {
                return $widgets[$key];
            }
        }
        return null;
    }

    /**
     * دریافت یک ویجت برای یک صفحه‌ی مشخص
     */
    public static function getForPage(string $page, string $key): ?array
    {
        return static::$widgets[$page][$key] ?? null;
    }

    /**
     * دریافت همه‌ی ویجت‌های ثبت‌شده توسط ماژول‌ها برای یک صفحه‌ی مشخص
     *
     * @return array<string, array>
     */
    public static function allForPage(string $page): array
    {
        return static::$widgets[$page] ?? [];
    }

    /**
     * دریافت همه‌ی ویجت‌های ثبت‌شده توسط ماژول‌ها (همه‌ی صفحه‌ها)
     *
     * @return array<string, array<string, array>>
     */
    public static function all(): array
    {
        return static::$widgets;
    }

    /**
     * بررسی وجود ویجت (در همه‌ی صفحه‌ها)
     */
    public static function has(string $key): bool
    {
        foreach (static::$widgets as $page => $widgets) {
            if (isset($widgets[$key])) {
                return true;
            }
        }
        return false;
    }

    /**
     * بررسی وجود ویجت برای یک صفحه‌ی مشخص
     */
    public static function hasForPage(string $page, string $key): bool
    {
        return isset(static::$widgets[$page][$key]);
    }

    /* ===================================================================
     *  متدهای کمکی برای ترکیب با configهای اصلی پروژه
     * =================================================================== */

    /**
     * دریافت همه‌ی کلیدهای مجاز برای یک صفحه (برای validation)
     * شامل: home-widgets (یا posts-widgets) + module widgets
     *
     * @return array<string>
     */
    public static function allKeysForPage(string $page): array
    {
        $coreConfig = static::getCoreConfigForPage($page);
        $moduleWidgets = static::allForPage($page);

        $keys = array_merge(
            array_keys($coreConfig),
            array_keys($moduleWidgets)
        );
        return array_unique($keys);
    }

    /**
     * دریافت config یک ویجت برای یک صفحه (از ماژول یا core)
     *
     * @return array|null
     */
    public static function findConfigForPage(string $page, string $key): ?array
    {
        // 1) ویجت‌های ماژول‌ها (اولویت اول)
        if (static::hasForPage($page, $key)) {
            return static::getForPage($page, $key);
        }

        // 2) configهای اصلی پروژه
        $coreConfig = static::getCoreConfigForPage($page);
        return $coreConfig[$key] ?? null;
    }

    /**
     * دریافت همه‌ی ویجت‌ها (هم اصلی و هم ماژول) برای یک صفحه
     *
     * @return array<string, array>
     */
    public static function mergedForPage(string $page): array
    {
        $coreConfig = static::getCoreConfigForPage($page);
        $moduleWidgets = static::allForPage($page);
        return array_merge($coreConfig, $moduleWidgets);
    }

    /**
     * نگاشت page → config key در پروژه اصلی
     *
     * @return array
     */
    protected static function getCoreConfigForPage(string $page): array
    {
        $mapping = [
            'home'     => 'front.home-widgets',
            'posts'    => 'front.posts-widgets',
            'products' => 'front.products-widgets', // اگه دارید
        ];

        $configKey = $mapping[$page] ?? "front.{$page}-widgets";
        return config($configKey, []);
    }

    /**
     * دریافت همه‌ی ویجت‌ها (هم اصلی و هم ماژول) برای یک صفحه
     * به‌صورت یک آرایه‌ی مسطح با metadata کامل (شامل source و module)
     *
     * @return array<int, array>  هر آیتم شامل: key, title, image, source, module
     */
    public static function listForPage(string $page): array
    {
        $result = [];
        $coreConfig = static::getCoreConfigForPage($page);
        $moduleWidgets = static::allForPage($page);

        // ویجت‌های اصلی پروژه
        foreach ($coreConfig as $key => $config) {
            $result[] = [
                'key'       => $key,
                'title'     => $config['title'] ?? $key,
                'image'     => isset($config['image']) ? theme_asset($config['image']) : null,
                'source'    => 'core',
                'module'    => null,
            ];
        }

        // ویجت‌های ماژول‌ها
        foreach ($moduleWidgets as $key => $config) {
            $result[] = [
                'key'       => $key,
                'title'     => $config['title'] ?? $key,
                'image'     => $config['image_url'] ?? null,
                'source'    => 'module',
                'module'    => $config['module'] ?? null,
            ];
        }

        return $result;
    }

    /**
     * پاک کردن همه (برای تست)
     */
    public static function clear(): void
    {
        static::$widgets = [];
    }
}
