<?php

namespace App\Services;

/**
 * Registry مرکزی برای ویجت‌های صفحه‌ی اصلی
 * ماژول‌ها داده‌ها و ویوهای ویجت‌شون رو اینجا ثبت می‌کنن
 */
class WidgetRegistry
{
    /**
     * @var array<string, callable> هندلرهای داده
     */
    protected static array $handlers = [];

    /**
     * @var array<string, string> مسیر ویوها
     */
    protected static array $views = [];

    /**
     * ثبت یک ویجت
     *
     * @param string $key      کلید ویجت (مثلاً 'main-story')
     * @param callable $handler تابعی که داده‌ها رو برمی‌گردونه ($widget) => array
     * @param string $view     مسیر ویو (مثلاً 'story::widgets.main-story')
     */
    public static function register(string $key, callable $handler, string $view): void
    {
        static::$handlers[$key] = $handler;
        static::$views[$key] = $view;
    }

    /**
     * دریافت داده‌های یک ویجت
     *
     * @param string $key
     * @param mixed $widget
     * @return array
     */
    public static function handle(string $key, $widget = null): array
    {
        if (!isset(static::$handlers[$key])) {
            return [];
        }

        try {
            return (static::$handlers[$key])($widget) ?? [];
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Widget handler failed for {$key}", [
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }

    /**
     * دریافت مسیر ویوی یک ویجت
     *
     * @param string $key
     * @return string|null
     */
    public static function getView(string $key): ?string
    {
        return static::$views[$key] ?? null;
    }

    /**
     * بررسی وجود ویجت
     */
    public static function has(string $key): bool
    {
        return isset(static::$handlers[$key]);
    }

    /**
     * دریافت همه‌ی کلیدهای ثبت‌شده
     *
     * @return array<string>
     */
    public static function keys(): array
    {
        return array_keys(static::$handlers);
    }
}
