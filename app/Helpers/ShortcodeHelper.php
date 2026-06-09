<?php

namespace App\Helpers;

use App\Models\Form;

class ShortcodeHelper
{
    /**
     * تبدیل متن حاوی شورتکد به HTML
     */
    public static function parse($content)
    {
        // الگوی شورتکد: [form-123]
        $pattern = '/\[form-(\d+)\]/';

        $content = preg_replace_callback($pattern, function($matches) {
            $formId = $matches[1];
            return self::renderForm($formId);
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
     * گرفتن همه شورتکدهای موجود در متن
     */
    public static function extractShortcodes($content)
    {
        $pattern = '/\[form-(\d+)\]/';
        preg_match_all($pattern, $content, $matches);
        return $matches[1] ?? [];
    }
}
