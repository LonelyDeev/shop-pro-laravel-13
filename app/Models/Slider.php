<?php

namespace App\Models;

use App\Traits\Languageable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Slider extends Model
{
    use Languageable;

    protected $guarded = ['id'];

    protected $casts = [
        'pages'     => 'array',
        'groups'    => 'array',
        'published' => 'boolean',
    ];

    /* -----------------------------------------------------------------
     |  کاتالوگ صفحات و گروه‌ها                                      |
     ----------------------------------------------------------------- */

    /**
     * صفحاتی که اسلایدر می‌تواند در آن‌ها نمایش داده شود.
     */
    public static function availablePages(): array
    {
        return [
            'home'    => 'صفحه اصلی',
            'posts'   => 'صفحه اصلی مقالات',
            'sellers' => 'صفحه اصلی فروشندگان',
        ];
    }

    /**
     * گروه‌های اسلایدر با ابعاد مورد نیاز تصویر.
     * کلید = مقدار ذخیره‌شده در DB
     * مقدار = ['label' => نام فارسی, 'size' => ابعاد تصویر, 'icon' => آیکون]
     */
    public static function availableGroups(): array
    {
        return [
            'main_sliders'      => [
                'label' => 'اسلایدر اصلی',
                'size'  => '890 × 1780',
                'icon'  => 'fa-image',
            ],
            'fullscreen_slider' => [
                'label' => 'اسلایدر تمام صفحه',
                'size'  => '600 × 1800',
                'icon'  => 'fa-expand',
            ],
            'search_sliders'    => [
                'label' => 'اسلایدر جستجو',
                'size'  => '518 × 189',
                'icon'  => 'fa-magnifying-glass',
            ],
            'coworker_sliders'  => [
                'label' => 'اسلایدر لوگو همکاران',
                'size'  => '100 × 100',
                'icon'  => 'fa-handshake',
            ],
            'sevices_sliders'   => [
                'label' => 'اسلایدر خدمات',
                'size'  => '60 × 60',
                'icon'  => 'fa-screwdriver-wrench',
            ],
        ];
    }


    /* -----------------------------------------------------------------
     |  اسکوپها و متدهای جستجو                                       |
     ----------------------------------------------------------------- */

    public function scopeOnPage($query, string $page)
    {
        return $query->whereJsonContains('pages', $page);
    }

    public function scopeInGroup($query, string $group)
    {
        return $query->whereJsonContains('groups', $group);
    }

    public function scopeDisplayedIn($query, string $page, string $group)
    {
        return $query->onPage($page)->inGroup($group);
    }

    public static function forPageAndGroup(string $page, string $group): Collection
    {
        return static::where('published', true)
            ->whereJsonContains('pages', $page)
            ->whereJsonContains('groups', $group)
            ->orderBy('ordering')
            ->get();
    }

    public static function forPage(string $page): Collection
    {
        return static::where('published', true)
            ->whereJsonContains('pages', $page)
            ->orderBy('ordering')
            ->get();
    }

    public static function forGroup(string $group): Collection
    {
        return static::where('published', true)
            ->whereJsonContains('groups', $group)
            ->orderBy('ordering')
            ->get();
    }

    /* -----------------------------------------------------------------
     |  متدهای کمکی نمایش                                            |
     ----------------------------------------------------------------- */

    public function pageLabels(): array
    {
        $all = static::availablePages();
        return array_map(fn ($key) => $all[$key] ?? $key, $this->pages ?: []);
    }

    public function groupLabels(): array
    {
        $all = static::availableGroups();
        return array_map(
            fn ($key) => ($all[$key]['label'] ?? $key) . ' (' . ($all[$key]['size'] ?? '') . ')',
            $this->groups ?: []
        );
    }
}
