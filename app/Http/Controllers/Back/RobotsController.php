<?php

namespace App\Http\Controllers\Back;

use App\Http\Controllers\Controller;
use App\Models\RobotsSetting;
use Illuminate\Http\Request;

class RobotsController extends Controller
{
    public function index()
    {
        if (!auth('adminPanel')->user()->isCreator()) {
            abort(403, 'شما دسترسی به این بخش را ندارید.');
        }

        $settings = RobotsSetting::getSettings();
        $preview = $this->generateRobotsTxt($settings);

        return view('back.settings.robots', compact('settings', 'preview'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'mode' => 'required|in:production,development,disabled',
            'disallow' => 'required|array',
            'disallow.*' => 'string',
            'allow' => 'nullable|array',
            'allow.*' => 'string',
            'crawl_delay' => 'required|integer|min:0|max:60',
            'sitemap' => 'nullable|url',
        ]);

        // ذخیره تنظیمات
        $settings = [
            'mode' => $request->mode,
            'disallow' => $request->disallow,
            'allow' => $request->allow,
            'crawl_delay' => $request->crawl_delay,
            'sitemap' => $request->sitemap ?? url('/sitemap.xml'),
        ];

        foreach ($settings as $key => $value) {
            RobotsSetting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        // تولید robots.txt
        $this->generateRobotsFile($settings);

        // ثبت لاگ
        $adminName = auth('adminPanel')->user()->full_name ?? auth('adminPanel')->user()->name ?? 'مدیر';
        activity()
            ->causedBy(auth('adminPanel')->user())
            ->withProperties([
                'action' => 'update_robots',
                'settings' => $settings,
                'ip' => request()->ip()
            ])
            ->log("مدیر {$adminName} تنظیمات robots.txt را به‌روزرسانی کرد");

        return response()->json([
            'success' => true,
            'message' => 'تنظیمات robots.txt با موفقیت ذخیره شد'
        ]);
    }

    public function preview(Request $request)
    {
        $settings = $request->all();
        $content = $this->generateRobotsTxt($settings);
        return response()->json(['content' => $content]);
    }

    private function generateRobotsTxt($settings)
    {
        $content = "# robots.txt\n";
        $content .= "# Generated: " . now() . "\n";
        $content .= "# Mode: " . $settings['mode'] . "\n\n";

        if ($settings['mode'] === 'disabled') {
            $content .= "User-agent: *\n";
            $content .= "Disallow: /\n";
            $content .= "Sitemap: " . ($settings['sitemap'] ?? url('/sitemap.xml')) . "\n";
            return $content;
        }

        if ($settings['mode'] === 'development') {
            $content .= "User-agent: *\n";
            $content .= "Disallow: /\n";
            $content .= "Sitemap: " . ($settings['sitemap'] ?? url('/sitemap.xml')) . "\n";
            return $content;
        }

        // حالت production
        $content .= "# All robots\n";
        $content .= "User-agent: *\n";
        $content .= "Crawl-delay: " . ($settings['crawl_delay'] ?? 5) . "\n";

        // Disallow
        foreach ($settings['disallow'] ?? [] as $path) {
            if (!empty($path)) {
                $content .= "Disallow: " . $path . "\n";
            }
        }

        // Allow
        foreach ($settings['allow'] ?? [] as $path) {
            if (!empty($path)) {
                $content .= "Allow: " . $path . "\n";
            }
        }

        // Sitemap
        $content .= "\n";
        $content .= "# Sitemap\n";
        $content .= "Sitemap: " . ($settings['sitemap'] ?? url('/sitemap.xml')) . "\n";

        // ربات‌های خاص
        $content .= "\n";
        $content .= "# Googlebot\n";
        $content .= "User-agent: Googlebot\n";
        $content .= "Crawl-delay: 1\n";

        $content .= "\n";
        $content .= "# Bingbot\n";
        $content .= "User-agent: Bingbot\n";
        $content .= "Crawl-delay: 2\n";

        return $content;
    }

    private function generateRobotsFile($settings)
    {
        $content = $this->generateRobotsTxt($settings);
        file_put_contents(public_path('robots.txt'), $content);
        return $content;
    }
}
