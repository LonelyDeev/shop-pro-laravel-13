<?php

namespace App\Http\Controllers\Back;

use App\Http\Controllers\Controller;
use App\Providers\AppServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Madnest\Madzipper\Madzipper;

class ThemeController extends Controller
{
    public function index()
    {
        $this->authorize('themes.index');

        $themes = Storage::disk('themes')->directories();

        foreach ($themes as $key => $theme) {
            $themes[$key] = [
                'name'   => $theme,
                'config' => customConfig(base_path() . '/themes/' . $theme . '/config/general.php')
            ];
        }

        return view('back.themes.index', compact('themes'));
    }

    public function store(Request $request)
    {
        $this->authorize('themes.create');

        $request->validate([
            'file' => 'required|file|mimes:zip'
        ]);

        $uuid = uniqid();
        $path = Storage::disk('public')->path('uploads/tmp/');

        $file = $request->file;
        $name = $uuid . '.' . $file->getClientOriginalExtension();
        $request->file->storeAs('tmp', $name);

        $zipper = new Madzipper;
        $zipper->make($path . $name)->extractTo($path . $uuid);
        $zipper->close();

        File::delete($path . $name);

        $themes = Storage::disk('themes')->directories();
        $new_theme = Storage::disk('public')->directories('uploads/tmp/' . $uuid);
        $new_theme = substr($new_theme[0], strrpos($new_theme[0], '/') + 1);

        if (in_array($new_theme, $themes)) {

            File::deleteDirectory($path . $uuid);

            return response(
                [
                    'errors' => [
                        'theme' => ["این قالب قبلا آپلود شده است."]
                    ]
                ],
                422
            );
        }

        File::moveDirectory($path . $uuid . '/' . $new_theme, Storage::disk('themes')->path($new_theme));
        File::deleteDirectory($path . $uuid);

        change_env('CURRENT_THEME', $new_theme);
        Artisan::call('dump-autoload');

        AppServiceProvider::loadTheme();
        Artisan::call('vendor:publish --tag="' . $new_theme . '" --force');
        Artisan::call('shop:link');

        return response()->json(['name' => $new_theme]);
    }

    public function create()
    {
        $this->authorize('themes.create');

        return view('back.themes.create');
    }

    public function update($theme)
    {
        $this->authorize('themes.update');

        if (!Storage::disk('themes')->exists($theme)) {
            return response(
                [
                    'errors' => [
                        'theme' => ["قالب پیدا نشد."]
                    ]
                ],
                422
            );
        }

        $targetPath = base_path("themes/{$theme}/src/resources/assets"); // مسیر اصلی فایل‌ها
        $linkPath = public_path("themes/{$theme}");       // مسیر شورت‌کات

        // اگر لینک وجود ندارد، ایجاد کن
        if (!File::exists($linkPath)) {

            // اول پوشه public/themes رو چک کن وجود داشته باشه
            if (!File::exists(public_path('themes' ))) {
                File::makeDirectory(public_path('themes'), 0755, true);
            }

            // ایجاد symlink
            File::link($targetPath, $linkPath);
        }


        change_env('CURRENT_THEME', $theme);

        return response('success');
    }

    public function destroy($theme)
    {
        $this->authorize('themes.delete');

        if (!Storage::disk('themes')->exists($theme)) {
            return response(
                [
                    'errors' => [
                        'theme' => ["قالب پیدا نشد."]
                    ]
                ],
                422
            );
        }

        $config = customConfig(base_path() . '/themes/' . $theme . '/config/general.php');
        Storage::disk('themes')->deleteDirectory($theme);
        Storage::disk('public')->deleteDirectory($config['asset_path']);

        return response('success');
    }

    public function showSettings()
    {
        $this->authorize('themes.settings');

        return view('back.themes.settings');
    }

    public function updateSettings(Request $request)
    {
        $this->authorize('themes.settings');

        $allChanges = [];
        $adminName = auth('adminPanel')->user()->full_name ?? auth('adminPanel')->user()->name ?? 'مدیر';

        // دریافت تنظیمات از کانفیگ
        $settingsFields = config('front.settings.fields', []);

        // فیلدهای فایل
        $fileFields = [];
        foreach ($settingsFields as $setting) {
            if (isset($setting['input-type']) && $setting['input-type'] === 'file') {
                $fileFields[$setting['key']] = $setting['title'] ?? $setting['key'];
            }
        }

        // ========== 1. بررسی تغییرات فایل‌ها ==========
        $fileChanges = [];
        foreach ($fileFields as $key => $title) {
            if ($request->hasFile("settings.{$key}")) {
                $file = $request->file("settings.{$key}");
                $oldFile = option($key);

                $imageName = time() . '_' . $key . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads/themes/'), $imageName);

                if ($oldFile && file_exists(public_path($oldFile))) {
                    unlink(public_path($oldFile));
                }

                $newFile = '/uploads/themes/' . $imageName;
                option_update($key, $newFile);

                $fileChanges[$key] = [
                    'old' => $oldFile ?: 'بدون فایل',
                    'new' => $newFile,
                    'title' => $title
                ];
            }
        }

        if (!empty($fileChanges)) {
            $allChanges['file_uploads'] = $fileChanges;
        }

        // ========== 2. بررسی تغییرات تنظیمات ==========
        $settings = $request->input('settings', []);
        $optionChanges = [];

        foreach ($settings as $key => $value) {
            // اگر فیلد فایل است، قبلاً بررسی شده
            if (isset($fileFields[$key])) {
                continue;
            }

            $oldValue = option($key);

            if ($oldValue != $value) {
                $optionChanges[$key] = [
                    'old' => $oldValue ?: 'خالی',
                    'new' => $value ?: 'خالی',
                    'title' => $this->getThemeFieldTitle($key, $settingsFields)
                ];
            }

            option_update($key, $value);
        }

        if (!empty($optionChanges)) {
            $allChanges['option_settings'] = $optionChanges;
        }

        // ========== 3. ثبت لاگ ==========
        $changeDetails = [];

        // تغییرات فایل‌ها
        if (isset($allChanges['file_uploads'])) {
            foreach ($allChanges['file_uploads'] as $change) {
                $changeDetails[] = "{$change['title']} آپلود شد";
            }
        }

        // تغییرات تنظیمات - همه به صورت جداگانه
        if (isset($allChanges['option_settings'])) {
            foreach ($allChanges['option_settings'] as $change) {
                $changeDetails[] = "{$change['title']} از «{$change['old']}» به «{$change['new']}»";
            }
        }

        // ثبت لاگ
        if (!empty($changeDetails)) {
            $logMessage = "مدیر {$adminName} تنظیمات قالب را به‌روزرسانی کرد: " . implode('، ', $changeDetails);

            activity()
                ->causedBy(auth('adminPanel')->user())
                ->withProperties([
                    'action' => 'تنظیمات قالب را به‌روزرسانی کرد',
                    'changes' => $allChanges,
                    'ip' => $request->ip()
                ])
                ->log($logMessage);
        }

        return response('success');
    }

    /**
     * دریافت عنوان فارسی فیلدهای تنظیمات قالب
     */
    private function getThemeFieldTitle($key, $settingsFields)
    {
        foreach ($settingsFields as $setting) {
            if (isset($setting['key']) && $setting['key'] === $key) {
                return $setting['title'] ?? $key;
            }
        }

        // ترجمه فیلدهای رایج
        $titles = [
            // تنظیمات نمایش
            'dt_show_price_change_chart' => 'نمایش نمودار تغییرات قیمت محصول',
            'show_product_share_links' => 'نمایش لینک‌های اشتراک‌گذاری محصول',
            'dt_product_reviews_description' => 'متن توضیحات بخش نظرات محصول',

            // تنظیمات پاپ آپ
            'dt_index_popup_type' => 'نوع پاپ آپ صفحه اصلی',
            'dt_index_popup_image' => 'تصویر پاپ آپ',
            'dt_index_popup_image_mobile' => 'تصویر پاپ آپ (نسخه موبایل)',
            'dt_index_popup_link' => 'لینک تصویر پاپ آپ',
            'dt_index_popup_text' => 'متن پاپ آپ',

            // تنظیمات صفحه تماس
            'dt_show_form_in_contact' => 'نمایش فرم تماس',
            'dt_show_map_in_contact' => 'نمایش نقشه در صفحه تماس',
            'dt_show_contact_top_description' => 'نمایش توضیحات بالای صفحه تماس',
            'dt_show_contact_bottom_description' => 'نمایش توضیحات پایین صفحه تماس',
            'dt_contact_bottom_description' => 'متن توضیحات پایین صفحه تماس',

            // تنظیمات هدر و فوتر
            'dt_show_top_banner' => 'نمایش بنر بالای صفحه',
            'dt_show_bottom_banner' => 'نمایش بنر پایین صفحه',
            'dt_header_style' => 'استایل هدر',
            'dt_footer_style' => 'استایل فوتر',

            // تنظیمات رنگ
            'dt_primary_color' => 'رنگ اصلی قالب',
            'dt_secondary_color' => 'رنگ ثانویه قالب',
            'dt_header_color' => 'رنگ هدر',
            'dt_footer_color' => 'رنگ فوتر',
        ];

        return $titles[$key] ?? str_replace('_', ' ', $key);
    }

    private function getRequestSettings($request)
    {
        $settings = [];

        foreach (config('front.settings.fields') as $setting) {
            switch ($setting['input-type']) {
                case 'input':
                case 'editor':
                case 'inline-editor':
                case 'select': {
                    $settings[$setting['key']] = $request->input('settings.' . $setting['key']);
                    break;
                }

                case 'file': {
                    $file = $request->file('settings.' . $setting['key']);

                    if ($file) {
                        $name = uniqid() . '_' . time() . '.' . $file->getClientOriginalExtension();
                        $path = $file->storeAs('themes', $name);
                        $settings[$setting['key']] = '/uploads/' . $path;
                    } else {
                        $settings[$setting['key']] = option($setting['key']);
                    }

                    break;
                }
            }
        }

        return $settings;
    }
}
