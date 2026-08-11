<?php

namespace App\Services;

/**
 * Registry مرکزی برای ویجت‌های داشبورد ادمین
 * ماژول‌ها ویجت‌های خودشون رو اینجا ثبت می‌کنن
 *
 * نحوه استفاده در ServiceProvider ماژول:
 *   DashboardWidgetRegistry::add('installment-payment::back.partials.dashboard_widget', 80);
 *
 * نحوه استفاده در داشبورد:
 *   @foreach(app(App\Services\DashboardWidgetRegistry::class)->all() as $widgetView)
 *       @if(view()->exists($widgetView))
 *           @include($widgetView)
 *       @endif
 *   @endforeach
 */
class ModuleWidgetRegistry
{
    /**
     * @var array<string, int> لیست viewهای ویجت با priority
     */
    protected static array $widgets = [];

    /**
     * ثبت یک ویجت داشبورد
     *
     * @param string $view  مسیر ویو (مثلاً 'mymodule::back.partials.dashboard_widget')
     * @param int $priority ترتیب نمایش (کمتر = بالاتر)
     */
    public static function add(string $view, int $priority = 100): void
    {
        static::$widgets[$view] = $priority;
    }

    /**
     * دریافت همه‌ی ویجت‌ها به ترتیب priority
     *
     * @return array<string>
     */
    public static function all(): array
    {
        $widgets = static::$widgets;
        asort($widgets);
        return array_keys($widgets);
    }

    /**
     * پاک کردن همه‌ی ویجت‌ها (برای تست)
     */
    public static function clear(): void
    {
        static::$widgets = [];
    }
}
