<?php

namespace App\Services;

/**
 * Registry مرکزی برای منوهای سایدبار
 * ماژول‌ها منوهای خودشون رو اینجا ثبت می‌کنن
 */
class ModuleMenuRegistry
{
    /**
     * @var array<string> لیست viewهای منو
     */
    protected static array $menus = [];

    /**
     * ثبت یک منوی ماژول
     *
     * @param string $view  مسیر ویو (مثلاً 'story::back.partials.menu')
     * @param int $priority ترتیب نمایش (کمتر = بالاتر)
     */
    public static function add(string $view, int $priority = 100): void
    {
        static::$menus[$view] = $priority;
    }

    /**
     * دریافت همه‌ی منوها به ترتیب priority
     *
     * @return array<string>
     */
    public static function all(): array
    {
        $menus = static::$menus;
        asort($menus);
        return array_keys($menus);
    }

    /**
     * پاک کردن همه‌ی منوها (برای تست)
     */
    public static function clear(): void
    {
        static::$menus = [];
    }
}
