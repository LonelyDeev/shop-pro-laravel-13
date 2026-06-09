<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Like extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'is_guest' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ========== روابط ==========

    /**
     * رابطه polymorphic با مدل‌های مختلف (Post, Product, Story, ...)
     */
    public function likeable()
    {
        return $this->morphTo();
    }

    /**
     * رابطه با کاربر (اگر کاربر عضو باشد)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ========== اسکوپ‌ها ==========

    /**
     * اسکوپ لایک‌ها (فقط نوع لایک)
     */
    public function scopeLikes($query)
    {
        return $query->where('type', 'like');
    }

    /**
     * اسکوپ دیسلایک‌ها
     */
    public function scopeDislikes($query)
    {
        return $query->where('type', 'dislike');
    }

    /**
     * اسکوپ لایک‌های کاربران عضو
     */
    public function scopeRegistered($query)
    {
        return $query->where('is_guest', false)->whereNotNull('user_id');
    }

    /**
     * اسکوپ لایک‌های کاربران مهمان
     */
    public function scopeGuest($query)
    {
        return $query->where('is_guest', true);
    }

    /**
     * اسکوپ لایک‌های یک مدل خاص (مثلاً پست‌ها)
     */
    public function scopeForModel($query, string $modelType)
    {
        return $query->where('likeable_type', $modelType);
    }

    /**
     * اسکوپ لایک‌های یک آیتم خاص
     */
    public function scopeForItem($query, string $modelType, int $itemId)
    {
        return $query->where('likeable_type', $modelType)
            ->where('likeable_id', $itemId);
    }

    /**
     * اسکوپ لایک‌های یک کاربر خاص
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * اسکوپ لایک‌های از یک آی پی
     */
    public function scopeByIp($query, string $ip)
    {
        return $query->where('ip_address', $ip);
    }

    /**
     * اسکوپ لایک‌های یک نشست
     */
    public function scopeBySession($query, string $sessionId)
    {
        return $query->where('session_id', $sessionId);
    }

    /**
     * اسکوپ لایک‌های امروز
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    // ========== متدهای کمکی ==========

    /**
     * بررسی اینکه آیا لایک متعلق به کاربر عضو است
     */
    public function isRegistered(): bool
    {
        return !$this->is_guest && !is_null($this->user_id);
    }

    /**
     * بررسی اینکه آیا لایک متعلق به کاربر مهمان است
     */
    public function isGuest(): bool
    {
        return $this->is_guest;
    }

    /**
     * دریافت نام مدل likeable به صورت خوانا
     */
    public function getLikeableTypeNameAttribute(): string
    {
        $mapping = [
            Post::class => 'مقاله',
            Product::class => 'محصول',
            Comment::class => 'نظر',
        ];

        return $mapping[$this->likeable_type] ?? class_basename($this->likeable_type);
    }
}
