<?php

namespace App\Services;

/**
 * Registry مرکزی برای منوهای فرانت (پنل کاربر)
 * ماژولها منوهای فرانت خودشون رو اینجا ثبت می‌کنن
 *
 * نحوه استفاده در ServiceProvider ماژول:
 *   FrontModuleMenuRegistry::add('installment-payment::front.partials.menu', 50);
 *
 * نحوه استفاده در layout فرانت:
 *   @foreach(app(App\Services\FrontModuleMenuRegistry::class)->all() as $menuView)
 *       @include($menuView)
 *   @endforeach
 */
class FrontModuleMenuRegistry
{
    /**
     * @var array<string> لیست viewهای منوی فرانت
     */
    protected static array $menus = [];

    /**
     * ثبت یک منوی فرانت ماژول
     *
     * @param string $view  مسیر ویو (مثلاً 'mymodule::front.partials.menu')
     * @param int $priority ترتیب نمایش (کمتر = بالاتر)
     */
    public static function add(string $view, int $priority = 100): void
    {
        static::$menus[$view] = $priority;
    }

    /**
     * دریافت همه‌ی منوهای فرانت به ترتیب priority
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
