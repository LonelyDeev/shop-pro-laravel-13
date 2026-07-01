<?php

namespace App\Http\Controllers\Back;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class Floatingwidgetcontroller extends Controller
{
    /**
     * نمایش صفحه مدیریت ویجت شناور
     */
    public function index()
    {
        return view('back.settings.floating-widget');
    }

    /**
     * ذخیره تنظیمات ویجت شناور
     */
    public function update(Request $request)
    {
        $request->validate([
            'fw_main_color'          => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            'fw_button_label'        => 'nullable|string|max:60',
            'fw_greeting'            => 'nullable|string|max:120',
            'fw_sub_greeting'        => 'nullable|string|max:180',
            'fw_working_hours'       => 'nullable|string|max:100',
            'fw_phone'               => 'nullable|string|max:30',
            'fw_email'               => 'nullable|email|max:120',
            'fw_address'             => 'nullable|string|max:300',

            'fw_whatsapp_label'      => 'nullable|string|max:60',
            'fw_whatsapp_url'        => 'nullable|url|max:300',
            'fw_telegram_label'      => 'nullable|string|max:60',
            'fw_telegram_url'        => 'nullable|url|max:300',
            'fw_instagram_label'     => 'nullable|string|max:60',
            'fw_instagram_url'       => 'nullable|url|max:300',
            'fw_twitter_label'       => 'nullable|string|max:60',
            'fw_twitter_url'         => 'nullable|url|max:300',
            'fw_youtube_label'       => 'nullable|string|max:60',
            'fw_youtube_url'         => 'nullable|url|max:300',
            'fw_linkedin_label'      => 'nullable|string|max:60',
            'fw_linkedin_url'        => 'nullable|url|max:300',
        ], [
            'fw_main_color.regex'    => 'رنگ باید در فرمت هگز مانند #5b6af7 باشد.',
            'fw_email.email'         => 'آدرس ایمیل معتبر نیست.',
            'fw_whatsapp_url.url'    => 'لینک واتساپ معتبر نیست.',
            'fw_telegram_url.url'    => 'لینک تلگرام معتبر نیست.',
            'fw_instagram_url.url'   => 'لینک اینستاگرام معتبر نیست.',
            'fw_twitter_url.url'     => 'لینک توییتر معتبر نیست.',
            'fw_youtube_url.url'     => 'لینک یوتیوب معتبر نیست.',
            'fw_linkedin_url.url'    => 'لینک لینکدین معتبر نیست.',
        ]);

        $keys = [
            'fw_enabled', 'fw_main_color', 'fw_button_label',
            'fw_greeting', 'fw_sub_greeting', 'fw_working_hours',
            'fw_phone', 'fw_email', 'fw_address',
            'fw_whatsapp_label', 'fw_whatsapp_url',
            'fw_telegram_label', 'fw_telegram_url',
            'fw_instagram_label', 'fw_instagram_url',
            'fw_twitter_label', 'fw_twitter_url',
            'fw_youtube_label', 'fw_youtube_url',
            'fw_linkedin_label', 'fw_linkedin_url',
        ];

        foreach ($keys as $key) {
            // fw_enabled: اگر چک‌باکس نبود مقدار صفر ذخیره شود
            if ($key === 'fw_enabled') {
                option_update($key, $request->has('fw_enabled') ? '1' : '0');
            } else {
                option_update($key, $request->input($key, ''));
            }
        }

        return redirect()->route('admin.settings.floating-widget.index')
            ->with('toast-success', 'تنظیمات ویجت شناور با موفقیت ذخیره شد.');
    }
}
