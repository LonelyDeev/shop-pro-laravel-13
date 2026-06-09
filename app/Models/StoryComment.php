<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoryComment extends Model
{

    protected $fillable = [
        'story_id',
        'user_id',
        'guest_name',
        'comment',
        'ip_address',
        'status',
        'is_approved'
    ];

    protected $casts = [
        'is_approved' => 'boolean'
    ];

    public function story()
    {
        return $this->belongsTo(Story::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getAvatarAttribute()
    {
        if ($this->user) {
            return $this->user->avatar ?? 'default-avatar.jpg';
        }
        return 'default-avatar.jpg';
    }

    public function getNameAttribute()
    {
        if ($this->user) {
            return $this->user->full_name ?? $this->user->name ?? 'کاربر';
        }
        return $this->guest_name ?? 'کاربر مهمان';
    }
}
