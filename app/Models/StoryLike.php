<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoryLike extends Model
{
    protected $fillable = [
        'story_id', 'user_id', 'ip_address', 'session_id',
        'user_agent', 'device_type', 'is_guest'
    ];

    protected $casts = [
        'is_guest' => 'boolean'
    ];

    // رابطه با استوری
    public function story()
    {
        return $this->belongsTo(Story::class);
    }

    // رابطه با کاربر
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Event برای آپدیت خودکار likes_count
    protected static function booted()
    {
        static::created(function ($storyLike) {
            $storyLike->story->updateLikesCount();
        });

        static::deleted(function ($storyLike) {
            $storyLike->story->updateLikesCount();
        });
    }
}
