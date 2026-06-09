<?php

namespace App\Traits;

use App\Models\Like;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasLikes
{
    /**
     * دریافت همه لایک‌ها و دیسلایک‌ها
     */
    public function allLikes(): MorphMany
    {
        return $this->morphMany(Like::class, 'likeable');
    }

    /**
     * دریافت فقط لایک‌ها
     */
    public function likes(): MorphMany
    {
        return $this->morphMany(Like::class, 'likeable')->where('type', 'like');
    }

    /**
     * دریافت فقط دیسلایک‌ها
     */
    public function dislikes(): MorphMany
    {
        return $this->morphMany(Like::class, 'likeable')->where('type', 'dislike');
    }

    /**
     * بررسی اینکه آیا کاربر فعلی لایک کرده است
     */
    public function isLikedByUser($userId = null): bool
    {
        $userId = $userId ?? auth()->id();

        if (!$userId) {
            return false;
        }

        return $this->likes()->where('user_id', $userId)->exists();
    }

    /**
     * بررسی اینکه آیا کاربر فعلی دیسلایک کرده است
     */
    public function isDislikedByUser($userId = null): bool
    {
        $userId = $userId ?? auth()->id();

        if (!$userId) {
            return false;
        }

        return $this->dislikes()->where('user_id', $userId)->exists();
    }

    /**
     * دریافت تعداد لایک‌ها (cached)
     */
    public function getLikesCountAttribute(): int
    {
        return $this->likes()->count();
    }

    /**
     * دریافت تعداد دیسلایک‌ها (cached)
     */
    public function getDislikesCountAttribute(): int
    {
        return $this->dislikes()->count();
    }

    /**
     * لایک کردن توسط کاربر فعلی
     */
    public function like(?int $userId = null): Like
    {
        $userId = $userId ?? auth()->id();

        // حذف دیسلایک قبلی اگر وجود داشته باشد
        $this->dislikes()->where('user_id', $userId)->delete();

        // بررسی لایک قبلی
        $existingLike = $this->likes()->where('user_id', $userId)->first();

        if ($existingLike) {
            $existingLike->delete();
            return $existingLike;
        }

        return $this->likes()->create([
            'user_id' => $userId,
            'type' => 'like',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'session_id' => session()->getId(),
            'device_type' => $this->getDeviceType(),
            'is_guest' => false
        ]);
    }

    /**
     * دیسلایک کردن توسط کاربر فعلی
     */
    public function dislike(?int $userId = null): Like
    {
        $userId = $userId ?? auth()->id();

        // حذف لایک قبلی اگر وجود داشته باشد
        $this->likes()->where('user_id', $userId)->delete();

        // بررسی دیسلایک قبلی
        $existingDislike = $this->dislikes()->where('user_id', $userId)->first();

        if ($existingDislike) {
            $existingDislike->delete();
            return $existingDislike;
        }

        return $this->dislikes()->create([
            'user_id' => $userId,
            'type' => 'dislike',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'session_id' => session()->getId(),
            'device_type' => $this->getDeviceType(),
            'is_guest' => false
        ]);
    }

    /**
     * تشخیص نوع دستگاه
     */
    private function getDeviceType(): string
    {
        $agent = request()->userAgent();
        if (str_contains($agent, 'Mobile')) return 'mobile';
        if (str_contains($agent, 'Tablet')) return 'tablet';
        return 'desktop';
    }
}
