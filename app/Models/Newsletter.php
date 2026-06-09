<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Newsletter extends Model
{
    use HasFactory;

    protected $table = 'newsletters';

    protected $fillable = [
        'contact',
        'is_active',
        'ip_address',
        'user_agent',
        'device_type',
        'browser',
        'os',
        'referrer',
        'landing_page',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // تشخیص نوع contact (ایمیل یا موبایل)
    public function getContactTypeAttribute()
    {
        if (filter_var($this->contact, FILTER_VALIDATE_EMAIL)) {
            return 'email';
        }

        if (preg_match('/^09[0-9]{9}$/', $this->contact)) {
            return 'mobile';
        }

        return 'unknown';
    }

    // نمایش contact با فرمت مناسب
    public function getFormattedContactAttribute()
    {
        if ($this->contact_type == 'mobile') {
            // فرمت شماره: 0912 345 6789
            return substr($this->contact, 0, 4) . ' ' .
                substr($this->contact, 4, 3) . ' ' .
                substr($this->contact, 7, 4);
        }

        return $this->contact;
    }

    // متدهای تشخیص دستگاه (همان قبلی)
    public static function getDeviceType($userAgent)
    {
        $userAgent = strtolower($userAgent);

        if (str_contains($userAgent, 'mobile') || str_contains($userAgent, 'android') || str_contains($userAgent, 'iphone')) {
            return 'mobile';
        }

        if (str_contains($userAgent, 'tablet') || str_contains($userAgent, 'ipad')) {
            return 'tablet';
        }

        return 'desktop';
    }

    public static function getBrowser($userAgent)
    {
        $userAgent = strtolower($userAgent);

        if (str_contains($userAgent, 'chrome') && !str_contains($userAgent, 'edge')) {
            return 'Chrome';
        }
        if (str_contains($userAgent, 'firefox')) {
            return 'Firefox';
        }
        if (str_contains($userAgent, 'safari') && !str_contains($userAgent, 'chrome')) {
            return 'Safari';
        }
        if (str_contains($userAgent, 'edge')) {
            return 'Edge';
        }
        if (str_contains($userAgent, 'opera') || str_contains($userAgent, 'opr')) {
            return 'Opera';
        }

        return 'Unknown';
    }

    public static function getOS($userAgent)
    {
        $userAgent = strtolower($userAgent);

        if (str_contains($userAgent, 'windows')) {
            return 'Windows';
        }
        if (str_contains($userAgent, 'mac')) {
            return 'Mac';
        }
        if (str_contains($userAgent, 'linux')) {
            return 'Linux';
        }
        if (str_contains($userAgent, 'android')) {
            return 'Android';
        }
        if (str_contains($userAgent, 'iphone') || str_contains($userAgent, 'ipad')) {
            return 'iOS';
        }

        return 'Unknown';
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeEmailSubscribers($query)
    {
        return $query->where('contact', 'regexp', '^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$');
    }

    public function scopeMobileSubscribers($query)
    {
        return $query->where('contact', 'regexp', '^09[0-9]{9}$');
    }
}
