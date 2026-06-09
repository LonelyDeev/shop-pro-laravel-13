<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoryInteraction extends Model
{
    use HasFactory;

    protected $table = 'story_interactions';

    protected $fillable = [
        'story_id', 'type', 'element_id', 'element_text', 'target_url',
        'additional_data', 'ip_address', 'user_agent', 'session_id',
        'user_id', 'device_type', 'interacted_at','count','last_interacted_at'
    ];

    protected $casts = [
        'additional_data' => 'array',
        'interacted_at' => 'datetime',
    ];

    public function story()
    {
        return $this->belongsTo(Story::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // اسکوپ‌ها
    public function scopeWidgetClicks($query)
    {
        return $query->where('type', 'widget_click');
    }

    public function scopeProductClicks($query)
    {
        return $query->where('type', 'product_click');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('interacted_at', today());
    }

    public function scopeLastDays($query, int $days)
    {
        return $query->where('interacted_at', '>=', now()->subDays($days));
    }
}
