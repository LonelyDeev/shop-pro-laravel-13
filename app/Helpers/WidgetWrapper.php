<?php

namespace App\Helpers;

use App\Models\Widget;
use Illuminate\Support\Collection;

class WidgetWrapper
{
    protected array $data;
    protected Collection $options;

    public function __construct(array $data)
    {
        $this->data = $data;

        // اضافه کردن پراپرتی‌ها به صورت مستقیم (به جز 'options')
        foreach ($data as $key => $value) {
            if ($key !== 'options') {
                $this->$key = $value;
            }
        }

        // تبدیل options به Collection از اشیاء OptionWrapper
        $this->options = collect($data['options'] ?? [])->map(function ($option) {
            return new OptionWrapper($option);
        });
    }

    public function __get($name)
    {
        if ($name === 'options') {
            return $this->options;
        }
        return $this->data[$name] ?? null;
    }

    public function __set($name, $value)
    {
        if ($name === 'options') {
            if (is_array($value)) {
                $value = collect($value)->map(function ($option) {
                    return $option instanceof OptionWrapper ? $option : new OptionWrapper($option);
                });
            }
            if (!$value instanceof Collection) {
                throw new \InvalidArgumentException('Property options must be an instance of Collection');
            }
            $this->options = $value;
            $this->data[$name] = $value->toArray();
        } else {
            $this->data[$name] = $value;
            $this->$name = $value;
        }
    }

    public function __isset($name)
    {
        if ($name === 'options') {
            return true;
        }
        return isset($this->data[$name]);
    }

    public function option($key, $default = null)
    {
        // کش کردن مدل ویجت برای جلوگیری از کوئری‌های تکراری
        static $widgetModel = null;

        if ($widgetModel === null && isset($this->data['key'])) {
            $widgetModel = \App\Models\Widget::where('key', $this->data['key'])->first();
        }

        // اگر ویجت در دیتابیس وجود داشت، گزینه را از آن بگیر
        if ($widgetModel) {
            // فرض بر این است که مدل Widget دارای متد یا رابطه‌ای برای دریافت گزینه‌ها است
            // به عنوان مثال: فرض کنید یک متد getOption در مدل Widget وجود دارد
            if (method_exists($widgetModel, 'getOption')) {
                return $widgetModel->getOption($key, $default);
            }

            // یا اگر رابطه‌ای به نام options دارد که یک Collection از مدل‌های Option است
            if (method_exists($widgetModel, 'options')) {
                $option = $widgetModel->options()->where('key', $key)->first();
                if ($option) {
                    return $option->value ?? $default;
                }
            }

            // در غیر این صورت، اگر خود مدل دارای پراپرتی‌های مستقیم برای گزینه‌ها است
            // (بستگی به ساختار دیتابیس شما دارد)
            // می‌توانید به صورت مستقیم به پراپرتی دسترسی پیدا کنید
            if (isset($widgetModel->{$key})) {
                return $widgetModel->{$key};
            }
        }

        // اگر ویجت در دیتابیس نبود، از مقدار پیش‌فرض کانفیگ استفاده کنید
        // (اختیاری: می‌توانید همان منطق قبلی آرایه را هم保留 کنید)
        foreach ($this->data['options'] ?? [] as $option) {
            if (isset($option['key']) && $option['key'] == $key) {
                return $option['default'] ?? $default;
            }
        }

        return $default;
    }
    public function getOptions(): Collection
    {

        return $this->options;
    }

    public function toArray(): array
    {
        return $this->data;
    }
}

/**
 * کلاس Wrapper برای هر Option
 */
class OptionWrapper
{
    protected array $data;

    public function __construct(array $data)
    {
        $this->data = $data;

        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
    }

    public function __get($name)
    {
        return $this->data[$name] ?? null;
    }

    public function hasCategory(): bool
    {
        return false;
    }

    public function categories()
    {
        return collect([]);
    }

    public function categoryFilter()
    {
        return null;
    }

    public function getCategoryIds(): array
    {
        return [];
    }

    public function toArray(): array
    {
        return $this->data;
    }
}
