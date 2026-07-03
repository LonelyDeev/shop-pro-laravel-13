<?php

namespace App\Helpers;

use App\Models\Form;
use App\Models\Widget; // فرض بر این است که مدل ویجت ها اینجاست

class ShortcodeHelper
{
    /**
     * تبدیل متن حاوی شورتکد به HTML
     */
    public static function parse($content)
    {
        // 1. پردازش شورتکدهای فرم: [form-123]
        $content = preg_replace_callback('/\[form-(\d+)\]/', function($matches) {
            return self::renderForm($matches[1]);
        }, $content);

        // 2. پردازش شورتکد عمومی ویجت‌ها: [widget-KEY] (مثلا [widget-faqs])
        $content = preg_replace_callback('/\[widget-([a-zA-Z0-9_-]+)\]/', function($matches) {
            return self::renderWidget($matches[1]);
        }, $content);

        return $content;
    }

    /**
     * رندر فرم بر اساس ID
     */
    public static function renderForm($formId)
    {
        $form = Form::with('fields')
            ->where('id', $formId)
            ->where('published', true)
            ->first();

        if (!$form) {
            return '<div class="alert alert-danger">فرم مورد نظر یافت نشد</div>';
        }

        return view('front::forms.embed', compact('form'))->render();
    }

    /**
     * رندر عمومی ویجت‌ها (بدون نیاز به کدهای تکراری)
     */
    public static function renderWidget($key)
    {
        // دریافت آرایه ویجت‌ها از کانفیگ
        $homeWidgets = config('front.home-widgets', []);
        $postsWidgets = config('front.posts-widgets', []);
        $allWidgets = array_merge($homeWidgets, $postsWidgets);

        if (!isset($allWidgets[$key])) {
            return '<div class="alert alert-warning">ویجت [' . $key . '] یافت نشد.</div>';
        }

        $widgetData = $allWidgets[$key];
        $widgetData['key'] = $key;

        if (isset($homeWidgets[$key])) {
            $widgetData['page'] = 'home';
        } elseif (isset($postsWidgets[$key])) {
            $widgetData['page'] = 'posts';
        } else {
            $widgetData['page'] = 'unknown';
        }
        $widgetData['shortcode'] = true;
        // ایجاد شیء wrapper با کلاس کامل
        $widget = new \App\Helpers\WidgetWrapper($widgetData);

        if (!function_exists('get_widget')) {
            return '<div class="alert alert-danger">تابع get_widget پیدا نشد</div>';
        }

        $variables = get_widget($widget);

        try {
            $viewPath = "front::blogs.widgets." . $key;

            if (isset($homeWidgets[$key])) {
                $viewPath = "front::widgets." . $key;
                if ($key=="products-moment-block"){
                    $viewPath = "front::widgets.products-default-block";
                }
            }

            $html = view($viewPath, compact('variables', 'widget'))->render();
            return '<div class="shortcode-widget shortcode-widget-' . $key . '">' . $html . '</div>';
        } catch (\Exception $e) {
            return '<div class="alert alert-danger">خطا در رندر ویجت ' . $key . ': فایل view پیدا نشد</div>';
        }
    }

    /**
     * گرفتن همه شورتکدهای موجود در متن
     */
    public static function extractShortcodes($content)
    {
        preg_match_all('/\[form-(\d+)\]/', $content, $formMatches);
        preg_match_all('/\[widget-([a-zA-Z0-9_-]+)\]/', $content, $widgetMatches);

        return [
            'forms'   => $formMatches[1] ?? [],
            'widgets' => $widgetMatches[1] ?? []
        ];
    }
}
