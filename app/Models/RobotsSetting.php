<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RobotsSetting extends Model
{
    protected $fillable = ['key', 'value'];
    protected $casts = [
        'value' => 'json',
    ];

    public static function getSettings()
    {
        $settings = self::all()->pluck('value', 'key')->toArray();

        return [
            'mode' => $settings['mode'] ?? 'production',
            'disallow' => $settings['disallow'] ?? [
                    '/admin/',
                    '/panel/',
                    '/login',
                    '/register',
                    '/cart',
                    '/checkout',
                    '/profile',
                    '/favorites',
                    '/orders',
                    '/wallet',
                    '/tickets',
                    '/api/',
                    '/assets/',
                    '/storage/framework/',
                    '/vendor/',
                ],
            'allow' => $settings['allow'] ?? [
                ],
            'crawl_delay' => $settings['crawl_delay'] ?? 5,
            'sitemap' => $settings['sitemap'] ?? url('/sitemap.xml'),
        ];
    }
}
