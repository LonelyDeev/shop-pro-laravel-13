<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class Story extends Model
{
    use HasFactory;


    protected $fillable = [
        'title', 'type', 'cover_image', 'video', 'image',
        'views_count','real_views_count', 'likes_count', 'expiry_date', 'expiry_date_persian','duration',
        'widget_title', 'widget_link', 'product_id', 'ordering', 'description', 'meta_data',
        'active_likes', 'active_comments', 'admin_id', 'user_id', 'seller_id',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'views_count' => 'integer',
        'likes_count' => 'integer',
        'sort_order' => 'integer',
        'meta_data' => 'array',
        'published_at' => 'datetime',
    ];

    // رابطه‌ها
    public function interactions()
    {
        return $this->hasMany(StoryInteraction::class);
    }

    public function comments()
    {
        return $this->hasMany(StoryComment::class);
    }

    public function likes()
    {
        return $this->hasMany(StoryLike::class);
    }
    // اکسسورها
    public function getIsExpiredAttribute(): bool
    {
        if (!$this->expiry_date) {
            return false;
        }
        return Carbon::now()->startOfDay()->gt($this->expiry_date);
    }

    public function getEngagementRateAttribute(): float
    {
        if ($this->views_count == 0) {
            return 0;
        }
        return round(($this->likes_count / $this->views_count) * 100, 2);
    }

    // متدهای آمار (با Cache)
    public function incrementViews(string $sessionKey = 'story_viewed_'): void
    {
        $cacheKey = $sessionKey . $this->id;

        if (!Cache::has($cacheKey)) {
            $this->increment('views_count');
            Cache::put($cacheKey, true, now()->addHours(24));
        }
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function toggleLike(string $sessionKey = 'story_liked_'): array
    {
        $cacheKey = $sessionKey . $this->id;

        if (Cache::has($cacheKey)) {
            Cache::forget($cacheKey);
            $this->decrement('likes_count');
            return ['action' => 'unliked'];
        } else {
            Cache::put($cacheKey, true, now()->addDays(30));
            $this->increment('likes_count');
            return ['action' => 'liked'];
        }
    }

    // ثبت تعامل (مهم و مفید)
    public function recordInteraction(array $data): StoryInteraction
    {
        $interaction = $this->interactions()->create([
            'type' => $data['type'],
            'element_id' => $data['element_id'] ?? null,
            'element_text' => $data['element_text'] ?? null,
            'target_url' => $data['target_url'] ?? null,
            'additional_data' => $data['additional_data'] ?? null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'session_id' => session()->getId(),
            'user_id' => auth()->id(),
            'device_type' => $this->getDeviceType(),
            'interacted_at' => now(),
        ]);

        return $interaction;
    }

    // ثبت کلیک روی ویجت
    public function recordWidgetClick(?string $elementText = null): void
    {
        $this->recordInteraction([
            'type' => 'widget_click',
            'element_id' => 'widget_button',
            'element_text' => $elementText ?? $this->widget_title,
            'target_url' => $this->widget_link,
            'additional_data' => ['widget_title' => $this->widget_title]
        ]);
    }

    // ثبت کلیک روی محصول
    public function recordProductClick(): void
    {
        $this->recordInteraction([
            'type' => 'product_click',
            'element_id' => 'product_link',
            'element_text' => $this->product_code,
            'target_url' => $this->product_link,
            'additional_data' => ['product_code' => $this->product_code]
        ]);
    }

    // ثبت اشتراک‌گذاری
    public function recordShare(string $platform = 'unknown'): void
    {
        $this->recordInteraction([
            'type' => 'share',
            'element_id' => 'share_button',
            'additional_data' => ['platform' => $platform]
        ]);
    }

    // آمار تعاملات
    public function getInteractionStats(): array
    {
        return [
            'total_interactions' => $this->interactions()->count(),
            'widget_clicks' => $this->interactions()->where('type', 'widget_click')->count(),
            'product_clicks' => $this->interactions()->where('type', 'product_click')->count(),
            'shares' => $this->interactions()->where('type', 'share')->count(),
            'last_24h' => $this->interactions()
                ->where('interacted_at', '>=', now()->subDay())
                ->count(),
            'by_device' => $this->interactions()
                ->selectRaw('device_type, count(*) as count')
                ->groupBy('device_type')
                ->pluck('count', 'device_type')
                ->toArray(),
        ];
    }

    // نرخ تبدیل (CTR)
    public function getConversionRateAttribute(): float
    {
        $totalClicks = $this->interactions()
            ->whereIn('type', ['widget_click', 'product_click'])
            ->count();

        if ($this->views_count == 0) return 0;
        return round(($totalClicks / $this->views_count) * 100, 2);
    }

    private function getDeviceType(): string
    {
        $agent = request()->userAgent();

        if (str_contains($agent, 'Mobile')) return 'mobile';
        if (str_contains($agent, 'Tablet')) return 'tablet';
        return 'desktop';
    }

    // اسکوپ‌ها
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expiry_date')
                    ->orWhere('expiry_date', '>=', Carbon::now());
            });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    public function seller()
    {
        return $this->belongsTo(Seller::class);
    }

    public function updateLikesCount()
    {
        $count = $this->likes()->count();
        $this->update(['likes_count' => $count]);

        // پاک کردن کش
        Cache::forget("story_likes_count_{$this->id}");

        return $count;
    }

    // گرفتن تعداد لایک با کش
    public function getLikesCountAttribute($value)
    {
        // استفاده از کش برای 5 دقیقه
        return Cache::remember("story_likes_count_{$this->id}", 300, function () use ($value) {
            return $value;
        });
    }

    // بررسی اینکه آیا کاربر لایک کرده
    public function isLikedByUser($userId = null)
    {
        if ($userId) {
            return $this->likes()->where('user_id', $userId)->exists();
        }

        // برای کاربر مهمان (با session)
        $sessionId = session()->getId();
        return $this->likes()->where('session_id', $sessionId)->exists();
    }



}
