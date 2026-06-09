<?php

namespace App\Http\Controllers\Back;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Widget;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class WidgetController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Widget::class, 'widget');

        if (!config('front.home-widgets')) {
            abort(404);
        }
    }

    public function index()
    {
        $theme   = get_current_theme();
        $widgets = Widget::detectLang()->where('theme', $theme['name'] ?? '')
            ->where('page','home')
            ->orderBy('ordering')
            ->get();

        return view('back.widgets.index', compact('widgets'));
    }

    public function create()
    {
        return view('back.widgets.create');
    }

    public function store(Request $request)
    {
        $keys = implode(',', array_keys(config('front.home-widgets')));

        $request->validate([
            'key'         => "required|in:$keys",
            'options'     => 'required|array',
            'is_active'   => 'boolean'
        ]);

        $key   = config('front.home-widgets.' . $request->key);

        Validator::make($request->options, $key['rules'])->validate();

        $widget = Widget::create([
            'title'       => $request->title,
            'key'         => $request->key,
            'is_active'   => $request->is_active,
            'theme'       => current_theme_name(),
            'lang'        => app()->getLocale(),
        ]);

        $options = $this->getRequestOptions($key, $request, $widget);

        $this->saveWidgetOptions($widget, $options);

        session()->put('toast-success','ابزارک با موفقیت ایجاد شد.');
        return response('success');
    }

    public function edit(Widget $widget)
    {
        $template = $this->template($widget->key, $widget);

        return view('back.widgets.edit', compact('widget', 'template'));
    }

    public function update(Widget $widget, Request $request)
    {
        $keys = implode(',', array_keys(config('front.home-widgets')));

        $request->validate([
            'key'         => "required|in:$keys",
            'options'     => 'required|array',
            'is_active'   => 'boolean'
        ]);

        $key = config('front.home-widgets.' . $request->key);

        Validator::make($request->options, $key['rules'])->validate();

        $allChanges = [];
        $adminName = auth()->user()->full_name ?? auth()->user()->name ?? 'مدیر';
        $widgetTitle = $widget->title;

        // ========== 1. بررسی تغییرات فیلدهای اصلی ==========
        $mainFields = ['title', 'key', 'is_active'];
        $mainChanges = [];

        foreach ($mainFields as $field) {
            $oldValue = $widget->$field;
            $newValue = $request->$field;

            if ($oldValue != $newValue) {
                $fieldTitles = [
                    'title' => 'عنوان ابزارک',
                    'key' => 'نوع ابزارک',
                    'is_active' => 'وضعیت'
                ];

                // تبدیل مقادیر boolean به متن
                if ($field === 'is_active') {
                    $oldValue = $oldValue ? 'فعال' : 'غیرفعال';
                    $newValue = $newValue ? 'فعال' : 'غیرفعال';
                }

                // تبدیل key به نام خوانا
                if ($field === 'key') {
                    $widgetsList = config('front.home-widgets', []);
                    $oldValue = $widgetsList[$oldValue]['title'] ?? $oldValue;
                    $newValue = $widgetsList[$newValue]['title'] ?? $newValue;
                }

                $mainChanges[$field] = [
                    'old' => $oldValue ?: 'خالی',
                    'new' => $newValue ?: 'خالی',
                    'title' => $fieldTitles[$field] ?? $field
                ];
            }
        }

        if (!empty($mainChanges)) {
            $allChanges['main_fields'] = $mainChanges;
        }

        // ========== 2. دریافت options قدیمی ==========
        $oldOptions = [];
        foreach ($widget->options as $option) {
            if ($option->key === 'post_categories' || $option->key === 'product_categories') {
                $oldOptions[$option->key] = $option->categories->pluck('id')->toArray();
            } else {
                $oldOptions[$option->key] = $option->value;
            }
        }

        // ========== 3. به‌روزرسانی ویجت ==========
        $widget->update([
            'title'       => $request->title,
            'key'         => $request->key,
            'is_active'   => $request->is_active,
        ]);

        // ========== 4. ذخیره options جدید ==========
        $options = $this->getRequestOptions($key, $request, $widget);
        $widget->options()->delete();
        $this->saveWidgetOptions($widget, $options);

        // ========== 5. دریافت options جدید ==========
        $widget->refresh();
        $newOptions = [];
        foreach ($widget->options as $option) {
            if ($option->key === 'post_categories' || $option->key === 'product_categories') {
                $newOptions[$option->key] = $option->categories->pluck('id')->toArray();
            } else {
                $newOptions[$option->key] = $option->value;
            }
        }

        // ========== 6. بررسی تغییرات options ==========
        $optionChanges = [];
        $allKeys = array_unique(array_merge(array_keys($oldOptions), array_keys($newOptions)));

        foreach ($allKeys as $optKey) {
            $oldValue = $oldOptions[$optKey] ?? null;
            $newValue = $newOptions[$optKey] ?? null;

            if ($oldValue != $newValue) {
                // فرمت کردن مقادیر برای نمایش
                $formattedOld = $this->formatOptionValue($optKey, $oldValue);
                $formattedNew = $this->formatOptionValue($optKey, $newValue);

                $optionChanges[$optKey] = [
                    'old' => $formattedOld,
                    'new' => $formattedNew,
                    'title' => $this->getHomeOptionFieldTitle($optKey)
                ];
            }
        }

        if (!empty($optionChanges)) {
            $allChanges['options'] = $optionChanges;
        }

        // ========== 7. ثبت لاگ ==========
        if (!empty($allChanges)) {
            $changeDetails = [];

            // تغییرات فیلدهای اصلی
            if (isset($allChanges['main_fields'])) {
                foreach ($allChanges['main_fields'] as $change) {
                    $changeDetails[] = "{$change['title']} از «{$change['old']}» به «{$change['new']}»";
                }
            }

            // تغییرات options
            if (isset($allChanges['options'])) {
                foreach ($allChanges['options'] as $change) {
                    $changeDetails[] = "{$change['title']} از «{$change['old']}» به «{$change['new']}»";
                }
            }

            $logMessage = "مدیر {$adminName} ابزارک صفحه اصلی «{$widgetTitle}» را ویرایش کرد: " . implode('، ', $changeDetails);

            activity()
                ->performedOn($widget)
                ->causedBy(auth()->user())
                ->withProperties([
                    'action' => 'ابزارک  صفحه اصلی را ویرایش کرد',
                    'widget_title' => $widgetTitle,
                    'widget_id' => $widget->id,
                    'page' => 'home',
                    'changes' => $allChanges,
                    'ip' => $request->ip()
                ])
                ->log($logMessage);
        } else {
            // اگر تغییری نکرده بود
          /*  activity()
                ->performedOn($widget)
                ->causedBy(auth()->user())
                ->withProperties([
                    'action' => 'update_home_widget_no_change',
                    'widget_title' => $widgetTitle,
                    'widget_id' => $widget->id,
                    'page' => 'home',
                    'ip' => $request->ip()
                ])
                ->log("مدیر {$adminName} ابزارک صفحه اصلی «{$widgetTitle}» را ویرایش کرد اما تغییری اعمال نشد");*/
        }

        session()->put('toast-success', 'ابزارک با موفقیت ویرایش شد.');
        return response('success');
    }

    /**
     * فرمت کردن مقدار option برای نمایش
     */
    private function formatOptionValue($key, $value)
    {
        if (is_null($value)) {
            return 'خالی';
        }

        if (is_array($value)) {
            if (empty($value)) {
                return 'هیچ موردی انتخاب نشده';
            }

            // برای دسته‌بندی‌ها، نام دسته‌بندی‌ها را بگیر
            if ($key === 'post_categories' || $key === 'product_categories') {
                try {
                    $categoryNames = [];
                    $categories = \App\Models\Category::whereIn('id', $value)->get();

                    foreach ($categories as $category) {
                        if (isset($category->title) && $category->title) {
                            $categoryNames[] = $category->title;
                        } elseif (isset($category->name) && $category->name) {
                            $categoryNames[] = $category->name;
                        } else {
                            $categoryNames[] = "#{$category->id}";
                        }
                    }

                    return !empty($categoryNames) ? implode('، ', $categoryNames) : implode('، ', $value);
                } catch (\Exception $e) {
                    return implode('، ', $value);
                }
            }

            return implode('، ', $value);
        }

        // تبدیل مقادیر boolean
        if (is_bool($value)) {
            return $value ? 'بله' : 'خیر';
        }

        return $value ?: 'خالی';
    }

    /**
     * دریافت عنوان فارسی فیلدهای option برای صفحه اصلی
     */
    private function getHomeOptionFieldTitle($key)
    {
        $titles = [
            // تنظیمات عمومی
            'title' => 'عنوان',
            'link' => 'لینک',
            'link_title' => 'عنوان لینک',
            'limit' => 'تعداد نمایش',
            'order_by' => 'ترتیب نمایش',
            'sort_by' => 'مرتب‌سازی بر اساس',
            'show_more' => 'نمایش دکمه بیشتر',
            'show_pagination' => 'نمایش صفحه‌بندی',
            'ordering' => 'ترتیب نمایش',

            // تنظیمات ظاهری
            'background_color' => 'رنگ پس‌زمینه',
            'text_color' => 'رنگ متن',
            'button_text' => 'متن دکمه',
            'button_link' => 'لینک دکمه',

            // تنظیمات تصاویر
            'image' => 'تصویر',
            'image_mobile' => 'تصویر موبایل',
            'banner_link' => 'لینک بنر',
            'alt_text' => 'متن جایگزین تصویر',

            // تنظیمات اسلایدر
            'slides' => 'اسلایدها',
            'autoplay' => 'پخش خودکار',
            'autoplay_speed' => 'سرعت پخش خودکار',
            'dots' => 'نقاط ناوبری',
            'arrows' => 'فلش‌های ناوبری',

            // تنظیمات دسته‌بندی
            'post_categories' => 'دسته‌بندی‌های مقالات',
            'product_categories' => 'دسته‌بندی‌های محصولات',

            // تنظیمات محصولات
            'product_ids' => 'محصولات خاص',
            'show_price' => 'نمایش قیمت',
            'show_rating' => 'نمایش امتیاز',
            'show_quick_view' => 'نمایش دکمه مشاهده سریع',

            // تنظیمات مقالات
            'post_ids' => 'مقالات خاص',
            'show_excerpt' => 'نمایش خلاصه',
            'show_author' => 'نمایش نویسنده',
            'show_date' => 'نمایش تاریخ',

            // تنظیمات بنر
            'banner_position' => 'موقعیت بنر',
            'banner_width' => 'عرض بنر',
            'banner_height' => 'ارتفاع بنر',
        ];

        return $titles[$key] ?? str_replace('_', ' ', $key);
    }

    public function destroy(Widget $widget)
    {
        $widget->delete();

        return response('success');
    }

    public function sort(Request $request)
    {
        $this->authorize('themes.widgets');

        $this->validate($request, [
            'widgets' => 'required|array'
        ]);

        $i = 1;

        foreach ($request->widgets as $widget) {
            Widget::findOrFail($widget)->update([
                'ordering' => $i++,
            ]);
        };

        return response('success');
    }

    public function template($key, $widget = null)
    {
        $this->authorize('themes.widgets');

        $options = config('front.home-widgets.' . $key . '.options');

        $product_categories = Category::detectLang()->where('type', 'productcat')->orderBy('ordering')->get();
        $post_categories    = Category::detectLang()->where('type', 'postcat')->orderBy('ordering')->get();

        if (!$options) {
            return '';
        }

        return view('back.widgets.template', compact(
            'options',
            'widget',
            'product_categories',
            'post_categories'
        ));
    }

    private function getRequestOptions($key, $request, Widget $widget)
    {
        $options = [];

        foreach ($key['options'] as $key => $option) {
            switch ($option['input-type']) {
                case 'input': {
                        $options[$key]['input-type'] = $option['input-type'];
                        $options[$key]['key'] = $option['key'];
                        $options[$key]['value'] = $request->input('options.' . $option['key']);
                        break;
                    }

                case 'file': {
                        $file = $request->file('options.' . $option['key']);
                    if (!file_exists(public_path("/uploads/widgets"))) {
                        Storage::disk('public')->makeDirectory("/uploads/widgets");
                    }
                        if ($file) {
                            $name = uniqid() . '_' . time() . '.' . $file->getClientOriginalExtension();
                            $path = $file->storeAs('widgets', $name);
                            $options[$key]['value'] = '/uploads/' . $path;
                        } else {
                            $options[$key]['value'] = $widget->option($option['key']);
                        }

                        $options[$key]['input-type'] = $option['input-type'];
                        $options[$key]['key'] = $option['key'];

                        break;
                    }

                case 'select': {
                        $options[$key]['input-type'] = $option['input-type'];
                        $options[$key]['key'] = $option['key'];
                        $options[$key]['value'] = $request->input('options.' . $option['key']);
                        break;
                    }

                case 'post_categories':
                case 'product_categories': {
                        $options[$key]['input-type'] = $option['input-type'];
                        $options[$key]['key'] = $option['key'];
                        $options[$key]['value'] = $request->input('options.' . $option['key']);
                        break;
                    }
            }
        }

        return $options;
    }

    private function saveWidgetOptions(Widget $widget, $options)
    {
        foreach ($options as $option) {
            switch ($option['input-type']) {
                case 'post_categories':
                case 'product_categories': {
                        $value = is_array($option['value']) && !empty($option['value']) ? 'on' : 'off';

                        $inserted_option = $widget->options()->create([
                            'key'   => $option['key'],
                            'value' => $value
                        ]);

                        $inserted_option->categories()->sync($option['value']);

                        break;
                    }

                default: {
                        $widget->options()->create([
                            'key'   => $option['key'],
                            'value' => $option['value']
                        ]);
                    }
            }
        }
    }
}
