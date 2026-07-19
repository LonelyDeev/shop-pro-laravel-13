<?php

namespace App\Http\Controllers\Back;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Widget;
use App\Services\WidgetTemplateRegistry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PostWidgetController extends Controller
{
    public function __construct()
    {
        // اگه هیچ ویجتی برای صفحه‌ی posts وجود نداشت → 404
        if (empty(WidgetTemplateRegistry::allKeysForPage('posts'))) {
            abort(404);
        }
    }

    public function index()
    {
        $this->authorize('themes.widgets');

        $theme   = get_current_theme();
        $widgets = Widget::detectLang()->where('theme', $theme['name'] ?? '')
            ->where('page', 'posts')
            ->orderBy('ordering')
            ->get();

        return view('back.posts-widgets.index', compact('widgets'));
    }

    public function create()
    {
        $this->authorize('themes.widgets');
        return view('back.posts-widgets.create');
    }

    public function store(Request $request)
    {
        // کلیدهای مجاز برای صفحه‌ی posts (شامل ماژول‌ها)
        $keys = implode(',', WidgetTemplateRegistry::allKeysForPage('posts'));

        $request->validate([
            'key'         => "required|in:$keys",
            'options'     => 'required|array',
            'is_active'   => 'boolean'
        ]);

        // دریافت template از هر source (برای صفحه‌ی posts)
        $template = WidgetTemplateRegistry::findConfigForPage('posts', $request->key);

        if (!$template) {
            return back()->withErrors(['key' => 'ویجت یافت نشد.'])->withInput();
        }

        Validator::make($request->options, $template['rules'] ?? [])->validate();

        $widget = Widget::create([
            'title'       => $request->title,
            'page'        => 'posts',
            'key'         => $request->key,
            'is_active'   => $request->is_active,
            'theme'       => current_theme_name(),
            'lang'        => app()->getLocale(),
        ]);

        $options = $this->getRequestOptions($template, $request, $widget);

        $this->saveWidgetOptions($widget, $options);

        session()->put('toast-success','ابزارک با موفقیت ایجاد شد.');
        return response('success');
    }

    public function edit(Widget $posts_widget)
    {
        $this->authorize('themes.widgets');
        $widget = $posts_widget;
        $template = $this->template($widget->key, $widget);

        return view('back.posts-widgets.edit', compact('widget', 'template'));
    }

    public function update(Widget $posts_widget, Request $request)
    {
        $widget = $posts_widget;
        $keys = implode(',', WidgetTemplateRegistry::allKeysForPage('posts'));

        $request->validate([
            'key'         => "required|in:$keys",
            'options'     => 'required|array',
            'is_active'   => 'boolean'
        ]);

        $template = WidgetTemplateRegistry::findConfigForPage('posts', $request->key);

        if (!$template) {
            return back()->withErrors(['key' => 'ویجت یافت نشد.'])->withInput();
        }

        Validator::make($request->options, $template['rules'] ?? [])->validate();

        $allChanges = [];
        $adminName = auth('adminPanel')->user()->full_name ?? auth('adminPanel')->user()->name ?? 'مدیر';
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

                // تبدیل key به نام خوانا (با پشتیبانی از ماژول‌ها)
                if ($field === 'key') {
                    $widgetsList = WidgetTemplateRegistry::mergedForPage('posts');
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

        // ========== 2. دریافت options قدیمی (قبل از update) ==========
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
            'page'        => 'posts',
            'title'       => $request->title,
            'key'         => $request->key,
            'is_active'   => $request->is_active,
        ]);

        // ========== 4. ذخیره options جدید ==========
        $widget->options()->delete();
        $options = $this->getRequestOptions($template, $request, $widget);
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
        $allOptionKeys = array_unique(array_merge(array_keys($oldOptions), array_keys($newOptions)));

        foreach ($allOptionKeys as $optKey) {
            $oldValue = $oldOptions[$optKey] ?? null;
            $newValue = $newOptions[$optKey] ?? null;

            if ($oldValue != $newValue) {
                $formattedOld = $this->formatOptionValue($optKey, $oldValue);
                $formattedNew = $this->formatOptionValue($optKey, $newValue);

                $optionChanges[$optKey] = [
                    'old' => $formattedOld,
                    'new' => $formattedNew,
                    'title' => $this->getOptionFieldTitle($optKey)
                ];
            }
        }

        if (!empty($optionChanges)) {
            $allChanges['options'] = $optionChanges;
        }

        // ========== 7. ثبت لاگ ==========
        if (!empty($allChanges)) {
            $changeDetails = [];

            if (isset($allChanges['main_fields'])) {
                foreach ($allChanges['main_fields'] as $change) {
                    $changeDetails[] = "{$change['title']} از «{$change['old']}» به «{$change['new']}»";
                }
            }

            if (isset($allChanges['options'])) {
                foreach ($allChanges['options'] as $change) {
                    $changeDetails[] = "{$change['title']} از «{$change['old']}» به «{$change['new']}»";
                }
            }

            $logMessage = "مدیر {$adminName} ابزارک صفحه مقالات «{$widgetTitle}» را ویرایش کرد: " . implode('، ', $changeDetails);

            activity()
                ->performedOn($widget)
                ->causedBy(auth('adminPanel')->user())
                ->withProperties([
                    'action' => 'ابزارک صفحه مقالات را ویرایش کرد',
                    'widget_title' => $widgetTitle,
                    'widget_id' => $widget->id,
                    'page' => 'posts',
                    'changes' => $allChanges,
                    'ip' => $request->ip()
                ])
                ->log($logMessage);
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

        if (is_bool($value)) {
            return $value ? 'بله' : 'خیر';
        }

        return $value ?: 'خالی';
    }

    /**
     * دریافت عنوان فارسی فیلدهای option
     */
    private function getOptionFieldTitle($key)
    {
        $titles = [
            'title' => 'عنوان',
            'link' => 'لینک',
            'link_title' => 'عنوان لینک',
            'post_categories' => 'دسته‌بندی‌های مقالات',
            'product_categories' => 'دسته‌بندی‌های محصولات',
            'limit' => 'تعداد نمایش',
            'order_by' => 'ترتیب نمایش',
            'sort_by' => 'مرتب‌سازی بر اساس',
            'show_more' => 'نمایش دکمه بیشتر',
            'show_pagination' => 'نمایش صفحه‌بندی',
            'background_color' => 'رنگ پس‌زمینه',
            'text_color' => 'رنگ متن',
            'button_text' => 'متن دکمه',
            'button_link' => 'لینک دکمه',
            'image' => 'تصویر',
            'image_mobile' => 'تصویر موبایل',
            'banner_link' => 'لینک بنر',
            'alt_text' => 'متن جایگزین تصویر',
            // تنظیمات استوری (اگه ماژول برای posts هم ثبت بشه)
            'number' => 'تعداد استوری',
            'ordering' => 'ترتیب نمایش',
        ];

        return $titles[$key] ?? str_replace('_', ' ', $key);
    }

    public function destroy(Widget $posts_widget)
    {
        $this->authorize('themes.widgets');
        $widget = $posts_widget;
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

    /**
     * نمایش فرم تنظیمات ویجت برای صفحه‌ی posts
     */
    public function template($key, $widget = null)
    {
        $this->authorize('themes.widgets');

        // دریافت template از هر source (برای صفحه‌ی posts)
        $template = WidgetTemplateRegistry::findConfigForPage('posts', $key);
        $options = $template['options'] ?? null;

        $product_categories = Category::detectLang()->where('type', 'productcat')->orderBy('ordering')->get();
        $post_categories    = Category::detectLang()->where('type', 'postcat')->orderBy('ordering')->get();

        if (!$options) {
            return '';
        }

        return view('back.posts-widgets.template', compact(
            'options',
            'widget',
            'product_categories',
            'post_categories'
        ));
    }

    private function getRequestOptions($template, $request, Widget $widget)
    {
        $options = [];

        foreach ($template['options'] as $key => $option) {
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
