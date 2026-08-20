<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Banner extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'pages'     => 'array',
        'groups'    => 'array',
        'places'    => 'array',
        'published' => 'boolean',
    ];

    /* -----------------------------------------------------------------
     |  کاتالوگ صفحات، گروه‌ها و موقعیت‌ها                           |
     ----------------------------------------------------------------- */

    /**
     * صفحاتی که بنر می‌تواند در آن‌ها نمایش داده شود.
     */
    public static function availablePages(): array
    {
        return [
            'home'  => 'صفحه اصلی',
            'posts' => 'صفحه اصلی مقالات',
        ];
    }

    /**
     * گروه‌های بنر با ابعاد تصویر.
     */
    public static function availableGroups(): array
    {
        return [
            'index_middle_banners'   => [
                'label' => 'بنر تکی',
                'size'  => '300 × 820',
                'icon'  => 'fa-square',
            ],
            'index_middle_2_banners' => [
                'label' => 'بنر دوتایی',
                'size'  => '300 × 820',
                'icon'  => 'fa-grip-vertical',
            ],
            'index_middle_4_banners' => [
                'label' => 'بنر چهارتایی',
                'size'  => '300 × 820',
                'icon'  => 'fa-grip',
            ],
            'index_slider_banners'   => [
                'label' => 'بنر کنار اسلایدر اصلی',
                'size'  => '428 × 856',
                'icon'  => 'fa-images',
            ],
            'index_highest_banner'   => [
                'label' => 'بنر تکی بالای هدر',
                'size'  => '1352 × 60',
                'icon'  => 'fa-window-maximize',
            ],
        ];
    }

    /**
     * موقعیت‌های نمایش بنر.
     */
    public static function availablePlaces(): array
    {
        return [
            'index_banners_place1' => 'گروه اول',
            'index_banners_place2' => 'گروه دوم',
            'index_banners_place3' => 'گروه سوم',
            'index_banners_place4' => 'گروه چهارم',
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

    public function scopeInPlace($query, string $place)
    {
        return $query->whereJsonContains('places', $place);
    }

    public static function forPage(string $page): Collection
    {
        return static::where('published', true)
            ->whereJsonContains('pages', $page)
            ->orderBy('ordering')
            ->get();
    }

    public static function forPageAndGroup(string $page, string $group): Collection
    {
        return static::where('published', true)
            ->whereJsonContains('pages', $page)
            ->whereJsonContains('groups', $group)
            ->orderBy('ordering')
            ->get();
    }

    public static function forPageAndPlace(string $page, string $place): Collection
    {
        return static::where('published', true)
            ->whereJsonContains('pages', $page)
            ->whereJsonContains('places', $place)
            ->orderBy('ordering')
            ->get();
    }

    public static function forPageGroupPlace(string $page, string $group, string $place): Collection
    {
        return static::where('published', true)
            ->whereJsonContains('pages', $page)
            ->whereJsonContains('groups', $group)
            ->whereJsonContains('places', $place)
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

    public function placeLabels(): array
    {
        $all = static::availablePlaces();
        return array_map(fn ($key) => $all[$key] ?? $key, $this->places ?: []);
    }
}
